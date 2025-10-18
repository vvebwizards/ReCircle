<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialImage;
use App\Models\WasteItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function create(): View
    {
        $wasteItems = WasteItem::where('maker_id', Auth::id())->get();

        return view('maker.create_material', compact('wasteItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $messages = [
            'name.required' => 'The material name is required.',
            'name.max' => 'The material name may not be greater than 255 characters.',
            'category.required' => 'Please select a category.',
            'category.in' => 'Please select a valid category.',
            'unit.required' => 'Please select a unit.',
            'unit.in' => 'Please select a valid unit.',
            'quantity.required' => 'The quantity is required.',
            'quantity.numeric' => 'The quantity must be a number.',
            'quantity.min' => 'The quantity must be at least 0.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
            'recyclability_score.required' => 'The recyclability score is required.',
            'recyclability_score.numeric' => 'The recyclability score must be a number.',
            'recyclability_score.min' => 'The recyclability score must be at least 0%.',
            'recyclability_score.max' => 'The recyclability score may not be greater than 100%.',
            'description.required' => 'The description is required.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'waste_item_id.required' => 'Please select a waste item to link.',
            'waste_item_id.exists' => 'Selected waste item does not exist or does not belong to you.',
            'image_path.required' => 'At least one image is required.',
            'image_path.array' => 'Please upload at least one image.',
            'image_path.min' => 'At least one image is required.',
            'image_path.*.required' => 'Each image file is required.',
            'image_path.*.image' => 'Each file must be an image (jpeg, png, jpg, gif).',
            'image_path.*.mimes' => 'Each image must be a file of type: jpeg, png, jpg, gif.',
            'image_path.*.max' => 'Each image may not be greater than 2MB.',
        ];

        // FIRST: Run Laravel validation to catch all field errors
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:'.implode(',', Material::CATEGORIES),
            'unit' => 'required|in:'.implode(',', Material::UNITS),
            'quantity' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'recyclability_score' => 'required|numeric|min:0|max:100',
            'description' => 'required|string|max:1000',
            'waste_item_id' => 'required|numeric|min:1',
            'image_path' => 'required|array|min:1',
            'image_path.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        // SECOND: Check if waste item belongs to the current maker
        $wasteItem = WasteItem::where('maker_id', Auth::id())
            ->find($validated['waste_item_id']);

        if (! $wasteItem) {
            return back()->withErrors([
                'waste_item_id' => 'Selected waste item does not exist or does not belong to you.',
            ])->withInput();
        }

        // THIRD: Validate quantity against waste item weight
        if ($wasteItem->estimated_weight && $validated['quantity'] > $wasteItem->estimated_weight) {
            return back()->withErrors([
                'quantity' => "Quantity cannot exceed the available waste item weight ({$wasteItem->estimated_weight}kg).",
            ])->withInput();
        }

        // FOURTH: Additional business logic validations
        if ($validated['price'] > 100000) {
            return back()->withErrors([
                'price' => 'Price seems too high. Please verify the amount.',
            ])->withInput();
        }

        if ($validated['quantity'] > 100000) {
            return back()->withErrors([
                'quantity' => 'Quantity seems too high. Please verify the amount.',
            ])->withInput();
        }

        try {
            // Create the material
            $material = Material::create([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'unit' => $validated['unit'],
                'quantity' => $validated['quantity'],
                'price' => $validated['price'],
                'recyclability_score' => $validated['recyclability_score'],
                'description' => $validated['description'],
                'waste_item_id' => $validated['waste_item_id'],
                'maker_id' => Auth::id(),
            ]);

            // Handle image uploads
            $order = 0;
            $uploadedImagesCount = 0;

            if ($request->hasFile('image_path')) {
                foreach ($request->file('image_path') as $image) {
                    if ($image->isValid()) {
                        $imageName = time().'_'.uniqid().'_'.$order.'.'.$image->getClientOriginalExtension();
                        $imagePath = 'images/materials/'.$imageName;

                        // Ensure directory exists
                        if (! file_exists(public_path('images/materials'))) {
                            mkdir(public_path('images/materials'), 0755, true);
                        }

                        $image->move(public_path('images/materials'), $imageName);

                        MaterialImage::create([
                            'material_id' => $material->id,
                            'image_path' => $imagePath,
                            'order' => $order,
                        ]);

                        $order++;
                        $uploadedImagesCount++;
                    }
                }
            }

            if ($uploadedImagesCount === 0) {
                // If no images were uploaded, delete the material and return error
                $material->delete();

                return back()->withErrors([
                    'image_path' => 'Failed to upload images. Please try again.',
                ])->withInput();
            }

            return redirect()->route('maker.materials.index')
                ->with('success', "Material created successfully with {$uploadedImagesCount} images!");

        } catch (\Exception $e) {
            \Log::error('Material creation failed: '.$e->getMessage());

            return back()->withErrors([
                'error' => 'Failed to create material. Please try again.',
            ])->withInput();
        }
    }

    public function index(Request $request): View
    {
        $query = Material::with(['images' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->where('maker_id', Auth::id());

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('category') && ! empty($request->category)) {
            $query->where('category', $request->category);
        }

        switch ($request->get('sort', 'newest')) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'score_high':
                $query->orderBy('recyclability_score', 'desc');
                break;
            case 'score_low':
                $query->orderBy('recyclability_score', 'asc');
                break;
            default:
                $query->latest();
        }

        $materials = $query->paginate(12);

        $averageScore = Material::where('maker_id', Auth::id())->avg('recyclability_score') ?? 0;
        $categoriesCount = Material::where('maker_id', Auth::id())->distinct()->count('category');

        return view('maker.materials', compact('materials', 'averageScore', 'categoriesCount'));
    }

    public function destroy(int $materialId): RedirectResponse
    {
        $material = Material::where('maker_id', Auth::id())->findOrFail($materialId);

        try {
            foreach ($material->images as $image) {
                $imagePath = public_path($image->image_path);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $image->delete();
            }

            $material->delete();

            return redirect()->route('maker.materials.index')
                ->with('success', 'Material deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Material deletion failed: '.$e->getMessage());

            return redirect()->route('maker.materials.index')
                ->with('error', 'Failed to delete material. Please try again.');
        }
    }

    public function edit(int $id): View
    {
        $material = Material::with('images')
            ->where('maker_id', Auth::id())
            ->findOrFail($id);

        $wasteItems = WasteItem::where('maker_id', Auth::id())->get();

        return view('maker.update_materials', compact('material', 'wasteItems'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $material = Material::where('maker_id', Auth::id())->findOrFail($id);

        $messages = [
            'name.required' => 'The material name is required.',
            'category.required' => 'Please select a category.',
            'unit.required' => 'Please select a unit.',
            'quantity.required' => 'The quantity is required.',
            'quantity.min' => 'The quantity must be at least 0.',
            'recyclability_score.required' => 'The recyclability score is required.',
            'recyclability_score.min' => 'The recyclability score must be at least 0%.',
            'recyclability_score.max' => 'The recyclability score may not be greater than 100%.',
            'description.required' => 'The description is required.',
            'waste_item_id.required' => 'Please select a waste item to link.',
            'waste_item_id.exists' => 'The selected waste item is invalid.',
            'image_path.*.image' => 'Each file must be an image (jpeg, png, jpg, gif).',
            'image_path.*.max' => 'Each image may not be greater than 2MB.',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:'.implode(',', Material::CATEGORIES),
            'unit' => 'required|in:'.implode(',', Material::UNITS),
            'quantity' => 'required|numeric|min:0',
            'recyclability_score' => 'required|numeric|min:0|max:100',
            'description' => 'required|string|max:1000',
            'waste_item_id' => 'required|exists:waste_items,id',
            'image_path' => 'sometimes|array',
            'image_path.*' => 'sometimes|image|max:2048',
            'remove_images' => 'sometimes|array',
            'remove_images.*' => 'sometimes|numeric',
        ], $messages);

        // Check if waste item belongs to the current maker
        $wasteItem = WasteItem::where('maker_id', Auth::id())
            ->find($validated['waste_item_id']);

        if (! $wasteItem) {
            return back()->withErrors([
                'waste_item_id' => 'Selected waste item does not exist or does not belong to you.',
            ])->withInput();
        }

        // Validate quantity against waste item weight
        if ($wasteItem->estimated_weight && $validated['quantity'] > $wasteItem->estimated_weight) {
            return back()->withErrors([
                'quantity' => "Quantity cannot exceed the available waste item weight ({$wasteItem->estimated_weight}kg).",
            ])->withInput();
        }

        try {
            $material->update([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'unit' => $validated['unit'],
                'quantity' => $validated['quantity'],
                'recyclability_score' => $validated['recyclability_score'],
                'description' => $validated['description'],
                'waste_item_id' => $validated['waste_item_id'],
            ]);

            // Handle removed images
            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $imageId) {
                    $image = MaterialImage::where('material_id', $material->id)
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

            // Handle new image uploads
            if ($request->hasFile('image_path')) {
                $existingImagesCount = $material->images()->count();
                $order = $existingImagesCount;

                foreach ($request->file('image_path') as $image) {
                    if ($image->isValid()) {
                        $imageName = time().'_'.uniqid().'_'.$order.'.'.$image->getClientOriginalExtension();
                        $imagePath = 'images/materials/'.$imageName;

                        // Ensure directory exists
                        if (! file_exists(public_path('images/materials'))) {
                            mkdir(public_path('images/materials'), 0755, true);
                        }

                        $image->move(public_path('images/materials'), $imageName);

                        MaterialImage::create([
                            'material_id' => $material->id,
                            'image_path' => $imagePath,
                            'order' => $order,
                        ]);

                        $order++;
                    }
                }
            }

            $this->reorderImages($material->id);

            return redirect()->route('maker.materials.index')
                ->with('success', 'Material updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Material update failed: '.$e->getMessage());

            return back()->withErrors([
                'error' => 'Failed to update material. Please try again.',
            ])->withInput();
        }
    }

    public function show(int $id): View
    {
        $material = Material::with([
            'images' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'wasteItem',
        ])->where('maker_id', Auth::id())
            ->findOrFail($id);

        $relatedMaterials = Material::with(['images' => function ($query) {
            $query->orderBy('order', 'asc');
        }])
            ->where('maker_id', Auth::id())
            ->where('category', $material->category)
            ->where('id', '!=', $material->id)
            ->limit(4)
            ->get();

        $availableStock = $material->quantity;

        return view('maker.material-details', compact(
            'material',
            'relatedMaterials',
            'availableStock'
        ));
    }

    private function reorderImages(int $materialId): void
    {
        $images = MaterialImage::where('material_id', $materialId)
            ->orderBy('order')
            ->get();

        foreach ($images as $index => $image) {
            $image->update(['order' => $index]);
        }
    }

    public function getMaterialImages(int $materialId): JsonResponse
    {
        $material = Material::with(['images' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->where('maker_id', Auth::id())->findOrFail($materialId);

        $images = $material->images->map(function (MaterialImage $image) {
            return [
                'id' => $image->id,
                'path' => asset($image->image_path),
                'order' => $image->order,
            ];
        })->toArray();

        return response()->json([
            'images' => $images,
        ]);
    }
}
