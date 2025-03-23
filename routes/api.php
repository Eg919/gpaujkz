<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AxeStrategiqueController;
use App\Http\Controllers\ObjectifStrategiqueController;
use App\Http\Controllers\EffetAttenduController;
use App\Http\Controllers\PlanStrategiqueController;
use App\Http\Controllers\SessionActiviteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\IndicateurController;
use App\Http\Controllers\TacheController;
use App\Http\Controllers\StrategieController;
use App\Http\Controllers\ImportUserController;
use App\Http\Controllers\NotificationActiviteController;
use App\Http\Controllers\ImportStructuresController;
use App\Http\Controllers\ActiviteImportsController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

use App\Http\Controllers\EmailController;

Route::post('/send-email', [EmailController::class, 'sendEmail']);

    Route::post('/login', [AuthController::class, 'login']);
    
    
  
    Route::middleware(['auth:sanctum'])->group(function () {
        
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
       // Route pour récupérer les notifications non lues
        Route::get('/notifications/non-lues', [NotificationActiviteController::class, 'getNotificationsNonLues']);
        
        // Route pour marquer une notification comme lue
        Route::put('/notifications/{id}/lue', [NotificationActiviteController::class, 'marquerCommeLue']);
        // Routes pour les structures
       // Route::get('/structures/{id}', [StructureController::class, 'show']);
        Route::put('/structures/{id}', [StructureController::class, 'update']);
        Route::post('/structures', [StructureController::class, 'store']);
        Route::get('/structures', [StructureController::class, 'index']);
        Route::get('/structures/count', [StructureController::class, 'count']);
        Route::post('/import-structures', [ImportStructuresController::class, 'importStructures']);
        Route::delete('/structures/supprimer/{id}', [StructureController::class, 'supprimerStructure']);

        // Routes pour les utilisateurs
        

        Route::post('/import-users', [ImportUserController::class, 'import']);

        Route::get('/utilisateurs', [UserController::class, 'index']);
        Route::post('/utilisateurs', [UserController::class, 'store']);
        Route::put('/utilisateurs/{id}', [UserController::class, 'update']);
        Route::get('/users/count', [UserController::class, 'countUsers']);
        Route::put('/utilisateurs/{id}/reset-password', [UserController::class, 'resetPassword']);
        Route::get('/user-info', [UserController::class, 'getUserInfo']);
        Route::delete('/utilisateurs/supprimer/{id}', [UserController::class, 'supprimerUtilisateur']);
        // Routes pour les axes stratégiques
        Route::post('/axes-strategiques', [AxeStrategiqueController::class, 'store']);
        Route::get('/axes-strategiques', [AxeStrategiqueController::class, 'index']);
        Route::put('/axes-strategiques/{id}/modifier', [AxeStrategiqueController::class, 'update']);
        Route::get('axes-strategiques-Ouvert', [AxeStrategiqueController::class, 'getAxesStrategiquesEnCours']);
        Route::delete('/axes-strategiques/{id}/supprimer', [AxeStrategiqueController::class, 'supprimerAxe']);
        // Routes pour les objectifs stratégiques
        Route::post('/objectifs-strategiques', [ObjectifStrategiqueController::class, 'store']);
        Route::get('/objectifs-strategiques/{axeStrategiqueId}', [ObjectifStrategiqueController::class, 'getByAxe']);
        Route::get('/objectifs-strategiques', [ObjectifStrategiqueController::class, 'index']);
        Route::put('/objectifs-strategiques/{id}/modifier', [ObjectifStrategiqueController::class, 'update']);
        Route::get('/objectifs-strategiques-Ouvert', [ObjectifStrategiqueController::class, 'objectifsStrategiquesEnCours']);
        Route::get('/objectifs/rapports', [ObjectifStrategiqueController::class, 'rapport']);
        Route::get('/objectifs-strategiques-Ouvert-activites', [ObjectifStrategiqueController::class, 'objectifsStrategiquesEnCoursAvecActivites']);// Routes pour les effets attendus
        Route::delete('/objectifs-strategiques/{id}/supprimer', [ObjectifStrategiqueController::class, 'supprimerObjectif']);
        Route::post('/effets-attendus', [EffetAttenduController::class, 'store']);
        Route::get('/effets-attendus', [EffetAttenduController::class, 'index']);
        Route::put('/effets-attendus/{id}/modifier', [EffetAttenduController::class, 'update']);
         // Récupérer les effets attendus pour un axe stratégique spécifique
         Route::get('/axes-strategiques/{axeId}/effets-attendus', [EffetAttenduController::class, 'getEffetsAttendusParAxe']);
        Route::delete('/axes-strategiques/{id}/supprimer', [AxeStrategiqueController::class, 'supprimerAxe']);
         // Routes pour les plans stratégiques
        Route::post('/plans-strategiques', [PlanStrategiqueController::class, 'store']);
        Route::get('/plans-strategiques', [PlanStrategiqueController::class, 'index']);
        Route::get('/plan-strategique-en-cour', [PlanStrategiqueController::class, 'getPlanActif']);
        Route::get('/plans-strategiques/{id}', [PlanStrategiqueController::class, 'show']);
        Route::put('/plans-strategiques/{id}', [PlanStrategiqueController::class, 'update']);
        Route::delete('/plans-strategiques/{id}', [PlanStrategiqueController::class, 'supprimerPlan']);
         // Routes pour les Strategie
        Route::get('/axes/{planId}', [StrategieController::class, 'getAxesByPlan']);
        Route::get('/objectifs/{axeId}', [StrategieController::class, 'getObjectifsByAxe']);
        Route::get('/effets/{objectifId}', [StrategieController::class, 'getEffetsByObjectif']);
        Route::get('/effets-activites/{objectifId}', [StrategieController::class, 'getEffetsByObjectifActivite']);
        Route::delete('/effets-attendus/{id}', [EffetAttenduController::class, 'supprimerEffet']);
        // Routes pour les sessions
        Route::get('/sessions-activites', [SessionActiviteController::class, 'index']);
        Route::post('/sessions-activites', [SessionActiviteController::class, 'store']);
        Route::put('/sessions-activites/{id}', [SessionActiviteController::class, 'update']);
        Route::get('/session-Ouvert', [SessionActiviteController::class, 'getSessionEnCours']);
        Route::get('/sessions-activites/count', [SessionActiviteController::class, 'countSessions']);
        Route::delete('/sessions/supprimer/{id}', [SessionActiviteController::class, 'supprimerSession']);
        
        Route::get('/activites/{activiteId}/session', [SessionActiviteController::class, 'getSessionByActivite']);
        Route::post('/activites', [ActiviteController::class, 'store']);
        Route::get('/activites/count-valide', [ActiviteController::class, 'countValidatedActivities']);
        Route::get('/activites/count-Ouvert', [ActiviteController::class, 'countActivitesEnCours']);
        Route::get('/activites/count', [ActiviteController::class, 'countUserActivities']);
        Route::get('/activites', [ActiviteController::class, 'index']);
        Route::get('/activites-structure', [ActiviteController::class, 'select-activite']);
        Route::get('/activites/structure-session', [ActiviteController::class, 'getActivitesByStructureAndSession']);
        Route::get('/activites/session/{id}', [ActiviteController::class, 'getActivitesBySession']);
        Route::get('/activites/session/{id}/hort/programme', [ActiviteController::class, 'getActivitesBySessionHortProgramme']);
        Route::get('/activites/session/{id}/pa', [ActiviteController::class, 'getActivitesBySessionPa']);
        Route::get('/activites/session/{id}/st', [ActiviteController::class,'getActivitesBySessionStructure']);
        Route::get('/activites/{activiteId}', [ActiviteController::class, 'show']);
        Route::post('/activites/{id}/soumission', [ActiviteController::class, 'soumettreActivite']);
        Route::get('/activite-statistique/{id}', [TacheController::class, 'getActivite']);
        Route::get('/activites-validees', [ActiviteController::class, 'getActivitesValidees']);
        Route::post('/activites/{id}/confirmer', [ActiviteController::class, 'confirmationPresi']);
        Route::post('/activites-confirmer', [ActiviteController::class, 'tousConfirmer']);
        // Route pour mettre à jour l'état financier d'une activité
        Route::put('/activites/{id}/etat-financier', [ActiviteController::class, 'mettreAJourEtatFinancier']);
        Route::post('/activites/{id}/taches', [TacheController::class, 'store']);
        Route::put('/activites-etat/{id}', [ActiviteController::class, 'mettreAJourEtatActivite']);
        Route::post('/activites/observation/{id}', [ActiviteController::class, 'mettreAJourObservation']);
        Route::put('activites/{id}', [ActiviteController::class, 'updateEtatActiviteSelection']);
        Route::get('/activites-detaille/{id}', [ActiviteController::class, 'ActiviteDetailles']);
        Route::put('/activites/{id}/reconduire', [ActiviteController::class, 'recomduireActivite']);
        Route::put('activites/{activiteId}/modification', [ActiviteController::class, 'update']);
        Route::delete('/activites/{id}/supprimer', [ActiviteController::class, 'supprimerActivite']);
        Route::put('/taches/{id}', [TacheController::class, 'modifierTache']);
        Route::post('/tachesEtat/{id}', [TacheController::class, 'modifierTacheEtat']);
        Route::delete('/taches/{id}/supprimer', [TacheController::class, 'destroy'])->name('taches.destroy');
        Route::get('/activites-effet/{effetAttenduId}', [ActiviteController::class, 'getActivitesByEffetAttendu']);
        Route::get('/activites-effet/{effetAttenduId}/st', [ActiviteController::class, 'getActivitesByEffetAttenduStructure']);
        Route::get('/activites-effet/{effetAttenduId}/trimestres', [ActiviteController::class, 'getActivitesByEffetAttenduTrimestre']);
        Route::get('/taches/{activiteId}', [TacheController::class, 'getTachesByActivite']);
        Route::post('/indicateurs', [IndicateurController::class, 'store']);
    });
    
    
