<?php

namespace App\Exports;

use App\Models\Equipement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EquipementsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $equipements;

    public function __construct($equipements)
    {
        $this->equipements = $equipements;
    }

    public function collection()
    {
        return $this->equipements;
    }

    public function headings(): array
    {
        return [
            'N°',
            'N° série',
            'N° inventaire',
            'Nom',
            'Désignation',
            'Type',
            'État',
            'Statut',
            'Matricule propriétaire',
            'Nom propriétaire',
            'Direction',
            'Localisation'
        ];
    }

    public function map($equipement): array
    {
        return [
            $this->equipements->search($equipement) + 1,
            $equipement->num_serie_eq,
            $equipement->num_inventaire_eq,
            $equipement->nom_eq,
            $equipement->designation_eq,
            $equipement->categorieEquipement->lib_cat ?? 'Non défini',
            $equipement->etat_eq,
            $this->formatStatut($equipement->statut_eq),
            $equipement->user->mat_ag ?? 'N/A',
            $equipement->user->nom_ag ?? 'N/A',
            $equipement->user->dir_ag ?? 'N/A',
            $equipement->user->loc_ag ?? 'N/A'
        ];
    }

    protected function formatStatut($statut)
    {
        switch ($statut) {
            case 'réformé':
                return   $statut;
            case 'en service':
                return   $statut;
            case 'disponible':
                return   $statut;
            default:
                return  $statut;
        }
    }

    public function styles(Worksheet $sheet)
    {
        // Style pour l'en-tête
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF']
            ],
            'alignment' => [
                'horizontal' => 'center'
            ]
        ];

        // Appliquer des bordures à toutes les cellules
        $sheet->getStyle('A1:L' . ($this->equipements->count() + 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return [
            1 => $headerStyle,
            // Alignement central pour certaines colonnes
            'A' => ['alignment' => ['horizontal' => 'center']],
            'B' => ['alignment' => ['horizontal' => 'center']],
            'C' => ['alignment' => ['horizontal' => 'center']],
            'G' => ['alignment' => ['horizontal' => 'center']],
            'H' => ['alignment' => ['horizontal' => 'center']],
            'I' => ['alignment' => ['horizontal' => 'center']],
            // Couleur conditionnelle pour le statut
            'H' => [
                'font' => [
                    'color' => [
                        'value' => function ($cellValue) {
                            if (str_contains($cellValue, 'réformé')) return 'FF0000';
                            if (str_contains($cellValue, 'en service')) return '00AA00';
                            if (str_contains($cellValue, 'disponible')) return 'FFA500';
                            return '000000';
                        }
                    ]
                ]
            ]
        ];
    }
}
