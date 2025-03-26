<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class EmployeeAuthController extends Controller
{
    // Employee registration
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:employees,email',
            'password' => 'required|min:6'
        ]);

        $employee = Employee::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        $token = $employee->createToken('employee-token')->plainTextToken;
        return response()->json(['employee' => $employee, 'token' => $token], 201);
    }

    // Employee login
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $employee = Employee::where('email', $data['email'])->first();
        if (!$employee || !Hash::check($data['password'], $employee->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $employee->createToken('employee-token')->plainTextToken;
        return response()->json(['employee' => $employee, 'token' => $token]);
    }

    // Employee logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }
}
