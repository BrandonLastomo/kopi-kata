<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function loginPage(){
        return view('admin_login');
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }
    }

    public function index(Request $request)
    {
        $query = User::query();
        $search = $request->input('search');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->orderBy('created_at', 'desc')->get();
        
        $editMode = false; // Initialize editMode
        $editUser = null;
        if ($editId = $request->input('edit')) {
            $editUser = User::find($editId);
        }

        return view('admin_users', compact('users', 'editUser', 'editMode', 'search'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => ['required', 'string', Password::min(8)],
            'role' => 'required|in:admin,user',
        ]);

        User::create($data); // Model User akan otomatis hash password

        return redirect()->route('admin_users')->with('success', 'User baru berhasil ditambahkan');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', Password::min(8)],
            'role' => 'required|in:admin,user',
        ]);

        // Update password hanya jika diisi
        if (!empty($data['password'])) {
            $user->password = $data['password']; // Model akan otomatis hash
        }
        
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Data user berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        // Mencegah admin menghapus akunnya sendiri
        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        try {
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }
}