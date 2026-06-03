<template>
  <div class="min-h-screen bg-gray-50/50 pb-20">
    <!-- Header Harmonisé Style Canevas/Sessions -->
    <div class="w-full bg-gray-50 border-b border-gray-200 py-3 px-4 md:px-8 flex items-center mb-8 shadow-sm">
      <!-- Gauche : Retour -->
      <div class="w-1/4">
        <router-link to="/admin" class="text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-2">
          <i class="fas fa-arrow-left text-xl"></i>
          <span class="text-[10px] font-black uppercase hidden md:inline tracking-widest">Retour</span>
        </router-link>
      </div>

      <!-- Centre : Titre -->
      <div class="w-2/4 text-center">
        <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter">Archives & Historique</h1>
      </div>

      <!-- Droite : Espace vide ou Info -->
      <div class="w-1/4 flex justify-end">
        <div class="px-3 py-1 bg-white border border-gray-200 rounded-lg text-[9px] font-black text-slate-400 uppercase tracking-widest hidden md:block">
           Consultation Uniquement
        </div>
      </div>
    </div>

    <!-- Barre de Filtres et Contrôles -->
    <div class="w-full max-w-[99%] mx-auto px-4 md:px-8 mb-8">
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-6 transition-all hover:shadow-md">
        
        <div class="flex flex-col md:flex-row items-center gap-6 w-full lg:w-auto">
          <!-- Sélecteur de Session -->
          <div class="relative group w-full md:w-80">
             <label class="absolute -top-2 left-3 bg-white px-1 text-[9px] font-black text-amber-500 uppercase tracking-widest z-10 transition-all">Session de Travail</label>
             <select
                v-model="sessionsId"
                class="w-full pl-4 pr-10 py-3 bg-gray-50 border border-gray-100 rounded-xl text-xs font-bold text-slate-700 outline-none focus:ring-4 focus:ring-amber-50/50 focus:border-amber-400 focus:bg-white transition-all appearance-none cursor-pointer"
                @change="handleSessionChange"
              >
                <option :value="null" disabled>Choisissez une année...</option>
                <option v-for="session in sessions" :key="session.id" :value="session.id">
                  SESSION {{ session.annee }} ({{ session.etat }})
                </option>
              </select>
              <i class="fas fa-calendar-alt absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none group-focus-within:text-amber-400 transition-colors"></i>
          </div>

          <!-- Barre de Recherche -->
          <div class="relative group w-full md:w-96">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-amber-500 transition-colors"></i>
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Rechercher une activité ou une structure responsable..."
              class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-xs font-bold text-slate-600 focus:ring-4 focus:ring-amber-50/50 focus:border-amber-400 focus:bg-white transition-all outline-none"
            />
          </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="flex items-center gap-4">
          <div class="flex flex-col items-end">
            <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest leading-none">Total Activités</span>
            <span class="text-xl font-black text-slate-800 tracking-tighter">{{ filteredActivites.length }}</span>
          </div>
          <div class="w-px h-8 bg-gray-100"></div>
          <i class="fas fa-history text-amber-200 text-2xl"></i>
        </div>
      </div>
    </div>

    <!-- Navigation par Onglets -->
    <div class="w-full max-w-[99%] mx-auto mt-8 px-4 md:px-8">
      <div class="flex items-center gap-2 bg-indigo-50/50 p-1.5 rounded-2xl border border-indigo-100/50 w-fit mb-8 shadow-sm">
        <button 
          @click="activeTab = 'activites'"
          :class="activeTab === 'activites' ? 'bg-white text-indigo-600 shadow-md ring-1 ring-black/5' : 'text-slate-500 hover:bg-white/50 hover:text-indigo-400'"
          class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2"
        >
          <i class="fas fa-list-ul"></i> Liste des Activités
        </button>
        <button 
          @click="activeTab = 'matrice'"
          :class="activeTab === 'matrice' ? 'bg-white text-emerald-600 shadow-md ring-1 ring-black/5' : 'text-slate-500 hover:bg-white/50 hover:text-emerald-400'"
          class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2"
        >
          <i class="fas fa-th"></i> Matrice de Session
        </button>
        <button 
          @click="activeTab = 'rapports'"
          :class="activeTab === 'rapports' ? 'bg-white text-amber-600 shadow-md ring-1 ring-black/5' : 'text-slate-500 hover:bg-white/50 hover:text-amber-400'"
          class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2"
        >
          <i class="fas fa-file-invoice"></i> Rapports d'Exécution
        </button>
      </div>

      <!-- Contenu des Onglets -->
      <transition name="fade" mode="out-in">
        <div :key="activeTab">
          <!-- Onglet ACTIVITES -->
          <div v-if="activeTab === 'activites'">
            <!-- Indicateur de chargement -->
            <div v-if="isLoading" class="flex flex-col items-center justify-center py-24">
              <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
              <p class="text-xs font-black text-indigo-400 uppercase tracking-widest animate-pulse">Chargement des données...</p>
            </div>

            <!-- Version PC/Tablette : Master-Detail -->
            <div v-else-if="filteredActivites.length" class="flex flex-col lg:flex-row gap-8 min-h-[600px] h-[calc(100vh-280px)]">
              <!-- Colonne GAUCHE : Liste d'activités (40%) -->
              <div class="w-full lg:w-2/5 flex flex-col gap-4">
                 <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ filteredActivites.length }} Activités trouvées</span>
                 </div>
                 <div class="flex-1 overflow-y-auto px-1 space-y-4 custom-scrollbar pb-10">
                    <div
                      v-for="activite in filteredActivites"
                      :key="activite.id"
                      @click="selectionnerActivite(activite.id)"
                      class="group relative p-5 bg-white rounded-2xl border cursor-pointer transition-all duration-300 active:scale-[0.98]"
                      :class="activiteSelectionneeId === activite.id 
                        ? 'border-indigo-400 shadow-lg shadow-indigo-50 ring-2 ring-indigo-50' 
                        : 'border-slate-50 shadow-sm hover:border-indigo-200 hover:shadow-md'"
                    >
                      <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                           <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[9px] font-black uppercase tracking-wider">{{ activite.structure_sigle }}</span>
                           <div class="w-2 h-2 rounded-full" 
                                :class="{
                                  'bg-emerald-500': activite.etat_slection === 'Validé',
                                  'bg-amber-500': activite.etat_slection === 'Selectionné',
                                  'bg-rose-500': activite.etat_slection === 'Rejeté',
                                }"></div>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 leading-tight transition-colors group-hover:text-indigo-600"
                            :class="{'text-indigo-600': activiteSelectionneeId === activite.id}">
                          {{ activite.libelle }}
                        </h3>
                      </div>
                      <!-- Indicateur de sélection à DROITE pour la colonne gauche -->
                      <div v-if="activiteSelectionneeId === activite.id" class="absolute right-0 top-4 bottom-4 w-1 bg-indigo-500 rounded-l-full"></div>
                    </div>
                 </div>
              </div>

              <!-- Colonne DROITE : Détails de l'activité (60%) -->
              <div class="w-full lg:w-3/5 bg-white rounded-[2.5rem] border border-gray-100 shadow-xl overflow-y-auto custom-scrollbar relative">
                <div v-if="activiteSelectionneeId" class="h-full flex flex-col">
                   <!-- Barre d'Action Détails -->
                   <div class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-100 px-8 py-4 flex items-center justify-between">
                      <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Détails de l'activité</span>
                      <button 
                        @click="redirectToPlanning(activites.find(a => a.id === activiteSelectionneeId))"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all flex items-center gap-2 active:scale-95"
                      >
                        <i class="fas fa-tasks"></i> Planification
                      </button>
                   </div>
                   <div class="p-4 flex-1">
                      <AfficherActivite :key="activiteSelectionneeId" :activite-id="activiteSelectionneeId" />
                   </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center h-full text-center opacity-40">
                  <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 text-4xl">
                    <i class="fas fa-hand-pointer text-indigo-300"></i>
                  </div>
                  <h4 class="text-lg font-black text-slate-400 uppercase tracking-widest">Sélectionnez une activité</h4>
                  <p class="text-xs font-bold text-slate-300 mt-2 uppercase tracking-tight">Utilisez la liste de gauche pour explorer les archives</p>
                </div>
              </div>
            </div>

            <!-- État vide -->
            <div v-else class="flex flex-col items-center justify-center py-24 bg-white rounded-[40px] border-2 border-dashed border-gray-100 text-center">
              <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mb-6 text-3xl">
                <i class="fas fa-folder-open"></i>
              </div>
              <h3 class="text-xl font-bold text-slate-400 uppercase tracking-widest">Aucune activité archivée</h3>
              <p class="text-gray-400 text-xs mt-2 italic">Sélectionnez une session pour charger l'historique correspondante.</p>
            </div>
          </div>

          <!-- Onglet MATRICE -->
          <div v-else-if="activeTab === 'matrice'" class="max-h-[75vh] overflow-y-auto pr-2 scrollbar-premium">
            <GestionMatriceDactivite :standalone="false" :targetSessionId="sessionsId" />
          </div>

          <!-- Onglet RAPPORTS -->
          <div v-else-if="activeTab === 'rapports'" class="max-h-[75vh] overflow-y-auto pr-2 scrollbar-premium">
            <GestoinRapport :standalone="false" :targetSessionId="sessionsId" />
          </div>
        </div>
      </transition>
    </div>

    <!-- Alertes -->
    <div v-if="alertMessage" 
      class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[100] px-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3 transition-all animate-bounceIn"
      :class="isSuccess ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"
    >
      <i class="fas" :class="isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
      <span class="text-xs font-black uppercase tracking-wider">{{ alertMessage }}</span>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import AfficherActivite from './AfficherActivite.vue';
import GestionMatriceDactivite from '../ComposantsGestionRapportsTrimestriels/GestionMatriceDactivite.vue';
import GestoinRapport from '../ComposantsGestionRapportsTrimestriels/GestoinRapport.vue';

export default {
  name: 'GestionProgrammesActivitesHistorique',
  components: {
    AfficherActivite,
    GestionMatriceDactivite,
    GestoinRapport
  },
  data() {
    return {
      activites: [],
      sessions: [],
      sessionsId: "",
      searchQuery: '',
      alertMessage: '',
      isSuccess: true,
      activeTab: 'activites',
      activiteSelectionneeId: null,
      isLoading: false,
      isPointFocal: false,
      isResponsable: false,
      isPlanificateur: false,
    };
  },
  computed: {
    filteredActivites() {
      if (this.isLoading) return [];
      const q = this.searchQuery.toLowerCase();
      return this.activites.filter(a => 
        a.libelle?.toLowerCase().includes(q) ||
        a.structure_sigle?.toLowerCase().includes(q)
      );
    }
  },
  methods: {
    handleSessionChange() {
      if (this.sessionsId) {
        this.activites = [];
        this.fetchActivites(this.sessionsId);
      }
    },
    redirectToPlanning(activite) {
      this.$router.push({ name: 'GestionActivites', params: { id: activite.id } });
    },
    selectionnerActivite(id) {
       this.activiteSelectionneeId = id;
    },
    async fetchActivites(sessionId) {
      this.isLoading = true;
      try {
        // CHANGEMENT : Filtrage par structure pour les rôles spécifiques
        const endpoint = (this.isPointFocal || this.isResponsable || this.isPlanificateur) 
          ? `/api/activites/session/${sessionId}/st` 
          : `/api/activites/session/${sessionId}/pa`;
          
        const response = await axios.get(endpoint);
        this.activites = response.data || [];
        
        // Auto-sélection de la première activité si disponible
        if (this.activites.length > 0 && !this.activiteSelectionneeId) {
          this.activiteSelectionneeId = this.activites[0].id;
        }
      } catch (error) {
        console.error('Erreur :', error);
        this.activites = [];
        this.showAlert('Erreur lors du chargement des activités', false);
      } finally {
        this.isLoading = false;
      }
    },
    async fetchSessions() {
      try {
        const response = await axios.get('/api/sessions-activites');
        this.sessions = response.data;
      } catch (error) {
        console.error('Erreur sessions:', error);
      }
    },
    async autoSelectSession() {
      if (this.$route.params.id) {
        this.sessionsId = Number(this.$route.params.id);
        await this.fetchActivites(this.sessionsId);
      } else {
        try {
          const res = await axios.get('/api/session-Ouvert');
          if (res.data && res.data.id) {
            this.sessionsId = res.data.id;
            await this.fetchActivites(this.sessionsId);
          } else if (this.sessions.length > 0) {
            // Si pas de session ouverte, on prend la plus récente par ID ou année
            const latest = [...this.sessions].sort((a,b) => b.annee - a.annee)[0];
            this.sessionsId = latest.id;
            await this.fetchActivites(this.sessionsId);
          } else {
            this.showAlert('Aucune session trouvée dans le système.', false);
          }
        } catch (e) {
          // Fallback sur la liste locale si l'API de session ouverte échoue
          if (this.sessions.length > 0) {
            const latest = [...this.sessions].sort((a,b) => b.annee - a.annee)[0];
            this.sessionsId = latest.id;
            await this.fetchActivites(this.sessionsId);
          }
        }
      }
    },
    async fetchUserInfo() {
      try {
        const response = await axios.get('/api/user-info');
        const user = response.data;
        this.isPointFocal = user.role === 'Point-Focale';
        this.isResponsable = user.role === 'Responsable-de-structure';
        this.isPlanificateur = user.role === 'Planificateur';
      } catch (error) {
        console.error('Erreur info utilisateur :', error);
      }
    },
    showAlert(message, isSuccess = true) {
      this.alertMessage = message;
      this.isSuccess = isSuccess;
      setTimeout(() => { this.alertMessage = ''; }, 3000);
    },
  },
  async mounted() {
    await this.fetchUserInfo();
    await this.fetchSessions();
    await this.autoSelectSession();
  },
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

@keyframes floatIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes bounceIn {
  0% { transform: translate(-50%, 40px); opacity: 0; }
  60% { transform: translate(-50%, -5px); opacity: 1; }
  100% { transform: translate(-50%, 0); }
}

.animate-floatIn { animation: floatIn 0.4s ease-out; }
.animate-bounceIn { animation: bounceIn 0.5s ease-out; }

/* Custom Line Clamp if Tailwind isn't enabled */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}

/* Scrollbar Premium */
.scrollbar-premium::-webkit-scrollbar {
  width: 6px;
}
.scrollbar-premium::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-premium::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
  transition: background 0.3s;
}
.scrollbar-premium::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
.scrollbar-premium {
  scrollbar-width: thin;
  scrollbar-color: #e2e8f0 transparent;
}
</style>
  