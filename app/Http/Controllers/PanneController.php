<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use App\Models\Panne;
use App\Models\Equipement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Events\PanneSignaleeEvent;
use App\Events\PanneTraiteeEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PanneSignaleeNotification;
use App\Notifications\PanneTraiteeNotification;
// Removed: use App\Notifications\PanneAnnuleeNotification; // Suppression de cette ligne

class PanneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Panne::with(['equipement', 'user']); // Eager load both relationships

        // Add your search and filter logic here
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('lib_pan', 'like', '%' . $searchTerm . '%')
                ->orWhereHas('equipement', function ($q) use ($searchTerm) {
                    $q->where('nom_eq', 'like', '%' . $searchTerm . '%')
                        ->orWhere('num_inventaire_eq', 'like', '%' . $searchTerm . '%');
                })
                ->orWhereHas('user', function ($q) use ($searchTerm) {
                    $q->where('nom_ag', 'like', '%' . $searchTerm . '%')
                        ->orWhere('pren_ag', 'like', '%' . $searchTerm . '%')
                        ->orWhere('mat_ag', 'like', '%' . $searchTerm . '%');
                });
        }

        if ($request->filled('status')) {
            $query->where('sta_pan', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('lib_cat', $request->input('category'));
        }

        $pannes = $query->latest()->paginate(4); // Order by latest and paginate

        return view('pages.pannes.index', compact('pannes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        // Only show available equipment or equipment already associated with the current user for reporting a new panne
        // If 'create' is only for a new signalement from the user himself, restrict to his equipment
        $equipements = Equipement::where('user_id', Auth::id())->get();
        return view('pages.pannes.create', compact('users', 'equipements')); // Corrected view path if needed
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'lib_pan' => 'required|string|max:255',
            'lib_cat' => 'nullable|string|in:Materielle,Logicielle',
            'diag_pan' => 'nullable|string|max:255',
            'action_pan' => 'nullable|string|max:255',
            'equipement_id' => [
                'required',
                'integer',
                Rule::exists('equipements', 'id')->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }),
            ],
        ]);

        DB::beginTransaction();
        try {
            $panne = Panne::create([
                'lib_pan' => $validated['lib_pan'],
                'lib_cat' => $validated['lib_cat'] ?? null,
                'sta_pan' => 'En attente',
                'diag_pan' => $validated['diag_pan'] ?? null,
                'action_pan' => $validated['diag_pan'] ?? null,
                'date_signa' => now(),
                'user_id' => $user->id,
                'user2_id' => $user->id, // L'auteur est aussi le signaleur par défaut
                'equipement_id' => $validated['equipement_id']
            ]);

            // Notifier tous les Super Admins
            $admins = User::whereHas('roles', function ($q) {
                $q->where('name', 'Super Admin');
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new PanneSignaleeNotification($panne));
                // Dispatch event for Super Admins
                event(new \App\Events\PanneSignaleeEvent(
                    $panne,
                    $user->nom_ag . ' ' . $user->pren_ag, // Nom du signaleur
                    $admin->id // ID de l'utilisateur à notifier (l'admin)
                ));
            }

            DB::commit();
            return redirect()->back()->with('success', 'Panne signalée avec succès.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur signalement panne : " . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Erreur lors du signalement.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $panne = Panne::with(['equipement', 'user'])->findOrFail($id);
            return view('pages.pannes.show', compact('panne'));
        } catch (\Exception $e) {
            Log::error("Error showing panne {$id}: " . $e->getMessage());
            return redirect()->route('pannes.index')
                ->with('error', 'Failed to show panne details.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $panne = Panne::with(['equipement', 'user', 'auteur'])->findOrFail($id);
            $users = User::all(); // All users might be needed for admin to re-assign if allowed
            $equipements = Equipement::all(); // All equipment might be needed for admin to re-assign if allowed
            return view('pages.pannes.edit', compact('panne', 'users', 'equipements'));
        } catch (\Exception $e) {
            Log::error("Error editing panne {$id}: " . $e->getMessage());
            return redirect()->route('pannes.index')
                ->with('error', 'Failed to load edit form.');
        }
    }

    /**
     * Update the specified resource in storage.
     * This method seems to be for initial diagnostic/taking a panne 'En cours'.
     */
    public function update(Request $request, Panne $panne)
    {
        $auteur = Auth::user();

        // Validate the request data
        $validated = $request->validate([
            'diag_pan' => 'nullable|string|max:255',
            'action_pan' => 'nullable|string|max:255',
            'lib_cat' => 'nullable|string|max:255',
            'status' => 'nullable|in:Annulée' // Ajout pour gérer l'annulation
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $panne->sta_pan;
            $newStatus = $panne->sta_pan;

            $updateData = [
                'diag_pan' => $validated['diag_pan'] ?? $panne->diag_pan,
                'action_pan' => $validated['action_pan'] ?? $panne->action_pan,
                'lib_cat' => $validated['lib_cat'] ?? $panne->lib_cat,
                'user2_id' => $auteur->id,
            ];

            // Cas 1: Annulation explicite
            if (isset($validated['status']) && $validated['status'] === 'Annulée') {
                $newStatus = 'Annulée';
                $updateData['sta_pan'] = $newStatus;
                $updateData['date_an'] = now();
                $equipmentState = 'Bon';
            }
            // Cas 2: Résolution (action_pan fournie)
            elseif (!empty($validated['action_pan'])) {
                $newStatus = 'Résolue';
                $updateData['sta_pan'] = $newStatus;
                $updateData['date_rsl'] = now();
                $equipmentState = 'Bon';
            }
            // Cas 3: Prise en charge (diagnostic ou catégorie fourni)
            elseif ($oldStatus === 'En attente' && (isset($validated['diag_pan']) || isset($validated['lib_cat']))) {
                $newStatus = 'En cours';
                $updateData['sta_pan'] = $newStatus;
                $updateData['date_dt'] = now();
                $equipmentState = 'En panne';
            }
            // Cas par défaut
            else {
                $equipmentState = $panne->equipement->etat_eq ?? 'En panne';
            }

            // Update the panne
            $panne->update($updateData);

            // Update equipment status
            if ($panne->equipement && isset($equipmentState)) {
                $panne->equipement->update(['etat_eq' => $equipmentState]);
            }

            // Notifications si statut changé
            if ($oldStatus !== $newStatus) {
                if ($panne->user) {
                    // Utilise toujours PanneTraiteeNotification, car elle gère déjà tous les statuts
                    $panne->user->notify(new PanneTraiteeNotification($panne, $auteur, $newStatus));
                    event(new PanneTraiteeEvent($panne, $auteur, $newStatus, $panne->user->id));
                } else {
                    Log::warning("Panne {$panne->id} has no associated user");
                }
            }

            DB::commit();

            // Messages de retour cohérents
            if ($newStatus === 'Résolue') {
                return back()->with('success', 'Panne résolue et équipement marqué comme bon.');
            } elseif ($newStatus === 'Annulée') {
                return back()->with('success', 'Panne annulée avec succès.');
            } elseif ($newStatus === 'En cours') {
                return back()->with('success', 'Panne prise en charge.');
            }

            return back()->with('success', 'Panne mise à jour.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur mise à jour panne {$panne->id}: " . $e->getMessage());
            return back()->with('error', 'Erreur lors de la mise à jour.');
        }
    }

    /**
     * Update only the status of the specified resource.
     * This method handles status changes (e.g., to Resolved, Rejected, etc.).
     */
    public function updateStatus(Request $request, Panne $panne)
    {
        $auteur = Auth::user();

        // 1. Validate the request data
        $validated = $request->validate([
            'status' => 'required|in:En attente,En cours,Résolue,Annulée',
            'diagnostic' => 'nullable|string|max:500',
            'action' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $panne->sta_pan;
            $newStatus = $validated['status'];

            $updatePanneData = [
                'sta_pan' => $newStatus,
                'user2_id' => $auteur->id,
            ];

            // Only update diagnostic if provided, otherwise retain existing
            if (isset($validated['diagnostic'])) {
                $updatePanneData['diag_pan'] = $validated['diagnostic'];
            }

            if (isset($validated['action'])) {
                $updatePanneData['action_pan'] = $validated['action'];
            }
            $equipmentState = null;

            switch ($newStatus) {
                case 'En cours':
                    if ($oldStatus !== 'En cours') {
                        $updatePanneData['date_dt'] = now();
                    }
                    $equipmentState = 'En panne'; // Equipment is still "En panne" while being repaired
                    break;

                case 'Résolue':
                    // Make 'action' required when status is 'Résolue'
                    if (empty($validated['action'])) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'action' => 'Le champ "Action Effectuée" est requis pour la résolution de la panne.',
                        ]);
                    }
                    $updatePanneData['date_rsl'] = now();
                    $equipmentState = 'Bon';
                    break;

                case 'Annulée':
                    $updatePanneData['date_an'] = now();
                    $equipmentState = 'Bon';
                    break;
            }
            // 3. Update the Panne record
            $panne->update($updatePanneData);

            // 4. Update the associated Equipement's state
            if ($equipmentState !== null && $panne->equipement) { // Ensure there is an associated equipment
                $panne->equipement->update(['etat_eq' => $equipmentState]);
            }

            // Notifications si statut changé
            if ($oldStatus !== $newStatus) {
                if ($panne->user) {
                    // Utilise toujours PanneTraiteeNotification, car elle gère déjà tous les statuts
                    $panne->user->notify(new PanneTraiteeNotification($panne, $auteur, $newStatus));
                    event(new PanneTraiteeEvent($panne, $auteur, $newStatus, $panne->user->id));
                } else {
                    Log::warning("Panne {$panne->id} has no associated user");
                }
            }

            DB::commit();

            return redirect()->route('pannes.index')->with('success', 'Statut de la panne mis à jour avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error("Validation Error updating panne status {$panne->id}: " . $e->getMessage(), ['errors' => $e->errors()]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating panne status {$panne->id}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Échec de la mise à jour du statut de la panne.');
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $panne = Panne::findOrFail($id);
            $panne->delete();
            DB::commit();
            return redirect()->route('pannes.index')->with('success', 'Panne supprimée avec succès.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur lors de la suppression de la panne {$id}: " . $e->getMessage());
            return redirect()->route('pannes.index')->with('error', 'Erreur lors de la suppression de la panne.');
        }
    }
}