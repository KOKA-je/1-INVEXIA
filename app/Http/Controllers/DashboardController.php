<?php

namespace App\Http\Controllers;

use App\Models\Panne;
use App\Models\Equipement;
use App\Models\CategorieEquipement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques des équipements avec eager loading
        $equipementsStats = CategorieEquipement::withCount([
            'equipements as total',
            'equipements as disponibles' => fn($q) => $q->where('statut_eq', 'disponible'),
            'equipements as attribues' => fn($q) => $q->where('statut_eq', 'attribué'),
            'equipements as reformes' => fn($q) => $q->where('statut_eq', 'réformé'),
            'equipements as en_panne' => fn($q) => $q->where('etat_eq', 'En panne'),
        ])->get();

        // Calcul des indicateurs globaux optimisés
        $globalStats = $this->calculateGlobalStats($equipementsStats);

        // Statistiques avancées des pannes
        $panneStats = $this->getEnhancedPanneStats();

        // Équipements nécessitant une attention urgente
        $equipementsUrgents = $this->getCriticalEquipments();

        // 1. Nombre total de pannes (tous statuts confondus)
        $totalPannes = Panne::count();

        // 2. Répartition par diagnostic (y compris non diagnostiqués)
        $diagnosticsRepartition = Panne::query()
            ->select(
                DB::raw("COALESCE(diag_pan, 'Non diagnostiqué') as diagnostic"),
                DB::raw('COUNT(*) as count'),
                DB::raw('ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM pannes), 2) as percentage')
            )
            ->groupBy('diagnostic')
            ->orderByDesc('count')
            ->get();

        // 3. Top 5 des diagnostics les plus fréquents
        $topDiagnostics = $diagnosticsRepartition->take(5);

        // 4. Export pour la vue

        return view('dashboard', [
            'equipementsStats' => $equipementsStats,
            'pourcentagePanne' => $globalStats['pourcentagePanne'],
            'panneStats' => $panneStats,
            'recentPannes' => Panne::with(['equipement.categorieEquipement', 'user'])
                ->latest()
                ->limit(5)
                ->get(),
            'globalStats' => $globalStats,
            'equipementsUrgents' => $equipementsUrgents,
            'maintenanceTrends' => $this->getMaintenanceTrends(),

            'total_pannes' => $totalPannes,
            'diagnostics_repartition' => $diagnosticsRepartition,
            'top_diagnostics' => $topDiagnostics,
        ]);
    }

    private function calculateGlobalStats($equipementsStats)
    {
        $totalEquipements = $equipementsStats->sum('total');

        return [
            'total' => $totalEquipements,
            'disponibles' => $equipementsStats->sum('disponibles'),
            'attribues' => $equipementsStats->sum('attribues'),
            'reformes' => $equipementsStats->sum('reformes'),
            'en_panne' => $equipementsStats->sum('en_panne'),
            'pourcentagePanne' => $totalEquipements > 0
                ? round(($equipementsStats->sum('en_panne') / $totalEquipements) * 100, 2)
                : 0,
        ];
    }

    private function getEnhancedPanneStats()
    {
        $stats = Panne::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN sta_pan = "En attente" THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN sta_pan = "En cours" THEN 1 ELSE 0 END) as en_cours,
            SUM(CASE WHEN sta_pan = "Résolue" THEN 1 ELSE 0 END) as resolues
        ')->first()->toArray();

        // Ajout des statistiques par catégorie
        $stats['par_categorie'] = Panne::with('equipement.categorieEquipement')
            ->get()
            ->groupBy(function ($panne) {
                return $panne->equipement->categorieEquipement->lib_cat ?? 'Non catégorisé';
            })
            ->map->count();

        // Temps moyen de résolution avec Carbon
        $stats['temps_moyen'] = Panne::where('sta_pan', 'Résolue')
            ->get()
            ->avg(function ($panne) {
                if ($panne->date_signa && $panne->updated_at) {
                    return Carbon::parse($panne->date_signa)
                        ->diffInHours(Carbon::parse($panne->updated_at));
                }
                return 0;
            });

        // Pannes critiques (en attente depuis plus de 48h)
        $stats['pannes_critiques'] = Panne::where('sta_pan', 'En attente')
            ->where('date_signa', '<', now()->subHours(48))
            ->count();

        return $stats;
    }

    private function getCriticalEquipments()
    {
        return Equipement::where('etat_eq', 'En panne')
            ->with(['categorieEquipement', 'pannes' => function ($q) {
                $q->where('sta_pan', '!=', 'Résolue')
                    ->orderBy('date_signa', 'desc');
            }])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(function ($equipement) {
                $equipement->duree_panne = $equipement->pannes->isEmpty()
                    ? null
                    : now()->diffInHours($equipement->pannes->first()->date_signa);
                return $equipement;
            });
    }

    private function getMaintenanceTrends()
    {
        return [
            'monthly' => Panne::selectRaw('
            MONTH(date_signa) as month,
            COUNT(*) as total,
            SUM(CASE WHEN sta_pan = "Résolue" THEN 1 ELSE 0 END) as resolues
        ')
                ->whereYear('date_signa', date('Y'))
                ->groupBy('month')
                ->orderBy('month')
                ->get(),

            'categories' => CategorieEquipement::with(['equipements.pannes'])
                ->withCount('equipements')
                ->get()
                ->map(function ($categorie) {
                    return [
                        'lib_cat' => $categorie->lib_cat,
                        'pannes_count' => $categorie->equipements->sum(function ($equipement) {
                            return $equipement->pannes->count();
                        }),
                        'equipements_count' => $categorie->equipements_count
                    ];
                })

                ->sortByDesc('pannes_count')
                ->take(5)
                ->values()
        ];
    }
}