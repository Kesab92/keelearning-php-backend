<?php

namespace App\Imports\Excel;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class DefaultImport implements WithCalculatedFormulas
{
    use Importable;
}
