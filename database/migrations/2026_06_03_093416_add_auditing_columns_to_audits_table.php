<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute les colonnes attendues par le package owen-it/laravel-auditing
     */
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            // Colonnes requises par owen-it/laravel-auditing
            // Toutes nullable pour compatibilité avec les données existantes
            if (!Schema::hasColumn('audits', 'event')) {
                $table->string('event')->nullable()->after('id');
            }
            if (!Schema::hasColumn('audits', 'auditable_type')) {
                $table->string('auditable_type')->nullable()->after('event');
            }
            if (!Schema::hasColumn('audits', 'auditable_id')) {
                $table->unsignedBigInteger('auditable_id')->nullable()->after('auditable_type');
            }
            if (!Schema::hasColumn('audits', 'user_type')) {
                $table->string('user_type')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('audits', 'tags')) {
                $table->text('tags')->nullable();
            }
            if (!Schema::hasColumn('audits', 'ip_address')) {
                $table->ipAddress('ip_address')->nullable();
            }
            if (!Schema::hasColumn('audits', 'url')) {
                $table->text('url')->nullable();
            }
            if (!Schema::hasColumn('audits', 'user_agent')) {
                $table->text('user_agent')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropColumn(['event', 'auditable_type', 'auditable_id', 'user_type', 'tags', 'ip_address', 'url', 'user_agent']);
        });
    }
};
