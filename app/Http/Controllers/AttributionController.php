<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Equipement;
use App\Models\HistoAttri;
use App\Models\Attribution;
use App\Services\LogService; // Assurez-vous que ce service existe
use Illuminate\Http\Request;
use App\Exports\AttributionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewAttributionNotification;
use App\Notifications\AttributionRemovedNotification;
use App\Events\NewAttributionEvent;
use App\Events\AttributionRemovedEvent;

class AttributionController extends Controller
{
    /**
     * Exporte les attributions filtrées vers un fichier Excel.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $query = Attribution::with(['user', 'equipements.categorieEquipement'])
            ->latest();

        // Filtre par matricule
        if ($request->filled('matricule')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('mat_ag', 'like', '%' . $request->matricule . '%');
            });
        }

        // Filtre par numéro d'inventaire
        if ($request->filled('num_inventaire')) {
            $query->whereHas('equipements', function ($q) use ($request) {
                $q->where('num_inventaire_eq', 'like', '%' . $request->num_inventaire . '%');
            });
        }

        // Filtre par direction
        if ($request->filled('direction')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('dir_ag', 'like', '%' . $request->direction . '%');
            });
        }

        $attributions = $query->get();

        return Excel::download(new AttributionsExport($attributions), 'attributions_' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Affiche l'historique des attributions avec des options de filtrage.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function logs(Request $request)
    {
        // 1. Récupérer et assainir les paramètres d'entrée
        $searchTerm = $request->input('q');
        $beneficiaireMatAg = $request->input('beneficiaire_mat_ag');
        $auteurMatAg = $request->input('auteur_mat_ag');
        $from = $request->input('from');
        $to = $request->input('to');
        $action = $request->input('action');
        $numInventaire = $request->input('num_inventaire');

        // 2. Initialiser la requête avec chargement anticipé
        $query = HistoAttri::with(['auteur', 'beneficiaire']);

        // 3. Appliquer les filtres de recherche dynamiquement

        // Filtre de recherche général (searchTerm)
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                // Recherche par bénéficiaire (nom, prénom)
                $q->orWhereHas('beneficiaire', function ($subQuery) use ($searchTerm) {
                    $subQuery->where('nom_ag', 'like', '%' . $searchTerm . '%')
                        ->orWhere('pren_ag', 'like', '%' . $searchTerm . '%');
                });

                // Recherche par auteur (nom, prénom)
                $q->orWhereHas('auteur', function ($subQuery) use ($searchTerm) {
                    $subQuery->where('nom_ag', 'like', '%' . $searchTerm . '%')
                        ->orWhere('pren_ag', 'like', '%' . $searchTerm . '%');
                });

                // Recherche par type d'action
                $q->orWhere('action_type', 'like', '%' . $searchTerm . '%');

                // Recherche par numéro d'inventaire ou numéro de série d'équipement dans les colonnes JSON
                $matchingEquipementIdsForSearchTerm = Equipement::where('num_inventaire_eq', 'like', '%' . $searchTerm . '%')
                    ->orWhere('num_serie_eq', 'like', '%' . $searchTerm . '%')
                    ->pluck('id')
                    ->toArray();

                if (!empty($matchingEquipementIdsForSearchTerm)) {
                    $q->orWhere(function ($subQ) use ($matchingEquipementIdsForSearchTerm) {
                        foreach ($matchingEquipementIdsForSearchTerm as $eqId) {
                            $subQ->orWhereJsonContains('equipements', $eqId)
                                ->orWhereJsonContains('equipements_ajoutes', $eqId)
                                ->orWhereJsonContains('equipements_retires', $eqId);
                        }
                    });
                }
            });
        }

        // Filtre par matricule bénéficiaire
        if ($beneficiaireMatAg) {
            $query->whereHas('beneficiaire', function ($q) use ($beneficiaireMatAg) {
                $q->where('mat_ag', 'like', '%' . $beneficiaireMatAg . '%');
            });
        }

        // Filtre par matricule auteur
        if ($auteurMatAg) {
            $query->whereHas('auteur', function ($q) use ($auteurMatAg) {
                $q->where('mat_ag', 'like', '%' . $auteurMatAg . '%');
            });
        }

        // Nouveau filtre pour le numéro d'inventaire d'équipement
        if ($numInventaire) {
            $matchingEquipementIds = Equipement::where('num_inventaire_eq', 'like', "%{$numInventaire}%")->pluck('id')->toArray();

            if (!empty($matchingEquipementIds)) {
                $query->where(function ($q) use ($matchingEquipementIds) {
                    foreach ($matchingEquipementIds as $eqId) {
                        $q->orWhereJsonContains('equipements', $eqId)
                            ->orWhereJsonContains('equipements_ajoutes', $eqId)
                            ->orWhereJsonContains('equipements_retires', $eqId);
                    }
                });
            } else {
                // Si aucun équipement ne correspond, s'assurer qu'aucun historique n'est retourné
                $query->whereRaw('0 = 1');
            }
        }

        // 4. Appliquer les filtres de plage de dates (created_at)
        if ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        // 5. Appliquer le filtre de type d'action
        if ($action) {
            $query->where('action_type', $action);
        }

        // 6. Exécuter la requête, ordonner les résultats et paginer
        $logs = $query->latest()->paginate(10);

        // 7. Retourner la vue avec les données nécessaires
        return view('pages.attributions.log', compact(
            'logs',
            'searchTerm',
            'from',
            'to',
            'action',
            'auteurMatAg',
            'beneficiaireMatAg',
            'numInventaire'
        ));
    }

    /**
     * Affiche une liste paginée des attributions actives.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {

        try {
            $numeroMatriculeFilter = $request->input('matricule');
            $numInventaireFilter = $request->input('num_inventaire');

            $query = Attribution::with(['user', 'equipements.categorieEquipement'])
                ->whereNull('attributions.date_retrait'); // Filtrer uniquement les attributions non retirées

            if ($numeroMatriculeFilter) {
                $query->whereHas('user', function ($q) use ($numeroMatriculeFilter) {
                    $q->where('mat_ag', 'like', "%{$numeroMatriculeFilter}%");
                });
            }

            if ($numInventaireFilter) {
                $query->whereHas('equipements', function ($q) use ($numInventaireFilter) {
                    $q->where('num_inventaire_eq', 'like', "%{$numInventaireFilter}%");
                });
            }

            // Order by user matricule directly if possible, or join if needed for performance on large datasets
            // For simplicity and to avoid issues with groupBy on paginated results, we'll paginate directly.
            $attributions = $query
                ->join('users', 'attributions.user_id', '=', 'users.id') // Join to order by user's matricule
                ->orderBy('users.mat_ag', 'ASC')
                ->select('attributions.*') // Select only attribution columns to avoid ambiguity
                ->paginate(10);

            return view('pages.attributions.index', compact('attributions', 'numeroMatriculeFilter', 'numInventaireFilter'));
        } catch (\Exception $e) {
            Log::error("Error loading attributions: " . $e->getMessage());
            return redirect()->back()->with('error', 'Échec du chargement des attributions.');
        }
    }
    /**
     * Affiche le formulaire de création d'une nouvelle attribution.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        try {

            $usersWithAttributedEquipmentsIds = Equipement::whereNotNull('user_id')
                ->pluck('user_id')
                ->unique();

            // Sélectionner uniquement les utilisateurs qui n'ont AUCUN équipement attribué
            $users = User::select('id', 'mat_ag', 'pren_ag')
                ->whereNotIn('id', $usersWithAttributedEquipmentsIds)
                ->orderBy('mat_ag')
                ->get();


            $equipements = Equipement::with('categorieEquipement')
                ->whereNull('user_id')
                ->where('statut_eq', 'disponible')
                ->select('id', 'nom_eq', 'num_serie_eq', 'statut_eq', 'num_inventaire_eq', 'categorie_equipement_id')
                ->get();

            return view('pages.attributions.create', compact('users', 'equipements'));
        } catch (\Exception $e) {
            Log::error("Error preparing creation form: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Échec du chargement des données du formulaire.');
        }
    }

    /**
     * Stocke une nouvelle attribution dans la base de données.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date_attribution' => 'required|date',
            'date_retrait' => 'nullable|date|after_or_equal:date_attribution',
            'equipements' => 'required|array|min:1',
            'equipements.*' => 'exists:equipements,id',
        ]);

        try {

            DB::beginTransaction();
            $currentAuthUserId = Auth::id();
            $currentAuthUser = Auth::user();
            $beneficiaryUser = User::findOrFail($validatedData['user_id']);

            $attribution = Attribution::create([
                'user_id' => $validatedData['user_id'],
                'date_attribution' => $validatedData['date_attribution'],
                'date_retrait' => $validatedData['date_retrait'] ?? null,
            ]);

            $attribution->equipements()->sync($validatedData['equipements']);

            $attributedEquipementModels = Equipement::whereIn('id', $validatedData['equipements'])->get();

            foreach ($attributedEquipementModels as $equipement) {
                $oldStatus = $equipement->statut_eq;
                $oldState = $equipement->etat_eq;

                $equipement->update([
                    'etat_eq' => 'en service',
                    'statut_eq' => 'attribué',
                    'user_id' => $validatedData['user_id'],
                ]);

                $equipement->historique()->create([
                    'action' => 'Attribution',
                    'details' => 'L\'équipement ' . $equipement->num_inventaire_eq . ' a été attribué à ' . $beneficiaryUser->mat_ag . '.', // Use full_name if available
                    'old_status' => $oldStatus,
                    'new_status' => $equipement->statut_eq,
                    'old_state' => $oldState,
                    'new_state' => $equipement->etat_eq,
                    'user_id' => $currentAuthUserId,
                ]);
            }

            // // Log historique de l'attribution globale
            // HistoAttri::create([
            //     'action_type' => 'Création',
            //     'attribution_id' => $attribution->id,
            //     'user_id' => $currentAuthUserId,
            //     'user2_id' => $validatedData['user_id'],
            //     'equipements_ajoutes' => json_encode($validatedData['equipements']),
            //     'equipements_retires' => json_encode([]), // Nothing removed on creation
            //     'equipements_gardes' => json_encode([]), // Nothing kept on creation
            // ]);

            $beneficiaryUser->notify(new NewAttributionNotification(
                $currentAuthUser->nom_ag . ' ' . $currentAuthUser->pren_ag,
                $attributedEquipementModels,
                'nouvelle attribution'
            ));

            LogService::attributionLog(
                'Création',
                $currentAuthUserId,
                $validatedData['user_id'],
                $attribution->id,
                $validatedData['equipements'], // All are added on creation
                [], // No removed equipments
                []  // No kept equipments
            );

            // --- C'EST ICI QU'ON DÉCLENCHE L' ÉVÉNEMENT PUSHER ! ---
            // Le nom de l'utilisateur qui effectue l'attribution (celui connecté)
            $userNameForEvent = $currentAuthUser->mat_ag;

            event(new NewAttributionEvent(
                $userNameForEvent,
                $attributedEquipementModels,
                $beneficiaryUser->id, // ID de l'utilisateur concerné// Passez la collection des modèles Equipement
                'nouvelle attribution'
            ));

            DB::commit();

            return redirect()->route('attributions.index')
                ->with('success', 'Attribution créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la création de l'attribution: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'attribution: ' . $e->getMessage());
        }
    }
    /**
     * Affiche les détails d'une attribution spécifique.
     *
     * @param string $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(string $id)
    {
        try {
            $attribution = Attribution::with(['user', 'equipements.categorieEquipement'])->findOrFail($id);
            // Fetch all users for the dropdown, including the current beneficiary.
            $users = User::select('id', 'mat_ag', 'pren_ag')->orderBy('mat_ag')->get();
            return view('pages.attributions.show', compact('attribution', 'users'));
        } catch (\Exception $e) {
            Log::error("Error showing attribution {$id}: " . $e->getMessage());
            return redirect()->route('attributions.index')
                ->with('error', 'Échec de l\'affichage des détails de l\'attribution.');
        }
    }
    /**
     * Affiche le formulaire d'édition d'une attribution.
     *
     * @param string $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(string $id)
    {
        try {
            $attribution = Attribution::with(['equipements', 'user'])->findOrFail($id);
            // Equipements disponibles: ceux qui ne sont pas attribués (user_id est null)
            // OU ceux qui font déjà partie de l'attribution actuelle.
            $availableEquipements = Equipement::where(function ($query) use ($attribution) {
                $query->whereNull('user_id')
                    ->orWhereIn('id', $attribution->equipements->pluck('id'));
            })
                ->where('statut_eq', '!=', 'réformé') // Exclure les équipements réformés
                ->select('id', 'nom_eq', 'num_serie_eq', 'statut_eq', 'num_inventaire_eq') // Ajout de num_inventaire_eq
                ->get();

            $equipements = Equipement::with('categorieEquipement')
                ->whereNull('user_id')
                ->where('statut_eq', 'disponible')
                ->select('id', 'nom_eq', 'num_serie_eq', 'statut_eq', 'num_inventaire_eq', 'categorie_equipement_id') // Ensure categorie_equipement_id is selected
                ->get();



            $users = User::select('id', 'mat_ag', 'pren_ag')->orderBy('mat_ag')->get();

            return view('pages.attributions.edit', [
                'attribution' => $attribution,
                'users' => $users,
                'available_equipements' => $availableEquipements,
                'equipements' => $equipements
            ]);
        } catch (\Exception $e) {
            Log::error("Error editing attribution {$id}: " . $e->getMessage());
            return redirect()->route('attributions.index')
                ->with('error', 'Échec du chargement du formulaire de modification.');
        }
    }

    /**
     * Met à jour une attribution existante dans la base de données.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date_attribution' => 'required|date',
            'date_retrait' => 'nullable|date|after_or_equal:date_attribution',
            'equipements' => 'required|array|min:1',
            'equipements.*' => 'exists:equipements,id',
        ]);

        try {
            DB::beginTransaction();
            $currentAuthUserId = Auth::id();
            $currentAuthUser = Auth::user();
            $attribution = Attribution::findOrFail($id);

            $oldAttributionUserId = $attribution->user_id;
            $oldAttributionDateAttribution = $attribution->date_attribution;
            $oldAttributionDateRetrait = $attribution->date_retrait;

            $oldEquipementIds = $attribution->equipements->pluck('id')->toArray();
            $newEquipementIds = $validatedData['equipements'];

            $addedEquipementIds = array_diff($newEquipementIds, $oldEquipementIds);
            $removedEquipementIds = array_diff($oldEquipementIds, $newEquipementIds);
            $keptEquipementIds = array_intersect($oldEquipementIds, $newEquipementIds);

            // Update the attribution record first
            $attribution->update([
                'user_id' => $validatedData['user_id'],
                'date_attribution' => $validatedData['date_attribution'],
                'date_retrait' => $validatedData['date_retrait'] ?? null,
            ]);

            // Sync equipments to update pivot table
            $attribution->equipements()->sync($newEquipementIds);

            $newBeneficiaryUser = User::findOrFail($validatedData['user_id']);
            $oldBeneficiaryUser = ($oldAttributionUserId !== $validatedData['user_id'])
                ? User::find($oldAttributionUserId)
                : $newBeneficiaryUser;

            // --- Logique et Notifications pour les équipements RETIRÉS ---

            $removedEquipementModels = Equipement::whereIn('id', $removedEquipementIds)->get();
            foreach ($removedEquipementModels as $equipement) {
                $oldStatus = $equipement->statut_eq;
                $oldState = $equipement->etat_eq;

                //mise à jour de l'etat du statut de(s) équipement(s) retiré(s)

                $equipement->update([
                    'etat_eq' => 'Bon',
                    'statut_eq' => 'disponible',
                    'user_id' => null
                ]);
                //mise à jour de l'hisorique d'attribution de(s) équipement(s) retiré(s)

                $equipement->historique()->create([
                    'action' => 'Rétrait d\'attribution',
                    'details' => "L'équipement " . $equipement->num_inventaire_eq . "  a été retiré à " . $oldBeneficiaryUser->mat_ag . '.',
                    'old_status' => $oldStatus,
                    'new_status' => $equipement->statut_eq,
                    'old_state' => $oldState,
                    'new_state' => $equipement->etat_eq,
                    'user_id' => $currentAuthUserId,
                ]);
            }
            if ($removedEquipementModels->isNotEmpty()) {
                // Notifier l'utilisateur qui détenait ces équipements
                if ($oldBeneficiaryUser) {
                    $oldBeneficiaryUser->notify(new AttributionRemovedNotification(
                        $currentAuthUser->mat_ag . ' ' . $currentAuthUser->mat_ag,
                        $removedEquipementModels,
                        'retrait machines'
                    ));

                    event(new AttributionRemovedEvent(
                        $currentAuthUser->mat_ag . ' ' . $currentAuthUser->mat_ag,
                        $removedEquipementModels,
                        $oldBeneficiaryUser->id,
                        'retrait machines'
                    ));
                }
            }

            // --- Logique et Notifications pour les équipements AJOUTÉS ---
            $addedEquipementModels = Equipement::whereIn('id', $addedEquipementIds)->get();
            foreach ($addedEquipementModels as $equipement) {
                $oldStatus = $equipement->statut_eq;
                $oldState = $equipement->etat_eq;

                //mise à jour de l'etat du statut de(s) équipement(s) ajouté(s)

                $equipement->update([
                    'etat_eq' => 'en service',
                    'statut_eq' => 'attribué',
                    'user_id' => $validatedData['user_id'],
                ]);

                //mise à jour de l'historique d'attribution de(s) équipement(s) ajouté(s)
                $equipement->historique()->create([
                    'action' => 'Nouvelle Attribution (modification)',
                    'details' => "L'équipement " . $equipement->num_inventaire_eq .  " a été attribuer à " . $newBeneficiaryUser->mat_ag . '.',
                    'old_status' => $oldStatus,
                    'new_status' => $equipement->statut_eq,
                    'old_state' => $oldState,
                    'new_state' => $equipement->etat_eq,
                    'user_id' => $currentAuthUserId,
                ]);
            }



            if ($addedEquipementModels->isNotEmpty()) {
                // Notifier l'utilisateur qui détient maintenant ces équipements
                $newBeneficiaryUser->notify(new NewAttributionNotification(
                    $currentAuthUser->nom_ag . ' ' . $currentAuthUser->pren_ag,
                    $addedEquipementModels,
                    'ajout machines'
                ));

                // Événement temps réel
                event(new NewAttributionEvent(
                    $currentAuthUser->nom_ag . ' ' . $currentAuthUser->pren_ag,
                    $addedEquipementModels,
                    'ajout machines'
                ));
            }




            // Enregistrement de l'historique de l'attribution globale
            HistoAttri::create([
                'action_type' => 'Modification',
                'attribution_id' => $attribution->id,
                'user_id' => $currentAuthUserId,
                'user2_id' => $validatedData['user_id'],
                'equipements_ajoutes' => json_encode($addedEquipementIds),
                'equipements_retires' => json_encode($removedEquipementIds),
                'equipements_gardes' => json_encode($keptEquipementIds),
            ]);


            // Log de l'attribution pour garder une trace
            LogService::attributionLog(
                'Modification',
                $currentAuthUserId,
                $validatedData['user_id'],
                $attribution->id,
                $newEquipementIds,
                $addedEquipementIds,
                $removedEquipementIds
            );
            DB::commit();

            return redirect()->route('attributions.index')
                ->with('success', 'Attribution mise à jour avec succès.')
                ->with('updated_attribution_id', $attribution->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la mise à jour de l'attribution {$id}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour de l\'attribution: ' . $e->getMessage());
        }
    }

    /**
     * Retire une attribution et met à jour les équipements associés.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $currentAuthUser = Auth::user();

            $attribution = Attribution::findOrFail($id);
            $beneficiaryUser = User::findOrFail($attribution->user_id);

            $removedEquipementModels = $attribution->equipements;
            $removedEquipementIds = $removedEquipementModels->pluck('id')->toArray();

            // Mettre à jour la date de retrait de l'attribution
            $attribution->update(['date_retrait' => now()]);

            // Mettre à jour le statut des équipements associés
            foreach ($removedEquipementModels as $equipement) {
                $oldStatus = $equipement->statut_eq;
                $oldState = $equipement->etat_eq;

                $equipement->update([
                    'etat_eq' => 'Bon',
                    'statut_eq' => 'disponible',
                    'user_id' => null
                ]);
                // Enregistrer l'historique pour chaque équipement retiré
                $equipement->historique()->create([
                    'action' => 'Retrait Attribution Complète',
                    'details' => "L'équipement " . $equipement->num_inventaire_eq . "a été retiré à  " . $beneficiaryUser->mat_ag . '.',
                    'old_status' => $oldStatus, // L'ancien statut AVANT le retrait
                    'new_status' => $equipement->statut_eq, // Le nouveau statut APRÈS le retrait
                    'old_state' => $oldState, // L'ancien état
                    'new_state' => $equipement->etat_eq, // Le nouvel état après le retrait
                    'user_id' => $currentAuthUser->id,
                ]);
            }

            // Notification pour le retrait complet de l'attribution

            $beneficiaryUser->notify(new AttributionRemovedNotification(
                $currentAuthUser->nom_ag . ' ' . $currentAuthUser->pren_ag,
                $removedEquipementModels,
                'retrait attribution'
            ));

            // Enregistrer dans la retrait d'attribution dans la table d'historique d'attribution

            HistoAttri::create([
                'action_type' => 'Retrait',
                'attribution_id' => $attribution->id,
                'user_id' => $currentAuthUser->id,
                'user2_id' => $beneficiaryUser->id,
                'equipements_ajoutes' => json_encode([]),
                'equipements_retires' => json_encode($removedEquipementIds),
                'equipements_gardes' => json_encode([]),
            ]);
            // Lors du retrait d'une attributio
            event(new AttributionRemovedEvent(
                $currentAuthUser->nom_ag . ' ' . $currentAuthUser->pren_ag,
                $removedEquipementModels,
                $beneficiaryUser->id,
                'retrait attribution'
            ));

            // LogService::attributionLog(
            //     'Retrait',
            //     $currentAuthUser->id,
            //     $attribution->user_id,
            //     $attribution->id,
            //     [], // No current equipments left
            //     [], // No added equipments
            //     $removedEquipementIds // All previous equipments are removed
            // );

            DB::commit();

            return redirect()->route('attributions.index')
                ->with('success', 'Attribution retirée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error withdrawing attribution {$id}: " . $e->getMessage());
            return redirect()->route('attributions.index')
                ->with('error', 'Erreur lors de l\'opération: ' . $e->getMessage());
        }
    }
}