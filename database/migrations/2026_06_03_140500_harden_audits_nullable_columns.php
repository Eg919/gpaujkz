<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rend les colonnes d'audit tolérantes (nullable)
     * et ajoute celles manquantes pour éviter les erreurs SQL en production.
     */
    public function up(): void
    {
        if (!Schema::hasTable('audits')) {
            return;
        }

        // Colonnes legacy utilisées par le code applicatif.
        Schema::table('audits', function (Blueprint $table) {
            if (!Schema::hasColumn('audits', 'record_id')) {
                $table->unsignedBigInteger('record_id')->nullable()->after('table_name');
            }
            if (!Schema::hasColumn('audits', 'url')) {
                $table->text('url')->nullable()->after('new_values');
            }
            if (!Schema::hasColumn('audits', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('url');
            }
        });

        // Forcer nullable pour éviter les échecs d'insertion partielle.
        if (Schema::hasColumn('audits', 'action')) {
            DB::statement('ALTER TABLE audits ALTER COLUMN action DROP NOT NULL');
        }
        if (Schema::hasColumn('audits', 'table_name')) {
            DB::statement('ALTER TABLE audits ALTER COLUMN table_name DROP NOT NULL');
        }
        if (Schema::hasColumn('audits', 'address_mail')) {
            DB::statement('ALTER TABLE audits ALTER COLUMN address_mail DROP NOT NULL');
        }
        if (Schema::hasColumn('audits', 'record_id')) {
            DB::statement('ALTER TABLE audits ALTER COLUMN record_id DROP NOT NULL');
        }
        if (Schema::hasColumn('audits', 'url')) {
            DB::statement('ALTER TABLE audits ALTER COLUMN url DROP NOT NULL');
        }
        if (Schema::hasColumn('audits', 'user_agent')) {
            DB::statement('ALTER TABLE audits ALTER COLUMN user_agent DROP NOT NULL');
        }
    }

    public function down(): void
    {
        // Migration de sécurité: on ne réimpose pas NOT NULL pour éviter une régression.
    }
};
