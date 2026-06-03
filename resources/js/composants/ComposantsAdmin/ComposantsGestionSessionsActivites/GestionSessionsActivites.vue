<template>
  <div class="flex flex-col items-center min-h-screen bg-gray-50/50 pb-12">
    <!-- Header avec titre centré Jaune-Orange -->
    <div class="w-full bg-gray-50 shadow-md border-b border-gray-200 py-3 px-4 md:px-8 flex items-center mb-8">
      <!-- Gauche : Retour -->
      <div class="w-1/4">
        <router-link to="/admin" class="text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-2">
          <i class="fas fa-arrow-left text-xl"></i>
          <span class="text-xs font-bold uppercase hidden md:inline">Retour</span>
        </router-link>
      </div>

      <!-- Centre : Titre -->
      <div class="w-2/4 text-center">
        <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter">Sessions d'Activités</h1>
      </div>

      <!-- Droite : Action -->
      <div class="w-1/4 flex justify-end">
        <button 
          @click="accederFormulaireCreation" 
          class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg shadow-sm transition-all active:scale-95 flex items-center gap-2 font-bold text-sm"
        >
          <i class="fas fa-plus text-xs"></i> 
          <span class="hidden md:inline">Nouvelle Session</span>
          <span class="md:hidden">Session</span>
        </button>
      </div>
    </div>

    <!-- Filtres et Recherche -->
    <div class="w-full max-w-[99%] mx-auto px-4 md:px-8 mb-6">
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-96">
          <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Rechercher par année ou état..." 
            class="w-full pl-11 pr-4 py-2.5 bg-gray-50 border border-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white text-sm font-medium text-gray-600 transition-all"
          />
        </div>
        
        <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-widest">
          <i class="fas fa-filter text-amber-500"></i>
          <span>{{ filteredSessions.length }} sessions configurées</span>
        </div>
      </div>
    </div>

    <!-- Tableau de Sessions Premium Style Sobre -->
    <div class="w-full max-w-[99%] mx-auto px-4 md:px-8 overflow-x-auto">
      <div class="min-w-full inline-block align-middle">
        <div class="overflow-hidden border border-gray-200 rounded-lg shadow-sm bg-white">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 uppercase text-[10px] tracking-widest font-black text-gray-400">
              <tr>
                <th scope="col" class="px-4 py-4 text-left">N°</th>
                <th scope="col" class="px-3 py-4 text-left">Année d'exercice</th>
                <th scope="col" class="px-3 py-4 text-left">Période (Début - Fin)</th>
                <th scope="col" class="px-3 py-4 text-left">État</th>
                <th scope="col" class="px-4 py-4 text-center">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr 
                v-for="(session, index) in paginatedSessions" 
                :key="session.id" 
                class="hover:bg-gray-50 transition-colors"
                :class="editableRowId === session.id ? 'bg-amber-50/30' : ''"
              >
                <td class="px-4 py-4 whitespace-nowrap text-[10px] font-bold text-gray-400">
                  {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                </td>
                <td class="px-3 py-4 whitespace-nowrap max-w-[200px]">
                  <div v-if="editableRowId !== session.id" class="text-sm font-bold text-gray-800 truncate" :title="session.annee">
                    {{ session.annee }}
                  </div>
                  <input 
                    v-else
                    type="text"
                    v-model="session.annee" 
                    class="bg-white border-2 border-amber-300 rounded-lg px-2 py-1 text-sm font-bold text-gray-800 focus:ring-0 focus:border-amber-500 w-20"
                    @input="validateYear(session)"
                  />
                </td>
                <td class="px-3 py-4 whitespace-nowrap">
                  <div v-if="editableRowId !== session.id" class="flex items-center gap-3 text-[11px] font-medium text-gray-600 tracking-tight">
                    <span class="flex items-center gap-1"><i class="far fa-calendar-alt text-emerald-500"></i> {{ session.date_debut }}</span>
                    <span class="text-gray-300">/</span>
                    <span class="flex items-center gap-1"><i class="far fa-calendar-times text-rose-500"></i> {{ session.date_fin }}</span>
                  </div>
                  <div v-else class="flex items-center gap-1">
                    <input v-model="session.date_debut" type="date" class="bg-white border border-gray-200 rounded px-1 py-0.5 text-[10px] font-bold focus:ring-1 focus:ring-amber-400" />
                    <input v-model="session.date_fin" type="date" class="bg-white border border-gray-200 rounded px-1 py-0.5 text-[10px] font-bold focus:ring-1 focus:ring-amber-400" />
                  </div>
                </td>
                <td class="px-3 py-4 whitespace-nowrap">
                  <div v-if="editableRowId !== session.id">
                    <div 
                      :class="{
                        'bg-emerald-50 text-emerald-700': session.etat === 'Ouvert',
                        'bg-amber-50 text-amber-700': session.etat === 'En_Cours',
                        'bg-gray-100 text-gray-500': session.etat === 'Clôturé'
                      }"
                      class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter"
                    >
                      {{ session.etat.replace('_', ' ') }}
                    </div>
                  </div>
                  <select 
                    v-else
                    v-model="session.etat" 
                    class="bg-white border border-gray-200 rounded px-1 py-0.5 text-[10px] font-bold focus:ring-1 focus:ring-amber-400"
                  >
                    <option value="Ouvert">Ouvert</option>
                    <option value="En_Cours">En Cours</option>
                    <option value="Clôturé">Clôturé</option>
                  </select>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <div class="flex items-center justify-center gap-1.5">
                    <template v-if="editableRowId !== session.id">
                      <button 
                        @click="editableRowId = session.id" 
                        :disabled="session.etat === 'Clôturé'"
                        class="flex items-center gap-1 px-2 py-1 text-amber-600 hover:bg-amber-50 rounded border border-transparent hover:border-amber-100 transition-all disabled:opacity-30 group relative"
                      >
                        <i class="fas fa-edit text-[10px]"></i>
                        <span class="text-[9px] font-black uppercase tracking-tighter">Modifier</span>
                        <div v-if="session.etat === 'Clôturé'" class="absolute -top-10 left-1/2 -translate-x-1/2 hidden group-hover:block bg-slate-800 text-white text-[9px] px-3 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50">
                          Session Clôturée - Modification interdite
                        </div>
                      </button>
                      <button 
                        @click="redirectToActivite(session.id)"
                        class="flex items-center gap-1 px-2 py-1 text-indigo-600 hover:bg-indigo-50 rounded border border-transparent hover:border-indigo-100 transition-all"
                      >
                        <i class="fas fa-history text-[10px]"></i>
                        <span class="text-[9px] font-black uppercase tracking-tighter">Historique</span>
                      </button>
                      <button 
                        @click="supprimerSession(session.id)" 
                        :disabled="session.etat === 'Clôturé'"
                        class="flex items-center gap-1 px-3 py-1 text-gray-400 hover:text-rose-500 hover:bg-rose-50 rounded-md transition-all disabled:opacity-20"
                      >
                        <i class="fas fa-trash-alt text-xs"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Supprimer</span>
                      </button>
                    </template>

                    <template v-else>
                      <button 
                        @click="confirmerModification(session)" 
                        class="px-4 py-1.5 bg-emerald-600 text-white rounded-md font-bold text-[10px] uppercase tracking-wider shadow-sm"
                        :disabled="isLoading" 
                      >
                        <i class="fas fa-check mr-1 text-[8px]"></i> Valider
                      </button>
                      <button 
                        @click="editableRowId = null" 
                        class="px-3 py-1.5 bg-gray-100 text-gray-500 rounded-md font-bold text-[10px] uppercase tracking-wider"
                      >
                        Annuler
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pagination Sobre (Toujours visible si résultats) -->
    <div v-if="filteredSessions.length > 0" class="flex justify-center items-center gap-4 py-8">
      <button
        @click="previousPage"
        :disabled="currentPage === 1"
        class="px-4 py-2 bg-white border border-gray-200 text-gray-500 hover:text-emerald-600 hover:border-emerald-200 rounded-lg transition-all disabled:opacity-30"
      >
        <i class="fas fa-chevron-left text-xs"></i>
      </button>
      
      <div class="flex items-center gap-3">
        <div class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold text-sm shadow-sm">
          {{ currentPage }}
        </div>
        <span class="text-xs font-bold text-gray-400">sur {{ totalPages }}</span>
      </div>

      <button
        @click="nextPage"
        :disabled="currentPage === totalPages"
        class="px-4 py-2 bg-white border border-gray-200 text-gray-500 hover:text-emerald-600 hover:border-emerald-200 rounded-lg transition-all disabled:opacity-30"
      >
        <i class="fas fa-chevron-right text-xs"></i>
      </button>
    </div>

    <!-- Alert Styled Simply -->
    <div v-if="alertMessage" 
      class="fixed top-24 left-1/2 -translate-x-1/2 z-[100] px-5 py-2.5 rounded-lg shadow-lg flex items-center gap-2 transition-all"
      :class="isSuccess ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"
    >
      <i class="fas" :class="isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
      <span class="text-xs font-bold uppercase tracking-wider">{{ alertMessage }}</span>
    </div>


    <!-- Modals (Outside main spacing) -->
    <FormulaireCreationSession 
      v-if="showFormulaire" 
      @close="showFormulaire = false" 
      @submitForm="fetchSessions"
    />
  </div>
</template>
<script>
import FormulaireCreationSession from './FormulaireCreationSession.vue';
import axios from 'axios';

export default {
  name: 'GestionSessionsActivites',
  components: {
    FormulaireCreationSession,
  },
  data() {
    return {
      sessions: [],
      currentPage: 1,
      itemsPerPage: 7,
      showFormulaire: false,
      searchQuery: '',
      isLoading: false,
      alertMessage: '',
      isSuccess: false,
      editableRowId: null,
      actionLoading: false,
    };
  },
  computed: {
    
    filteredSessions() {
      return this.sessions.filter(session => 
        session.annee.toString().includes(this.searchQuery) || 
        session.etat.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    totalPages() {
      return Math.ceil(this.filteredSessions.length / this.itemsPerPage);
    },
    paginatedSessions() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredSessions.slice(start, start + this.itemsPerPage);
    },
  },
  methods: {
    redirectToActivite(id) {
      this.$router.push({ name: 'GestionProgrammesActivitesHistorique', params: { id } });

    },
    async fetchSessions() {
      this.isLoading = true;
      try {
        const response = await axios.get('/api/sessions-activites');
        this.sessions = response.data;
      } catch (error) {
        console.error('Erreur lors de la récupération des sessions:', error);
        this.showAlert('Erreur lors de la récupération des sessions.', false);
      } finally {
        this.isLoading = false;
      }
    },
    accederFormulaireCreation() {
      this.showFormulaire = true;
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    async confirmerModification(session) {
      if (confirm('Êtes-vous sûr de vouloir modifier cette session ?')) {
        this.isLoading = true;
        try {
          const response = await axios.put(
            `/api/sessions-activites/${session.id}`,
            {
              annee: session.annee,
              date_debut: session.date_debut,
              date_fin: session.date_fin,
              etat: session.etat,
            }
          );

          if (response.status === 200) {
            this.showAlert('Modifications enregistrées avec succès!', true);
            await this.fetchSessions();
          }
           this.editableRowId = null; // Quitte le mode édition après validation
        } catch (error) {
          console.error('Erreur lors de la modification de la session:', error);
          if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            let messages = [];
            for (const key in errors) {
              messages.push(...errors[key]);
            }
            this.showAlert('Erreur de validation : ' + messages.join(' | '), false);
          } else {
            this.showAlert('Erreur lors de la modification de la session. Veuillez réessayer.', false);
          }
        } finally {
          this.isLoading = false;
        }
      }
    },
    annulerModification() {
      if (!confirm("Êtes-vous sûr de vouloir annuler cette modification ?")) {
        console.log('Soumission annulée par l\'utilisateur.');
        return; // Sort de la fonction si l'utilisateur annule.
      }
    this.editableRowId = null; // Annule l'édition et rétablit les valeurs initiales
  },
    validateYear(session) {
      if (session.annee && isNaN(session.annee)) {
        alert('Veuillez entrer une année valide.');
        session.annee = '';
      }
    },
    showAlert(message, success) {
      this.alertMessage = message;
      this.isSuccess = success;
      setTimeout(() => {
        this.alertMessage = '';
      }, 2000);
    },
    async supprimerSession(id) {
      if (this.actionLoading) return;
      if (!confirm("Voulez-vous vraiment supprimer cette session ?")) return;

      this.actionLoading = true;
      try {
        const response = await axios.delete(`/api/sessions/supprimer/${id}`);
        this.message = response.data.message;
        this.fetchSessions();
      } catch (error) {
        console.error("Erreur lors de la suppression :", error);
      } finally {
        this.actionLoading = false;
      }
    },
  },
  mounted() {
    this.fetchSessions();
  },
};
</script>


<style scoped>
.gestion-sessions-activites {
  padding: 20px;
}
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
}
.alert {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 1rem;
  border-radius: 0.5rem;
  z-index: 1000;
  width: auto;
}
.alert-success {
  background-color: #4caf50;
  color: white;
}
.alert-error {
  background-color: #f44336;
  color: white;
}
</style>
