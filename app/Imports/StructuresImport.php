<?php
namespace App\Imports;

use App\Models\Structure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Support\Collection;

class StructuresImport implements ToCollection, WithHeadingRow, WithValidation, WithCustomCsvSettings
{
    private $rowCount = 0;

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';'
        ];
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $structure = Structure::where('sigle', $row['sigle'])->first();

            if (!$structure) {
                Structure::create([
                    'libelle_structure' => $row['libelle_structure'],
                    'sigle' => $row['sigle'],
                    'etat' => $row['etat'] ?? 'Actif',
                ]);
                $this->rowCount++;
            } else {
                $structure->update([
                    'libelle_structure' => $row['libelle_structure'],
                    'etat' => $row['etat'] ?? $structure->etat,
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'libelle_structure' => 'required|string',
            'sigle' => 'required|string',
        ];
    }
    
    public function getRowCount()
    {
        return $this->rowCount;
    }
}

