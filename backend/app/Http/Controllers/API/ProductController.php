<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    // List all products
    public function index()
    {
        return response()->json(Product::all());
    }

    // Show a single product
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    // Create a new product
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
            'stock'       => 'required|integer',
            'image'       => 'nullable|string'
        ]);
        $product = Product::create($data);
        return response()->json($product, 201);
    }

    // Update an existing product
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validate([
            'name'        => 'sometimes|required|string',
            'description' => 'sometimes|nullable|string',
            'price'       => 'sometimes|required|numeric',
            'stock'       => 'sometimes|required|integer',
            'image'       => 'sometimes|nullable|string'
        ]);
        $product->update($data);
        return response()->json($product);
    }

    // Delete a product (prevent deletion if in any order)
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->orders()->exists()) {
            return response()->json(['message' => 'Cannot delete product; it is included in an order.'], 403);
        }
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully.']);
    }
}

