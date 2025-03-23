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
        Schema::create('plans_strategiques', function (Blueprint $table) {
            $table->id(); 
            $table->string('titre');
            $table->date('debut');
            $table->date('fin');
            $table->string('etat');
            $table->integer('masque')->nullable();
            $table->timestamps();
        });

        Schema::create('axes_strategiques', function (Blueprint $table) {
            $table->id(); 
            $table->string('libelle');
            $table->integer('masque')->nullable();
            $table->foreignId('plan_strategique_id')->constrained('plans_strategiques', )->onDelete('cascade'); 
            $table->timestamps();
        });

        Schema::create('objectifs_strategiques', function (Blueprint $table) {
            $table->id(); 
            $table->string('libelle');
            $table->integer('masque')->nullable();
            $table->foreignId('axe_strategique_id')->constrained('axes_strategiques', )->onDelete('cascade'); 
            $table->timestamps(); 
        });

        Schema::create('effets_attendus', function (Blueprint $table) {
            $table->id(); 
            $table->string('libelle');
            $table->integer('masque')->nullable();
            $table->foreignId('objectif_strategique_id')->constrained('objectifs_strategiques', )->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('effets_attendus');
        Schema::dropIfExists('objectifs_strategiques'); 
        Schema::dropIfExists('axes_strategiques'); 
        Schema::dropIfExists('plans_strategiques'); 
    }
};
