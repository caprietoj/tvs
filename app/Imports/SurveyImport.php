<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class SurveyImport implements ToArray
{
    public function array(array $array): array
    {
        return $array;
    }
}
