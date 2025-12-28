<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Panne;
use App\Models\Equipement;
use App\Models\HistoAttri;
use App\Models\Attribution;
use Illuminate\Http\Request;
use App\Imports\EquipementImport;
use App\Exports\EquipementsExport;
use Illuminate\Support\Facades\DB;
use App\Models\CategorieEquipement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Notifications\NewAttributionNotification; // Importation ajoutée
use App\Notifications\AttributionRemovedNotification; // Importation ajoutée
use Carbon\Carbon; // Assurez-vous que Carbon est importé si utilisé pour les dates

class EquipementController extends Controller
{
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);

        try {
            Excel::import(new EquipementImport, $request->file('file'));
            return redirect()->route('equipements.index')->with('success', 'Importation réussie.');
        } catch (\Exception $e) {
            Log::error("Erreur d'importation : " . $e->getMessage());
            return redirect()->back()->with('error', "Erreur lors de l'importation : " . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $query = Equipement::query();

        // Apply filters based on request parameters
        if ($request->filled('numero')) {
            $query->where('num_inventaire_eq', 'like', '%' . $request->input('numero') . '%');
        }

        if ($request->filled('etat_eq')) {
            $query->where('etat_eq', $request->input('etat_eq'));
        }

        if ($request->filled('statut_eq')) {
            $query->where('statut_eq', $request->input('statut_eq'));
        }

        if ($request->filled('direction')) {
            // Assuming 'user' is the relationship and 'dir_ag' is on the related user model
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('dir_ag', 'like', '%' . $request->input('direction') . '%');
            });
        }

        $equipements = $query->get();

        return Excel::download(new EquipementsExport($equipements), 'equipements.xlsx');
    }

    public function mesEquipements()
    {
        $user = Auth::user();
        $equipements = Equipement::with(['categorieEquipement', 'pannes' => fn($q) => $q->latest('created_at')])
            ->where('user_id', $user->id)->get();

        return view('dashboard1', compact('equipements', 'user'));
    }


    public function statsMesEquipements()
    {
        try {
            // Récupère l'utilisateur connecté
            $user = Auth::user();


            if (!$user || !isset($user->id)) {
                return redirect()->route('login')->with('error', 'Veuillez vous connecter pour voir vos statistiques.');
            }

            $userEquipementIds = DB::table('equipements')
                ->where('user_id', $user->id)
                ->pluck('id');

            // --- KPI Cards ---

            // Total des équipements de l'utilisateur
            $totalEquipements = DB::table('equipements')
                ->where('user_id', $user->id)
                ->count();


            $equipementsEnPanne = DB::table('pannes')
                ->whereIn('equipement_id', $userEquipementIds) // On filtre par les équipements de l'utilisateur
                ->whereIn('sta_pan', ['En cours', 'En attente'])
                ->distinct('equipement_id') // On compte chaque équipement une seule fois
                ->count();



            $equipementsParCategorieRaw = DB::table('equipements')
                ->leftJoin('categorie_equipements', 'equipements.categorie_equipement_id', '=', 'categorie_equipements.id')
                ->where('equipements.user_id', $user->id)
                ->select(
                    DB::raw('COALESCE(categorie_equipements.lib_cat, "Inconnu") as category_name'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('category_name')
                ->get();
            $equipementsParCategorie = [];
            foreach ($equipementsParCategorieRaw as $item) {
                $equipementsParCategorie[$item->category_name] = $item->count;
            }

            $allPannesForUserEquipments = DB::table('pannes')
                ->whereIn('equipement_id', $userEquipementIds)
                ->whereNotNull('sta_pan') // On ne s'intéresse qu'aux pannes avec un statut
                ->orderBy('equipement_id')
                ->orderBy('created_at', 'desc')
                ->get();

            $latestPannesPerEquipment = [];
            foreach ($allPannesForUserEquipments as $panne) {
                if (!isset($latestPannesPerEquipment[$panne->equipement_id])) {
                    $latestPannesPerEquipment[$panne->equipement_id] = $panne;
                }
            }

            $pannesParStatut = [];
            foreach ($latestPannesPerEquipment as $panne) {
                $status = $panne->sta_pan ?? 'Inconnu';
                if (!isset($pannesParStatut[$status])) {
                    $pannesParStatut[$status] = 0;
                }
                $pannesParStatut[$status]++;
            }

            $diagnosticsCountsRaw = Panne::whereIn('equipement_id', $userEquipementIds)
                ->whereNotNull('diag_pan') // Only count entries where a diagnostic was actually provided
                ->select('diag_pan', DB::raw('COUNT(*) as count'))
                ->groupBy('diag_pan')
                ->orderByDesc('count') // Order by most frequent diagnostic
                ->get();

            $diagnosticsCounts = []; // Initialize as an empty array
            foreach ($diagnosticsCountsRaw as $item) {
                $diagnosticsCounts[$item->diag_pan] = $item->count;
            }


            // Répartition par état d'équipement (ex: Neuf, Bon, Usé)
            $equipementsParEtatRaw = DB::table('equipements')
                ->where('user_id', $user->id)
                ->select('etat_eq', DB::raw('COUNT(*) as count'))
                ->groupBy('etat_eq')
                ->get();
            $equipementsParEtat = [];
            foreach ($equipementsParEtatRaw as $item) {
                $equipementsParEtat[$item->etat_eq] = $item->count;
            }


            // Répartition par statut d'équipement (ex: Actif, Inactif)
            $equipementsParStatutRaw = DB::table('equipements')
                ->where('user_id', $user->id)
                ->select('statut_eq', DB::raw('COUNT(*) as count'))
                ->groupBy('statut_eq')
                ->get();
            $equipementsParStatut = [];
            foreach ($equipementsParStatutRaw as $item) {
                $equipementsParStatut[$item->statut_eq] = $item->count;
            }

            // --- Préparation des données pour les graphiques (Line Charts - Évolution) ---

            // Pour "Équipements par catégorie (Évolution)"
            // Objectif : Compter les équipements ajoutés par mois/catégorie
            $categoriesEvolutionDataRaw = DB::table('equipements')
                ->leftJoin('categorie_equipements', 'equipements.categorie_equipement_id', '=', 'categorie_equipements.id')
                ->where('equipements.user_id', $user->id)
                ->select(
                    DB::raw('DATE_FORMAT(equipements.created_at, "%Y-%m") as month'),
                    DB::raw('COALESCE(categorie_equipements.lib_cat, "Inconnu") as category_name'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('month', 'category_name')
                ->orderBy('month')
                ->get();

            // Collecter tous les mois uniques pour l'axe X
            $categoriesLabels = $categoriesEvolutionDataRaw->pluck('month')->unique()->sort()->values()->all();
            // Collecter toutes les catégories uniques
            $allCategoriesForEvolution = $categoriesEvolutionDataRaw->pluck('category_name')->unique()->values()->all();

            // Préparer les séries pour ApexCharts (une série par catégorie)
            $categoriesData = [];
            foreach ($allCategoriesForEvolution as $categoryName) {
                $dataForThisCategory = [];
                foreach ($categoriesLabels as $month) {
                    // Chercher la donnée pour ce mois et cette catégorie
                    $item = $categoriesEvolutionDataRaw->first(function ($val) use ($month, $categoryName) {
                        return $val->month === $month && $val->category_name === $categoryName;
                    });
                    $dataForThisCategory[] = $item ? $item->count : 0; // Ajouter le count ou 0 si pas de données
                }
                $categoriesData[] = [
                    'name' => $categoryName,
                    'data' => $dataForThisCategory
                ];
            }


            // Pour "Pannes par statut (Évolution)"
            // Objectif : Compter les pannes signalées par mois/statut
            $pannesEvolutionDataRaw = DB::table('pannes')
                ->whereIn('equipement_id', $userEquipementIds) // On filtre par les équipements de l'utilisateur
                ->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), // Date de création de la panne
                    'sta_pan', // Statut de la panne
                    DB::raw('COUNT(*) as count')
                )
                ->whereNotNull('sta_pan') // Ne prendre que les pannes avec un statut défini
                ->groupBy('month', 'sta_pan')
                ->orderBy('month')
                ->get();

            // Collecter tous les mois uniques pour l'axe X
            $pannesLabels = $pannesEvolutionDataRaw->pluck('month')->unique()->sort()->values()->all();
            // Collecter tous les statuts de pannes uniques
            $allPannesStatusForEvolution = $pannesEvolutionDataRaw->pluck('sta_pan')->unique()->values()->all();

            // Préparer les séries pour ApexCharts (une série par statut de panne)
            $pannesData = [];
            foreach ($allPannesStatusForEvolution as $statusCode) {
                $dataForThisStatus = [];
                foreach ($pannesLabels as $month) {
                    $item = $pannesEvolutionDataRaw->first(function ($val) use ($month, $statusCode) {
                        return $val->month === $month && $val->sta_pan === $statusCode;
                    });
                    $dataForThisStatus[] = $item ? $item->count : 0;
                }
                $pannesData[] = [
                    'name' => $statusCode,
                    'data' => $dataForThisStatus
                ];
            }


            // Retourne la vue avec toutes les données
            return view('myspace', compact( // Assurez-vous que le chemin de la vue est correct
                'user',
                'totalEquipements',
                'equipementsParCategorie',
                'equipementsEnPanne',
                'pannesParStatut',
                'equipementsParEtat',
                'equipementsParStatut',
                'categoriesLabels', // Labels temporels pour l'axe X du graphique "Catégorie (Évolution)"
                'categoriesData',   // Séries de données pour l'axe Y (une par catégorie)
                'pannesLabels',
                'diagnosticsCounts',     // Labels temporels pour l'axe X du graphique "Pannes (Évolution)"
                'pannesData'        // Séries de données pour l'axe Y (une par statut de panne)
            ));
        } catch (\Throwable $e) {
            // Enregistre l'erreur et redirige en cas de problème
            Log::error("Erreur lors du chargement des statistiques de l'utilisateur (QB): " . $e->getMessage());
            return redirect()->back()->with('error', 'Impossible de charger vos statistiques d\'équipement.');
        }
    }


    public function historique(Equipement $equipement)
    {
        $history = $equipement->historique()->with('user')->latest('created_at')->get();
        return view('pages.equipements.historique', compact('equipement', 'history'));
    }

    public function index(Request $request)
    {
        try {
            $equipements = $this->buildFilterQuery($request)->paginate(5)->appends($request->query());
            $categories = CategorieEquipement::all();
            return view('pages.equipements.index', compact('equipements', 'categories'));
        } catch (\Throwable $e) {
            Log::error("Erreur chargement équipements : " . $e->getMessage());
            return redirect()->back()->with('error', 'Impossible de charger les équipements.');
        }
    }

    public function create()
    {
        $cats = CategorieEquipement::orderBy('lib_cat')->get();
        $users = User::active()->orderBy('nom_ag')->get();
        return view('pages.equipements.create', compact('cats', 'users'));
    }

    public function store(Request $request)
    {
        try {
            $this->validateEquipmentNumbers($request);
            $data = $this->cleanEmptyFields($this->validateEquipmentData($request));

            DB::transaction(function () use ($data) {
                $currentAuthUser = Auth::user(); // Obtenir l'utilisateur authentifié
                // Création de l'équipement
                $equipement = Equipement::create($data);

                $attribution_id = null;
                $beneficiaryUser = null; // Initialiser l'utilisateur bénéficiaire pour la notification

                // Si l'équipement est attribué à un user à la création
                if (!empty($data['user_id'])) {
                    $beneficiaryUser = User::find($data['user_id']); // Récupérer l'utilisateur bénéficiaire

                    // On cherche une attribution existante pour ce user
                    $existingAttribution = Attribution::where('user_id', $data['user_id'])->first();

                    if ($existingAttribution) {
                        // Mise à jour de l'attribution existante
                        $existingAttribution->equipements()->syncWithoutDetaching([$equipement->id]);
                        $attribution_id = $existingAttribution->id;
                    } else {
                        // Création d'une nouvelle attribution
                        $newAttribution = Attribution::create([
                            'user_id' => $data['user_id'],
                            'date_attribution' => now()
                        ]);
                        $newAttribution->equipements()->syncWithoutDetaching([$equipement->id]);
                        $attribution_id = $newAttribution->id;
                    }

                    // --- Notification Ajout d'équipement à une attribution existante ou nouvelle ---
                    if ($beneficiaryUser) {
                        $beneficiaryUser->notify(new NewAttributionNotification(
                            $currentAuthUser->nom_ag . ' ' . $currentAuthUser->pren_ag,
                            collect([$equipement]), // Passer l'équipement nouvellement créé comme une collection
                            'nouvelle attribution'
                        ));
                    }
                    // --- Fin Notification ---
                } else {
                    // Si pas d'attribution user, tu peux créer une attribution neutre ou laisser null selon ta logique métier
                    $attribution_id = null;
                }

                // Historique : Ajout simple (avec attribution_id renseigné si dispo)
                // Historique : distinguer création d’attribution et ajout à une attribution existante
                $historiqueData = [
                    'attribution_id' => $attribution_id,
                    'user_id' => Auth::id(),
                    'user2_id' => !empty($data['user_id']) ? $data['user_id'] : null,
                ];

                if (!empty($data['user_id']) && $existingAttribution) {
                    // Ajout à une attribution existante => Modification
                    $historiqueData['action_type'] = 'Modification';
                    $historiqueData['equipements_ajoutes'] = json_encode([$equipement->id]);
                } else {
                    // Nouvelle attribution ou pas de user => Création
                    $historiqueData['action_type'] = 'Création';
                    $historiqueData['equipements'] = json_encode([$equipement->id]);
                }

                HistoAttri::create($historiqueData);
            });

            return redirect()->route('equipements.index')->with('success', 'Équipement ajouté avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'ajout de l\'équipement : ' . $e->getMessage());
        }
    }
    public function show(Equipement $equipement)
    {
        $equipement->load('categorieEquipement', 'user', 'historique.user');
        $categories = CategorieEquipement::orderBy('lib_cat')->get();
        return view('pages.equipements.show', compact('equipement', 'categories'));
    }

    public function edit(Equipement $equipement)
    {
        $categories = CategorieEquipement::orderBy('lib_cat')->get();
        $users = User::active()->orderBy('nom_ag')->get();
        return view('pages.equipements.edit', compact('equipement', 'categories', 'users'));
    }

    public function update(Request $request, Equipement $equipement)
    {
        try {
            $data = $this->cleanEmptyFields($this->validateEquipmentData($request, $equipement->id));
            $original = $equipement->getOriginal();

            DB::transaction(function () use ($equipement, $data, $original) {
                $currentAuthUser = Auth::user(); // Obtenir l'utilisateur authentifié

                // Déterminer l'ancien et le nouvel utilisateur bénéficiaire pour les notifications
                $oldBeneficiaryUser = null;
                if ($original['user_id']) {
                    $oldBeneficiaryUser = User::find($original['user_id']);
                }
                $newBeneficiaryUser = null;
                if ($data['user_id']) {
                    $newBeneficiaryUser = User::find($data['user_id']);
                }

                $equipement->update($data);
                $this->recordUpdateHistory($equipement, $original);

                // --- Logique de notification pour le changement d'attribution d'équipement individuel ---
                // Cas 1: L'équipement est attribué à un nouvel utilisateur (ou ré-attribué)
                if ($original['user_id'] !== $equipement->user_id) {
                    if ($equipement->user_id !== null) { // Si l'équipement est maintenant attribué
                        if ($newBeneficiaryUser) {
                            $newBeneficiaryUser->notify(new NewAttributionNotification(
                                $currentAuthUser->nom_ag . ' ' . $currentAuthUser->pren_ag,
                                collect([$equipement]), // L'équipement lui-même
                                'nouvelle attribution' // Ou 'changement attribution' si tu veux être plus précis
                            ));
                        }
                    }

                    // Cas 2: L'équipement a été désattribué de l'ancien utilisateur
                    if ($original['user_id'] !== null) { // Si l'équipement était attribué avant
                        if ($oldBeneficiaryUser) {
                            $oldBeneficiaryUser->notify(new AttributionRemovedNotification(
                                $currentAuthUser->nom_ag . ' ' . $currentAuthUser->pren_ag,
                                collect([$equipement]), // L'équipement lui-même
                                'retrait machines' // Ou 'désattribution'
                            ));
                        }
                    }
                }
                // --- Fin Logique de notification ---
            });

            return redirect()->route('equipements.index')->with('success', 'Équipement mis à jour avec succès');
        } catch (\Exception $e) {
            Log::error("Erreur modification équipement {$equipement->id} : " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur modification: ' . $e->getMessage());
        }
    }

    public function destroy(Equipement $equipement)
    {
        try {
            DB::transaction(function () use ($equipement) {
                $currentAuthUser = Auth::user(); // Obtenir l'utilisateur authentifié
                $oldBeneficiaryUser = null;
                if ($equipement->user_id) {
                    $oldBeneficiaryUser = User::find($equipement->user_id);
                }

                $equipement->update(['statut_eq' => 'réformé', 'etat_eq' => 'N/A', 'date_retrait' => now()]);
                $equipement->historique()->create([
                    'action' => 'Réforme',
                    'details' => "L'équipement a été mis au rebut.",
                    'old_status' => $equipement->getOriginal('statut_eq'),
                    'new_status' => 'réformé',
                    'user_id' => Auth::id(),
                ]);

                // --- Notification de réforme/désattribution si l'équipement était attribué ---
                if ($oldBeneficiaryUser) {
                    $oldBeneficiaryUser->notify(new AttributionRemovedNotification(
                        $currentAuthUser->nom_ag . ' ' . $currentAuthUser->pren_ag,
                        collect([$equipement]), // L'équipement lui-même
                        'reforme equipement' // Message plus approprié pour la réforme
                    ));
                }
                // --- Fin Notification ---
            });

            return redirect()->route('equipements.index')->with('success', 'Équipement réformé avec succès');
        } catch (\Exception $e) {
            Log::error("Erreur réforme équipement {$equipement->id} : " . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur réforme: ' . $e->getMessage());
        }
    }

    private function buildFilterQuery(Request $request)
    {
        $query = Equipement::with(['categorieEquipement', 'user'])
            ->orderByRaw("FIELD(statut_eq, 'disponible', 'attribué', 'réformé')");

        if ($request->filled('numero')) {
            $query->where(fn($q) => $q->where('num_serie_eq', 'like', "%{$request->numero}%")
                ->orWhere('num_inventaire_eq', 'like', "%{$request->numero}%"));
        }

        if ($request->filled('etat_eq')) $query->where('etat_eq', $request->etat_eq);
        if ($request->filled('statut_eq')) $query->where('statut_eq', $request->statut_eq);

        if ($request->filled('direction')) {
            $query->whereHas('user', fn($q) => $q->where('dir_ag', 'like', "%{$request->direction}%"));
        }

        return $query;
    }

    private function validateEquipmentNumbers(Request $request)
    {
        if (Equipement::where('num_inventaire_eq', $request->num_inventaire_eq)->exists()) {
            throw new \Exception("Le numéro d'inventaire existe déjà.");
        }
        if (Equipement::where('num_serie_eq', $request->num_serie_eq)->exists()) {
            throw new \Exception("Le numéro de série existe déjà.");
        }
    }

    private function validateEquipmentData(Request $request, $id = null)
    {
        return $request->validate([
            'num_serie_eq' => 'nullable|string|max:255|unique:equipements,num_serie_eq,' . $id,
            'num_inventaire_eq' => 'nullable|string|max:255|unique:equipements,num_inventaire_eq,' . $id,
            'nom_eq' => 'required|string|max:255',
            'designation_eq' => 'nullable|string|max:255',
            'etat_eq' => 'nullable|string|max:255',
            'statut_eq' => 'nullable|string|max:255',
            'date_acq' => 'required|date',
            'user_id' => 'nullable|exists:users,id',
            'categorie_equipement_id' => 'required|exists:categorie_equipements,id',
        ]);
    }

    private function cleanEmptyFields(array $data)
    {
        $data['etat_eq'] = trim($data['etat_eq'] ?? '') ?: 'N/A';
        $data['statut_eq'] = trim($data['statut_eq'] ?? '') ?: 'N/A';
        return $data;
    }

    private function recordInitialHistory(Equipement $equipement)
    {
        $equipement->historique()->create([
            'action' => 'Mise en service',
            'details' => "L'équipement a été mis en service.",
            'new_state' => $equipement->etat_eq,
            'new_status' => $equipement->statut_eq,
            'user_id' => Auth::id(),
        ]);
    }

    private function recordUpdateHistory(Equipement $equipement, array $original)
    {
        if ($original['etat_eq'] !== $equipement->etat_eq) {
            $action = $equipement->etat_eq === 'En panne' ? 'Panne signalée' : 'Réparation effectuée';
            $details = $equipement->etat_eq === 'En panne' ? "Signalé en panne." : "Réparé et remis en service.";

            $equipement->historique()->create([
                'action' => $action,
                'details' => $details,
                'old_state' => $original['etat_eq'],
                'new_state' => $equipement->etat_eq,
                'user_id' => Auth::id(),
            ]);
        }

        if ($original['statut_eq'] !== $equipement->statut_eq) {
            $equipement->historique()->create([
                'action' => 'Changement de statut',
                'details' => "Statut changé de '{$original['statut_eq']}' à '{$equipement->statut_eq}'.",
                'old_status' => $original['statut_eq'],
                'new_status' => $equipement->statut_eq,
                'user_id' => Auth::id(),
            ]);

            if ($original['statut_eq'] !== 'réformé' && $equipement->statut_eq === 'réformé') {
                $equipement->historique()->create([
                    'action' => 'Réforme',
                    'details' => "L'équipement a été mis au rebut.",
                    'old_status' => $original['statut_eq'],
                    'new_status' => 'réformé',
                    'user_id' => Auth::id(),
                ]);
            }
        }

        if ($original['user_id'] !== $equipement->user_id) {
            $equipement->historique()->create([
                'action' => $equipement->user_id ? 'Attribution' : 'Désattribution',
                'details' => $equipement->user_id ? "Attribué à un nouvel utilisateur." : "Désattribué.",
                'user_id' => Auth::id(),
            ]);
        }
    }
}
