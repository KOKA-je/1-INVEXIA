<?php

namespace App\Imports;

use App\Models\Equipement;
use App\Models\CategorieEquipement;
use App\Models\Attribution;
use App\Models\HistoAttri;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Added for logging skipped rows

class EquipementImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $groupes = [];

        // 1. Group rows by user (using matricule or nom/prenom)
        foreach ($rows as $row) {
            $user = $this->findUser($row);

            // Crucial Fix: Ensure at least 'num_serie' is present if 'num_inventaire' is empty.
            // A row is skipped ONLY if there's no user AND no unique identifier (num_inventaire OR num_serie).
            // We'll process if a user is found AND (num_inventaire is not empty OR num_serie is not empty).
            // If user is not found, we still skip.
            if (!$user) {
                continue;
            }

            // If a user is found, ensure at least one identifier is present for the equipment itself.
            if (empty($row['num_inventaire']) && empty($row['num_serie'])) {
                continue;
            }

            // If a user is found and at least one identifier is present, we group the row.
            $groupes[$user->id][] = $row;
        }

        // 2. Process each group to create attributions and equipment, then log history
        foreach ($groupes as $userId => $equipementsRows) {
            $user = User::find($userId);
            if (!$user) {
                // Should ideally not happen if the first grouping logic is robust, but as a safeguard.
                Log::error("Skipping group: User ID '{$userId}' not found after grouping. This indicates an issue in findUser or initial grouping.");
                continue;
            }

            // Create a new attribution record for this batch of equipment for this user
            $attribution = Attribution::create([
                'user_id' => $user->id,
                'date_attribution' => now(),
            ]);

            $currentBatchEquipementIds = [];

            foreach ($equipementsRows as $row) {
                $categorie = CategorieEquipement::where('lib_cat', $row['type_poste'])->first();
                $categorieId = $categorie ? $categorie->id : null;

                // Trying to find existing equipment:
                // We use both 'num_serie_eq' and 'num_inventaire_eq' for lookup.
                // If 'num_inventaire' is empty in the Excel, it will be ignored in the `where` clause for finding.
                // If 'num_serie' is empty, it will be ignored too.
                // The key is that `orWhere` allows finding by either.
                $equipementQuery = Equipement::query();

                if (!empty($row['num_serie'])) {
                    $equipementQuery->where('num_serie_eq', $row['num_serie']);
                }

                if (!empty($row['num_inventaire'])) {
                    // If num_serie is also present, we need an orWhere if num_inventaire is also a primary lookup
                    // If num_inventaire is the ONLY identifier for some equipment, this becomes critical.
                    // The previous `orWhere` was good. Let's make sure it's applied correctly if both exist.
                    // Reverted to previous logic: try finding by either. It's more forgiving.
                    if (!empty($row['num_serie'])) {
                        $equipementQuery->orWhere('num_inventaire_eq', $row['num_inventaire']);
                    } else { // Only num_inventaire is present for lookup
                        $equipementQuery->where('num_inventaire_eq', $row['num_inventaire']);
                    }
                }

                // If only num_serie is present, the first `where` will apply.
                // If only num_inventaire is present, the second `where` (or its `orWhere` branch) will apply.
                // If both are present, it tries to match either.
                $equipement = $equipementQuery->first();


                // Prepare data for equipment creation or update
                $data = [
                    // IMPORTANT: Assign `null` if the Excel column is empty.
                    // Ensure your database columns `num_serie_eq` and `num_inventaire_eq` are nullable.
                    'num_serie_eq' => $row['num_serie'] ?? null,
                    'num_inventaire_eq' => $row['num_inventaire'] ?? null,
                    'nom_eq' => $row['nom_poste_travail'] ?? null,
                    'designation_eq' => $row['designation'] ?? null,
                    'etat_eq' => $row['etat'] ?? 'En service',
                    'statut_eq' => ($row['statut_eq'] ?? 'En service') === 'En service' ? 'attribué' : ($row['statut_eq'] ?? 'disponible'),
                    'date_acq' => $this->formatDate($row['dateacq'] ?? null),
                    'user_id' => $user->id,
                    'categorie_equipement_id' => $categorieId,
                ];

                // Create or update the equipment
                if ($equipement) {
                    $equipement->update($data);
                } else {
                    $equipement = Equipement::create($data);
                }

                // Attach the equipment to the attribution
                $attribution->equipements()->syncWithoutDetaching([$equipement->id]);

                // Collect the equipment ID for the HistoAttri record
                $currentBatchEquipementIds[] = $equipement->id;

                // Record individual history for each equipment
                $equipement->historique()->create([ // Ensure this is `history()` as per earlier fixes
                    'action' => 'Attribution',
                    'details' => "L'équipement a été attribué à " . ($user->mat_ag ?? ''),
                    'new_state' => $equipement->etat_eq,
                    'new_status' => $equipement->statut_eq,
                    'user_id' => Auth::id() ?? null,
                ]);
            }

            // After processing all equipment for this user's batch, create the HistoAttri record.
            HistoAttri::create([
                'action_type' => 'Création',
                'attribution_id' => $attribution->id,
                'user_id' => Auth::id() ?? null,
                'user2_id' => $user->id,
                'equipements' => json_encode($currentBatchEquipementIds),
            ]);
        }
    }

    private function findUser($row)
    {
        if (!empty($row['matricule'])) {
            return User::where('mat_ag', $row['matricule'])->first();
        }

        if (!empty($row['nom']) && !empty($row['prenom'])) {
            return User::where('nom_ag', $row['nom'])
                ->where('pren_ag', $row['prenom'])
                ->first();
        }

        return null;
    }
    private function formatDate($dateInput): ?string
    {
        if (empty($dateInput)) {
            return null;
        }

        // 1. Cas où c'est juste une année (ex: "2023" ou 2023)
        if (preg_match('/^\d{4}$/', $dateInput)) {
            try {
                // Crée une date au 1er janvier de cette année
                return Carbon::createFromFormat('Y', $dateInput)->startOfYear()->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning("Format d'année invalide: " . $dateInput);
                return null;
            }
        }

        // 2. Gestion des formats de date complets
        $formats = [
            'Y-m-d',    // 2023-12-31
            'd/m/Y',    // 31/12/2023
            'm/d/Y',    // 12/31/2023
            'Y/m/d',    // 2023/12/31
            'd-m-Y',    // 31-12-2023
            'd.m.Y',    // 31.12.2023
            'Ymd',      // 20231231 (sans séparateurs)
            'd M Y',    // 31 Dec 2023
            'j F Y',    // 31 décembre 2023
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateInput);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        // 3. Gestion des dates Excel (nombre de jours depuis 1900)
        if (is_numeric($dateInput) && $dateInput > 1) {
            try {
                // Conversion des dates Excel (nombre de jours depuis 1900)
                return Carbon::createFromTimestamp((int) (($dateInput - 25569) * 86400))->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning("Conversion date Excel échouée: " . $dateInput);
            }
        }

        Log::warning("Format de date non reconnu: " . $dateInput);
        return null;
    }
}
