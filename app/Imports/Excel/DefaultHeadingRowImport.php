<?php

namespace App\Imports\Excel;

use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\HeadingRowImport;

class DefaultHeadingRowImport extends HeadingRowImport implements WithCalculatedFormulas
{
}
