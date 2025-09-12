<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Register a normal user
    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|min:4|max:20",
            "email" => [
                "required",
                "email",
                "unique:users,email",
                "regex:/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/"
            ],
            "phone" => [
                "required",
                "string",
                'digits:9',
                "regex:/^(070|071|072|075|076|077|078|079)\\d{6}$/"
            ],
            "password" => [
                "required",
                "string",
                "min:8",
                "regex:/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/"
            ],
        ]);
        
    
        if ($validator->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "Validation errors.",
                "errors" => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'role' => 'user'
        ]);

        $response = [
            "name" => $user->name,
        ];

        $user->sendEmailVerificationNotification();

    
        return response()->json([
            'message' => 'User registered. Please check your email to verify your account.',
        ], 201);
    }

    // Register a business account
    public function registerBusiness(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|min:4|max:20",
            "email" => [
                "required",
                "email",
                "unique:users,email",
                "regex:/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/"
            ],
            "phone" => [
                "required",
                "string",
                'digits:9',
                "regex:/^(070|071|072|075|076|077|078|079)\\d{6}$/"
            ],
            "password" => [
                "required",
                "string",
                "min:8",
                "regex:/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/"
            ],
        ]);
        
    
        if ($validator->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "Validation errors.",
                "errors" => $validator->errors()
            ], 422);
        }

        $business = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt(value: $request->password),
            'role' => 'business'
        ]);

        
        $response = [
            "name" =>  $business->name,
        ];

        $business->sendEmailVerificationNotification();

    
        return response()->json([
            'message' => 'Business registered. Please check your email to verify your account.',
        ], 201);
    
        // return response()->json([
        //     "status" => 200,
        //     "message" => "User registered successfully.",
        //     "data" => $response
        // ]);
    }

    // Login for both users and businesses
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Attempt login
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
                'password' => ['Invalid credentials'],
            ]);
        }

        $user = Auth::user(); // Get the authenticated user

        // Check if user account is active
        if ($user->status === 'blocked' || $user->status === 'suspended') {
            Auth::logout(); // Log out the user
            throw ValidationException::withMessages([
                'email' => ['Your account has been ' . $user->status . '. Please contact support.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role' => $user->role,
            'name' => $user->name
        ]);
    }

    // Get authenticated user profile
    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'status' => $user->status,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'admin_messages' => $user->admin_messages ?? []
        ]);
    }

    // Logout user (delete current token)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
