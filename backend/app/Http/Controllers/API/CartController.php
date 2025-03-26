<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // Add an item to the cart (prevent if stock is empty)
    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($data['product_id']);
        if ($product->stock < $data['quantity']) {
            return response()->json(['message' => 'Not enough stock available.'], 400);
        }

        // For simplicity, assume one cart item per product per customer.
        $cart = Cart::updateOrCreate(
            ['customer_id' => auth()->id(), 'product_id' => $data['product_id']],
            ['quantity' => DB::raw("quantity + {$data['quantity']}")]
        );

        return response()->json($cart, 201);
    }

    // Checkout: Create an order from the cart items
    public function checkout(Request $request)
    {
        $customerId = auth()->id();
        $cartItems = Cart::with('product')->where('customer_id', $customerId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 400);
        }

        DB::beginTransaction();
        try {
            $total = 0;
            foreach ($cartItems as $item) {
                if ($item->product->stock < $item->quantity) {
                    throw new \Exception('Insufficient stock for product: ' . $item->product->name);
                }
                $total += $item->product->price * $item->quantity;
            }

            $order = Order::create([
                'customer_id' => $customerId,
                'total'       => $total,
                'status'      => 'completed'
            ]);

            // Attach each product to the order and decrease stock
            foreach ($cartItems as $item) {
                $order->products()->attach($item->product_id, [
                    'quantity' => $item->quantity,
                    'price'    => $item->product->price
                ]);
                $item->product->decrement('stock', $item->quantity);
            }

            // Clear the customer's cart
            Cart::where('customer_id', $customerId)->delete();

            DB::commit();
            return response()->json($order, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
