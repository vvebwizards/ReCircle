<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the tabbed auth page.
     */
    public function show(): View
    {
        return view('auth.auth');
    }

    /**
     * Handle registration.
     */
    public function register(Request $request): RedirectResponse|JsonResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'in:generator,maker,buyer,courier'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ];

        // If the client expects JSON (AJAX), return JSON without redirecting
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'password' => $data['password'],
            ]);

            return response()->json([
                'status' => 'ok',
                'message' => 'Account created. Please verify your email to continue.',
            ], 201);
        }

        // Default full-page request
        $validated = $request->validate($rules);
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => $validated['password'],
        ]);

        return redirect()
            ->to(route('auth') . '#signin')
            ->with('verify_message', 'Account created. Please verify your email to continue.');
    }
}
