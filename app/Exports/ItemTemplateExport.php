<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'name',
            'category',
            'stock',
            'unit',
            'min_stock',
        ];
    }

    public function array(): array
    {
        return [
            [
                'name' => 'Gate Valve 3 Inch Class 150',
                'category' => 'Mechanical & Piping',
                'stock' => 25,
                'unit' => 'Pcs',
                'min_stock' => 5,
            ],
            [
                'name' => 'Kabel Control NYYHY 4x1.5mm',
                'category' => 'Electrical & Instrumentation',
                'stock' => 10,
                'unit' => 'Roll',
                'min_stock' => 2,
            ],
            [
                'name' => 'Kacamata Safety Clear 3M',
                'category' => 'Safety & APD',
                'stock' => 50,
                'unit' => 'Pcs',
                'min_stock' => 10,
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0284C7'], // Brand Sky Blue
                ],
            ],
        ];
    }
}
