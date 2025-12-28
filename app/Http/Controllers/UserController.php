<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\UserEquipmentHistory;
use App\Models\Equipment;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $role = $request->input('role');

            $users = User::with('roles')
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('mat_ag', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->when($role, function ($query, $role) {
                    $query->whereHas('roles', function ($q) use ($role) {
                        $q->where('name', $role); // correction ici
                    });
                })
                ->paginate(4);

            $roles = Role::all();
            return view('pages.users.index', compact('users', 'roles'));
        } catch (\Throwable $e) {
            Log::error("Erreur lors du chargement des utilisateurs : " . $e->getMessage());
            return redirect()->back()->with('error', 'Impossible de charger la liste des utilisateurs.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        $roles = Role::all();
        return view('pages.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'mat_ag' => 'required|string|max:255|unique:users,mat_ag',
            'nom_ag' => 'required|string|max:255',
            'pren_ag' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'dir_ag' => 'required|string|max:255',
            'loc_ag' => 'required|string|max:255',
            'sta_ag' => 'nullable|string|max:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'mat_ag' => $validatedData['mat_ag'],
                'nom_ag' => $validatedData['nom_ag'],
                'pren_ag' => $validatedData['pren_ag'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'dir_ag' => $validatedData['dir_ag'],
                'loc_ag' => $validatedData['loc_ag'],
                'sta_ag' => $validatedData['sta_ag'],
            ]);

            $user->roles()->attach($validatedData['role_id']);

            DB::commit();

            return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'utilisateur : ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création de l\'utilisateur.');
        }
    }





    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $user = User::with('roles')->findOrFail($id);
            $userRoles = $user->roles;
            return view('pages.users.show', compact('user', 'userRoles'));
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'affichage de l'utilisateur : " . $e->getMessage());
            return redirect()->route('users.index')->with('error', 'Utilisateur introuvable.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            $roles = Role::all();
            return view('pages.users.edit', compact('user', 'roles'));
        } catch (\Exception $e) {
            Log::error("Erreur lors du chargement de l'édition de l'utilisateur : " . $e->getMessage());
            return redirect()->route('users.index')->with('error', 'Utilisateur introuvable ou erreur lors du chargement.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() === $user->id && $request->sta_ag === 'inactif') {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $rules = [
            'mat_ag' => 'required|string|max:255|unique:users,mat_ag,' . $id,
            'nom_ag' => 'required|string|max:255',
            'pren_ag' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'dir_ag' => 'required|string|max:255',
            'loc_ag' => 'required|string|max:255',
            'sta_ag' => 'nullable|string|max:8',
            'role_id' => 'required|exists:roles,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|confirmed|min:8';
        }

        $validatedData = $request->validate($rules);

        try {
            DB::beginTransaction();

            $user->update([
                'mat_ag' => $validatedData['mat_ag'],
                'nom_ag' => $validatedData['nom_ag'],
                'pren_ag' => $validatedData['pren_ag'],
                'email' => $validatedData['email'],
                'dir_ag' => $validatedData['dir_ag'],
                'loc_ag' => $validatedData['loc_ag'],
                'sta_ag' => $validatedData['sta_ag'],
            ]);

            if (!empty($validatedData['password'])) {
                $user->update([
                    'password' => bcrypt($validatedData['password']),
                ]);
            }

            $user->roles()->sync([$validatedData['role_id']]);

            DB::commit();

            return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la mise à jour de l'utilisateur : " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour de l\'utilisateur.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $user->roles()->detach();
            $user->delete();

            DB::commit();
            return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
            return redirect()->route('users.index')->with('error', 'Erreur lors de la suppression de l\'utilisateur.');
        }
    }
}
