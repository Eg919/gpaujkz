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
              Gestion des Utilisateurs
            </span>
          </h2>
        </div>
       
        <!-- Bouton Ajouter Utilisateur -->
        <div class="flex items-center space-x-1">
          <button 
            @click="accederFormulaireCreation" 
            class="bg-green-500 text-white px-3 py-1.5 rounded shadow-md"
            title="Créer un Utilisateur"
          >
            <i class="fas fa-plus text-lg"></i> 
            <span class="hidden sm:inline text-xs sm:text-sm md:text-base">Utilisateur</span>
          </button>
        </div>
      </div>

      <!-- Contenu principal -->
      <div class="w-full mt-4 px-4 md:px-6">
        <!-- Barre de recherche -->
        <div class="mb-4 flex flex-col justify-between md:flex-row items-center space-y-2 md:space-y-0 md:space-x-2">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Rechercher un utilisateur..." 
            class="border border-gray-300 px-4 py-2 rounded shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 w-full md:w-auto"
          />
          <div class="mx-2">
            <input type="file" @change="handleFileUpload" class="p-2 border rounded w-full md:w-auto mx-2">
            <button 
              @click="uploadFile" 
              :disabled="loading"
              class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50 w-full md:w-auto">
              {{ loading ? 'Importation en cours...' : 'Importer' }}
            </button>
          </div>
          
        </div>


        <!-- Tableau -->
        <div class="w-full overflow-x-auto md:overflow-visible">
          <table class="w-full text-sm text-left text-gray-500 border-collapse border border-gray-200 shadow-md sm:rounded-lg">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                <th class="border border-gray-300 px-4 py-2 text-left">Nom</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Prénom</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Email</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Structure</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Rôle</th>
                <th class="border border-gray-300 px-4 py-2 text-left">État</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="utilisateur in paginatedUtilisateurs" 
                :key="utilisateur.id" 
                class="cursor-pointer hover:bg-gray-200 transition-colors duration-200"
              >
                <td class="border border-gray-300 px-4 py-1">
                  <input 
                    v-model="utilisateur.nom"
                    :disabled="utilisateur.role != 'Point-Focale' && userInfo.role !='Administrateur'" 
                    class="bg-transparent px-4 py-2 w-full text-gray-900 dark:text-white"
                  />
                </td>
                <td class="border border-gray-300 px-4 py-1">
                  <input 
                    v-model="utilisateur.prenom" 
                    :disabled="utilisateur.role != 'Point-Focale' && userInfo.role !='Administrateur'"
                    class="bg-transparent px-4 py-2 w-full text-gray-900 dark:text-white"
                  />
                </td>
                <td class="border border-gray-300 px-4 py-1">
                  <input 
                    v-model="utilisateur.email" 
                    :disabled="utilisateur.role != 'Point-Focale' && userInfo.role !='Administrateur'"
                    class="bg-transparent px-4 py-2 w-full text-gray-900 dark:text-white"
                  />
                </td>
                <td class="border border-gray-300 px-4 py-1 text-gray-900 dark:text-white">
                  {{ utilisateur.structure.sigle }}
                </td>
                <td class="border border-gray-300 px-4 py-1">
                  <input 
                    v-model="utilisateur.role" 
                    :disabled="utilisateur.role != 'Point-Focale' && userInfo.role !='Administrateur'"

                    class="bg-transparent px-4 py-2 w-full text-gray-900 dark:text-white"
                  />
                </td>
                <td class="border border-gray-300 px-4 py-1">
                  <select 
                    v-model="utilisateur.etat" 
                    :disabled="utilisateur.role != 'Point-Focale' && userInfo.role !='Administrateur'"
                    class="bg-transparent px-4 py-2 w-full text-gray-900 dark:text-white"
                  >
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                  </select>
                </td>
                <td class="border border-gray-300 px-4 py-1 flex items-center space-x-2">
                  <button 
                  :disabled="utilisateur.role != 'Point-Focale' && userInfo.role !='Administrateur'"
                    @click="confirmerModification(utilisateur)" 
                    class="text-green-700 py-1 rounded hover:bg-green-200 flex flex-col items-center justify-center"
                    title="Valider la modification"
                  >
                    <i class="fas fa-check"></i>
                    <span class="text-xs hidden sm:inline">Valider</span>
                  </button>
                  <button 
                  :disabled="utilisateur.role != 'Point-Focale' && userInfo.role !='Administrateur'"
                    @click="resetPassword(utilisateur.id)" 
                    class="text-red-700 py-1 rounded hover:bg-red-200 flex flex-col items-center justify-center"
                    title="Réinitialiser le mot de passe"
                  >
                    <i class="fas fa-key"></i>
                    <span class="text-xs hidden sm:inline">Réinitialiser</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
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
    <FormulaireCreationUtilisateur 
      v-if="showFormulaire" 
      @close="showFormulaire = false" 
      @submitForm="fetchUtilisateurs"
    />

    <!-- Alert -->
    <div v-if="alertMessage" class="alert mt-4" :class="isSuccess ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'">
      {{ alertMessage }}
    </div>
  </div>
</template>

<script>
import FormulaireCreationUtilisateur from './FormulaireCreationUtilisateur.vue';
import axios from 'axios';

export default {
  name: 'GestionUtilisateurs',
  components: {
    FormulaireCreationUtilisateur,
  },
  data() {
    return {
      utilisateurs: [],
      currentPage: 1,
      itemsPerPage: 8,
      showFormulaire: false,
      searchQuery: '',
      alertMessage: '',
      isSuccess: false,
      userInfo: null,
      file: null,
      message: '',
      success: false,
      loading: false,
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    };
  },
  mounted() {
    this.fetchUtilisateurs();
    this.fetchUserInfo();
    // this.fetchStructures();
  },
  computed: {
    filteredUtilisateurs() {
      return this.utilisateurs.filter(utilisateur => 
        utilisateur.nom.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        utilisateur.email.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    totalPages() {
      return Math.ceil(this.filteredUtilisateurs.length / this.itemsPerPage);
    },
    paginatedUtilisateurs() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredUtilisateurs.slice(start, start + this.itemsPerPage);
    },
  },
  methods: {
    handleFileUpload(event) {
            this.file = event.target.files[0];
        },
        async uploadFile() {
            if (!this.file) {
                this.message = "Veuillez sélectionner un fichier.";
                this.success = false;
                return;
            }

            this.loading = true;
            this.message = '';

            let formData = new FormData();
            formData.append('file', this.file);

            try {
                const response = await axios.post('/api/import-users', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                this.message = response.data.message;
                this.success = true;
                this.showAlert(this.message, true);
            } catch (error) {
                this.message = error.response?.data?.message || "Une erreur est survenue.";
                this.success = false;
            } finally {
                this.loading = false;
            }
        },
    async fetchUserInfo() {
  try {
    const response = await axios.get('/api/user-info', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}` 
      }
    });

    this.userInfo = response.data;
    this.isAdmin = this.userInfo.role === 'Administrateur';
    this.isInvite = this.userInfo.role === 'Ordonateur';
    this.isPointFocal = this.userInfo.role === 'Point-Focale';
    this.isChefService = this.userInfo.role === 'Administrateur';
    this.userId = this.userInfo.id;


  } catch (error) {
    console.error('Erreur lors de la récupération des informations utilisateur :', error);
  }
},
    async fetchUtilisateurs() {
      try {
        const response = await axios.get('/api/utilisateurs', {
          headers: {
            'X-CSRF-TOKEN': this.csrfToken
          }
        });
        this.utilisateurs = response.data;
      } catch (error) {
        console.error('Erreur lors de la récupération des utilisateurs:', error);
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
    async confirmerModification(utilisateur) {
      if (confirm('Êtes-vous sûr de vouloir modifier cet utilisateur ?')) {
        try {
          const response = await axios.put(
            `/api/utilisateurs/${utilisateur.id}`,
            {
              nom: utilisateur.nom,
              prenom: utilisateur.prenom,
              email: utilisateur.email,
              role: utilisateur.role,
              etat: utilisateur.etat
            },
            {
              headers: {
                'X-CSRF-TOKEN': this.csrfToken
              }
            }
          );
          if (response.status === 200) {
            this.showAlert('Modifications enregistrées avec succès!', true);
            this.fetchUtilisateurs();
          }
        } catch (error) {
          console.error('Erreur lors de la modification de l\'utilisateur:', error);
          this.showAlert('Erreur lors de la modification de l\'utilisateur. Veuillez réessayer.', false);
        }
      }
    },
    async resetPassword(userId) {
      if (!confirm('Êtes-vous sûr de vouloir restaurer le mot de passe ?')) {
        console.log('Soumission annulée par l\'utilisateur.');
        return; // Sort de la fonction si l'utilisateur annule.
      }
        try {
          const response = await axios.put(
            `/api/utilisateurs/${userId}/reset-password`,
            {},
            {
              headers: {
                'X-CSRF-TOKEN': this.csrfToken
              }
            }
          );
          
          if (response.status === 200) {
            this.showAlert('Mot de passe réinitialisé avec succès!', true);
          }
        } catch (error) {
          console.error('Erreur lors de la réinitialisation du mot de passe:', error);
          this.showAlert('Erreur lors de la réinitialisation du mot de passe. Veuillez réessayer.', false);
        
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
 
};
</script>

<style scoped>

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
button {
    transition: background-color 0.3s ease;
}
</style>
