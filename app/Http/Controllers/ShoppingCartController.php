<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductInput;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShoppingCartController extends Controller
{
    /**
     * Format cart for JSON response
     */
    private function formatCartResponse(ShoppingCart $cart): array
    {
        $cart->load(['products.product']);
        
        return [
            'id' => (int) $cart->id,
            'userId' => (int) $cart->userId,
            'sum' => (float) $cart->sum,
            'state' => $cart->state,
            'updatedAt' => $cart->updatedAt?->toISOString() ?? now()->toISOString(),
            'products' => $cart->products->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'shoppingCartId' => (int) $item->shoppingCartId,
                    'userId' => (int) $item->userId,
                    'productId' => (int) $item->productId,
                    'quantity' => (int) $item->quantity,
                    'product' => [
                        'id' => (int) $item->product->id,
                        'name' => $item->product->name,
                        'price' => (float) $item->product->price,
                        'unit' => $item->product->unit,
                    ],
                ];
            }),
        ];
    }

    /**
     * Get or create active shopping cart for the authenticated user
     */
    public function getActiveCart(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if there's an active cart for this user
        $cart = ShoppingCart::where('userId', $user->id)
            ->where('state', 'active')
            ->first();
        
        // If no active cart exists, create a new one
        if (!$cart) {
            $cart = ShoppingCart::create([
                'userId' => $user->id,
                'state' => 'active',
                'sum' => 0,
                'updatedAt' => now(),
            ]);
        }

        return response()->json($this->formatCartResponse($cart));
    }

    /**
     * Finish order - change cart state to 'ordered'
     */
    public function finishOrder(Request $request): JsonResponse
    {
        $user = $request->user();

        DB::beginTransaction();
        try {
            $cart = ShoppingCart::where('userId', $user->id)
                ->where('state', 'active')
                ->firstOrFail();

            $cart->state = 'ordered';
            $cart->updatedAt = now();
            $cart->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'cart' => $this->formatCartResponse($cart),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to finish order.',
            ], 500);
        }
    }

    /**
     * Add product to shopping cart
     */
    public function addProduct(Request $request): JsonResponse
    {
        $request->validate([
            'productId' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $productId = $request->productId;
        $quantity = $request->quantity;

        // Check product availability before adding
        $product = Product::with('productInputs')->findOrFail($productId);
        $availableQuantity = $product->getAvailableQuantity();
        
        if ($availableQuantity < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not available in the requested quantity.',
                'availableQuantity' => $availableQuantity,
            ], 400);
        }

        if ($availableQuantity === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Product is no longer available.',
                'availableQuantity' => 0,
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Get or create active cart
            $cart = ShoppingCart::where('userId', $user->id)
                ->where('state', 'active')
                ->first();
            
            // If no active cart exists, create a new one
            if (!$cart) {
                $cart = ShoppingCart::create([
                    'userId' => $user->id,
                    'state' => 'active',
                    'sum' => 0,
                    'updatedAt' => now(),
                ]);
            }

            // Check if product already in cart
            $cartProduct = ShoppingCartProduct::where('shoppingCartId', $cart->id)
                ->where('productId', $productId)
                ->first();

            if ($cartProduct) {
                $newQuantity = $cartProduct->quantity + $quantity;
                
                // Re-check availability for total quantity
                // Note: getAvailableQuantity excludes items in active carts, so we need to add back
                // the current cart quantity to check if we have enough total stock
                $product = Product::with('productInputs')->findOrFail($productId);
                $currentAvailable = $product->getAvailableQuantity();
                // Add back the quantity already in this cart to get total available
                $totalAvailable = $currentAvailable + $cartProduct->quantity;
                
                if ($totalAvailable < $newQuantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Not enough stock available. Current stock: ' . $currentAvailable,
                        'availableQuantity' => $currentAvailable,
                    ], 400);
                }

                $cartProduct->quantity = $newQuantity;
                $cartProduct->updatedAt = now();
                $cartProduct->save();
            } else {
                ShoppingCartProduct::create([
                    'shoppingCartId' => $cart->id,
                    'userId' => $user->id,
                    'productId' => $productId,
                    'quantity' => $quantity,
                    'updatedAt' => now(),
                ]);
            }

            // Recalculate cart sum
            $this->recalculateCartSum($cart);

            DB::commit();

            // Get updated available quantity
            $product = Product::with('productInputs')->findOrFail($productId);
            $updatedAvailable = $product->getAvailableQuantity();
            
            // Check for low stock and dispatch notification if needed
            $product->checkLowStock();

            $cart = ShoppingCart::findOrFail($cart->id);

            return response()->json([
                'success' => true,
                'cart' => $this->formatCartResponse($cart),
                'updatedProduct' => [
                    'id' => (int) $product->id,
                    'stockQuantity' => $updatedAvailable,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to cart.',
            ], 500);
        }
    }

    /**
     * Update product quantity in cart
     */
    public function updateQuantity(Request $request, $cartProductId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $newQuantity = $request->quantity;

        $cartProduct = ShoppingCartProduct::where('id', $cartProductId)
            ->where('userId', $user->id)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $product = Product::with('productInputs')->findOrFail($cartProduct->productId);
            
            $oldQuantity = $cartProduct->quantity;
            $quantityDifference = $newQuantity - $oldQuantity;

            // Check if we need more stock
            if ($quantityDifference > 0) {
                // Need to check if enough stock available
                // getAvailableQuantity excludes items in active carts, so we need to add back
                // the old quantity from this cart item
                $currentAvailable = $product->getAvailableQuantity();
                $totalAvailable = $currentAvailable + $oldQuantity;
                
                if ($totalAvailable < $newQuantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Not enough stock available. Current stock: ' . $currentAvailable,
                        'availableQuantity' => $currentAvailable,
                    ], 400);
                }
            }

            $cartProduct->quantity = $newQuantity;
            $cartProduct->updatedAt = now();
            $cartProduct->save();

            $cart = ShoppingCart::findOrFail($cartProduct->shoppingCartId);
            $this->recalculateCartSum($cart);

            DB::commit();

            // Get updated available quantity
            $product = Product::with('productInputs')->findOrFail($cartProduct->productId);
            $updatedAvailable = $product->getAvailableQuantity();
            
            // Check for low stock and dispatch notification if needed
            $product->checkLowStock();

            $cart = ShoppingCart::findOrFail($cart->id);

            return response()->json([
                'success' => true,
                'cart' => $this->formatCartResponse($cart),
                'updatedProduct' => [
                    'id' => (int) $product->id,
                    'stockQuantity' => $updatedAvailable,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity.',
            ], 500);
        }
    }

    /**
     * Remove product from cart
     */
    public function removeProduct(Request $request, $cartProductId): JsonResponse
    {
        $user = $request->user();

        $cartProduct = ShoppingCartProduct::where('id', $cartProductId)
            ->where('userId', $user->id)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $productId = $cartProduct->productId;
            $product = Product::with('productInputs')->findOrFail($productId);

            $cart = ShoppingCart::findOrFail($cartProduct->shoppingCartId);
            $cartProduct->delete();

            $this->recalculateCartSum($cart);

            DB::commit();

            // Get updated available quantity (stock is automatically returned when removed from cart)
            $product = Product::with('productInputs')->findOrFail($productId);
            $updatedAvailable = $product->getAvailableQuantity();
            
            // Check for low stock and dispatch notification if needed
            $product->checkLowStock();

            $cart = ShoppingCart::findOrFail($cart->id);

            return response()->json([
                'success' => true,
                'cart' => $this->formatCartResponse($cart),
                'updatedProduct' => [
                    'id' => (int) $product->id,
                    'stockQuantity' => $updatedAvailable,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove product.',
            ], 500);
        }
    }

    /**
     * Recalculate cart sum
     */
    private function recalculateCartSum(ShoppingCart $cart): void
    {
        $sum = ShoppingCartProduct::where('shoppingCartId', $cart->id)
            ->join('products', 'shoppingCartProducts.productId', '=', 'products.id')
            ->selectRaw('SUM(shoppingCartProducts.quantity * products.price) as total')
            ->value('total') ?? 0;

        $cart->sum = $sum;
        $cart->updatedAt = now();
        $cart->save();
    }
}
