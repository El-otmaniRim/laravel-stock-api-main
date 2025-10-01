<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function store(Request $request)
    {
        $this->authorizeRole('admin');

        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'image' => 'nullable|image|max:2048' // accept real image file
        ]);

        $validated['image'] = base64_encode(file_get_contents($request->file('image')->getRealPath()));

        return Product::create($validated);
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeRole('admin');

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
            'image' => 'nullable|string'
        ]);

        $product->update($validated);
        return $product;
    }

    public function destroy(Product $product)
    {
        $this->authorizeRole('admin');

        $product->delete();
        return response()->json(['message' => 'Deleted']);
    }
    private function authorizeRole($role)
    {
        if (!Auth::user()->hasRole($role)) {
            abort(403, 'Unauthorized');
        }
    }
}