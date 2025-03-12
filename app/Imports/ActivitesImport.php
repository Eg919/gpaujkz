<?php
namespace App\Imports;

use App\Models\Activite;
use App\Models\Indicateur;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Row;

class ActivitesImport implements ToModel, WithHeadingRow, OnEachRow, WithValidation, WithChunkReading
{
    use Importable;

    public function model(array $row)
    {
        // Crée une activité de base
        return new Activite([
            'user_id' => auth()->id(),
            'structure_id' => $row['structure_id'],
            'objectif_strategique_id' => $row['objectif_strategique_id'],
            'effets_attendus_id' => $row['effets_attendus_id'],
            'libelle' => $row['libelle'],
            'finance_etat' => $row['finance_etat'] ?? null,
            'partenaire' => $row['partenaire'] ?? null,
            'hort_progamme' => $row['hort_progamme'] ?? null,
            'finance_partenaire' => $row['finance_partenaire'] ?? null,
            'trimestre_1' => $row['trimestre_1'] ?? 0,
            'trimestre_2' => $row['trimestre_2'] ?? 0,
            'trimestre_3' => $row['trimestre_3'] ?? 0,
            'trimestre_4' => $row['trimestre_4'] ?? 0,
        ]);
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // Créer l'activité à partir des données
        $activite = Activite::create([
            'user_id' => auth()->id(),
            'structure_id' => $data['structure_id'],
            'objectif_strategique_id' => $data['objectif_strategique_id'],
            'effets_attendus_id' => $data['effets_attendus_id'],
            'libelle' => $data['libelle'],
            'finance_etat' => $data['finance_etat'] ?? null,
            'partenaire' => $data['partenaire'] ?? null,
            'hort_progamme' => $data['hort_progamme'] ?? null,
            'finance_partenaire' => $data['finance_partenaire'] ?? null,
            'trimestre_1' => $data['trimestre_1'] ?? 0,
            'trimestre_2' => $data['trimestre_2'] ?? 0,
            'trimestre_3' => $data['trimestre_3'] ?? 0,
            'trimestre_4' => $data['trimestre_4'] ?? 0,
        ]);

        // Ajouter les indicateurs associés à l'activité
        if (isset($data['Indicateur'])) {
            foreach ($data['Indicateur'] as $indicateurData) {
                Indicateur::create([
                    'activite_id' => $activite->id,
                    'indicateur' => $indicateurData['indicateur'],
                    'unite' => $indicateurData['unite'],
                    'reference' => $indicateurData['reference'],
                    'cible' => $indicateurData['cible']
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'structure_id' => 'required|exists:structures,id',
            'objectif_strategique_id' => 'required|exists:objectifs_strategiques,id',
            'effets_attendus_id' => 'required|exists:effets_attendus,id',
            'libelle' => 'required|string|max:255',
            'finance_etat' => 'nullable|numeric',
            'partenaire' => 'nullable|string|max:255',
            'hort_progamme' => 'nullable|boolean',
            'finance_partenaire' => 'nullable|numeric',
            'trimestre_1' => 'nullable|boolean',
            'trimestre_2' => 'nullable|boolean',
            'trimestre_3' => 'nullable|boolean',
            'trimestre_4' => 'nullable|boolean',
            'Indicateur.*.indicateur' => 'required|string|max:255',
            'Indicateur.*.unite' => 'required|string|max:50',
            'Indicateur.*.reference' => 'required|string|min:0',
            'Indicateur.*.cible' => 'required|string|min:0',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
