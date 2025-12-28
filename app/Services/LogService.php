<?php

namespace App\Services;

use App\Models\HistoAttri;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;
use App\Models\ActionHistoriquePoste;


class LogService
{
    public static function addLog(string $eventType, string $details): void
    {
        // Log::create([
        //     'user' => Auth::user() ? Auth::user()->name : 'System',
        //     'event_type' => $eventType,
        //     'details' => $details,
        // ]);
    }

    public static function attributionLog(
        string $action,
        int $authorId, // Auteur de l'action
        int $beneficiaryId, // Bénéficiaire
        int $attributionId,
        ?array $equipements = null,
        ?array $equipementsAjoutes = null,
        ?array $equipementsRetires = null
    ) {
        try {
            DB::table('histo_attri_tables')->insert([
                'action_type' => $action,
                'user_id' => $authorId,
                'user2_id' => $beneficiaryId,
                'attribution_id' => $attributionId,
                'equipements' => $action !== 'Modification' && !empty($equipements) ? json_encode($equipements) : null,
                'equipements_ajoutes' => !empty($equipementsAjoutes) ? json_encode($equipementsAjoutes) : null,
                'equipements_retires' => !empty($equipementsRetires) ? json_encode($equipementsRetires) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur journalisation historique: " . $e->getMessage(), [
                'action' => $action,
                'author_id' => $authorId,
                'beneficiary_id' => $beneficiaryId,
                'attribution_id' => $attributionId,
            ]);
        }
    }






}
