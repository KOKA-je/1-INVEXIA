<?php

namespace App\Exports;

use App\Models\Attribution;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AttributionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $attributions;

    public function __construct($attributions)
    {
        $this->attributions = $attributions;
    }

    public function collection()
    {
        return $this->attributions;
    }

    public function headings(): array
    {
        return [
            '#',
            'Matricule',
            'Nom',
            'Prénom',
            'Équipement(s)',
            'Direction',
            'Localisation'
        ];
    }

    public function map($attribution): array
    {
        // Cette méthode est requise mais nous allons tout gérer dans registerEvents
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style de l'en-tête
            1 => [
                'font' => ['bold' => true],

                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            // Alignement général
            'A:G' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ]
            ]
        ];
        // Appliquer des bordures à toutes les cellules
        $sheet->getStyle('A1:L' . ($this->attributions->count() + 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowIndex = 2; // Commence après l'en-tête

                foreach ($this->attributions as $attribution) {
                    $groupedEquipements = $attribution->equipements->groupBy(
                        fn($eq) => $eq->categorieEquipement->lib_cat ?? 'Non défini'
                    );

                    $firstRow = true;
                    $rowSpan = $groupedEquipements->count();

                    foreach ($groupedEquipements as $categorie => $equipements) {
                        // Écrire les données communes seulement sur la première ligne
                        if ($firstRow) {
                            $sheet->setCellValue('A' . $rowIndex, $attribution->id);
                            $sheet->setCellValue('B' . $rowIndex, $attribution->user->mat_ag);
                            $sheet->setCellValue('C' . $rowIndex, $attribution->user->nom_ag);
                            $sheet->setCellValue('D' . $rowIndex, $attribution->user->pren_ag);
                            $sheet->setCellValue('F' . $rowIndex, $attribution->user->dir_ag);
                            $sheet->setCellValue('G' . $rowIndex, $attribution->user->loc_ag);
                        }

                        // Format des équipements comme dans la vue
                        $equipementText = $categorie . " :\n";
                        $equipementNumbers = $equipements->map(function ($eq) {
                            return $eq->num_inventaire_eq . ($eq->num_serie_eq ? ' (' . $eq->num_serie_eq . ')' : '');
                        })->implode("\n");

                        $sheet->setCellValue('E' . $rowIndex, $equipementText . $equipementNumbers);

                        // Style des bordures
                        $styleArray = [
                            'borders' => [
                                // 'allBorders' => [
                                //     'borderStyle' => Border::BORDER_THIN,
                                //     'color' => ['rgb' => '000000'],
                                // ],
                            ],
                        ];
                        $sheet->getStyle('A' . $rowIndex . ':G' . $rowIndex)->applyFromArray($styleArray);

                        // Fusionner les cellules communes si nécessaire
                        if ($firstRow && $rowSpan > 1) {
                            foreach (['A', 'B', 'C', 'D', 'F', 'G'] as $col) {
                                $sheet->mergeCells($col . $rowIndex . ':' . $col . ($rowIndex + $rowSpan - 1));
                            }
                            $firstRow = false;
                        }

                        $rowIndex++;
                    }
                }
            },
        ];
    }
}
