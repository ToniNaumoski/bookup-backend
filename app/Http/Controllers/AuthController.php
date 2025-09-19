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
            "password_confirmation" => "required|same:password",
        ], [
            'name.required' => __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'name.min' => __('validation.min.string', ['attribute' => __('validation.attributes.name'), 'min' => 4]),
            'name.max' => __('validation.max.string', ['attribute' => __('validation.attributes.name'), 'max' => 20]),
            'email.required' => __('validation.required', ['attribute' => __('validation.attributes.email')]),
            'email.email' => __('validation.email', ['attribute' => __('validation.attributes.email')]),
            'email.unique' => 'Оваа емаил адреса е веќе регистрирана.',
            'email.regex' => __('validation.email', ['attribute' => __('validation.attributes.email')]),
            'phone.required' => __('validation.required', ['attribute' => __('validation.attributes.phone')]),
            'phone.digits' => 'Внесете валиден телефонски број.',
            'phone.regex' => 'Внесете валиден телефонски број.',
            'password.required' => __('validation.required', ['attribute' => __('validation.attributes.password')]),
            'password.min' => __('validation.min.string', ['attribute' => __('validation.attributes.password'), 'min' => 8]),
            'password.regex' => 'Лозинката мора да има најмалку 8 карактери, вклучувајќи голема буква и симбол.',
            'password_confirmation.required' => 'Потврдата е задолжителна.',
            'password_confirmation.same' => __('validation.same', ['attribute' => __('validation.attributes.password_confirmation'), 'other' => __('validation.attributes.password')]),
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
            'message' => 'Корисникот е регистриран успешно! Проверете ја вашата емаил адреса за линк за верификација. Без верификација нема да можете да се најавите.',
            'requires_verification' => true,
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
                "regex:/^(?=.*[A-Z]).{8,}$/"
            ],
            "password_confirmation" => "required|same:password",
        ], [
            'name.required' => __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'name.min' => __('validation.min.string', ['attribute' => __('validation.attributes.name'), 'min' => 4]),
            'name.max' => __('validation.max.string', ['attribute' => __('validation.attributes.name'), 'max' => 20]),
            'email.required' => __('validation.required', ['attribute' => __('validation.attributes.email')]),
            'email.email' => __('validation.email', ['attribute' => __('validation.attributes.email')]),
            'email.unique' => 'Оваа емаил адреса е веќе регистрирана.',
            'email.regex' => __('validation.email', ['attribute' => __('validation.attributes.email')]),
            'phone.required' => __('validation.required', ['attribute' => __('validation.attributes.phone')]),
            'phone.digits' => 'Внесете валиден телефонски број.',
            'phone.regex' => 'Внесете валиден телефонски број.',
            'password.required' => __('validation.required', ['attribute' => __('validation.attributes.password')]),
            'password.min' => __('validation.min.string', ['attribute' => __('validation.attributes.password'), 'min' => 8]),
            'password.regex' => 'Лозинката мора да има најмалку 8 карактери, вклучувајќи голема буква.',
            'password_confirmation.required' => 'Потврдата е задолжителна.',
            'password_confirmation.same' => __('validation.same', ['attribute' => __('validation.attributes.password_confirmation'), 'other' => __('validation.attributes.password')]),
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
            'message' => 'Бизнисот е регистриран успешно! Проверете ја вашата емаил адреса за линк за верификација. Без верификација нема да можете да се најавите. Проверете го вашиот inbox/spam фолдер.',
            'requires_verification' => true,
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
                'email' => ['Невалидни податоци'],
                'password' => ['Невалидни податоци'],
            ]);
        }

        $user = Auth::user(); // Get the authenticated user

        // Check if user account is blocked (blocked users cannot log in)
        if ($user->status === 'blocked') {
            Auth::logout(); // Log out the user
            throw ValidationException::withMessages([
                'email' => ['Вашиот профил е блокиран. Контактирајте со поддршка.'],
            ]);
        }

        // Suspended users can log in but have restrictions
        $isSuspended = $user->status === 'suspended';

        // Check email verification for non-super-admin users
        if ($user->role !== 'super_admin' && !$user->hasVerifiedEmail()) {
            Auth::logout(); // Log out the user
            throw ValidationException::withMessages([
                'email' => ['Ве молиме верифицирајте ја вашата емаил адреса пред да се најавите. Проверете го вашиот емаил за линк за верификација.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role' => $user->role,
            'name' => $user->name,
            'status' => $user->status,
            'is_suspended' => $isSuspended
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
        return response()->json(['message' => 'Одјавени сте']);
    }
}
