<?php

namespace App\Services\Reports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportExport  implements WithMapping, FromArray, WithHeadings, ShouldAutoSize
{
    private array $headers;
    private array $data;

    /**
     * @param array $headers
     * @param array $data
     */
    public function __construct(array $headers, array $data)
    {
        $this->headers = $headers;
        $this->data = $data;
    }


    public function headings(): array
    {
        return $this->headers;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function map($row): array
    {
        return $row;
    }
}
