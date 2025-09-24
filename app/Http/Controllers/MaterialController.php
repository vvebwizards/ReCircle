<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialImage;
use Illuminate\Http\Request;
use App\Services\JwtService;

class MaterialController extends Controller
{
    public function __construct(private JwtService $jwt) {}

    private function ensureTemporaryWasteItemsExist()
    {
        // Check if we have any waste items in the database
        if (! \App\Models\WasteItem::exists()) {
            $temporaryItems = [
                [
                    'title' => 'Plastic Bottles',
                    'received_date' => '2024-01-15',
                    'generator_id' => 1,
                ],
                [
                    'title' => 'Cardboard Boxes',
                    'received_date' => '2024-01-16',
                    'generator_id' => 1,
                ],
                [
                    'title' => 'Glass Containers',
                    'received_date' => '2024-01-17',
                    'generator_id' => 1,
                ],
                [
                    'title' => 'Aluminum Cans',
                    'received_date' => '2024-01-18',
                    'generator_id' => 1,
                ],
                [
                    'title' => 'Electronic Waste',
                    'received_date' => '2024-01-19',
                    'generator_id' => 1,
                ],
            ];

            foreach ($temporaryItems as $item) {
                \App\Models\WasteItem::create($item);
            }
        }
    }

    public function create()
    {
        $this->ensureTemporaryWasteItemsExist();
        $wasteItems = \App\Models\WasteItem::all();

        return view('maker.create_material', compact('wasteItems'));
    }

    public function store(Request $request)
    {

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

            'image_path.required' => 'At least one image is required.',
            'image_path.min' => 'At least one image is required.',
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
            'waste_item_id' => 'required|numeric|min:1',
            'image_path' => 'required|array|min:1',
            'image_path.*' => 'required|image|max:2048',
        ], $messages);

        $material = Material::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'unit' => $validated['unit'],
            'quantity' => $validated['quantity'],
            'recyclability_score' => $validated['recyclability_score'],
            'description' => $validated['description'],
            'waste_item_id' => $validated['waste_item_id'],
            'maker_id' => auth()->id(), 
        ]);

        if ($request->hasFile('image_path')) {
            $order = 0;
            foreach ($request->file('image_path') as $image) {
                $imageName = time().'_'.uniqid().'_'.$order.'.'.$image->getClientOriginalExtension();
                $image->move(public_path('images/materials'), $imageName);
                $imagePath = 'images/materials/'.$imageName;

                MaterialImage::create([
                    'material_id' => $material->id,
                    'image_path' => $imagePath,
                    'order' => $order,
                ]);

                $order++;
            }
        }

        return redirect()->route('materials.create')->with('success', 'Material created with '.$order.' images!');
    }

    public function index() {}
}
