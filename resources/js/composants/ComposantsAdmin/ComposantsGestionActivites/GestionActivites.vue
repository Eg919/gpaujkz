<template>
  <div class="flex flex-col items-center min-h-screen">
    <!-- Header avec retour et titre (Sober Style) -->
    <div class="w-full bg-gray-50 shadow-md border-b border-gray-200 py-3 px-4 md:px-8 flex items-center mb-8">
      <div class="w-1/4">
        <button 
          @click="$router.go(-1)"
          class="text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-2"
        >
          <i class="fas fa-arrow-left text-xl"></i>
          <span class="text-xs font-bold uppercase hidden md:inline">Retour</span>
        </button>
      </div>
      <div class="w-2/4 text-center">
        <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter">Planification de l'activité</h1>
      </div>
      <div class="w-1/4 flex justify-end">
        <div v-if="isSession" class="px-5 py-2 bg-rose-50 border border-rose-100 rounded-xl text-rose-600 font-black text-xs uppercase tracking-widest flex items-center gap-2 shadow-sm">
          <i class="fas fa-lock animate-pulse"></i>
          Lecture Seule
        </div>
      </div>
    </div>
    <!-- Section pour les tâches et les détails de l'activité -->
    <div class="w-full shadow-inner flex flex-col items-center h-[calc(100vh-150px)] sm:h-[calc(100vh-200px)] px-2 overflow-y-auto">
    <!-- Contenu principal -->
    <div class="space-y-4 sm:space-y-8 p-4 sm:p-6 bg-white rounded-lg shadow-lg w-full mt-2 sm:mt-3">
      <!-- Titre et état activité -->
      <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b flex flex-col sm:flex-row justify-between items-center rounded-t-lg">
        <div class="flex items-center gap-3">
          <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl">
            <i class="fas fa-tasks text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-800">Planification des tâches</h3>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Détails et suivi de l'activité</p>
          </div>
        </div>
        
        <div class="flex items-center gap-4 mt-4 sm:mt-0">
          <div class="flex flex-col items-end">
            <span class="text-[10px] font-bold text-gray-400 uppercase mb-1">Status de l'activité</span>
            <div 
              v-if="activite.etat_activite" 
              :class="{
                'bg-amber-100 text-amber-700 border-amber-200': activite.etat_activite === 'Ouvert',
                'bg-emerald-100 text-emerald-700 border-emerald-200': activite.etat_activite === 'terminer',
                'bg-rose-100 text-rose-700 border-rose-200': activite.etat_activite === 'inachever',
              }"
              class="px-3 py-1 rounded-full border text-xs font-bold flex items-center gap-2"
            >
              <span class="w-2 h-2 rounded-full" :class="{
                'bg-amber-500 animate-pulse': activite.etat_activite === 'Ouvert',
                'bg-emerald-500': activite.etat_activite === 'terminer',
                'bg-rose-500': activite.etat_activite === 'inachever',
              }"></span>
              {{ activite.etat_activite }}
            </div>
            <span v-else class="px-3 py-1 bg-gray-100 text-gray-500 rounded-full text-xs font-bold italic">En attente</span>
          </div>
        </div>
      </div>

      <!-- Section Layout (Main Content + Sidebar) -->
      <div class="flex flex-col lg:flex-row gap-6 p-6">
        <!-- Colonne Principale : Liste des tâches -->
        <div class="flex-1 space-y-6">
          <div class="flex items-center justify-between mb-2">
            <h4 class="text-lg font-bold text-gray-700 flex items-center gap-2">
              <i class="fas fa-list-check text-emerald-500"></i>
              Liste des Tâches
            </h4>
            <div class="flex items-center gap-3">
              <span v-if="isPlanningLocked" class="px-2.5 py-1 bg-amber-50 text-amber-600 text-[10px] font-bold rounded border border-amber-100 flex items-center gap-1.5 uppercase tracking-wider">
                <i class="fas fa-lock text-[8px]"></i> Planning Verrouillé
              </span>
              <span 
                :class="sommePourcentages === 100 ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-600 border-slate-100'"
                class="px-2.5 py-1 text-[10px] font-bold rounded border uppercase tracking-wider flex items-center gap-2"
              >
                <i :class="sommePourcentages === 100 ? 'fas fa-check-circle text-emerald-500' : 'fas fa-info-circle text-slate-400'"></i>
                Total : {{ sommePourcentages }}%
              </span>
              <span class="px-2.5 py-1 bg-gray-50 text-gray-600 text-[10px] font-bold rounded border border-gray-100 uppercase tracking-wider">
                {{ taches.length }} tâche(s)
              </span>
            </div>
          </div>

          <ul class="space-y-4">
            <li
              v-for="tache in taches"
              :key="tache.id"
              class="group bg-white border border-gray-100 hover:border-emerald-200 rounded-xl p-4 transition-all duration-200 hover:shadow-md relative overflow-hidden"
            >
              <!-- Indicateur de bordure discret -->
              <div class="absolute left-0 top-0 bottom-0 w-1 bg-gray-100 group-hover:bg-emerald-400 transition-colors"></div>

              <div class="flex flex-col gap-4">
                <!-- Libellé de la tâche -->
                <div class="flex flex-col sm:flex-row items-end gap-4">
                  <div class="flex-1 w-full">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Libellé de la tâche</label>
                    <div class="relative">
                      <input
                        :disabled="isPlanningLocked || isSession"
                        v-model="tache.libelle"
                        type="text"
                        placeholder="Qu'est-ce qui doit être fait ?"
                        class="w-full pl-3 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:bg-white transition-all text-sm font-medium text-gray-700 disabled:opacity-70 disabled:cursor-not-allowed"
                      />
                    </div>
                  </div>
                  
                  <!-- Numérique inputs -->
                  <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="w-1/2 sm:w-24">
                      <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Poids (%)</label>
                      <input
                        :disabled="isPlanningLocked || isSession"
                        v-model.number="tache.pourcentage_tache"
                        type="number"
                        class="w-full px-2 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:bg-white text-center text-sm font-bold text-gray-700 disabled:opacity-70 disabled:cursor-not-allowed"
                      />
                    </div>
                    <div class="w-1/2 sm:w-24">
                      <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Exécution (%)</label>
                      <input
                        :disabled="!isFollowUpEnabled || isSession"
                        v-model.number="tache.taux_execution_tache"
                        type="number"
                        class="w-full px-2 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:bg-white text-center text-sm font-bold text-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        :title="!isFollowUpEnabled ? 'Session clôturée' : ''"
                      />
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="flex items-center gap-1 sm:self-end h-10">
                    <input 
                      type="file" 
                      :ref="'file_' + tache.id" 
                      class="hidden" 
                      @change="handleFileUpload($event, tache.id)"
                    >
                    <button
                      @click="$refs['file_' + tache.id][0].click()"
                      :disabled="!isFollowUpEnabled || isSession"
                      class="p-2.5 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                      :title="!isFollowUpEnabled ? 'Session clôturée' : 'Joindre un justificatif'"
                    >
                      <i class="fas fa-paperclip"></i>
                    </button>
                    <button
                      @click="modifierTache(tache)"
                        :disabled="(isPlanningLocked && !isFollowUpEnabled) || isSession"
                        class="px-3 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-lg flex items-center gap-2 transition-all font-bold text-[10px] uppercase tracking-wider border border-emerald-100 hover:border-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed"
                      >
                        <i class="fas fa-save"></i>
                        <span>Sauver</span>
                      </button>
                      <button
                         v-if="!isPlanningLocked"
                         @click="supprimerTache(tache.id)"
                         :disabled="isSession"
                       class="p-2.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors disabled:opacity-20 disabled:cursor-not-allowed"
                       :title="isSession ? 'Session Clôturée' : 'Supprimer la tâche'"
                     >
                       <i class="fas fa-trash-alt"></i>
                     </button>
                  </div>
                </div>

                <!-- Pièces Jointes : Style "Pills" -->
                <div v-if="tache.pieces_justificatives && tache.pieces_justificatives.length > 0" class="flex flex-wrap gap-2 pt-2 border-t border-gray-50 mt-1">
                  <div 
                    v-for="piece in tache.pieces_justificatives" :key="piece.id"
                    class="flex items-center gap-2 bg-amber-50/50 border border-amber-100 pl-2 pr-1 py-1 rounded-full text-[10px] font-medium text-amber-700 transition-all hover:bg-amber-100"
                  >
                    <i class="fas text-[10px]" :class="getFileIcon(piece.type_fichier)"></i>
                    <span class="truncate max-w-[120px]">{{ piece.nom_fichier }}</span>
                    <div class="flex items-center ml-1 border-l border-amber-200 pl-1 gap-1">
                       <button 
                        @click="openPreview(piece)" class="w-5 h-5 flex items-center justify-center rounded-full hover:bg-amber-200 text-amber-600">
                        <i class="fas fa-eye text-[9px]"></i>
                      </button>
                      <button 
                         v-if="isFollowUpEnabled"
                         @click="supprimerPiece(piece.id)" 
                         :disabled="isSession"
                         class="w-5 h-5 flex items-center justify-center rounded-full hover:bg-rose-200 text-rose-500 disabled:opacity-30"
                       >
                         <i class="fas fa-times text-[9px]"></i>
                       </button>
                    </div>
                  </div>
                </div>
              </div>
            </li>
          </ul>

          <!-- Formulaire d'ajout : Intégré au flux -->
          <!-- Formulaire d'ajout : Verrouillé si soumis -->
          <div v-if="!isPlanningLocked" class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl p-5 mt-8 hover:bg-gray-100/50 transition-colors">
            <h5 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
              <i class="fas fa-plus-circle text-amber-500"></i>
              Nouvelle Tâche
            </h5>
            <form @submit.prevent="ajouterTache" class="flex flex-col sm:flex-row items-end gap-4">
              <div class="flex-1 w-full">
                <input
                  v-model="nouvelleTache.libelle"
                  type="text"
                  placeholder="Décrivez la nouvelle tâche..."
                  class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-400 text-sm"
                  required
                />
              </div>
              <div class="w-full sm:w-32">
                <div class="relative">
                  <input
                    v-model.number="nouvelleTache.pourcentage_tache"
                    type="number"
                    placeholder="Poids %"
                    class="w-full pl-3 pr-8 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-400 text-sm font-bold text-center"
                    required
                  />
                  <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">%</span>
                </div>
              </div>
              <button
                type="submit"
                class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md hover:shadow-lg flex items-center justify-center gap-2 font-bold text-sm transition-all active:scale-95"
              >
                <i class="fas fa-plus"></i>
                Ajouter
              </button>
            </form>
          </div>
          <div v-else class="mt-8"></div>
        </div>

        <!-- Sidebar : Informations Globales -->
        <div class="w-full lg:w-80 space-y-6">
          <div class="bg-slate-50 border border-slate-100 rounded-2xl p-6 shadow-sm">
            <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6 flex items-center gap-2 border-b border-slate-200 pb-3">
              <i class="fas fa-chart-line text-emerald-500"></i>
              Suivi de l'activité
            </h4>

            <!-- État Financier -->
            <div class="space-y-4 mb-8">
              <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">État Financier (FCFA)</label>
                <div class="relative group">
                  <input
                    :disabled="!isFollowUpEnabled || isSession"
                    :value="formatNombreAvecEspaces(activite.etat_financier)"
                    @input="onInputEtatFinancier($event.target.value)"
                    type="text"
                    class="w-full pl-3 pr-3 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-400 font-mono font-bold text-slate-700 text-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  />
                  <div class="absolute right-3 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full" :class="isFollowUpEnabled ? 'bg-amber-400' : 'bg-slate-300'"></div>
                </div>
              </div>
              
              <button
                @click="mettreAjourEtatFinancier"
                :disabled="!isFollowUpEnabled || isSession"
                class="w-full py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 shadow-sm hover:shadow-md transition-all font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <i class="fas fa-sync-alt"></i>
                Mettre à jour
              </button>
              <p v-if="!isFollowUpEnabled" class="text-[9px] text-rose-500 font-bold uppercase tracking-tighter text-center">
                <i class="fas fa-info-circle"></i> Suivi financier bloqué (session clôturée)
              </p>
            </div>

            <!-- Observation -->
            <div class="space-y-4">
              <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Observations de l'administration</label>
                <textarea
                  :disabled="!isFollowUpEnabled || isSession"
                  v-model="activite.observation"
                  rows="4"
                  placeholder="Notes, remarques ou instructions..."
                  class="w-full p-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-400 text-xs text-slate-600 leading-relaxed transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                ></textarea>
              </div>

              <button
                @click="mettreAjourObservation"
                :disabled="!isFollowUpEnabled || isSession"
                class="w-full py-3 bg-slate-800 text-white rounded-xl hover:bg-black shadow-sm transition-all font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <i class="fas fa-edit"></i>
                Enregistrer
              </button>
            </div>
          </div>

          <!-- Tip Card -->
          <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 border-l-4 border-l-amber-400">
            <h5 class="text-xs font-bold text-amber-800 uppercase mb-2 flex items-center gap-2">
              <i class="fas fa-lightbulb"></i>
              Conseil
            </h5>
            <p class="text-[11px] text-amber-700 leading-relaxed italic">
              N'oubliez pas que la somme des poids de vos tâches doit être égale à 100% pour une soumission valide.
            </p>
          </div>
        </div>
      </div>
      
    <!-- Section pour les statistiques -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full mx-auto mt-8 pb-12">
      <div class="bg-white p-6 shadow-md rounded-2xl border border-gray-100">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
            <i class="fas fa-chart-pie"></i>
          </div>
          <div>
            <h4 class="font-bold text-gray-800">Part des Tâches</h4>
            <p class="text-[10px] text-gray-500 uppercase tracking-widest">Répartition en pourcentage</p>
          </div>
        </div>
        <div class="flex justify-center h-64">
          <canvas id="progressChart"></canvas>
        </div>
      </div>

      <div class="bg-white p-6 shadow-md rounded-2xl border border-gray-100">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
            <i class="fas fa-coins"></i>
          </div>
          <div>
            <h4 class="font-bold text-gray-800">Suivi des Coûts</h4>
            <p class="text-[10px] text-gray-500 uppercase tracking-widest">Prévisionnel vs Effectif (FCFA)</p>
          </div>
        </div>
        <div class="flex justify-center h-64">
          <canvas id="financialChart"></canvas>
        </div>
      </div>
    </div>
      <div class="p-4 sm:p-6 bg-white shadow-lg rounded-lg w-full mt-4 sm:mt-6">
        <AfficherActivite
              v-if="activiteId"
              :activite-id="activiteId"
            />
      </div>
      <!-- Alertes -->
      <div
        v-if="alertMessage"
        :class="['alert', isSuccess ? 'alert-success' : 'alert-error']"
        class="mt-4 sm:mt-6 text-sm sm:text-base"
      >
        {{ alertMessage }}
      </div>
    </div>
    
    <!-- Modal de prévisualisation -->
    <div v-if="showPreview" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-75 p-4">
      <div class="bg-white rounded-lg shadow-2xl w-full max-w-4xl h-[90vh] flex flex-col relative">
        <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-lg">
          <h4 class="font-bold text-gray-700 truncate mr-8">Prévisualisation : {{ previewFile.nom_fichier }}</h4>
          <button @click="closePreview" class="text-gray-500 hover:text-red-600 transition-colors">
            <i class="fas fa-times text-2xl"></i>
          </button>
        </div>
        
        <div class="flex-1 overflow-hidden p-2 bg-gray-200">
          <img v-if="isPreviewableImage" :src="previewUrl" class="w-full h-full object-contain mx-auto" />
          <iframe v-else-if="isPreviewablePDF" :src="previewUrl" class="w-full h-full border-0"></iframe>
          <div v-else class="flex flex-col items-center justify-center h-full space-y-4">
            <i class="fas fa-file-alt text-6xl text-gray-400"></i>
            <p class="text-gray-600 text-center px-4">
              La prévisualisation n'est pas disponible pour ce type de fichier.<br>
              Veuillez le télécharger pour le consulter.
            </p>
            <a :href="previewUrl" download class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              Télécharger le fichier
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Renseignement Financier -->
    <div v-if="showFinanceModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-75 p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md flex flex-col relative overflow-hidden">
        <div class="bg-amber-50 border-b border-amber-100 p-5 flex items-center gap-3">
          <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center shrink-0">
            <i class="fas fa-exclamation-triangle text-lg"></i>
          </div>
          <div>
            <h3 class="font-bold text-amber-800 text-lg">Action Requise</h3>
            <p class="text-xs text-amber-600">État financier manquant</p>
          </div>
        </div>
        
        <div class="p-6">
          <p class="text-sm text-gray-600 mb-6">
            Pour passer cette activité à 100% (Terminée), vous devez obligatoirement renseigner son État Financier (Dépenses réelles).
          </p>
          
          <div class="mb-6">
            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block mb-2">Montant (FCFA) <span class="text-rose-500">*</span></label>
            <div class="relative">
              <input
                :value="formatNombreAvecEspaces(tempEtatFinancier)"
                @input="onInputTempEtatFinancier($event.target.value)"
                type="text"
                class="w-full pl-4 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-400 font-mono font-bold text-gray-800 text-lg transition-all"
                placeholder="Ex: 500 000"
              />
              <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">FCFA</span>
            </div>
          </div>
          
          <div class="flex gap-3 mt-4">
            <button @click="cancelFinanceModal" class="flex-1 py-3 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-xl font-bold text-sm transition-colors">
              Annuler
            </button>
            <button @click="submitFinanceAndFinishTache" class="flex-1 py-3 bg-emerald-600 text-white hover:bg-emerald-700 rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center justify-center gap-2" :disabled="actionLoading">
              <i class="fas fa-check" v-if="!actionLoading"></i>
              <i class="fas fa-circle-notch fa-spin" v-else></i>
              Valider & Terminer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</template>
  <script>
  

  import axios from "axios";
  import Chart from 'chart.js/auto';
  import AfficherActivite from './AfficherActivite.vue';
  export default {
    components: {
    AfficherActivite,
  },
    data() {
      return {
      taches: [],
      alertMessage: '',
      etatFinancier: 0,
      financeEtat: 0,
      financePartenaire: 0,
      financement: 0,
      isPointFocal: false,
      isPlanificateur: false,
      activite: {},
      isSession: false,
      isInvite: false,
      indicateurs: [],
      loading: true,
      error: false,
      nouvelleTache: {
        libelle: "",
        pourcentage_tache: 0,
      },
      activiteId: null,
      etatache:null,
      actionLoading: false,
      showPreview: false,
      previewFile: null,
      previewUrl: '',
      progressChartInstance: null,
      financialChartInstance: null,
      showFinanceModal: false,
      pendingTache: null,
      tempEtatFinancier: null,
    };
  },
    computed: {
      defaultValue() {
        return (value, fallback = "Non renseigné") => value || fallback;
      },
      sommePourcentages() {
        return this.taches.reduce((sum, tache) => sum + tache.pourcentage_tache, 0);
      },
      isPreviewableImage() {
        if (!this.previewFile) return false;
        const type = (this.previewFile.type_fichier || '').toLowerCase();
        return type.includes('image') || ['jpg', 'jpeg', 'png', 'gif'].some(ext => (this.previewFile.nom_fichier || '').toLowerCase().endsWith(ext));
      },
      isPreviewablePDF() {
        if (!this.previewFile) return false;
        const type = (this.previewFile.type_fichier || '').toLowerCase();
        return type.includes('pdf') || (this.previewFile.nom_fichier || '').toLowerCase().endsWith('.pdf');
      },
      // Nouvelles propriétés de contrôle de flux
      isPlanningLocked() {
        return false;
      },
      isFollowUpEnabled() {
        return true;
      },
    },

    mounted() {
      const id = this.$route.params.id;
      this.activiteId = id;  
      console.log('ID reçu:', id);  

      this.fetchData();  
      this.fetchDetails();
      this.fetchUserInfo();
      this.fetchSessionByActivite();
  },

  methods: {
    // Formate le nombre avec des espaces tous les 3 chiffres
  formatNombreAvecEspaces(valeur) {
    if (!valeur) return ''
    return valeur.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
  },

  // Gère la saisie utilisateur
  onInputEtatFinancier(valeur) {
    const chiffreNet = valeur.replace(/\s+/g, '') // Enlève les espaces
    if (/^\d*$/.test(chiffreNet)) {
      this.activite.etat_financier = Number(chiffreNet)
    }
  },

  onInputTempEtatFinancier(valeur) {
    const chiffreNet = valeur.replace(/\s+/g, '') // Enlève les espaces
    if (/^\d*$/.test(chiffreNet)) {
      this.tempEtatFinancier = Number(chiffreNet)
    }
  },
   async mettreAjourObservation() {
    if (!confirm("Êtes-vous sûr de vouloir mettre à jour l'observation de l'activité ?")) {
                return;
            }
     const response = await axios.post(`/api/activites/observation/${this.activite.id}`, {
       observation: this.activite.observation,
     });
     console.log('Observation mise à jour avec succès :', response.data); 
     this.showAlert("Observation mis à jour avec succès :" ,response.data,true);
   },
    async mettreAJourEtatActivite() {
      if (!confirm("Êtes-vous sûr de vouloir mettre à jour l'État de l'activité à "+ this.activite.etat_activite +" ?")) {
                return;
            }
        const response = await axios.put(`/api/activites-etat/${this.activite.id}`, {
          etat_activite: this.activite.etat_activite,
        });
        console.log("État mis à jour avec succès :", response.data);
        this.showAlert("État mis à jour avec succès :" ,response.data,true);
        this.fetchData();
    },

    async mettreAjourEtatFinancier() {
      if (!confirm("Êtes-vous sûr de vouloir mtrer à jour l'État Financier à " +this.activite.etat_financier+" ?")) {
                return;
            }
        const response = await axios.put(`/api/activites/${this.activite.id}/etat-financier`, {
          etat_financier: this.activite.etat_financier,
        });
        this.showAlert('Mise à jour de l\'état financier réussie.', true);
        this.fetchData();
    },

    async fetchDetails() {
      this.loading = true;
      this.error = false;
      try {
        const response = await fetch(`/api/activites-detaille/${this.activiteId}`);
        if (!response.ok) throw new Error("Erreur lors de la récupération des données");
        const data = await response.json();
        this.activite = data.activite;
        this.indicateurs = data.indicateurs;

        // Réévalue le mode lecture seule après chargement de l'activité.
        this.updateInviteStatus();
      } catch (error) {
        console.error("Erreur :", error);
        this.error = true;
      } finally {
        this.loading = false;
      }
    },
    async fetchSessionByActivite() {
  try {
    const response = await axios.get(`/api/activites/${this.activiteId}/session`);
    console.log('Session associée :', response.data);
    this.isSession = false;
  } catch (error) {
    console.error('Erreur lors de la récupération de la session :', error);
    return null;
  }
},
    async fetchData() {
      try {
        const { data } = await axios.get(`/api/activite-statistique/${this.activiteId}`);
        this.taches = data.taches;
        this.etatache = data.taches.etat
        this.etatFinancier = data.etat_financier;
        this.financeEtat = data.finance_etat;
        this.financePartenaire = data.finance_partenaire;
        this.financement=data.financement;
        this.etatActivite = data.etat_activite;
      } catch (error) {
        console.error("Erreur lors du chargement des données :", error);
      }
      this.$nextTick(() => {
        this.renderCharts();
      });
    },

    ajouterTache() {
      if (this.actionLoading) return;
      if (this.nouvelleTache.libelle && this.nouvelleTache.pourcentage_tache) {
        this.actionLoading = true;
        axios
          .post(`/api/activites/${this.activiteId}/taches`, this.nouvelleTache)
          .then(() => {
            this.fetchData(this.activiteId); 
            this.nouvelleTache = { libelle: "", pourcentage_tache: 0 };
          })
          .finally(() => {
            this.actionLoading = false;
          });
          this.showAlert('Tâche ajouter avec succes',true);
      }
      this.fetchData();
    },

    async modifierTache(tache) {
      if (this.actionLoading) return;
      if (!confirm("Êtes-vous sûr de vouloir modifier cette tâche ?")) {
                return;
            }
        this.actionLoading = true;
        try {
        const response = await axios.put(`/api/taches/${tache.id}`, {
          libelle: tache.libelle,
          pourcentage_tache: tache.pourcentage_tache,
          taux_execution_tache:tache.taux_execution_tache,
          //etat: tache.etat,
        });
        this.fetchData();
        if(tache.etat) {
        const index = this.taches.findIndex(t => t.id === tache.id);
        if (index !== -1) {
          this.taches[index] = response.data.tache;
        }
        this.showAlert('Tâche Mise à jour avec succes',true);
        this.renderCharts();
      }
      } catch (error) {
        console.log("Erreur détectée dans modifierTache:", error.response);
        if (error.response && error.response.status === 422 && error.response.data.requires_finance) {
            this.pendingTache = tache;
            this.tempEtatFinancier = null;
            this.showFinanceModal = true;
        } else if (error.response && error.response.data && error.response.data.message) {
            this.showAlert(error.response.data.message, false);
            this.fetchData(); // Reload original data
        } else {
            this.showAlert('Une erreur est survenue lors de la modification de la tâche.', false);
        }
      } finally {
        this.actionLoading = false;
      }
    },

    cancelFinanceModal() {
      this.showFinanceModal = false;
      this.pendingTache = null;
      this.tempEtatFinancier = null;
      this.fetchData();
    },

    async submitFinanceAndFinishTache() {
      if (!this.tempEtatFinancier || this.tempEtatFinancier <= 0) {
          this.showAlert("Veuillez entrer un montant valide supérieur à 0.", false);
          return;
      }
      this.actionLoading = true;
      try {
          // 1. Mettre à jour l'état financier
          await axios.put(`/api/activites/${this.activiteId}/etat-financier`, {
            etat_financier: this.tempEtatFinancier,
          });
          
          // 2. Mettre à jour la tâche en attente
          await axios.put(`/api/taches/${this.pendingTache.id}`, {
            libelle: this.pendingTache.libelle,
            pourcentage_tache: this.pendingTache.pourcentage_tache,
            taux_execution_tache: this.pendingTache.taux_execution_tache,
          });
          
          this.showFinanceModal = false;
          this.pendingTache = null;
          this.tempEtatFinancier = null;
          this.fetchData();
          this.showAlert('Finances mises à jour et activité terminée avec succès !', true);
      } catch (error) {
          this.showAlert('Erreur lors de la mise à jour.', false);
      } finally {
          this.actionLoading = false;
      }
    },

    async supprimerTache(tacheId) {
        if (this.actionLoading) return;
        try {
            if (!confirm("Êtes-vous sûr de vouloir supprimer cette tâche ?")) {
                return;
            }
            this.actionLoading = true;
            const response = await axios.delete(`/api/taches/${tacheId}/supprimer`);
            if (response.status === 200) {
                this.showAlert('Tâche supprimée avec succès.',true);  
            }
            this.fetchData();
        } catch (error) {
            this.showAlert("Erreur lors de la suppression de la tâche :",false);
            if (error.response && error.response.status === 404) {
                this.showAlert("Tâche introuvable.",false);
            } else {
                this.showAlert("Une erreur est survenue lors de la suppression de la tâche.",false);
            }
        } finally {
            this.actionLoading = false;
        }
       
      },
      renderCharts() {
        // Détruire les instances existantes pour éviter les doublons et les bugs visuels
        if (this.progressChartInstance) {
          this.progressChartInstance.destroy();
        }
        if (this.financialChartInstance) {
          this.financialChartInstance.destroy();
        }

        const ctxProgress = document.getElementById("progressChart");
        if (ctxProgress) {
          this.progressChartInstance = new Chart(ctxProgress.getContext("2d"), {
            type: "doughnut",
            data: {
              labels: this.taches.map((tache) => `${tache.libelle} (${tache.pourcentage_tache}%)`),
              datasets: [
                {
                  label: "Répartition des tâches",
                  data: this.taches.map((tache) => tache.pourcentage_tache),
                  backgroundColor: this.taches.map((tache, index) =>
                    tache.etat === 1 ? "#10b981" : this.getCouleurUnique(index) // Emerald 500 for finished
                  ),
                },
              ],
            },
            options: {
              responsive: true,
              plugins: {
                legend: { position: "bottom" },
                tooltip: {
                  callbacks: {
                    label: function (tooltipItem) { return ` ${tooltipItem.raw}%`; },
                  },
                },
                datalabels: {
                  color: "#fff",
                  formatter: (value) => `${value}%`,
                  font: { size: 24, weight: "bold" },
                },
              },
            },
          });
        }

        const ctxFinancial = document.getElementById("financialChart");
        if (ctxFinancial) {
          this.financialChartInstance = new Chart(ctxFinancial.getContext("2d"), {
            type: "bar",
            data: {
              labels: ["Coût prévisionnel", "Coût Effectif"],
              datasets: [
                {
                  label: "Budget",
                  data: [this.financement, this.etatFinancier],
                  backgroundColor: ["#10b981", "#f59e0b"], // Emerald 500, Amber 500
                },
              ],
            },
            options: {
              responsive: true,
              scales: { y: { beginAtZero: true } },
              plugins: {
                datalabels: {
                  anchor: "end",
                  align: "top",
                  color: "#000",
                  formatter: (value) => `${value} FCFA`,
                  font: { weight: "bold" },
                },
              },
            },
          });
        }
      },

    getCouleurUnique(index) {
      // Palette centrée sur Vert et Jaune (Emerald, Amber, Lime, Teal)
      const couleursParDefaut = [
        "#10b981", "#f59e0b", "#84cc16", "#14b8a6", "#facc15", "#059669", "#d97706"
      ];
      return couleursParDefaut[index % couleursParDefaut.length]; 
    },

    async fetchUserInfo() {
      try {
        const response = await axios.get('/api/user-info');
        this.userInfo = response.data;
        this.isPointFocal=this.userInfo.role === 'Point-Focale';
        this.isPlanificateur=this.userInfo.role === 'Planificateur';
        this.isAdmin = this.userInfo.role === 'Administrateur';
        this.updateInviteStatus();
      } catch (error) {
        this.showAlert('Erreur lors de la récupération des informations utilisateur :', false);
      }
    },

      updateInviteStatus() {
        // Ordonnateur reste en lecture seule par règle métier.
        if (this.userInfo && this.userInfo.role === 'Ordonnateur') {
          this.isInvite = true;
          return;
        }

        // Tant que les données ne sont pas chargées, ne pas forcer le mode invité.
        if (!this.userInfo || !this.activite) {
          this.isInvite = false;
          return;
        }

        const userStructureId = Number(
          this.userInfo.structure_id ?? this.userInfo.structure?.id
        );
        const activiteStructureId = Number(this.activite.structure_id);

        const partenaires = this.activite.structures_partenaires || this.activite.structuresPartenaires || [];
        const isPartenaire = Array.isArray(partenaires)
          ? partenaires.some((s) => Number(s.id) === userStructureId)
          : false;

        const isStructureProprietaire = userStructureId === activiteStructureId;
        this.isInvite = !(isStructureProprietaire || isPartenaire);
      },

      showAlert(message, isSuccess = true) {
        this.alertMessage = message;
        this.isSuccess = isSuccess;
        setTimeout(() => {
          this.alertMessage = '';
        }, 3000);
      },
      handleFileUpload(event, tacheId) {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('fichier', file);

        axios.post(`/api/taches/${tacheId}/pieces`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(response => {
          this.fetchData();
          this.showAlert('Pièce justificative ajoutée avec succès.', true);
        })
        .catch(error => {
          console.error(error);
          this.showAlert('Erreur lors de l\'ajout du document.', false);
        });
      },
      async supprimerPiece(pieceId) {
        if (!confirm("Êtes-vous sûr de vouloir supprimer ce document ?")) return;
        try {
          await axios.delete(`/api/pieces/${pieceId}`);
          this.fetchData();
          this.showAlert('Document supprimé avec succès.', true);
        } catch (error) {
          console.error(error);
          this.showAlert('Erreur lors de la suppression.', false);
        }
      },
      getFileIcon(mimeType) {
        if (!mimeType) return 'fa-file';
        if (mimeType.includes('pdf')) return 'fa-file-pdf text-red-500';
        if (mimeType.includes('image')) return 'fa-file-image text-blue-500';
        if (mimeType.includes('word') || mimeType.includes('officedocument.wordprocessingml')) return 'fa-file-word text-blue-700';
        if (mimeType.includes('excel') || mimeType.includes('officedocument.spreadsheetml')) return 'fa-file-excel text-green-600';
        return 'fa-file-alt text-gray-500';
      },
      openPreview(piece) {
        this.previewFile = piece;
        this.previewUrl = '/storage/' + piece.chemin_fichier;
        this.showPreview = true;
        // Empêcher le scroll du body quand le modal est ouvert
        document.body.style.overflow = 'hidden';
      },
      closePreview() {
        this.showPreview = false;
        this.previewFile = null;
        this.previewUrl = '';
        // Réactiver le scroll
        document.body.style.overflow = 'auto';
      },
    },
    verifierSommePourcentages() {
      if (this.sommePourcentages !== 100) {
        this.showAlert(
          `La somme des pourcentages des tâches est actuellement ${this.sommePourcentages}%. Veuillez ajuster les valeurs pour atteindre exactement 100%.`,
          false
        );
        return false;
      }
      return true;
    },
    beforeRouteLeave(to, from, next) {
    if (this.sommePourcentages !== 100) {
      if(!this.isInvite){
        if (confirm("La somme des pourcentages des tâches est à " + this.sommePourcentages + "%. Veuillez ajuster les valeurs pour atteindre exactement 100%. Voulez-vous vraiment quitter ?")
        ) {
          next();
        } else {
          next(false);
        }
      } else {
      next();
      } 
    } else {
      next();
    }
  },
 
  };
  </script>