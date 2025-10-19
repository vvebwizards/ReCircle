<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialImage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialsAdminController extends Controller
{
    /**
     * Admin materials index page
     */
    public function materialsIndex()
    {
        $materials = Material::with(['maker', 'products', 'images'])
            ->latest()
            ->paginate(20);

        $makers = User::where('role', 'maker')->get();

        return view('admin.admin-materials', compact('materials', 'makers'));
    }

    /**
     * Admin materials data for AJAX
     */
    public function materialsData(Request $request): JsonResponse
    {
        $query = Material::with(['maker', 'products']);

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->unit) {
            $query->where('unit', $request->unit);
        }

        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        switch ($request->sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name_asc':
                $query->orderBy('name');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'quantity_desc':
                $query->orderBy('quantity', 'desc');
                break;
            case 'quantity_asc':
                $query->orderBy('quantity');
                break;
            default:
                $query->latest();
        }

        $materials = $query->paginate(20);

        return response()->json([
            'materials' => $materials->map(function ($material) {
                return [
                    'id' => $material->id,
                    'name' => $material->name,
                    'category' => $material->category,
                    'quantity' => $material->quantity,
                    'unit' => $material->unit,
                    'recyclability_score' => $material->recyclability_score,
                    'maker' => $material->maker,
                    'products_count' => $material->products->count(),
                    'created_at' => $material->created_at->diffForHumans(),
                ];
            }),
            'current_page' => $materials->currentPage(),
            'last_page' => $materials->lastPage(),
            'total' => $materials->total(),
        ]);
    }

    /**
     * Admin show material details
     */
    public function materialsShow(Material $material): JsonResponse
    {
        $material->load(['maker', 'wasteItem', 'products', 'images']);

        return response()->json([
            'id' => $material->id,
            'name' => $material->name,
            'category' => $material->category,
            'quantity' => $material->quantity,
            'unit' => $material->unit,
            'recyclability_score' => $material->recyclability_score,
            'description' => $material->description,
            'co2_kg_saved' => $material->co2_kg_saved,
            'landfill_kg_avoided' => $material->landfill_kg_avoided,
            'energy_saved_kwh' => $material->energy_saved_kwh,
            'maker' => $material->maker,
            'waste_item' => $material->wasteItem,
            'products' => $material->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'pivot' => $product->pivot,
                ];
            }),
            'images' => $material->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => $image->image_url,
                    'thumbnail_url' => $image->thumbnail_url,
                ];
            }),
            'created_at' => $material->created_at->diffForHumans(),
            'updated_at' => $material->updated_at?->diffForHumans(),
        ]);
    }

    /**
     * Admin edit material form data
     */
    public function materialsEdit(Material $material): JsonResponse
    {
        $material->load(['images']);

        return response()->json([
            'id' => $material->id,
            'name' => $material->name,
            'category' => $material->category,
            'quantity' => $material->quantity,
            'unit' => $material->unit,
            'recyclability_score' => $material->recyclability_score,
            'description' => $material->description,
            'maker_id' => $material->maker_id,
            'images' => $material->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => $image->image_url,
                    'thumbnail_url' => $image->thumbnail_url,
                ];
            }),
        ]);
    }

    /**
     * Admin update material
     */
    public function materialsUpdate(Request $request, Material $material): JsonResponse
    {
        try {
            \Log::info('Material update request received', [
                'material_id' => $material->id,
                'data' => $request->except(['new_images']),
                'has_new_images' => $request->hasFile('new_images'),
                'remove_images' => $request->remove_images,
                'keep_images' => $request->keep_images,
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|in:wood,metal,plastic,textile,electronic,glass,paper',
                'quantity' => 'required|numeric|min:0',
                'unit' => 'required|in:kg,pcs,m2,l',
                'recyclability_score' => 'nullable|integer|min:0|max:100',
                'description' => 'nullable|string',
                'maker_id' => 'nullable|exists:users,id',
                'new_images.*' => 'nullable|image|max:5120',
                'keep_images' => 'nullable|string',
                'remove_images' => 'nullable|string',
            ]);

            \Log::info('Validation passed', ['validated_data' => $validated]);

            // Update basic fields
            $updateData = [
                'name' => $validated['name'],
                'category' => $validated['category'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'recyclability_score' => $validated['recyclability_score'] ?? null,
                'description' => $validated['description'] ?? null,
                'maker_id' => $validated['maker_id'] ?? null,
            ];

            \Log::info('Updating material with data:', $updateData);

            $material->update($updateData);
            \Log::info('Material updated successfully');

            // Handle image removal
            if ($request->remove_images) {
                $removeIds = array_filter(explode(',', $request->remove_images));
                \Log::info('Removing images:', ['ids' => $removeIds]);

                if (! empty($removeIds)) {
                    MaterialImage::where('material_id', $material->id)
                        ->whereIn('id', $removeIds)
                        ->delete();
                    \Log::info('Images removed successfully');
                }
            }

            // Handle new images
            if ($request->hasFile('new_images')) {
                \Log::info('Processing new images', ['count' => count($request->file('new_images'))]);

                foreach ($request->file('new_images') as $image) {
                    $path = $image->store('materials', 'public');
                    \Log::info('Image stored', ['path' => $path]);

                    MaterialImage::create([
                        'material_id' => $material->id,
                        'image_path' => $path,
                    ]);
                }
                \Log::info('New images processed successfully');
            }

            // Recalculate impact if needed
            if ($material->isUsedInProducts()) {
                \Log::info('Recalculating environmental impact');
                $material->calculateAndUpdateImpact();
            }

            \Log::info('Material update completed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Material updated successfully',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Material update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update material: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin delete material
     */
    public function materialsDestroy(Material $material): JsonResponse
    {
        $material->delete();

        return response()->json(['success' => true]);
    }
}
