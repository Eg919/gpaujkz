<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * SAFE pour la production : que des ALTER TABLE ADD COLUMN (nullable) et CREATE TABLE.
     * Aucune donnée existante n'est affectée.
     */
    public function up(): void
    {
        // P2 : Ajouter motif_rejet à activites
        Schema::table('activites', function (Blueprint $table) {
            $table->text('motif_rejet')->nullable()->after('observation');
        });

        // P7 : Table des pièces justificatives
        Schema::create('pieces_justificatives', function (Blueprint $table) {
            $table->id();
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->string('type_fichier')->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->foreignId('tache_id')->constrained('taches')->onDelete('cascade');
            $table->timestamps();
        });

        // P10 : Ajouter colonne JSON pour plusieurs partenaires
        // L'ancien champ 'partenaire' (string) reste intact pour les données existantes
        Schema::table('activites', function (Blueprint $table) {
            $table->json('partenaires_list')->nullable()->after('partenaire');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pieces_justificatives');

        Schema::table('activites', function (Blueprint $table) {
            $table->dropColumn(['motif_rejet', 'partenaires_list']);
        });
    }
};
