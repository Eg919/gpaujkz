<?php
namespace App\Imports;

use App\Models\Structure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StructuresImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Structure([
            'libelle_structure' => $row['libelle_structure'],
            'sigle' => $row['sigle'],
            'etat' => $row['etat'],
        ]);
    }
}

