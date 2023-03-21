<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\DefaultValueBinder;

class ExcelValueBinderWithoutFormula extends DefaultValueBinder
{

    /**
     * DataType for value.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function dataTypeForValue($value): string
    {
        $dataType = parent::dataTypeForValue($value);

        if($dataType === DataType::TYPE_FORMULA) {
            return DataType::TYPE_STRING;
        }

        return $dataType;
    }
}
