<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Services\LogService;
use App\Events\RealTimeNotification;
use App\Events\UserConnection;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('pages.auth.login');
    }

    public function login(Request $request)
    {
        Log::info('Tentative de Connexion');

        $request->validate([
            'mat_ag' => 'required|string|max:255',
            'sta_ag' => 'nullable|string'
        ]);



        $user = User::where('mat_ag', $request->mat_ag)->first();

        if (!$user) {
            return back()->withInput()->with('error', 'Matricule incorrect.');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withInput()->with('error', 'Mot de passe incorrect.');
        }


        if ($user->sta_ag === 'inactive') {
            return back()->withInput()->with('error', 'Votre compte est désactivé. Veuillez contacter l\'administrateur.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        LogService::addLog('Connexion', 'Connexion réussie pour ' . $user->mat_ag);

        if ($user->hasRole('Super Admin')) {
            return redirect()->route('dashboard')->with('success', 'Connexion réussie');
        } elseif ($user->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('success', 'Connexion réussie');
        } else {

            return redirect()->route('mes.equipements.stats')->with('success', 'Connexion réussie.');
        }
    }

    public function showRegisterForm()
    {
        return view('pages.auth.register');
    }

    protected function create(array $data)
    {
        $user = User::create([
            'mat_ag' => $data['mat_ag'],
            'nom_ag' => $data['nom_ag'],
            'pren_ag' => $data['pren_ag'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),

            'dir_ag' => $data['dir_ag'],
            'loc_ag' => $data['loc_ag'],
            'sta_ag' => $data['sta_ag'],
        ]);

        event(new Registered($user));
        return $user;
    }


    public function register(Request $request, $id)
    {
        $request->validate([
            'mat_ag' => 'required|string|max:255|unique:users,mat_ag' . $id,
            'nom_ag' => 'required|string|max:255',
            'pren_ag' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',

            'dir_ag' => 'required|string|max:255',
            'loc_ag' => 'required|string|max:255',
            'sta_ag' => 'nullable|string|max:8',
        ]);

        // Attribuer un rôle par défaut

        try {
            $data = $request->only(['mat_ag', 'nom_ag', 'pren_ag', 'email', 'password', 'dir_ag', 'loc_ag', 'sta_ag']);
            $user = $this->create($data);
            Auth::login($user);

            return redirect()->route('verification.notice')
                ->with('success', 'Account registered successfully! Please verify your email.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred during registration. Please try again.');
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        Auth::logout();
        if ($user) {
            LogService::addLog('Tentative de Déconnexion', 'Déconnexion réussie pour ' . $user->username);
        } else {
            LogService::addLog('Déconnexion', 'Déconnexion réussie pour un utilisateur non authentifié');
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}