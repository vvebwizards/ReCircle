<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['material', 'workOrder', 'images'])
            ->where('maker_id', Auth::id())
            ->latest();

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('status') && ! empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('category') && ! empty($request->category)) {
            $query->whereHas('material', function ($q) use ($request) {
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

    public function create(Request $request): View
    {
        $materials = Material::with(['images', 'wasteItem'])
            ->where('maker_id', Auth::id())
            ->where('quantity', '>', 0)
            ->latest()
            ->get();

        $workOrders = WorkOrder::with(['match.listing.wasteItem'])
            ->whereHas('match.maker', function ($query) {
                $query->where('id', Auth::id());
            })
            ->where('status', 'completed')
            ->latest()
            ->get();

        $selectedMaterialId = $request->get('material_id');

        return view('maker.create_product', compact('materials', 'workOrders', 'selectedMaterialId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $messages = [
            'name.required' => 'The product name is required.',
            'material_id.required' => 'Please select a source material.',
            'description.required' => 'The description is required.',
            'price.required' => 'The price is required.',
            'price.min' => 'The price must be at least 0.',
            'stock.required' => 'The stock quantity is required.',
            'stock.min' => 'The stock must be at least 1.',
            'images.required' => 'At least one image is required.',
            'images.min' => 'At least one image is required.',
            'images.*.image' => 'Each file must be an image (jpeg, png, jpg, gif).',
            'images.*.max' => 'Each image may not be greater than 5MB.',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'material_id' => 'required|exists:materials,id',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], $messages);

        $sku = 'PROD-'.strtoupper(uniqid());

        $product = Product::create([
            'maker_id' => Auth::id(),
            'material_id' => $validated['material_id'],
            'sku' => $sku,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'status' => ProductStatus::DRAFT,
        ]);

        $order = 0;
        if ($request->hasFile('images')) {
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
        }

        $product->generateMaterialPassport();

        return redirect()->route('maker.products')
            ->with('success', 'Product created successfully with '.$order.' images!');
    }

    public function show(int $id): View
    {
        $product = Product::with([
            'material.images',
            'workOrder.match.listing.wasteItem',
            'images' => function ($query) {
                $query->orderBy('order');
            },
        ])->where('maker_id', Auth::id())
            ->findOrFail($id);

        return view('maker.product_details', compact('product'));
    }

    public function edit(int $id): View
    {
        $product = Product::with(['material', 'images' => function ($query) {
            $query->orderBy('order');
        }, 'workOrder'])
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
        $product = Product::where('maker_id', Auth::id())->findOrFail($id);

        $messages = [
            'name.required' => 'The product name is required.',
            'material_id.required' => 'Please select a source material.',
            'description.required' => 'The description is required.',
            'price.required' => 'The price is required.',
            'price.min' => 'The price must be at least 0.',
            'stock.required' => 'The stock quantity is required.',
            'stock.min' => 'The stock must be at least 1.',
            'status.required' => 'The status is required.',
            'images.*.image' => 'Each file must be an image (jpeg, png, jpg, gif).',
            'images.*.max' => 'Each image may not be greater than 5MB.',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'material_id' => 'required|exists:materials,id',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
            'status' => 'required|in:'.implode(',', ProductStatus::getValues()),
            'images' => 'sometimes|array',
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
            'remove_images' => 'sometimes|array',
            'remove_images.*' => 'sometimes|numeric',
        ], $messages);

        $stock = $validated['stock'];
        $status = $validated['status'];

        if ($stock == 0 && $status === ProductStatus::PUBLISHED) {
            $status = ProductStatus::SOLD_OUT;
        }

        if ($stock > 0 && $status === ProductStatus::SOLD_OUT) {
            $status = ProductStatus::PUBLISHED;
        }

        $product->update([
            'name' => $validated['name'],
            'material_id' => $validated['material_id'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $stock,
            'status' => $status,
        ]);

        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $imageId) {
                $image = ProductImage::where('product_id', $product->id)
                    ->where('id', $imageId)
                    ->first();

                if ($image) {
                    $imagePath = public_path($image->image_path);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                    $image->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            $existingImagesCount = $product->images()->count();
            $order = $existingImagesCount;

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
        }

        $this->reorderImages($product->id);

        return redirect()->route('maker.products.show', $product->id)
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = Product::where('maker_id', Auth::id())->findOrFail($id);

        foreach ($product->images as $image) {
            $imagePath = public_path($image->image_path);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            $image->delete();
        }

        $productName = $product->name;
        $product->delete();

        return redirect()->route('maker.products')
            ->with('success', "Product '{$productName}' deleted successfully!");
    }

    public function publish(int $id): RedirectResponse
    {
        $product = Product::where('maker_id', Auth::id())->findOrFail($id);

        if ($product->stock <= 0) {
            return redirect()->back()
                ->with('error', 'Cannot publish product with zero stock. Please update stock first.');
        }

        $product->update([
            'status' => ProductStatus::PUBLISHED,
        ]);

        return redirect()->back()
            ->with('success', 'Product published successfully! It is now visible to customers.');
    }

    public function updateStock(Request $request, int $id): RedirectResponse
    {
        $product = Product::where('maker_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $newStock = $validated['stock'];
        $newStatus = $product->status;

        if ($newStock > 0) {
            if ($product->status === ProductStatus::SOLD_OUT) {
                $newStatus = ProductStatus::PUBLISHED;
            }
        } else {
            if ($product->status === ProductStatus::PUBLISHED) {
                $newStatus = ProductStatus::SOLD_OUT;
            }
        }

        $product->update([
            'stock' => $newStock,
            'status' => $newStatus,
        ]);

        $message = 'Stock updated successfully!';
        if ($newStock == 0 && $product->status === ProductStatus::PUBLISHED) {
            $message .= ' Product status changed to sold out.';
        } elseif ($newStock > 0 && $product->status === ProductStatus::SOLD_OUT) {
            $message .= ' Product is now available for sale.';
        }

        return redirect()->back()
            ->with('success', $message);
    }

    private function reorderImages(int $productId): void
    {
        $images = ProductImage::where('product_id', $productId)
            ->orderBy('order')
            ->get();

        foreach ($images as $index => $image) {
            $image->update(['order' => $index]);
        }
    }
}
