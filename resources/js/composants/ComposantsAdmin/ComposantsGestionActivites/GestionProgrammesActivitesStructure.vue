<template>
  <div class="flex flex-col items-center min-h-screen" :class="{ '': standalone }">
    <!-- Header avec retour et titre (Sober Style) -->
    <div v-if="standalone" class="w-full bg-gray-50 shadow-md border-b border-gray-200 py-3 px-4 md:px-8 flex items-center mb-8">
      <div class="w-1/4">
        <router-link to="/admin" class="text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-2">
          <i class="fas fa-arrow-left text-xl"></i>
          <span class="text-xs font-bold uppercase hidden md:inline">Retour</span>
        </router-link>
      </div>
      <div class="w-2/4 text-center">
        <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter">Programme d'Activités de la Structure</h1>
      </div>
      <div class="w-1/4"></div>
    </div>
      <!-- Contenu principal -->
      <div class="w-full mt-4 px-4 md:px-6">
        <!-- Affichage conditionnel pour mobile : Liste des activités ou Détails -->
        <div v-if="isMobile">
          <!-- Bloc gauche : Liste des sessions et activités (visible par défaut) -->
          <div v-if="!activiteIdSelectionne" class="w-full h-[calc(100vh-150px)] overflow-y-auto px-4">
            <div class="flex flex-col items-center w-full space-y-4">
              <!-- Titre -->
              <h1 class="text-yellow-500 text-lg font-bold uppercase tracking-tight">Liste du Programme</h1>
              <div class="flex items-center space-x-4 w-full justify-center">
                <!-- Barre de recherche -->
                <input
                  type="text"
                  v-model="searchQuery"
                  placeholder="Rechercher..."
                  class="w-full border border-gray-300 px-4 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white text-gray-700"
                />
                 <!-- Sélecteur de sessions -->
                <select
                  v-model="sessionsId"
                  class="border border-gray-300 rounded px-4 py-2 bg-white"
                  @change="fetchActivites(sessionsId)"
                >
                  <option value="" disabled>Session</option>
                  <option v-for="session in sessions" :key="session.id" :value="session.id">
                    {{ session.annee }}
                  </option>
                </select>
              </div>
            </div>
            <!-- Liste des activités -->
            <div v-if="filteredActivites.length" class="w-full pb-10">
              <div
                v-for="activite in filteredActivites"
                :key="activite.id"
                class="rounded-xl shadow-sm border border-gray-100 py-5 px-4 mt-5 cursor-pointer transition w-full hover:shadow-md"
                :class="{
                  'bg-green-50 border-green-200': activite.etat_slection === 'Validé',
                  'bg-white': activite.etat_slection !== 'Validé',
                }"
                @click="selectionnerActivite(activite.id)"
              >
                <div class="flex justify-between items-start">
                   <div class="flex flex-col">
                      <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ activite.structure_sigle }}</span>
                      <p class="font-bold text-gray-800 line-clamp-2 leading-tight mt-1">{{ activite.libelle || 'Libellé indisponible' }}</p>
                   </div>
                   <span class="bg-green-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase">Validée</span>
                </div>
              </div>
            </div>
            <p v-else class="text-gray-500 mt-10 text-center italic">Aucune activité trouvée dans le programme.</p>
          </div>

          <!-- Bloc droit : Détails de l'activité -->
          <div v-if="activiteIdSelectionne" class="w-full h-[calc(100vh-150px)] flex flex-col items-center mt-4 pb-20">
            <!-- Bouton Retour -->
            <div class="fixed left-2 mt-0 z-40">
              <button 
                @click="retournerAuBlocGauche" 
                class="bg-white border border-gray-200 text-blue-500 px-3 py-2 rounded-lg shadow-lg flex items-center active:scale-95 transition-all"
              >
                <i class="fas fa-arrow-left text-lg"></i>
                <span class="ml-1 text-sm font-bold">RETOUR</span>
              </button>
            </div>
            <!-- Composant AfficherActivite -->
            <AfficherActivite
              :key="activiteIdSelectionne + '-' + refreshCount"
              class="mt-7 w-full"
              :activite-id="activiteIdSelectionne"
              :activite="activites.find(a => a.id === activiteIdSelectionne)"
            />
          </div>
        </div>

        <!-- Affichage pour PC et tablette -->
        <div v-if="!isMobile" class="flex w-full h-[calc(100vh-180px)] flex-col md:flex-row bg-white rounded-t-3xl shadow-2xl overflow-hidden border-t border-x border-gray-100">
          <!-- Bloc gauche : Liste des activités -->
          <div class="w-full md:w-2/5 overflow-y-auto px-6 py-6 h-full border-r border-gray-100 bg-gray-50/30">
            <div class="flex flex-col items-center w-full space-y-5">
              <!-- Filtres -->
              <div class="flex flex-col w-full space-y-3">
                <h2 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Recherche & Session</h2>
                <div class="flex items-center space-x-3">
                  <div class="relative flex-grow">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300"></i>
                    <input
                      type="text"
                      v-model="searchQuery"
                      placeholder="Filtrer le programme..."
                      class="w-full border border-gray-200 pl-10 pr-4 py-2 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 transition duration-200 bg-white text-gray-700 text-sm"
                    />
                  </div>
                  <!-- Sélecteur de sessions -->
                  <select
                    v-model="sessionsId"
                    class="border border-gray-200 rounded-xl px-4 py-2 bg-white text-sm shadow-sm focus:ring-2 focus:ring-amber-500 outline-none"
                    @change="fetchActivites(sessionsId)"
                  >
                    <option v-for="session in sessions" :key="session.id" :value="session.id">
                      {{ session.annee }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
            
            <!-- Liste des activités -->
            <div v-if="filteredActivites.length" class="w-full mt-6 space-y-4 pb-10">
              <div
                v-for="activite in filteredActivites"
                :key="activite.id"
                class="group relative rounded-2xl p-5 cursor-pointer transition-all duration-300 border bg-white"
                :class="{
                  'border-amber-400 ring-2 ring-amber-100 shadow-lg -translate-y-1 scale-[1.02]': activite.id === activiteIdSelectionne,
                  'border-gray-100 shadow-sm hover:border-amber-200 hover:shadow-md hover:-translate-y-0.5': activite.id !== activiteIdSelectionne,
                }"
                @click="selectionnerActivite(activite.id)"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-grow pr-4">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ activite.structure_sigle }}</span>
                      <span class="w-1 h-1 rounded-full bg-gray-200"></span>
                      <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">VALIDÉE</span>
                    </div>
                    <p class="font-black text-gray-800 text-sm leading-snug transition-colors group-hover:text-amber-600">
                      {{ activite.libelle || 'Libellé indisponible' }}
                    </p>
                  </div>
                  <div class="flex-shrink-0 pt-1">
                    <i class="fas fa-chevron-right text-gray-200 transition-transform group-hover:translate-x-1" :class="{'text-amber-500 translate-x-1': activite.id === activiteIdSelectionne}"></i>
                  </div>
                </div>
                <!-- Indicateur de sélection -->
                <div v-if="activite.id === activiteIdSelectionne" class="absolute left-0 top-1/4 bottom-1/4 w-1 bg-amber-500 rounded-r-full"></div>
              </div>
            </div>
            <p v-else class="text-gray-400 mt-10 text-center italic text-sm">Aucune activité trouvée pour votre structure.</p>
          </div>

          <!-- Bloc droit : Composant AfficherActivite -->
          <div class="w-full md:w-3/5 flex flex-col mt-4 md:mt-0 h-full overflow-y-auto bg-white p-6">
            <!-- Actions contextualisées -->
            <div v-if="activiteIdSelectionne" class="flex justify-end items-center space-x-3 mb-6 p-3 bg-gray-50/50 rounded-2xl border border-gray-100">
               <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mr-auto ml-2">Actions de gestion :</span>
               <button 
                    @click="redirectToPlanning(activites.find(a => a.id === activiteIdSelectionne))" 
                    class="bg-indigo-600 text-white px-4 py-2 rounded-xl shadow-sm hover:bg-indigo-700 transition-all flex items-center text-sm font-bold"
                >
                    <i class="fas fa-tasks mr-2"></i> 
                    <span>Planifier</span>
                </button>
                <button 
                    @click="accederFormulaireModification" 
                    class="bg-green-500 text-white px-4 py-2 rounded-xl shadow-sm transition-all duration-200 hover:bg-green-600 flex items-center text-sm font-bold"
                    :class="{'opacity-50 cursor-not-allowed': !pouvonsModifier}"
                    :disabled="!pouvonsModifier"
                >
                    <i class="fas fa-edit mr-2"></i> 
                    <span>Modifier</span>
                </button>
            </div>

            <AfficherActivite
              v-if="activiteIdSelectionne"
              :key="activiteIdSelectionne + '-' + refreshCount"
              :activite-id="activiteIdSelectionne"
              :activite="activites.find(a => a.id === activiteIdSelectionne)"
            />
            <div v-else class="flex flex-col items-center justify-center h-full text-gray-300">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                  <i class="fas fa-clipboard-list text-5xl"></i>
                </div>
                <span class="text-lg font-bold uppercase tracking-widest text-gray-400 px-10 text-center">Sélectionnez une activité pour en consulter les détails</span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="alertMessage" :class="['alert shadow-2xl', isSuccess ? 'alert-success' : 'alert-error']">
        <div class="flex items-center space-x-2">
          <i :class="isSuccess ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'"></i>
          <span class="font-bold">{{ alertMessage }}</span>
        </div>
      </div>
    
    <!-- Formulaire Modification Activité -->
    <FormulaireModificationActivite
      v-if="showFormulaire"
      @close="showFormulaire = false"
      @soumettreFormulaire="handleUpdate"
      :activiteId="activiteIdSelectionne"
      :activite="activites.find(a => a.id === activiteIdSelectionne)"
    />
  </div>
</template>

<script>
import axios from 'axios';
import AfficherActivite from './AfficherActivite.vue';
import FormulaireModificationActivite from './FormulaireModificationActivite.vue';

export default {
  name: 'GestionProgrammesActivitesStructure',
  props: {
    standalone: {
      type: Boolean,
      default: true
    }
  },
  components: {
    AfficherActivite,
    FormulaireModificationActivite,
  },
  data() {
    return {
      activites: [],
      sessions: [],
      activiteIdSelectionne: null,
      searchQuery: '',
      alertMessage: '',
      isSuccess: true,
      showFormulaire: false,
      sessionsId: null,
      isAdmin: false,
      isChefService: false,
      isResponsable: false,
      isPlanificateur: false,
      refreshCount: 0,
      isMobile: false,
      actionLoading: false,
    };
  },
  computed: {
    filteredActivites() {
      return this.activites.filter((activite) => {
        const searchQuery = this.searchQuery.toLowerCase();
        return (
          activite.libelle?.toLowerCase().includes(searchQuery) ||
          activite.structure_sigle?.toLowerCase().includes(searchQuery)
        );
      });
    },
    pouvonsModifier() {
      if (!this.activiteIdSelectionne) return false;
      return this.isAdmin || this.isChefService || this.isResponsable;
    }
  },
  methods: {
    selectionnerActivite(activiteId) {
      this.activiteIdSelectionne = activiteId;
    },
    retournerAuBlocGauche() {
      this.activiteIdSelectionne = null;
    },
    async fetchActivites(sessionsId) {
      if (!sessionsId) {
        this.showAlert('Veuillez sélectionner une session.', false);
        return;
      }
      try {
        // CHANGEMENT ICI : Utilisation de l'endpoint /st pour la structure
        const response = await axios.get(`/api/activites/session/${sessionsId}/st`);
        this.activites = response.data || [];
      } catch (error) {
        console.error('Erreur lors de la récupération des activités :', error);
        this.activites = [];
        this.showAlert('Erreur lors de la récupération des activités.', false);
      }
    },
    async fetchSessions() {
      try {
        const response = await axios.get('/api/sessions-activites');
        this.sessions = response.data;
      } catch (error) {
        console.error('Erreur lors de la récupération des sessions :', error);
      }
    },
    async fetchSessionEnCours() {
      try {
        const response = await axios.get('/api/session-Ouvert');
        this.sessionsId = this.$route.params.id 
          ? parseInt(this.$route.params.id) 
          : (response.data?.id ? response.data.id : null);
        
        if (this.sessionsId) {
          await this.fetchActivites(this.sessionsId);
        } else {
          this.showAlert('Aucune session en cours trouvée.', false);
        }
      } catch (error) {
        console.error('Erreur lors de la session en cours :', error);
      }
    },
    async fetchUserInfo() {
      try {
        const response = await axios.get('/api/user-info');
        const user = response.data;
        this.isAdmin = user.role === 'Administrateur';
        this.isChefService = user.role === 'Chef-de-service';
        this.isResponsable = user.role === 'Responsable-de-structure';
        this.isPlanificateur = user.role === 'Planificateur';
      } catch (error) {
        console.error('Erreur info utilisateur :', error);
      }
    },
    accederFormulaireModification() {
      if (!this.activiteIdSelectionne) {
        this.showAlert('Veuillez sélectionner une activité.', false);
        return;
      }
      this.showFormulaire = true;
    },
    handleUpdate() {
      this.fetchActivites(this.sessionsId);
      this.refreshCount++;
    },
    redirectToPlanning(activite) {
      if (!activite) return;
      if (activite.etat_session === 'Ouvert' || activite.etat_session === 'En_Cours') {
          this.$router.push({ name: 'GestionActivites', params: { id: activite.id } });
      } else {
          this.showAlert('Session Clôturée, impossible d\'accéder à l\'interface de planification.', false);
      }
    },
    showAlert(message, isSuccess = true) {
      this.alertMessage = message;
      this.isSuccess = isSuccess;
      setTimeout(() => {
        this.alertMessage = '';
      }, 3000);
    },
  },
  async mounted() {
    this.isMobile = window.innerWidth <= 768;
    window.addEventListener('resize', () => {
      this.isMobile = window.innerWidth <= 768;
    });
    
    await this.fetchUserInfo();
    await this.fetchSessions();
    await this.fetchSessionEnCours();
  },
};
</script>

<style scoped>
.alert {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 1rem 2rem;
  border-radius: 1rem;
  z-index: 1000;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  opacity: 1;
}
.alert-success {
  background-color: #10b981;
  color: white;
}
.alert-error {
  background-color: #ef4444;
  color: white;
}

::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>
