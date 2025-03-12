<template>
  <div class="flex flex-col items-center min-h-screen mt-2">
    <!-- Conteneur principal avec scroll vertical au-delà de 100vh -->
    <div class="w-full max-h-screen overflow-y-auto md:overflow-visible">
      <!-- Header -->
      <div class="flex justify-between items-center w-full px-4 md:px-6 py-2 bg-gray-50 shadow-md mt-8">
        <!-- Retour -->
        <div class="flex items-center space-x-1">
          <router-link to="/admin" class="text-blue-500 flex items-center">
            <i class="fas fa-arrow-left text-lg"></i>
            <span class="ml-1 text-xs sm:text-sm md:text-base hidden sm:inline">Retour</span>
          </router-link>
        </div>
        <!-- Titre centralisé -->
        <div class="flex-grow text-center">
          <h2 class="text-red-500 font-semibold truncate">
            <span class="block text-xs sm:text-sm md:text-xl lg:text-4xl">
              Gestion des Sessions d'Activités
            </span>
          </h2>
        </div>
        <!-- Bouton Ajouter Session -->
        <div class="flex items-center space-x-1">
          <button 
            @click="accederFormulaireCreation" 
            class="bg-green-500 text-white px-3 py-1.5 rounded shadow-md"
            title="Créer une Session"
          >
            <i class="fas fa-plus text-lg"></i> 
            <span class="hidden sm:inline text-xs sm:text-sm md:text-base">Session</span>
          </button>
        </div>
      </div>

      <!-- Contenu principal -->
      <div class="w-full mt-4 px-4 md:px-6">
        <!-- Barre de recherche -->
        <div class="mb-4">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Rechercher une session..." 
            class="border border-gray-300 px-4 py-2 rounded shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
          />
        </div>

        <!-- Tableau -->
        <table class="w-full text-sm text-left text-gray-500 border-collapse border border-gray-200 shadow-md sm:rounded-lg">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th class="border border-gray-300 px-4 py-2 text-left">#</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Année</th>
              <th class="border border-gray-300 px-4 py-2 text-left">État</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(session, index) in paginatedSessions" :key="session.id" 
                class="cursor-pointer hover:bg-gray-200 transition-colors duration-200">
              <td class="border border-gray-300 px-4 py-1">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
              <td class="border border-gray-300 px-4 py-1">
                <input 
                  type="text"
                  v-model="session.annee" 
                  class="bg-transparent px-4 py-2 m-0 w-full text-gray-900 dark:text-gray-200 dark:bg-gray-800 focus:ring focus:ring-blue-300 rounded-md"
                  @input="validateYear(session)" 
                  placeholder="Entrez l'année" 
                />
              </td>
              <td class="border border-gray-300 px-4 py-1">
                <select 
                  v-model="session.etat" 
                  class="bg-transparent px-4 py-2 m-0 w-full text-gray-900 dark:text-gray-200 dark:bg-gray-800 focus:ring focus:ring-blue-300 rounded-md"
                >
                <option value="Ouvert">Ouvert</option>
                <option value="En_Cours">En_Cours</option>
                <option value="Clôturé">Clôturé</option>
                </select>
              </td>
              <td class="border border-gray-300 px-4 py-1 flex items-center space-x-2">
                <button 
                  @click="confirmerModification(session)" 
                  class="text-green-700 py-1 rounded hover:bg-green-200 flex flex-col items-center justify-center"
                  :disabled="isLoading" 
                  title="Valider la modification"
                >
                  <i class="fas fa-check"></i>
                  <span class="text-xs hidden sm:inline">Valider</span>
                </button>
                <button
                  @click="redirectToActivite(session.id)"
                  title="Historique"
                  class="text-red-700 py-1 rounded hover:bg-red-200 flex flex-col items-center justify-center"
                >
                  <i class="fas fa-history"></i>
                  <span class="text-xs hidden sm:inline">Historique</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination mt-4">
        <div class="flex justify-center">
          <button 
            @click="previousPage" 
            :disabled="currentPage === 1" 
            class="bg-gray-300 px-2 py-1 rounded disabled:opacity-50"
          >
            <i class="fas fa-chevron-left"></i>
          </button>
          <span class="mx-2">Page {{ currentPage }} sur {{ totalPages }}</span>
          <button 
            @click="nextPage" 
            :disabled="currentPage === totalPages" 
            class="bg-gray-300 px-2 py-1 rounded disabled:opacity-50"
          >
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Formulaire de création -->
    <FormulaireCreationSession 
      v-if="showFormulaire" 
      @close="showFormulaire = false" 
      @submitForm="fetchSessions"
    />

    <!-- Alert -->
    <div v-if="alertMessage" class="alert mt-4" :class="isSuccess ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'">
      {{ alertMessage }}
    </div>
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
      itemsPerPage: 9,
      showFormulaire: false,
      searchQuery: '',
      isLoading: false,
      alertMessage: '',
      isSuccess: false,
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
        const response = await axios.get('/api/sessions-activites', {
          headers: {
            'X-CSRF-TOKEN': this.csrfToken
          }
        });
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
              etat: session.etat,
            },
            {
              headers: {
                'X-CSRF-TOKEN': this.csrfToken,
              },
            }
          );

          if (response.status === 200) {
            this.showAlert('Modifications enregistrées avec succès!', true);
            await this.fetchSessions();
          }
        } catch (error) {
          console.error('Erreur lors de la modification de la session:', error);
          this.showAlert('Erreur lors de la modification de la session. Veuillez réessayer.', false);
        } finally {
          this.isLoading = false;
        }
      }
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
