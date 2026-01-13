<?php

namespace App\Exports;

use App\Models\Solicitud;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class HistorialExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithDrawings, WithCustomStartCell, WithEvents
{
    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function query()
    {
        $query = Solicitud::query()->whereHas('correspondencia', function ($q) {
            $q->where('StatusSolicitud_FK', '!=', 1)
              ->where('StatusSolicitud_FK', '!=', 7); 
        });

        if (!empty($this->filtros['urgencia'])) {
            $query->where('NivelUrgencia', $this->filtros['urgencia']);
        }
        if (!empty($this->filtros['fecha_desde'])) {
            $query->whereDate('FechaSolicitud', '>=', $this->filtros['fecha_desde']);
        }
        if (!empty($this->filtros['fecha_hasta'])) {
            $query->whereDate('FechaSolicitud', '<=', $this->filtros['fecha_hasta']);
        }


        // 1. Municipio
        if (!empty($this->filtros['municipio_id'])) {
            $query->whereHas('persona.parroquia', function ($q) {
                $q->where('Municipio_FK', $this->filtros['municipio_id']);
            });
        }

        // 2. Código Interno
        if (!empty($this->filtros['codigo_interno'])) {
            $query->whereHas('correspondencia', function ($q) {
                $q->where('CodigoInterno', 'like', "%{$this->filtros['codigo_interno']}%");
            });
        }

        // 3. Nro UAC
        if (!empty($this->filtros['nro_uac'])) {
            $query->where('Nro_UAC', 'like', "%{$this->filtros['nro_uac']}%");
        }


        if (!empty($this->filtros['search'])) {
            $search = $this->filtros['search'];
            $query->where(function($q) use ($search) {
                $q->where('Nro_UAC', 'like', "%{$search}%")
                  ->orWhere('DescripcionSolicitud', 'like', "%{$search}%")
                  ->orWhereHas('persona', function ($q_persona) use ($search) {
                      $q_persona->where('NombresPersona', 'like', "%{$search}%")
                                ->orWhere('ApellidosPersona', 'like', "%{$search}%")
                                ->orWhere('CedulaPersona', 'like', "%{$search}%");
                  })
                  ->orWhereHas('correspondencia', function ($q_corr) use ($search) {
                      $q_corr->where('CodigoInterno', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('FechaSolicitud', 'desc');
    }

    // AQUI ES EL PRIMER CAMBIO CLAVE (Cantidad de palabras)
    public function map($solicitud): array
    {
        $codigo = $solicitud->correspondencia->CodigoInterno ?? $solicitud->Nro_UAC ?? 'S/N';
        $nombre = $solicitud->persona->NombresPersona . ' ' . $solicitud->persona->ApellidosPersona;

        // 25 palabras + ancho de 60 = Aprox 2 a 3 líneas de alto visualmente
        $descripcionCorta = Str::words($solicitud->DescripcionSolicitud, 25, '...');

        return [
            $codigo,
            $nombre,
            $solicitud->persona->CedulaPersona,
            $descripcionCorta, 
            $solicitud->FechaSolicitud->format('d/m/Y'),
            $solicitud->correspondencia->status->NombreStatusSolicitud ?? 'Sin Estado'
        ];
    }

    public function headings(): array
    {
        return [
            'CÓDIGO',
            'CIUDADANO',
            'CÉDULA',
            'DESCRIPCIÓN', // Título corto
            'FECHA',
            'ESTADO'
        ];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function drawings()
    {
        $drawings = [];
        if (file_exists(public_path('images/logo_corpointa.png'))) {
            $drawing1 = new Drawing();
            $drawing1->setName('Logo Corporación');
            $drawing1->setPath(public_path('images/logo_corpointa.png'));
            $drawing1->setHeight(60);
            $drawing1->setCoordinates('A1');
            $drawings[] = $drawing1;
        }
        if (file_exists(public_path('images/Gobernación_logo.jpg'))) {
            $drawing2 = new Drawing();
            $drawing2->setName('Logo Secundario');
            $drawing2->setPath(public_path('images/Gobernación_logo.jpg'));
            $drawing2->setHeight(60);
            $drawing2->setCoordinates('F1'); 
            $drawings[] = $drawing2;
        }
        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Títulos
                $sheet->setCellValue('C2', "REPORTE DE SOLICITUDES");
                $sheet->setCellValue('C3', "Generado el: " . date('d/m/Y h:i A'));
                $sheet->mergeCells('B2:E2'); 
                $sheet->mergeCells('B3:E3');

                $sheet->getStyle('B2:E3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => '1F497D']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // --- ESTILOS DE TABLA ---
                $highestRow = $sheet->getHighestRow();
                $rangoTabla = 'A6:F' . $highestRow;

                $sheet->getStyle($rangoTabla)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        // ALINEACIÓN SUPERIOR (Importante para que se vea ordenado si hay varias líneas)
                        'vertical' => Alignment::VERTICAL_TOP, 
                        // TEXT WRAP (Importante para que baje de línea)
                        'wrapText' => true 
                    ],
                ]);

                // Centrar horizontalmente columnas cortas (Código, Fecha, Cédula)
                $sheet->getStyle('A6:A'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C6:C'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E6:E'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- AJUSTES DE ANCHO Y PÁGINA ---
                
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setFitToWidth(1);  
                $sheet->getPageSetup()->setFitToHeight(1); 

        
                $sheet->getColumnDimension('D')->setWidth(45);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            6 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => '1F497D']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER, 
                ],
            ],
        ];
    }
}