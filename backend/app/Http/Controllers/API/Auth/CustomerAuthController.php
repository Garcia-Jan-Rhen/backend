<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    // Customer registration
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:customers,email',
            'password' => 'required|min:6'
        ]);

        $customer = Customer::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        $token = $customer->createToken('customer-token')->plainTextToken;
        return response()->json(['customer' => $customer, 'token' => $token], 201);
    }

    // Customer login
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $customer = Customer::where('email', $data['email'])->first();
        if (!$customer || !Hash::check($data['password'], $customer->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $customer->createToken('customer-token')->plainTextToken;
        return response()->json(['customer' => $customer, 'token' => $token]);
    }

    // Customer logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }
}
