<?php

namespace App\Reader;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ProductFileReader implements IReadFilter
{
    public function readCell($column, $row, $worksheetName = '')
    {
        dump($column);
        dd($row);
    }
}
