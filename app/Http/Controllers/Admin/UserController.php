<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:6','confirmed'],
            'role_id' => ['required','exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
        ]);

        return redirect()->route('admin.system.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.system.users.edit', compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role?->name === 'superadmin') {
        // kunci role_id agar tidak berubah
        $data['role_id'] = $user->role_id;
        }

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => [
                'required','email','max:255',
                Rule::unique('users','email')->ignore($user->id),
            ],
            'role_id' => ['required','exists:roles,id'],
            'password' => ['nullable','string','min:6','confirmed'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role_id = $data['role_id'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('admin.system.users.index')->with('success', 'User berhasil diperbarui.');
    }
    //delet
    public function destroy(User $user)
    {
    // 1) jangan bisa hapus diri sendiri
    if (auth()->id() === $user->id) {
        return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
    }

    // 2) OPTIONAL: blok hapus role superadmin
    // kalau kamu mau superadmin tidak bisa dihapus, aktifkan:
    // if ($user->role?->name === 'superadmin') {
    //     return back()->with('error', 'User superadmin tidak boleh dihapus.');
    // }

    $user->delete();

    return redirect()->route('admin.system.users.index')->with('success', 'User berhasil dihapus.');
    }

}
