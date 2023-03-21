<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ImportExample implements FromView
{
    private array $fields;

    /**
     * ImportExample constructor.
     * @param $fields
     */
    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function view(): View
    {
        return view('import.csv.example', ['fields' => $this->fields]);
    }
}
