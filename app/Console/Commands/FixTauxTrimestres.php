<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activite;
use App\Models\Tache;

class FixTauxTrimestres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-taux-trimestres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige les taux d\'exécution des anciennes activités pour les attribuer uniquement aux trimestres programmés';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la correction des taux d\'exécution...');
        
        $activites = Activite::all();
        $count = 0;
        
        $bar = $this->output->createProgressBar(count($activites));
        
        foreach ($activites as $activite) {
            // 1. Calculer l'exécution totale basée sur les tâches réelles
            $totalExecution = Tache::where('activite_id', $activite->id)->sum('taux_execution_tache');
            
            // 2. Compter le nombre de trimestres programmés
            $nbTrimestres = 0;
            if ($activite->trimestre_1) $nbTrimestres++;
            if ($activite->trimestre_2) $nbTrimestres++;
            if ($activite->trimestre_3) $nbTrimestres++;
            if ($activite->trimestre_4) $nbTrimestres++;
            
            // 3. Réinitialiser tous les taux à 0
            $activite->taux_t1 = 0;
            $activite->taux_t2 = 0;
            $activite->taux_t3 = 0;
            $activite->taux_t4 = 0;
            
            // 4. Répartir le taux total sur les trimestres programmés
            if ($nbTrimestres > 0 && $totalExecution > 0) {
                $tauxParTrimestre = round($totalExecution / $nbTrimestres);
                if ($activite->trimestre_1) $activite->taux_t1 = $tauxParTrimestre;
                if ($activite->trimestre_2) $activite->taux_t2 = $tauxParTrimestre;
                if ($activite->trimestre_3) $activite->taux_t3 = $tauxParTrimestre;
                if ($activite->trimestre_4) $activite->taux_t4 = $tauxParTrimestre;
            }
            
            $activite->save();
            $count++;
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Mise à jour de $count activités terminée avec succès !");
    }
}
