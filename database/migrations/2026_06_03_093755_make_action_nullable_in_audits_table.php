<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rend les colonnes personnalisées de la table audits nullable
     * pour éviter le conflit avec le package owen-it/laravel-auditing
     * qui insère ses propres colonnes (event) sans remplir les nôtres (action).
     */
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->string('action')->nullable()->change();
            $table->string('table_name')->nullable()->change();
            $table->string('address_mail')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->string('action')->nullable(false)->change();
            $table->string('table_name')->nullable(false)->change();
        });
    }
};
