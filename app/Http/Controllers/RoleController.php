<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
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

        $user->role = UserRole::from($data['role']);
        $user->save();

        switch ($user->role) {
            case UserRole::GENERATOR:
                $redirectTo = route('dashboard');
                break;
            case UserRole::MAKER:
                $redirectTo = route('maker.dashboard');
                break;
            case UserRole::ADMIN:
                $redirectTo = route('admin.dashboard');
                break;
            default:
                $redirectTo = route('home');
                break;
        }

        return redirect($redirectTo);
    }
}
