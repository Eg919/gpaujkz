<?php

namespace App\Observers;

use App\Models\Activite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ActiviteObserver
{
    /**
     * Handle the Activite "created" event.
     */
    public function created(Activite $activite): void
    {
        $this->logAudit('création', $activite, [], $activite->getAttributes());
    }

    /**
     * Handle the Activite "updated" event.
     */
    public function updated(Activite $activite): void
    {
        $oldValues = [];
        $newValues = [];

        foreach ($activite->getChanges() as $key => $value) {
            // Ignore updated_at column changes
            if ($key !== 'updated_at') {
                $oldValues[$key] = $activite->getOriginal($key);
                $newValues[$key] = $value;
            }
        }

        // Only log if there are actual changes
        if (!empty($newValues)) {
            $this->logAudit('modification', $activite, $oldValues, $newValues);
        }
    }

    /**
     * Handle the Activite "deleted" event.
     */
    public function deleted(Activite $activite): void
    {
        $this->logAudit('suppression', $activite, $activite->getAttributes(), []);
    }

    /**
     * Reusable logic to log into the audits table.
     */
    private function logAudit(string $action, Activite $activite, array $oldValues, array $newValues): void
    {
        $user = Auth::user();
        
        DB::table('audits')->insert([
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'table_name' => 'activites',
            'record_id' => $activite->id,
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($newValues),
            'url' => request()->fullUrl(),
            'user_agent' => request()->userAgent(),
            'address_mail' => request()->ip(), // L'adresse IP est stockée ici
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
