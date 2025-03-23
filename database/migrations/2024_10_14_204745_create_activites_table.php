<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->double('etat_financier')->nullable()->default(0);
            $table->string('etat_slection')->nullable()->default('Preselectionné');
            $table->string('etat_activite')->nullable()->default('en-attente');
            $table->string('partenaire')->nullable();
            $table->integer('hort_progamme')->nullable();
            $table->double('finance_partenaire')->nullable();
            $table->string('etat')->nullable()->default('UJKZ');
            $table->double('finance_etat')->nullable();
            $table->integer('trimestre_1')->nullable();
            $table->integer('trimestre_2')->nullable();
            $table->integer('trimestre_3')->nullable();
            $table->integer('trimestre_4')->nullable();
            $table->integer('soumi')->nullable();
            $table->integer('confirmation_presi')->nullable();
            $table->integer('reconduir')->nullable();
            $table->string('observation')->nullable();
            $table->integer('masque')->nullable();
            //rapports activites
            $table->integer('coute_t1')->nullable();
            $table->integer('taux_t1')->nullable();
            $table->integer('coute_t2')->nullable();
            $table->integer('taux_t2')->nullable();
            $table->integer('coute_t3')->nullable();
            $table->integer('taux_t3')->nullable();
            $table->integer('coute_t4')->nullable();
            $table->integer('taux_t4')->nullable();
            $table->foreignId('user_id')->constrained('users', )->onDelete('cascade'); 
            $table->foreignId('effets_attendus_id')->constrained('effets_attendus', )->onDelete('cascade'); 
            $table->foreignId('objectif_strategique_id')->constrained('objectifs_strategiques', )->onDelete('cascade'); 
            $table->foreignId('structure_id')->constrained()->onDelete('structures')->onDelete('cascade'); 
            $table->foreignId('sessions_id') ->constrained('sessions_activites',)->onDelete('cascade'); 
            $table->timestamps();
        });

        Schema::create('taches', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->integer('pourcentage_tache')->default(0);
            $table->integer('taux_execution_tache')->default(0);
            $table->integer('etat')->default(0);
            $table->integer('reconduir')->nullable();
            $table->integer('masque')->nullable();
            $table->foreignId('activite_id')->constrained('activites',)->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taches');
        Schema::dropIfExists('activites');
    }
};
