<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // GET /users
    public function index()
    {
        $users = User::with(['role', 'userStatus'])->get();

        return response()->json(['users' => $users], 200);
    }

    // POST /users
    public function store(Request $request)
    {
        $request->validate([
            'first_name'     => ['required', 'string', 'max:255'],
            'middle_name'    => ['nullable', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', 'unique:users'],
            'password'       => ['required', 'string', 'min:8'],
            'role_id'        => ['required', 'exists:roles,id'],
            'user_status_id' => ['required', 'exists:user_statuses,id'],
        ]);

        $user = User::create([
            'first_name'     => $request->first_name,
            'middle_name'    => $request->middle_name,
            'last_name'      => $request->last_name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'role_id'        => $request->role_id,
            'user_status_id' => $request->user_status_id,
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'user'    => $user->load(['role', 'userStatus']),
        ], 201);
    }

    // GET /users/{id}
    public function show($id)
    {
        $user = User::with(['role', 'userStatus'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        return response()->json(['user' => $user], 200);
    }

    // PUT /users/{id}
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $request->validate([
            'first_name'     => ['required', 'string', 'max:255'],
            'middle_name'    => ['nullable', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email,' . $id],
            'password'       => ['nullable', 'string', 'min:8'],
            'role_id'        => ['required', 'exists:roles,id'],
            'user_status_id' => ['required', 'exists:user_statuses,id'],
        ]);

        $data = [
            'first_name'     => $request->first_name,
            'middle_name'    => $request->middle_name,
            'last_name'      => $request->last_name,
            'email'          => $request->email,
            'role_id'        => $request->role_id,
            'user_status_id' => $request->user_status_id,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully.',
            'user'    => $user->load(['role', 'userStatus']),
        ], 200);
    }

    // DELETE /users/{id}
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}
