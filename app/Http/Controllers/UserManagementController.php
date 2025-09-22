<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserManagementController extends Controller
{
   public function index(Request $request)
{
    $query = User::query();
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
    $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
    return view('admin.usersDashboard', compact('users'));
}


}
