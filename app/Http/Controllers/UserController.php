<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $isAdmin = $request->boolean('is_admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'is_admin' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_admin'] = $isAdmin;
        $validated['device_id'] = null;

        User::create($validated);

        $message = $isAdmin
            ? 'Admin user created successfully.'
            : 'Mobile user created. They will link their Device ID in the app after login.';

        return redirect()->route('users')->with('success', $message);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'is_admin' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $validated['is_admin'] = $request->boolean('is_admin');

        $user->update($validated);

        return redirect()->route('users')->with('success', 'User updated successfully.');
    }

    /**
     * Admin override: force-assign or clear a user's device link (support only).
     */
    public function updateDevice(Request $request, User $user)
    {
        $validated = $request->validate([
            'device_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'device_id')->ignore($user->id),
            ],
        ]);

        if ($user->is_admin) {
            $validated['device_id'] = null;
            $user->update($validated);

            return redirect()->route('users')->with('success', 'Admin users do not use device links.');
        }

        if (!empty($validated['device_id'])) {
            $deviceId = Device::normalizeId($validated['device_id']);
            if (!Device::isLinkable($deviceId)) {
                return redirect()->route('users')->with('error', 'Device ID must be registered under Devices first, or ESP32 must have reported data.');
            }
            $validated['device_id'] = $deviceId;
        }

        $user->update($validated);

        return redirect()->route('users')->with('success', 'Device link updated (admin override).');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users')->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('users')->with('success', 'User deleted successfully.');
    }

    public function apiStore(Request $request)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $isAdmin = $request->boolean('is_admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'is_admin' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_admin'] = $isAdmin;
        $validated['device_id'] = null;

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user,
        ], 201);
    }

    public function apiIndex(Request $request)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        return response()->json(User::orderBy('name')->get());
    }
}
