<?php

namespace App\Http\Controllers\Access;

use App\Http\Requests\Access\StoreUserRequest;
use App\Http\Requests\Access\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display the list of users (admin only).
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Search by name or login
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('login', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('access.users.index', compact('users'));
    }

    /**
     * Show the create user form.
     */
    public function create(): View
    {
        return view('access.users.form', ['user' => null]);
    }

    /**
     * Store a new user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'login' => trim($data['login']),
            'email' => $data['email'] ?? null,
            'role' => $data['role'],
            'password' => $data['password'], // Hashed via model cast
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', "L'utilisateur \"{$user->name}\" a été créé avec succès.");
    }

    /**
     * Show the edit user form.
     */
    public function edit(User $user): View
    {
        return view('access.users.form', compact('user'));
    }

    /**
     * Update an existing user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // A checkbox that is unchecked is not sent at all, so read it as a boolean
        $isActive = $request->boolean('is_active');

        // Prevent an admin from deactivating their own account (would lock them out)
        if ($user->id === auth()->id() && !$isActive) {
            return back()
                ->withInput()
                ->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->fill([
            'name' => $data['name'],
            'login' => trim($data['login']),
            'email' => $data['email'] ?? null,
            'role' => $data['role'],
            'is_active' => $isActive,
        ]);

        // Only update password if provided
        if (!empty($data['password'])) {
            $user->password = $data['password']; // Hashed via model cast
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', "L'utilisateur \"{$user->name}\" a été modifié avec succès.");
    }

    /**
     * Delete a user.
     * RG15: Prevent deletion if the user is linked to business entities.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // RG15: Check if user has linked clients
        if ($user->clients()->exists()) {
            return back()->with('error', 'Impossible de supprimer cet utilisateur car il est lié à des clients.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', "L'utilisateur \"{$user->name}\" a été supprimé avec succès.");
    }
}