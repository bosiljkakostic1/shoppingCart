<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products
     */
    public function index(): JsonResponse
    {
        $products = Product::with('productInputs')->orderBy('name')->get()->map(function ($product) {
            return [
                'id' => (int) $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stockQuantity' => $product->getAvailableQuantity(),
                'minStockQuantity' => (int) $product->minStockQuantity,
                'unit' => $product->unit,
                'updatedAt' => $product->updatedAt?->toISOString() ?? now()->toISOString(),
            ];
        });

        return response()->json($products);
    }

    /**
     * Get a single product with current stock
     */
    public function show($id): JsonResponse
    {
        $product = Product::with('productInputs')->findOrFail($id);

        return response()->json([
            'id' => (int) $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'stockQuantity' => $product->getAvailableQuantity(),
            'minStockQuantity' => (int) $product->minStockQuantity,
            'unit' => $product->unit,
            'updatedAt' => $product->updatedAt?->toISOString() ?? now()->toISOString(),
        ]);
    }

    /**
     * Get available quantity for a specific product
     */
    public function getAvailableQuantity($id): JsonResponse
    {
        $product = Product::with('productInputs')->findOrFail($id);
        
        return response()->json([
            'productId' => (int) $product->id,
            'availableQuantity' => $product->getAvailableQuantity(),
        ]);
    }
}
