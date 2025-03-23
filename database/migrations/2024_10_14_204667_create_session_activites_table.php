<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('sessions_activites', function (Blueprint $table) {
        $table->id();  
        $table->integer('annee');  
        $table->date('date_debut') ;
        $table->date('date_fin') ;
        $table->integer('masque')->nullable();
        $table->string('etat');
        $table->foreignId('plan_strategiques_id')->constrained('plans_strategiques', )->onDelete('cascade'); 
        $table->timestamps(); 
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions_activites');
    }
};
