<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsAdminController extends Controller
{
    public function productsIndex()
    {
        $products = Product::with(['maker', 'materials', 'images'])
            ->latest()
            ->paginate(20);

        $makers = User::where('role', 'maker')->get();

        return view('admin.products', compact('products', 'makers'));
    }

    /**
     * Admin products data for AJAX
     */
    public function productsData(Request $request): JsonResponse
    {
        $query = Product::with(['maker', 'materials']);

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->maker) {
            $query->where('maker_id', $request->maker);
        }

        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('sku', 'like', '%'.$request->search.'%');
        }

        // Apply sorting
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
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price');
                break;
            case 'stock_desc':
                $query->orderBy('stock', 'desc');
                break;
            case 'stock_asc':
                $query->orderBy('stock');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(20);

        return response()->json([
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'status' => $product->status->value, // Get string value from enum
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'maker' => $product->maker,
                    'materials_count' => $product->materials->count(),
                    'created_at' => $product->created_at->diffForHumans(),
                ];
            }),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * Admin show product details
     */
    public function productsShow(Product $product): JsonResponse
    {
        $product->load(['maker', 'materials', 'images']);

        return response()->json([
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'status' => $product->status->value,
            'price' => $product->price,
            'stock' => $product->stock,
            'dimensions' => $product->dimensions,
            'care_instructions' => $product->care_instructions,
            'warranty_months' => $product->warranty_months,
            'tags' => $product->tags,
            'material_passport' => $product->material_passport,
            'maker' => $product->maker,
            'materials' => $product->materials->map(function ($material) {
                return [
                    'id' => $material->id,
                    'name' => $material->name,
                    'category' => $material->category,
                    'quantity_used' => $material->pivot->quantity_used,
                    'unit' => $material->pivot->unit,
                ];
            }),
            'images' => $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => $image->image_url,
                    'thumbnail_url' => $image->thumbnail_url,
                ];
            }),
            'created_at' => $product->created_at->diffForHumans(),
            'updated_at' => $product->updated_at?->diffForHumans(),
            'published_at' => $product->published_at?->diffForHumans(),
        ]);
    }

    /**
     * Admin edit product form data
     */
    public function productsEdit(Product $product): JsonResponse
    {
        $product->load(['images', 'materials']);

        return response()->json([
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'status' => $product->status->value,
            'price' => $product->price,
            'stock' => $product->stock,
            'dimensions' => $product->dimensions,
            'care_instructions' => $product->care_instructions,
            'warranty_months' => $product->warranty_months,
            'tags' => $product->tags,
            'images' => $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => $image->image_url,
                    'thumbnail_url' => $image->thumbnail_url,
                ];
            }),
        ]);
    }

    /**
     * Admin update product
     */
    public function productsUpdate(Request $request, Product $product): JsonResponse
    {
        try {
            \Log::info('Product update request received', [
                'product_id' => $product->id,
                'data' => $request->all(),
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:draft,published,archived',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'care_instructions' => 'nullable|string',
                'warranty_months' => 'nullable|integer|min:0',
                'tags' => 'nullable|array',
                'new_images.*' => 'nullable|image|max:5120',
                'keep_images' => 'nullable|string',
                'remove_images' => 'nullable|string',
            ]);

            \Log::info('Validation passed', ['validated_data' => $validated]);
            // Update basic fields
            $updateData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'care_instructions' => $validated['care_instructions'] ?? null,
                'warranty_months' => $validated['warranty_months'] ?? null,
            ];

            \Log::info('Updating product with data:', $updateData);

            $product->update($updateData);
            \Log::info('Product updated successfully');

            // Handle image removal
            if ($request->remove_images && ! empty($request->remove_images)) {
                $removeIds = array_filter(explode(',', $request->remove_images));
                \Log::info('Removing images:', ['ids' => $removeIds]);

                if (! empty($removeIds)) {
                    ProductImage::where('product_id', $product->id)
                        ->whereIn('id', $removeIds)
                        ->delete();
                    \Log::info('Images removed successfully');
                }
            }

            // Handle new images
            if ($request->hasFile('new_images')) {
                \Log::info('Processing new images', ['count' => count($request->file('new_images'))]);

                foreach ($request->file('new_images') as $image) {
                    $path = $image->store('products', 'public');
                    \Log::info('Image stored', ['path' => $path]);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                    ]);
                }
                \Log::info('New images processed successfully');
            }

            \Log::info('Product update completed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Product update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin delete product
     */
    public function productsDestroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Admin toggle featured status
     */
}
