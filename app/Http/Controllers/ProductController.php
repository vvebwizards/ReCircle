<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\SimpleEtsyPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    private $pricingService;

    public function __construct()
    {
        $this->pricingService = new SimpleEtsyPricingService;
    }

    public function getPricingSuggestions(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|min:2',
            'category' => 'required|string',
            'cost_price' => 'nullable|numeric|min:0',
        ]);

        try {
            $suggestions = $this->pricingService->getPricingSuggestions(
                $request->product_name,
                $request->category,
                $request->cost_price
            );

            return response()->json($suggestions);

        } catch (\Exception $e) {
            \Log::error('Pricing suggestions error: '.$e->getMessage());

            return response()->json([
                'error' => 'Unable to generate pricing suggestions',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request): View
    {
        $query = Product::with(['materials', 'images'])
            ->where('maker_id', Auth::id())
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->whereHas('materials', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        $products = $query->paginate(12);

        $stats = [
            'total' => Product::where('maker_id', Auth::id())->count(),
            'published' => Product::where('maker_id', Auth::id())->where('status', ProductStatus::PUBLISHED)->count(),
            'draft' => Product::where('maker_id', Auth::id())->where('status', ProductStatus::DRAFT)->count(),
            'sold_out' => Product::where('maker_id', Auth::id())->where('status', ProductStatus::SOLD_OUT)->count(),
        ];

        return view('maker.products', compact('products', 'stats'));
    }

    public function create(): View
    {
        $materials = Material::with(['images', 'wasteItem'])
            ->where('maker_id', Auth::id())
            ->where('quantity', '>', 0)
            ->latest()
            ->get();

        return view('maker.create_product', compact('materials'));
    }

    public function store(Request $request): RedirectResponse
    {
        $messages = [
            'name.required' => 'The product name is required.',
            'materials.required' => 'Please select at least one material.',
            'materials.*.id.exists' => 'Selected material is invalid.',
            'materials.*.quantity_used.required' => 'Quantity used is required.',
            'description.required' => 'The description is required.',
            'price.required' => 'The price is required.',
            'stock.required' => 'The stock quantity is required.',
            'images.required' => 'At least one image is required.',
            'images.*.image' => 'Each file must be an image (jpeg, png, jpg, gif).',
            'images.*.max' => 'Each image may not be greater than 5MB.',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'materials' => 'required|array|min:1',
            'materials.*.id' => 'required|exists:materials,id',
            'materials.*.quantity_used' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], $messages);

        try {
            DB::transaction(function () use ($validated, &$product, $request) {

                $sku = 'PROD-'.strtoupper(uniqid());

                $product = Product::create([
                    'maker_id' => Auth::id(),
                    'sku' => $sku,
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'price' => $validated['price'],
                    'stock' => $validated['stock'],
                    'status' => ProductStatus::DRAFT,
                ]);

                foreach ($validated['materials'] as $mat) {
                    $material = Material::findOrFail($mat['id']);

                    if ($material->quantity < $mat['quantity_used']) {
                        throw new \Exception("Insufficient material: {$material->name}");
                    }

                    $material->quantity -= $mat['quantity_used'];
                    $material->save();

                    $product->materials()->attach($material->id, [
                        'quantity_used' => $mat['quantity_used'],
                        'unit' => $material->unit,
                    ]);
                }

                $order = 0;
                foreach ($request->file('images') as $image) {
                    $imageName = time().'_'.uniqid().'_'.$order.'.'.$image->getClientOriginalExtension();
                    $image->move(public_path('images/products'), $imageName);
                    $imagePath = 'images/products/'.$imageName;

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                        'order' => $order,
                    ]);
                    $order++;
                }

                Material::recalculateImpactsForProduct($product);
                $product->generateMaterialPassport();
            });

            return redirect()->route('maker.products')
                ->with('success', 'Product created successfully!');

        } catch (\Exception $e) {
            \Log::error('Product creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(int $id): View
    {
        $product = Product::with(['materials', 'images' => fn ($q) => $q->orderBy('order')])
            ->where('maker_id', Auth::id())
            ->findOrFail($id);

        $materials = Material::where('maker_id', Auth::id())
            ->where('quantity', '>', 0)
            ->latest()
            ->get();

        return view('maker.edit_product', compact('product', 'materials'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $product = Product::with('materials')->where('maker_id', Auth::id())->findOrFail($id);

        $messages = [
            'name.required' => 'The product name is required.',
            'materials.required' => 'Please select at least one material.',
            'materials.*.id.exists' => 'Selected material is invalid.',
            'materials.*.quantity_used.required' => 'Quantity used is required.',
            'description.required' => 'The description is required.',
            'price.required' => 'The price is required.',
            'stock.required' => 'The stock quantity is required.',
            'status.required' => 'The status is required.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.max' => 'Each image may not be greater than 5MB.',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'materials' => 'required|array|min:1',
            'materials.*.id' => 'required|exists:materials,id',
            'materials.*.quantity_used' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:'.implode(',', ProductStatus::getValues()),
            'warranty_months' => 'nullable|integer|min:0',
            'care_instructions' => 'nullable|string|max:1000',
            'images' => 'sometimes|array',
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
            'remove_images' => 'sometimes|array',
            'remove_images.*' => 'sometimes|numeric',
        ], $messages);

        try {
            DB::transaction(function () use ($validated, $product, $request) {
                $product->update([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'price' => $validated['price'],
                    'stock' => $validated['stock'],
                    'status' => $validated['status'],
                    'warranty_months' => $validated['warranty_months'] ?? $product->warranty_months,
                    'care_instructions' => $validated['care_instructions'] ?? $product->care_instructions,
                ]);

                $currentMaterials = $product->materials->keyBy('id');
                $newMaterialIds = collect($validated['materials'])->pluck('id')->toArray();
                
                foreach ($currentMaterials as $materialId => $material) {
                    if (!in_array($materialId, $newMaterialIds)) {
                        $material->quantity += $material->pivot->quantity_used;
                        $material->save();
                    }
                }

                $syncData = [];
                foreach ($validated['materials'] as $mat) {
                    $material = Material::findOrFail($mat['id']);
                    $currentQuantityUsed = $currentMaterials[$mat['id']]->pivot->quantity_used ?? 0;
                    $quantityDifference = $mat['quantity_used'] - $currentQuantityUsed;

                    if ($quantityDifference > 0 && $quantityDifference > $material->quantity) {
                        throw new \Exception("Insufficient material: {$material->name}. Available: {$material->quantity}, Needed: {$quantityDifference}");
                    }

                    $material->quantity -= $quantityDifference;
                    $material->save();

                    $syncData[$material->id] = [
                        'quantity_used' => $mat['quantity_used'],
                        'unit' => $material->unit,
                    ];
                }

                $product->materials()->sync($syncData);

                if ($request->has('remove_images')) {
                    foreach ($request->remove_images as $imageId) {
                        $image = ProductImage::where('product_id', $product->id)
                            ->where('id', $imageId)
                            ->first();
                        if ($image) {
                            if (file_exists(public_path($image->image_path))) {
                                unlink(public_path($image->image_path));
                            }
                            $image->delete();
                        }
                    }
                }

                if ($request->hasFile('images')) {
                    $existingCount = $product->images()->count();
                    $order = $existingCount;

                    foreach ($request->file('images') as $image) {
                        if ($image->isValid()) {
                            $imageName = time().'_'.uniqid().'_'.$order.'.'.$image->getClientOriginalExtension();
                            $imagePath = 'images/products/'.$imageName;
                            
                            if (!file_exists(public_path('images/products'))) {
                                mkdir(public_path('images/products'), 0755, true);
                            }
                            
                            $image->move(public_path('images/products'), $imageName);

                            ProductImage::create([
                                'product_id' => $product->id,
                                'image_path' => $imagePath,
                                'order' => $order,
                            ]);
                            $order++;
                        }
                    }
                }

                $this->reorderImages($product->id);
                
                if (method_exists(Material::class, 'recalculateImpactsForProduct')) {
                    Material::recalculateImpactsForProduct($product);
                }
                
                $product->generateMaterialPassport();
            });

            return redirect()->route('maker.products.show', $product->id)
                ->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Product update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = Product::where('maker_id', Auth::id())->findOrFail($id);

        try {
            DB::transaction(function () use ($product) {
                foreach ($product->images as $image) {
                    if (file_exists(public_path($image->image_path))) {
                        unlink(public_path($image->image_path));
                    }
                    $image->delete();
                }

                foreach ($product->materials as $material) {
                    $material->quantity += $material->pivot->quantity_used;
                    $material->save();
                }

                $product->delete();
            });

            return redirect()->route('maker.products')
                ->with('success', "Product '{$product->name}' deleted successfully!");

        } catch (\Exception $e) {
            \Log::error('Product deletion failed: ' . $e->getMessage());
            return redirect()->route('maker.products')
                ->with('error', 'Failed to delete product. Please try again.');
        }
    }

    public function publish(int $id): RedirectResponse
    {
        $product = Product::where('maker_id', Auth::id())->findOrFail($id);

        if ($product->stock <= 0) {
            return redirect()->back()
                ->with('error', 'Cannot publish product with zero stock. Please update stock first.');
        }

        $product->update(['status' => ProductStatus::PUBLISHED]);

        return redirect()->back()
            ->with('success', 'Product published successfully!');
    }

    public function updateStock(Request $request, int $id): RedirectResponse
    {
        $product = Product::where('maker_id', Auth::id())->findOrFail($id);

        $validated = $request->validate(['stock' => 'required|integer|min:0']);
        $newStock = $validated['stock'];
        $newStatus = $product->status;

        if ($newStock > 0 && $product->status === ProductStatus::SOLD_OUT) {
            $newStatus = ProductStatus::PUBLISHED;
        } elseif ($newStock == 0 && $product->status === ProductStatus::PUBLISHED) {
            $newStatus = ProductStatus::SOLD_OUT;
        }

        $product->update(['stock' => $newStock, 'status' => $newStatus]);

        return redirect()->back()
            ->with('success', 'Stock updated successfully!');
    }

    private function reorderImages(int $productId): void
    {
        $images = ProductImage::where('product_id', $productId)->orderBy('order')->get();
        foreach ($images as $index => $image) {
            $image->update(['order' => $index]);
        }
    }

    public function show(int $id): View
    {
        $product = Product::with(['materials.images', 'images' => fn ($q) => $q->orderBy('order')])
            ->where('maker_id', Auth::id())
            ->findOrFail($id);

        return view('maker.product_details', compact('product'));
    }
}