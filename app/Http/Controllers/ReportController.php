<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Models\Panne;
use App\Models\Equipement;
use Illuminate\Support\Facades\DB;
use App\Models\CategorieEquipement;
use Illuminate\Support\Facades\Log; // Added for logging in helper methods
use Illuminate\Support\Facades\Auth; // Added in case Auth is used in helper methods or other methods not shown

class ReportController extends Controller
{
    /**
     * Helper method to parse a date string into a Carbon instance.
     * Handles null, empty, or invalid date strings gracefully.
     *
     * @param string|null $dateString
     * @return \Carbon\Carbon|null
     */
    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }
        try {
            // Carbon::parse is robust, but the year > 1900 check is a good extra safeguard
            $parsed = Carbon::parse($dateString);
            return $parsed->year > 1900 ? $parsed : null;
        } catch (\Exception $e) {
            Log::warning("Could not parse date '{$dateString}': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper method to get the base64 encoded logo for embedding in PDF.
     * Includes error handling for file not found.
     *
     * @return string|null Base64 encoded image string (without "data:image/...")
     */
    private function getLogoBase64()
    {
        $logoPath = public_path('images/logo.png'); // Your specified logo path

        try {
            if (file_exists($logoPath)) {
                return base64_encode(file_get_contents($logoPath));
            }
            Log::warning("Logo file not found at: {$logoPath}");
            return null;
        } catch (\Exception $e) {
            Log::error("Error reading logo file: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generates a PDF report summarizing equipment statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function generateEquipmentReport()
    {
        // pour décrou les stats des équipements par catégorie

        $equipementsStats = CategorieEquipement::withCount([
            'equipements as total',
            'equipements as disponibles' => fn($q) => $q->where('statut_eq', 'disponible'),
            'equipements as attribues' => fn($q) => $q->where('statut_eq', 'attribué'),
            'equipements as reformes' => fn($q) => $q->where('statut_eq', 'réformé'),
            'equipements as en_panne' => fn($q) => $q->where('etat_eq', 'En panne'),
        ])->get();

        // les passer en paramètre de la fonction calculateGlobalStats()

        $globalStats = $this->calculateGlobalStats($equipementsStats);

        $data = [
            'title' => 'Rapport des Équipements - ' . Carbon::now()->format('d/m/Y'),
            'date' => Carbon::now()->format('d/m/Y H:i'),
            'equipementsStats' => $equipementsStats,
            'globalStats' => $globalStats,
            'logo' => $this->getLogoBase64() // Use helper method
        ];

        $pdf = PDF::loadView('reports.equipements', $data);
        return $pdf->stream('rapport-equipements.pdf');
    }

    /**
     * Generates a detailed PDF report for a specific equipment.
     *
     * @param int $equipementId The ID of the equipment to generate the report for.
     * @return \Illuminate\Http\Response
     */
    /**
     * Generates a detailed PDF report for a specific equipment with complete panne history.
     *
     * @param int $equipementId The ID of the equipment to generate the report for.
     * @return \Illuminate\Http\Response
     */


    //générer les rappots d'équipements

    public function generateEquipmentDetailReport($equipementId)
    {
        // 1. Fetch the equipment with complete panne history
        $equipement = Equipement::with([
            'categorieEquipement',
            'user', // Owner of the equipment
            'pannes' => function ($query) {
                $query->orderBy('created_at', 'desc') // Most recent pannes first
                    ->with('auteur'); // Technician who handled the panne
            }
        ])->findOrFail($equipementId);

        // 2. Format equipment acquisition date
        $equipement->date_acq_formatted = $this->parseDate($equipement->date_acq)?->format('d/m/Y') ?? 'Non spécifiée';

        // 3. Prepare detailed panne history for this specific equipment
        $panneHistory = $equipement->pannes->map(function ($panne) {
            $dateSigna = $this->parseDate($panne->date_signa);
            $dateDt = $this->parseDate($panne->date_dt);
            $dateRsl = $this->parseDate($panne->date_rsl);

            return [
                'id' => $panne->id,
                'date_signa' => $dateSigna?->format('d/m/Y H:i') ?? 'N/A',
                'date_traitement' => $dateDt?->format('d/m/Y H:i') ?? 'Non traité',
                'date_resolution' => $dateRsl?->format('d/m/Y H:i') ?? 'Non résolue',
                'description' => $panne->lib_pan ?? 'Aucune description',
                'diagnostic' => $panne->diag_pan ?? 'Non diagnostiqué',
                'solution' => $panne->action_pan ?? 'Aucune solution appliquée',
                'status' => $panne->sta_pan,
                'technicien' => $panne->auteur->mat_ag ?? 'Non attribué',
                'duree_reparation' => ($dateDt && $dateRsl)
                    ? $dateDt->diffInHours($dateRsl) . ' heures'
                    : 'N/A',
                // 'urgence' => $panne->niv_urg ?? 'Non spécifié',
            ];
        });

        // 4. Calculer stats les résolutions
        $pannesResolues = $equipement->pannes->where('sta_pan', 'Résolue');

        //
        $diagnosticsCounts = $equipement->pannes
            ->whereNotNull('diag_pan')
            ->countBy('diag_pan')
            ->sortDesc();

        // stats de temps
        $dureeMoyenneReparation = $pannesResolues->avg(function ($panne) {
            $dateDt = $this->parseDate($panne->date_dt);
            $dateRsl = $this->parseDate($panne->date_rsl);
            return ($dateDt && $dateRsl) ? $dateDt->diffInHours($dateRsl) : 0;
        });

        $stats = [
            'total_pannes' => $equipement->pannes->count(),
            'pannes_resolues' => $pannesResolues->count(),
            'pannes_en_cours' => $equipement->pannes->where('sta_pan', 'En cours')->count(),
            'pannes_en_attente' => $equipement->pannes->where('sta_pan', 'En attente')->count(),
            'duree_moyenne_reparation' => round($dureeMoyenneReparation, 2),
            'taux_resolution' => $equipement->pannes->count() > 0
                ? round(($pannesResolues->count() / $equipement->pannes->count()) * 100, 2)
                : 0,
            'diagnostics_frequents' => $diagnosticsCounts->take(5), // Top 5 diagnostics
        ];

        $data = [
            'title' => 'Historique des Pannes - ' . ($equipement->nom_eq ?? $equipement->code_eq),
            'equipement' => $equipement,
            'panneHistory' => $panneHistory,
            'stats' => $stats,
            'logo' => $this->getLogoBase64(),
            'report_date' => Carbon::now()->format('d/m/Y H:i'),
        ];

        // 7. Genere PDF
        $pdf = PDF::loadView('reports.equipment-detail', $data);
        return $pdf->stream('historique-pannes-' . $equipement->code_eq . '.pdf');
    }
    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function generatePanneReport()
    {
        // Stats global par statut
        $panneStats = Panne::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN sta_pan = "En attente" THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN sta_pan = "En cours" THEN 1 ELSE 0 END) as en_cours,
            SUM(CASE WHEN sta_pan = "Résolue" THEN 1 ELSE 0 END) as resolues,
            SUM(CASE WHEN sta_pan != "Résolue" THEN 1 ELSE 0 END) as non_resolues_total
        ')->first()->toArray();

        $diagnosticsCountsRaw = Panne::whereNotNull('diag_pan')
            ->select('diag_pan', DB::raw('COUNT(*) as count'))
            ->groupBy('diag_pan')
            ->orderByDesc('count')
            ->get();

        $diagnosticsCounts = [];
        foreach ($diagnosticsCountsRaw as $item) {
            $diagnosticsCounts[$item->diag_pan] = $item->count;
        }

        $pannes = Panne::with(['equipement.categorieEquipement', 'user', 'auteur'])
            ->latest()
            ->limit(50)
            ->get();

        $data = [
            'title' => 'Rapport Global des Pannes - ' . Carbon::now()->format('d/m/Y'),
            'date' => Carbon::now()->format('d/m/Y H:i'),
            'panneStats' => $panneStats,
            'diagnosticsCounts' => $diagnosticsCounts,
            'pannes' => $pannes,
            'logo' => $this->getLogoBase64()
        ];

        return PDF::loadView('reports.pannes', $data)->stream('rapport-global-pannes.pdf');
    }

    /**
     *
     *
     * @param \Illuminate\Database\Eloquent\Collection $equipementsStats
     * @return array
     */
    public function calculateGlobalStats($equipementsStats)
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
}