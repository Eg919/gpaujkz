<template>
  <div class="flex flex-col items-center min-h-screen bg-gray-50/50 pb-10">
    <!-- Header avec titre centré Jaune-Orange -->
    <div class="w-full bg-gray-50 border-b border-gray-200 py-3 px-4 md:px-8 flex items-center mb-8">
      <!-- Gauche : Retour -->
      <div class="w-1/4">
        <router-link to="/admin" class="text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-2">
          <i class="fas fa-arrow-left text-xl"></i>
          <span class="text-xs font-bold uppercase hidden md:inline">Retour</span>
        </router-link>
      </div>

      <div class="w-2/4 text-center">
        <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter">Canevas d'Activités</h1>
      </div>

      <!-- Droite : Action -->
      <div class="w-1/4 flex justify-end">
        <button 
          @click="accederFormulaireCreation" 
          :disabled="!isSessionOuverte"
          class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg shadow-sm transition-all active:scale-95 flex items-center gap-2 font-bold text-sm disabled:opacity-50 disabled:cursor-not-allowed group relative"
        >
          <i class="fas fa-plus text-xs"></i> 
          <span class="hidden md:inline">Proposer une activité</span>
          <span class="md:hidden">Proposer</span>
          <div v-if="!isSessionOuverte" class="absolute -bottom-10 right-0 hidden group-hover:block bg-slate-800 text-white text-[9px] px-3 py-1.5 rounded-lg whitespace-nowrap shadow-xl z-50">
            Aucune session ouverte pour de nouvelles propositions
          </div>
        </button>
      </div>
    </div>

    <!-- Onglets pour séparer les activités -->
    <div class="w-full max-w-[99%] mx-auto px-4 md:px-8 mb-4">
      <div class="flex border-b border-gray-200">
        <button 
          @click="activeTab = 'structure'"
          :class="{'border-emerald-500 text-emerald-600 font-bold': activeTab === 'structure', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'structure'}"
          class="px-6 py-3 border-b-2 font-medium text-sm transition-colors uppercase tracking-widest flex items-center gap-2"
        >
          <i class="fas fa-building"></i>
          Activités de ma structure
        </button>
        <button 
          @click="activeTab = 'partenaire'"
          :class="{'border-emerald-500 text-emerald-600 font-bold': activeTab === 'partenaire', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'partenaire'}"
          class="px-6 py-3 border-b-2 font-medium text-sm transition-colors uppercase tracking-widest flex items-center gap-2"
        >
          <i class="fas fa-handshake"></i>
          Activités partenaires
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
            placeholder="Rechercher une activité..." 
            class="w-full pl-11 pr-4 py-2.5 bg-gray-50 border border-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white text-sm font-medium text-gray-600 transition-all"
          />
        </div>
        
        <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-widest">
          <i class="fas fa-filter text-amber-500"></i>
          <span>{{ filteredActivites.length }} résultats filtrés</span>
        </div>
      </div>
    </div>

    <!-- Tableau Premium Style Sobre -->
    <div class="w-full max-w-[99%] mx-auto px-4 md:px-8">
      <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 uppercase text-[10px] tracking-widest font-black text-gray-400">
            <tr>
              <th scope="col" class="px-4 py-4 text-left">Activité & ID</th>
              <th scope="col" class="px-3 py-4 text-left">État</th>
              <th scope="col" class="px-3 py-4 text-left">Validation</th>
              <th scope="col" class="px-4 py-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="activite in paginatedActivites" :key="activite.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-4 max-w-[300px] md:max-w-sm lg:max-w-md">
                <div class="flex flex-col gap-1">
                  <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded w-fit tracking-tighter">ID #{{ activite.id }}</span>
                  <p 
                    v-if="activite.etat_slection === 'Validé' && activite.confirmation_presi" 
                    @click="redirectToActivite(activite.id)" 
                    class="text-[13px] font-bold text-gray-800 hover:text-emerald-600 hover:underline cursor-pointer transition-colors break-words line-clamp-2 leading-snug"
                  >
                    {{ activite.libelle }}
                  </p>
                  <p v-else class="text-[13px] font-bold text-gray-700 break-words line-clamp-2 leading-snug">{{ activite.libelle }}</p>
                </div>
              </td>
              <td class="px-3 py-4 whitespace-nowrap">
                <div class="flex items-center gap-1.5">
                  <span 
                    class="w-2 h-2 rounded-full"
                    :class="{
                      'bg-rose-500': activite.etat_activite === 'abendoner',
                      'bg-amber-400': activite.etat_activite === 'en-cours' || activite.etat_activite === 'Ouvert',
                      'bg-emerald-500': activite.etat_activite === 'terminer',
                      'bg-slate-300': activite.etat_activite === 'en-attente' || !activite.etat_activite
                    }"
                  ></span>
                  <span class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter">{{ (activite.etat_activite || 'en-attente').replace('-', ' ') }}</span>
                </div>
              </td>
              <td class="px-3 py-4 whitespace-nowrap">
                <div 
                  :class="{
                    'bg-rose-50 text-rose-700': activite.etat_slection === 'Rejeté',
                    'bg-amber-50 text-amber-700': activite.etat_slection === 'Selectionné' || activite.etat_slection === 'PRESELECTIONNÉ' || activite.etat_slection === 'Selectionne' || (activite.etat_slection === 'Validé' && !activite.confirmation_presi),
                    'bg-emerald-50 text-emerald-700': activite.etat_slection === 'Validé' && activite.confirmation_presi == 1
                  }"
                  class="inline-flex items-center px-2 py-1 rounded text-[9px] font-black uppercase tracking-tighter transition-colors"
                >
                  <span v-if="activite.reconduir" class="flex items-center gap-1"><i class="fas fa-redo text-[8px]"></i> Reconduit</span>
                  <span v-else>
                    {{ (activite.etat_slection === 'Validé' && !activite.confirmation_presi) ? 'En attente' : (activite.etat_slection || 'En attente') }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="flex items-center justify-center gap-1">
                  <button
                    @click.stop="accederFormulaireModification(activite)" 
                    :disabled="(activite.soumi && !isAdmin && !isChefService) || activite.etat_session === 'Clôturé'"
                    class="flex items-center gap-1 px-2 py-1 bg-white border border-gray-200 text-amber-600 rounded hover:bg-amber-50 hover:border-amber-200 transition-all active:scale-95 shadow-sm disabled:opacity-30 group relative"
                  >
                    <i class="fas fa-edit text-[10px]"></i>
                    <span class="text-[9px] font-black uppercase tracking-tighter">Modifier</span>
                  </button>
                  
                  <button 
                    v-if="!activite.soumi && (isResponsable || isAdmin)"
                    @click.stop="toggleSoumission(activite)" 
                    :disabled="!activite.taches_count || activite.taches_count === 0"
                    class="flex items-center gap-1 px-2 py-1 bg-white border border-gray-200 text-emerald-600 rounded hover:bg-emerald-50 hover:border-emerald-200 transition-all active:scale-95 shadow-sm disabled:opacity-30"
                  >
                    <i class="fas fa-paper-plane text-[10px]"></i>
                    <span class="text-[9px] font-black uppercase tracking-tighter">Soumettre</span>
                  </button>

                  <button
                    @click.stop="redirectToActivite(activite.id)"
                    class="flex items-center gap-1 px-2 py-1 bg-white border border-gray-200 text-indigo-600 rounded hover:bg-indigo-50 hover:border-indigo-200 transition-all active:scale-95 shadow-sm relative font-bold"
                  >
                    <i class="fas fa-chart-line text-[10px]"></i>
                    <span class="text-[9px] font-black uppercase tracking-tighter">Planifier</span>
                    <span v-if="activite.taches_count > 0" class="absolute -top-1 -right-1 bg-indigo-600 text-white text-[8px] w-3 h-3 flex items-center justify-center rounded-full leading-none shadow-sm">{{ activite.taches_count }}</span>
                  </button>

                  <button 
                    v-if="!activite.soumi"
                    @click="supprimerActivite(activite)" 
                    :disabled="activite.etat_session === 'Clôturé'"
                    class="p-1 px-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-all active:scale-90 disabled:opacity-20"
                    title="Supprimer"
                  >
                    <i class="fas fa-trash-alt text-[10px]"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination Sobre (Toujours visible si résultats) -->
    <div v-if="filteredActivites.length > 0" class="flex justify-center items-center gap-4 py-8">
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


    <!-- Alert -->
    <div v-if="alertMessage" :class="['alert', isSuccess ? 'alert-success' : 'alert-error']">
      {{ alertMessage }}
    </div>

    <!-- Formulaire Canevas -->
    <FormulaireCanevas 
      v-if="showFormulaire" 
      @close="showFormulaire = false" 
      @soumettreFormulaire="fetchActivites"
    />

    <!-- Formulaire Modification Activité -->
    <FormulaireModificationActivite
      v-if="showFormulaireM"
      :activiteId="activiteAModifier"
      @close="showFormulaireM = false"
      @soumettreFormulaire="fetchActivites"
    />
  </div>
</template>

<script>
import axios from 'axios';
import FormulaireCanevas from './FormulaireCanevas.vue';
import FormulaireModificationActivite from './FormulaireModificationActivite.vue';

export default {
  name: 'GestionCanevasActivites',
  components: {
    FormulaireCanevas,
    FormulaireModificationActivite,
  },
  data() {
    return {
      //structures: [],
      activites: [],
      showFormulaire: false,
      showFormulaireM: false,
      searchQuery: '',
      alertMessage: '',
      isSuccess: false,
      activiteAModifier: null,
      currentPage: 1,
      itemsPerPage: 7,
      file: null,
      loading: false,
      actionLoading: false,
      isAdmin: false,
      isInvite: false,
      isPointFocal: false,
      isChefService: false,
      isPlanificateur: false,
      userId:'',
      userInfo: null,
      activeTab: 'structure',
      sessionEnCours: null,
    };
  },
  watch: {
  searchQuery() {
    this.currentPage = 1;
  },
},
  computed: {
    filteredActivites() {
      let filtered = this.activites.filter(activite =>
        activite.libelle.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
      
      if (this.userInfo && this.userInfo.structure) {
        if (this.activeTab === 'structure') {
          filtered = filtered.filter(a => Number(a.structure_id) === Number(this.userInfo.structure.id));
        } else if (this.activeTab === 'partenaire') {
          filtered = filtered.filter(a => Number(a.structure_id) !== Number(this.userInfo.structure.id));
        }
      }
      
      return filtered;
    },
    totalPages() {
    return Math.ceil(this.filteredActivites.length / this.itemsPerPage);
  },
    paginatedActivites() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredActivites.slice(start, end);
    },
    isSessionOuverte() {
      // On considère qu'il y a une session ouverte si on a récupéré une session avec l'état 'Ouvert'
      return this.sessionEnCours !== null;
    }
  },
  methods: {
    previousPage() {
    if (this.currentPage > 1) {
      this.currentPage--;
    }
  },
  nextPage() {
    if (this.currentPage < this.totalPages) {
      this.currentPage++;
    }
  },
    handleFileUpload(event) {
      this.file = event.target.files[0];
    },

    async uploadFile() {
      if (!this.file) {
        alert("Veuillez sélectionner un fichier !");
        return;
      }

      this.loading = true;
      let formData = new FormData();
      formData.append("file", this.file);

      try {
        const response = await axios.post("/api/import-activites", formData);
        this.message = response.data.message;
        this.showAlert(this.message,true);
      } catch (error) {
        console.error(error);
        alert("Erreur lors de l'importation.");
      }

      this.loading = false;
      this.file = null;
    },
    redirectToActivite(id) {
      this.$router.push({ name: 'GestionActivites', params: { id } });

    },
    
    async fetchSessionEnCours() {
      try {
        const response = await axios.get('/api/session-Ouvert');
        this.sessionEnCours = response.data;
        console.log('Session en cours récupérée :', this.sessionEnCours);
      } catch (error) {
        console.error('Erreur lors de la récupération de la session en cours :', error);
        this.sessionEnCours = null;
      }
    },
    async fetchUserInfo() {
  try {
    const response = await axios.get('/api/user-info');

    this.userInfo = response.data;
    this.isAdmin = this.userInfo.role === 'Administrateur';
    this.isInvite = this.userInfo.role === 'Ordonnateur';
    this.isPointFocal = this.userInfo.role === 'Point-Focale';
    this.isChefService = this.userInfo.role === 'Chef-de-service';
    this.isResponsable = this.userInfo.role === 'Responsable-de-structure';
    this.userId = this.userInfo.id;

    // Ajouter le nombre de notifications non lues
    this.notificationsCount = this.userInfo.notificationsCount || 0;

  } catch (error) {
    console.error('Erreur lors de la récupération des informations utilisateur :', error);
  }
},
    accederFormulaireCreation() {
      this.showFormulaire = true;
    },
    accederFormulaireModification(activite) {
      if (activite.etat_session === 'Clôturé') {
        this.showAlert('Cette session est clôturée. Toute modification est interdite.', false);
        return;
      }
      if (!activite.soumi || this.isAdmin || this.isChefService) {
        this.activiteAModifier = activite.id;
        this.showFormulaireM = true;
      } else {
        this.showAlert('Cette activité ne peut pas être modifiée car elle est déjà soumise.', false);
      }
    },
   
    fetchActivites() {
      axios.get('/api/activites/structure-session').then(response => {
          this.activites = response.data;
        })
        .catch(error => {
          console.error(error);
          this.isSuccess = false;
        });
    },

soumissionForme(activite) {
  if (activite._loading) return;
  if (!confirm('Êtes-vous sûr de soumettre?')) {
    console.log('Soumission annulée par l\'utilisateur.');
    this.fetchActivites();
    return; 
  }
  activite._loading = true;
  axios.post(`api/activites/${activite.id}/soumission`, 
    { Soumi: activite.soumi }
  )
  .then(response => {
    activite.soumi = response.data.activite.soumi;  
    this.showAlert('Activité soumi avec succès', true);
    console.log('Soumission effectuer avec succès:', response.data);
  })
  .catch(error => {
    console.error('Erreur lors de la mise à jour de la soumission:', error);
    const message = error.response && error.response.data && error.response.data.message 
      ? error.response.data.message 
      : 'Une erreur est survenue lors de la mise à jour de l\'état de soumission.';
    this.showAlert(message, false);
  })
  .finally(() => {
    activite._loading = false;
  });

},
    toggleSoumission(activite) {
      // Pour la vue mobile, on inverse l'état avant de soumettre
      const originalState = activite.soumi;
      activite.soumi = !originalState;
      this.soumissionForme(activite);
    },
    async supprimerActivite(activite) {
      if (this.actionLoading) return;
      if (activite.etat_session === 'Clôturé') {
        this.showAlert('Impossible de supprimer une activité d\'une session clôturée.', false);
        return;
      }
      if (!confirm('Êtes-vous sûr de vouloir supprimer cette activité?')) {
        console.log('Suppression annulée par l\'utilisateur.');
        return;
      }

      this.actionLoading = true;
      try {
        await axios.delete(`/api/activites/${activite.id}/supprimer`);
        this.fetchActivites();
        this.showAlert('Activité supprimée avec succès.', true);
      } catch (error) {
        console.error('Erreur lors de la suppression de l\'activité:', error);
        this.showAlert('Une erreur est survenue lors de la suppression de l\'activité.', false);
      } finally {
        this.actionLoading = false;
      }
    },
showAlert(message, success) {
      this.alertMessage = message;
      this.isSuccess = success;
      setTimeout(() => {
        this.alertMessage = '';
      }, 3000);
    },
  
    
  },
  mounted() {
    this.fetchSessionEnCours();
    this.fetchActivites();
    this.fetchUserInfo();
  },
};
</script>

<style>
.alert {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 1rem;
  border-radius: 0.5rem;
  z-index: 1000;
  transition: opacity 0.5s ease;
  opacity: 1;
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
