<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class CheckoutController extends Controller
{
    // List all checkout transactions
    public function index(Request $request)
    {
        // Optional date filter (e.g., ?date=2025-03-25)
        if ($request->has('date')) {
            $orders = Order::whereDate('created_at', $request->date)->get();
        } else {
            $orders = Order::all();
        }
        return response()->json($orders);
    }

    // View details of a specific checkout transaction
    public function show($id)
    {
        $order = Order::with('products')->findOrFail($id);
        return response()->json($order);
    }
}
