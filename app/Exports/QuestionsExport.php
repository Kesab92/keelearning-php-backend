<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use PhpOffice\PhpSpreadsheet\Document\Properties;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Str;

class QuestionsExport implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    private array $headers;
    private array $data;

    public function __construct($headers, $data)
    {
        $this->headers = $headers;
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->data['entries']);
    }

    public function headings(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function title(): string
    {
        return substr($this->data['app']->name.' Question Translations', 0, 30);
    }

    /**
     * {@inheritdoc}
     */
    public function registerEvents(): array
    {
        return [
            BeforeExport::class => function (BeforeExport $event) {
                /** @var Properties $properties */
                $properties = $event->writer->getProperties();
                $properties->setCreator('keeunit');
                $properties->setCompany('keeunit');
                $properties->setDescription('Question export from '.$this->data['from'].
                    ' to '.$this->data['to'].
                    ' at '.date('Y.m.d H:i'));
            },
            // Handle by a closure.
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->getDelegate();

                $sheet->getProtection()->setSheet(true);
                $cellPosition = 'D2:D'.(1 + count($this->data['entries']));
                $sheet->getStyle($cellPosition)
                    ->getProtection()
                    ->setLocked(
                        Protection::PROTECTION_UNPROTECTED
                    );
                $sheet->getStyle($cellPosition)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color('FFF6F6F6'));
                $sheet->getStyle($cellPosition)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getColumnDimension('C')->setWidth(100);
                $sheet->getColumnDimension('D')->setWidth(100);
            },
        ];
    }
}
