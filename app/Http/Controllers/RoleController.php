<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function show(): \Illuminate\View\View
    {
        return view('auth.choose-role');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:generator,maker,buyer,courier'],
        ]);

        $user = $request->user();
        $user->role = $data['role'];
        $user->save();

        return redirect()->route('dashboard');
    }
}
