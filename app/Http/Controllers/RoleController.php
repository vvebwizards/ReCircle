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
            'role' => ['required', 'in:generator,maker,buyer,courier,admin'],
        ]);

        $user = $request->user();
        $user->role = $data['role'];
        $user->save();

        switch ($user->role) {
            case 'generator':
                $redirectTo = route('dashboard');
                break;
            case 'maker':
                $redirectTo = route('maker.dashboard');
                break;
            case 'admin':
                $redirectTo = route('admin.dashboard');
                break;
            default:
                $redirectTo = route('home');
                break;
        }

        return redirect($redirectTo);
    }
}
