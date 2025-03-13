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
            <span class="ml-1 text-xs sm:text-sm md:text-base hidden sm:inline">Retourner</span>
          </router-link>
        </div>
        <!-- Titre centralisé -->
        <div class="flex-grow text-center">
          <h1 class="text-red-500 font-semibold truncate">
            <span class="block text-xs sm:text-sm md:text-xl lg:text-4xl">
              Projets d'Activités
            </span>
          </h1>
        </div>
        <!-- Bouton Proposer activité -->
        <div class="flex items-center space-x-1">
          <button 
            @click="accederFormulaireCreation" 
            class="bg-green-500 text-white px-3 py-1.5 rounded shadow-md"
            title="Proposer activité"
          >
            <i class="fas fa-plus text-lg"></i> 
            <span class="hidden sm:inline text-xs sm:text-sm md:text-base">Proposer activité</span>
          </button>
        </div>
      </div>

      <!-- Contenu principal -->
      <div class="w-full mt-4 px-4 md:px-6">
        <!-- Barre de recherche -->
        <div class="mb-4 flex flex-col   justify-between md:flex-row items-center space-y-2 md:space-y-0 md:space-x-2">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Rechercher une session..." 
            class=" border border-gray-300 px-4 py-2 rounded shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
          />
          <div class="mx-2">
            <!-- <input type="file" @change="handleFileUpload" class="p-2 border rounded w-full md:w-auto mx-2">
            <button 
              @click="uploadFile" 
              :disabled="loading"
              class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50 w-full md:w-auto">
              {{ loading ? 'Importation en cours...' : 'Importer Activités' }}
            </button> -->
          </div>
          
        </div>

        <!-- Tableau -->
        <div class="w-full overflow-x-auto md:overflow-visible">
          <table 
            v-if="filteredActivites.length" 
            class="w-full text-sm text-left text-gray-500 border-collapse border border-gray-200 shadow-md sm:rounded-lg"
          >
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                <th class="border border-gray-300 px-4 py-2 text-left">Libellé</th>
                <th class="border border-gray-300 px-4 py-2 text-left">État de l'activité</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Étape de Validation</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
              v-for="activite in paginatedActivites" :key="activite.id"
                :class="{
                  'bg-red-200': activite.etat_slection === 'Rejeté',
                  'bg-yellow-200': activite.etat_slection === 'Selectionné',
                  'bg-green-200': activite.etat_slection === 'Validé'
                }"
                class="cursor-pointer hover:bg-gray-200 transition-colors duration-200"
              >
                <td class="border border-gray-300 px-4 py-1">
                  <p 
                    v-if="activite.etat_slection === 'Validé'" 
                    @click="redirectToActivite(activite.id)" 
                    class="font-medium text-gray-900 dark:text-white"
                  >
                    {{ activite.libelle }}
                  </p>
                  <p v-else>{{ activite.libelle }}</p>
                </td>
                <td 
                  class="border border-gray-300 px-4 py-2"
                  :class="{
                    'text-red-700': activite.etat_activite === 'abendoner',
                    'text-yellow-700': activite.etat_activite === 'Ouvert',
                    'text-green-700': activite.etat_activite === 'terminer'
                  }"
                >
                  {{ activite.etat_activite }}
                </td>
                <td 
                  class="border border-gray-300 px-4 py-1"
                  :class="{
                    'text-red-700': activite.etat_slection === 'Rejeté',
                    'text-yellow-700': activite.etat_slection === 'Selectionné',
                    'text-green-700': activite.etat_slection === 'Validé'
                  }"
                >
                <span v-if="activite.reconduir">Reconduit </span>
                <span v-else> {{ activite.etat_slection }}</span>
                  
                </td>
                <td class="border border-gray-300 px-4 py-1 flex items-center space-x-2">
                  <button
                    @click.stop="accederFormulaireModification(activite)" 
                    :disabled="activite.soumi"
                    class="text-yellow-500 px-2 py-1 rounded hover:bg-green-200 flex flex-col items-center justify-center"
                    title="Modifier activité"
                  >
                    <i class="fas fa-edit"></i>
                    <span class="text-xs hidden sm:inline">Modifier</span>
                  </button>
                  <button 
                    v-if="!activite.soumi"
                    class="px-2 py-1 rounded hover:bg-green-200 flex flex-col items-center justify-center"
                  >
                    <input 
                    v-if="isResponsable ||isAdmin"
                      type="checkbox"
                      v-model="activite.soumi"  
                      :checked="activite.soumi" 
                      @change.stop="soumissionForme(activite)" 
                      title="Soumettre cette activité"
                      class="transition-all"
                    />
                    <span v-if="isResponsable ||isAdmin" class="text-xs hidden sm:inline">Soumettre</span>
                  </button>
                  <button
                    v-if="activite.etat_slection === 'Validé'" 
                    @click="redirectToActivite(activite.id)"
                    class="text-green-700 px-2 py-1 rounded hover:bg-green-200 flex flex-col items-center justify-center"
                    title="Planifier les activités"
                  >
                    <i class="fas fa-chart-line"></i>
                    <span class="text-xs hidden sm:inline">Planifier</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="text-gray-500 mt-4">Aucune activité trouvée.</div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="filteredActivites.length" class="pagination mt-4">
        <div class="flex justify-center items-center">
          <button
            @click="previousPage"
            :disabled="currentPage === 1"
            class="bg-gray-300 px-2 py-1 rounded disabled:opacity-50 flex items-center justify-center"
          >
            <i class="fas fa-chevron-left"></i>
          </button>
          <span class="mx-2 text-gray-700 text-sm">
            Page <span class="font-bold">{{ currentPage }}</span> sur <span class="font-bold">{{ totalPages }}</span>
          </span>
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="bg-gray-300 px-2 py-1 rounded disabled:opacity-50 flex items-center justify-center"
          >
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
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
      itemsPerPage:8,
      file: null,
      loading: false,
      isAdmin: false,
      isInvite: false,
      isPointFocal: false,
      isChefService: false,
      isResponsable: false,
      userId:'',
    };
  },
  watch: {
  searchQuery() {
    this.currentPage = 1;
  },
},
  computed: {
    filteredActivites() {
      return this.activites.filter(activite =>
        activite.libelle.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    totalPages() {
    return Math.ceil(this.filteredActivites.length / this.itemsPerPage);
  },
    paginatedActivites() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredActivites.slice(start, end);
    },
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
        const response = await axios.post("/api/import-activites", formData, {
          headers: { "Content-Type": "multipart/form-data" }
        });
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
      if (!activite.Soumi) {
        this.activiteAModifier = activite.id;
        this.showFormulaireM = true;
      } else {
        this.showAlert('Cette activité ne peut pas être modifiée car elle est déjà soumise.',false);
        this.isSuccess = false;
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
  if (!confirm('Êtes-vous sûr de soumettre?')) {
    console.log('Soumission annulée par l\'utilisateur.');
    this.fetchActivites();
    return; 
  }
  axios.post(`api/activites/${activite.id}/soumission`, 
    { Soumi: activite.soumi },  
    {
      headers: {
        'X-XSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),  
        'Content-Type': 'application/json',
      }
    }
  )
  .then(response => {
    activite.soumi = response.data.activite.soumi;  
    this.showAlert('Activité soumi avec succès', true);
    console.log('Soumission effectuer avec succès:', response.data);
  })
  .catch(error => {
    console.error('Erreur lors de la mise à jour de la soumission:', error);
    this.showAlert('Une erreur est survenue lors de la mise à jour de l\'état de soumission.', false);
  });

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
