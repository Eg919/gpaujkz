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
              Gestion des Structures
            </span>
          </h2>
        </div>
        <!-- Bouton Ajouter Structure -->
        <div class="flex items-center space-x-1">
          <button 
            @click="accederFormulaireCreation" 
            class="bg-green-500 text-white px-3 py-1.5 rounded shadow-md"
            title="Créer une Structure"
          >
            <i class="fas fa-plus text-lg"></i> 
            <span class="hidden sm:inline text-xs sm:text-sm md:text-base">Structure</span>
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
            placeholder="Rechercher une structure..." 
            class=" border border-gray-300 px-4 py-2 rounded shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
          />
          <div class="mx-2">
            <input type="file" @change="handleFileUpload" class="p-2 border rounded w-full md:w-auto mx-2">
            <button 
              @click="uploadFile" 
              :disabled="loading"
              class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50 w-full md:w-auto">
              {{ loading ? 'Importation en cours...' : 'Importer Structures' }}
            </button>
          </div>
          
        </div>

        <!-- Tableau -->
        <div class="w-full overflow-x-auto md:overflow-visible">
          <table class="w-full text-sm text-left text-gray-500 border-collapse border border-gray-200 shadow-md sm:rounded-lg">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                <th class="border border-gray-300 px-4 py-2 text-left">#</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Libellé de la Structure</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Sigle</th>
                <th class="border border-gray-300 px-4 py-2 text-left">État</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="structure in paginatedStructures" 
                :key="structure.id" 
                class="cursor-pointer hover:bg-gray-200 transition-colors duration-200"
              >
                <td class="border border-gray-300 px-4 py-1">{{ structure.displayOrder }}</td>
                <td class="border border-gray-300 px-4 py-1">
                  <input 
                    v-model="structure.libelle_structure" 
                    class="bg-transparent px-4 py-2 w-full text-gray-900 dark:text-white"
                    :disabled="editableRowId !== structure.id"
                  />
                </td>
                <td class="border border-gray-300 px-4 py-1">
                  <input 
                    v-model="structure.sigle" 
                    class="bg-transparent px-4 py-2 w-full text-gray-900 dark:text-white"
                    :disabled="editableRowId !== structure.id"
                  />
                </td>
                <td class="border border-gray-300 px-4 py-1">
                  <select 
                    v-model="structure.etat" 
                    class="bg-transparent px-4 py-2 w-full text-gray-900 dark:text-white"
                    :disabled="editableRowId !== structure.id"
                  >
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                  </select>
                </td>
                <td class="border border-gray-300 px-4 py-1 flex items-center justify-center space-x-2">
                  <button 
                    v-if="editableRowId !== structure.id"
                    @click="editableRowId = structure.id" 
                    class="text-yellow-500 px-3 py-1 rounded hover:bg-yellow-100 flex flex-col items-center justify-center"
                    title="Modifier"
                  >
                    <i class="fas fa-edit"></i>
                    <span class="text-xs hidden sm:inline">Modifier</span>
                  </button>
                  <button 
                    v-if="editableRowId === structure.id"
                    @click="confirmerModification(structure)" 
                    class="text-green-700 px-3 py-1 rounded hover:bg-green-200 flex flex-col items-center justify-center"
                    :disabled="isLoading"
                    title="Valider la modification"
                  >
                    <i class="fas fa-check"></i>
                    <span class="text-xs hidden sm:inline">Valider</span>
                  </button>
                   <!-- Bouton Annuler -->
                  <button 
                    v-if="editableRowId === structure.id"
                    @click="annulerModification" 
                    class="text-gray-700 px-3 py-1 rounded hover:bg-red-200  flex flex-col items-center justify-center"
                    title="Annuler"
                  >
                    <i class="fas fa-times"></i>
                    <span class="text-xs hidden sm:inline">Annuler</span>
                  </button>
                  <button 
                    @click="supprimerStructure(structure.id)" 
                    class=" text-red-700 px-3 py-1 rounded hover:bg-red-200 flex flex-col items-center justify-center">
                    <i class="fas fa-trash-alt"></i> <!-- Icône de suppression -->
                    <span class="text-red-700 text-xs hidden sm:inline">Supprimer</span>
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
    <FormulaireCreationStructure 
      v-if="showFormulaire" 
      @close="showFormulaire = false" 
      @submitForm="fetchStructures"
    />

    <!-- Alert -->
    <div v-if="alertMessage" class="alert mt-4" :class="isSuccess ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'">
      {{ alertMessage }}
    </div>
  </div>
</template>

<script>
import FormulaireCreationStructure from './FormulaireCreationStructure.vue';
import axios from 'axios';

export default {
  name: 'GestionStructures',
  components: {
    FormulaireCreationStructure,
  },
  data() {
    return {
      structures: [],
      currentPage: 1,
      itemsPerPage: 9,
      showFormulaire: false,
      searchQuery: '',
      isLoading: false,
      alertMessage: '',
      isSuccess: false,
      file: null,
      loading: false,
      editableRowId: null,
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    };
  },
  computed: {
    filteredStructures() {
      return this.structures.filter(structure => 
        structure.libelle_structure.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        structure.sigle.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    totalPages() {
      return Math.ceil(this.filteredStructures.length / this.itemsPerPage);
    },
    paginatedStructures() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredStructures.slice(start, start + this.itemsPerPage).map((structure, index) => ({
        ...structure,
        displayOrder: start + index + 1, // Numéro d'ordre
      }));
    },
  },
  methods: {
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
        const response = await axios.post("/api/import-structures", formData, {
          headers: { "Content-Type": "multipart/form-data" }
        });
        this.showAlert('Enregistrées des Structures avec succès!', true);
      } catch (error) {
        console.error(error);
        alert("Erreur lors de l'importation.");
      }

      this.loading = false;
      this.file = null;
    },
    async fetchStructures() {
  try {
    const response = await axios.get('/api/structures', {
      headers: {
        'X-CSRF-TOKEN': this.csrfToken
      }
    });

    // Ajouter la propriété enEdition à chaque structure
    this.structures = response.data.map((structure) => ({
      ...structure,
      enEdition: false, // Initialiser enEdition à false
    }));

  } catch (error) {
    console.error('Erreur lors de la récupération des structures:', error);
  }
},

    accederFormulaireCreation() {
      this.showFormulaire = true;
    },
    async supprimerStructure(id) {
      if (confirm("Voulez-vous vraiment supprimer cette structure ?")) {
        try {
          const response = await axios.delete(`/api/structures/supprimer/${id}`);
          alert(response.data.message);
          this.fetchStructures(); // Recharger la liste après suppression
        } catch (error) {
          console.error("Erreur lors de la suppression:", error);
          alert("Une erreur est survenue.");
        }
      }
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
    modifierEffet(structure) {
      // Activer un mode d'édition inline
      structure.enEdition = true;
    },
      async confirmerModification(structure) {
        if (!confirm("Êtes-vous sûr de vouloir enregistrer cette modification ?")) {
        console.log('Soumission annulée par l\'utilisateur.');
        return; // Sort de la fonction si l'utilisateur annule.
      }
        this.isLoading = true;
        try {
          const response = await axios.put(
            `/api/structures/${structure.id}`,
            {
              libelle_structure: structure.libelle_structure,
              sigle: structure.sigle,
              etat: structure.etat,
            },
            {
              headers: {
                'X-CSRF-TOKEN': this.csrfToken
              }
            }
          );
          this.editableRowId = null; // Quitte le mode édition après validation
          if (response.status === 200) {
            this.showAlert('Modifications enregistrées avec succès!', true);
            this.fetchStructures();
          }
        } catch (error) {
          console.error('Erreur lors de la modification de la structure:', error);
          alert('Erreur lors de la modification de la structure. Veuillez réessayer.');
        } finally {
          this.isLoading = false;
        }
      
    },
    annulerModification() {
      if (!confirm("Êtes-vous sûr de vouloir annuler cette modification ?")) {
        console.log('Soumission annulée par l\'utilisateur.');
        return; // Sort de la fonction si l'utilisateur annule.
      }
    this.editableRowId = null; // Annule l'édition et rétablit les valeurs initiales
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
    this.fetchStructures();
  },
};
</script>

<style scoped>
.gestion-structures {
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
