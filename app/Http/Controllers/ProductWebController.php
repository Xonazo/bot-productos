<?php

namespace App\Http\Controllers;

use App\Services\ProductLocationService;
use Illuminate\Http\Request;

class ProductWebController extends Controller
{
    public function __construct(
        protected ProductLocationService $productService
    ) {}

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $this->productService->create(
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'] ?? null,
            ],
            $validated['lat'],
            $validated['lng']
        );

        return redirect()
            ->route('products.create')
            ->with('success', '¡Producto publicado correctamente!');
    }
}