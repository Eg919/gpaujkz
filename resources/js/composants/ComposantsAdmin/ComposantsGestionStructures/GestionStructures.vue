<template>
  <div class="flex flex-col items-center min-h-screen bg-gray-50/50 pb-12">
    <!-- Header (Sober Style) -->
    <div class="w-full bg-gray-50 shadow-md border-b border-gray-200 py-3 px-4 md:px-8 flex items-center mb-8">
      <div class="w-1/4">
        <router-link to="/admin" class="text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-2">
          <i class="fas fa-arrow-left text-xl"></i>
          <span class="text-xs font-bold uppercase hidden md:inline">Retour</span>
        </router-link>
      </div>
      <div class="w-2/4 text-center">
        <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter">Gestion des Structures</h1>
      </div>
      <div class="w-1/4 flex justify-end">
        <button 
          @click="accederFormulaireCreation" 
          class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg shadow-sm transition-all active:scale-95 flex items-center gap-2 font-bold text-sm"
        >
          <i class="fas fa-building text-xs"></i> 
          <span class="hidden md:inline">Nouvelle Structure</span>
          <span class="md:hidden">Structure</span>
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
            placeholder="Rechercher par libellé, sigle..." 
            class="w-full pl-11 pr-4 py-2.5 bg-gray-50 border border-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500 text-sm font-medium text-gray-600 transition-all"
          />
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
          <button @click="telechargerCanevas" class="px-4 py-2.5 bg-indigo-50 text-indigo-600 rounded-lg font-bold text-[10px] uppercase tracking-widest flex items-center gap-2 hover:bg-indigo-100 transition-all">
            <i class="fas fa-download"></i> Canevas
          </button>
          <div class="relative overflow-hidden inline-block group">
            <button type="button" class="bg-gray-50 text-gray-600 px-4 py-2.5 rounded-lg font-bold text-xs uppercase tracking-widest flex items-center gap-2 border border-blue-50 hover:bg-blue-50/50 transition-all">
              <i class="fas fa-file-import text-blue-500"></i>
              {{ file && file.name ? (file.name.length > 15 ? file.name.substring(0,15) + '...' : file.name) : 'Choisir fichier' }}
            </button>
            <input type="file" accept=".csv, .xls, .xlsx" @click="$event.target.value = null" @change="handleFileUpload" class="absolute left-0 top-0 opacity-0 cursor-pointer w-full h-full z-10">
          </div>
          <button 
            @click="uploadFile" 
            :disabled="loading || !file"
            class="px-5 py-2.5 bg-slate-800 text-white rounded-lg font-bold text-[10px] uppercase tracking-widest disabled:opacity-30">
            {{ loading ? 'En cours...' : 'Importer' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Tableau -->
    <div class="w-full max-w-[99%] mx-auto px-4 md:px-8 overflow-x-auto">
      <div class="overflow-hidden border border-gray-200 rounded-lg shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 uppercase text-[10px] tracking-widest font-black text-gray-400">
            <tr>
              <th class="px-4 py-4 text-left">N°</th>
              <th class="px-6 py-4 text-left">Libellé de la Structure</th>
              <th class="px-6 py-4 text-left w-32">Sigle</th>
              <th class="px-4 py-4 text-center w-24">État</th>
              <th class="px-6 py-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr 
              v-for="(structure, index) in paginatedStructures" 
              :key="structure.id" 
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-4 text-[10px] font-bold text-gray-400">
                {{ (currentPage - 1) * itemsPerPage + index + 1 }}
              </td>
              <td class="px-6 py-4">
                <input 
                  v-model="structure.libelle_structure" 
                  class="bg-transparent text-sm font-bold text-gray-800 w-full outline-none focus:ring-1 focus:ring-amber-400 rounded px-1"
                  :disabled="editableRowId !== structure.id"
                />
              </td>
              <td class="px-6 py-4">
                <input 
                  v-model="structure.sigle" 
                  class="bg-transparent text-[10px] font-black uppercase text-indigo-600 tracking-tighter w-full outline-none"
                  :disabled="editableRowId !== structure.id"
                />
              </td>
              <td class="px-4 py-4 text-center">
                <select 
                  v-model="structure.etat" 
                  class="bg-transparent text-[9px] font-black uppercase tracking-tighter outline-none"
                  :class="structure.etat === 'actif' ? 'text-emerald-600' : 'text-gray-400'"
                  :disabled="editableRowId !== structure.id"
                >
                  <option value="actif">Actif</option>
                  <option value="inactif">Inactif</option>
                </select>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <template v-if="editableRowId !== structure.id">
                    <button @click="editableRowId = structure.id" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded transition-all" title="Modifier">
                      <i class="fas fa-edit text-xs"></i>
                    </button>
                    <button @click="supprimerStructure(structure.id)" class="p-1.5 text-gray-400 hover:text-rose-500 hover:bg-rose-50 rounded transition-all" title="Supprimer">
                      <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                  </template>
                  <template v-else>
                    <button @click="confirmerModification(structure)" class="px-3 py-1 bg-emerald-600 text-white rounded text-[10px] font-bold uppercase tracking-widest shadow-sm">
                      Valider
                    </button>
                    <button @click="editableRowId = null" class="px-3 py-1 bg-gray-100 text-gray-500 rounded text-[10px] font-bold uppercase tracking-widest">
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

    <!-- Pagination -->
    <div v-if="filteredStructures.length > 0" class="flex justify-center items-center gap-4 py-8">
      <button @click="previousPage" :disabled="currentPage === 1" class="px-4 py-2 bg-white border border-gray-200 text-gray-500 hover:text-emerald-600 rounded-lg transition-all disabled:opacity-30">
        <i class="fas fa-chevron-left text-xs"></i>
      </button>
      <div class="flex items-center gap-3">
        <div class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold text-sm shadow-sm">{{ currentPage }}</div>
        <span class="text-xs font-bold text-gray-400">sur {{ totalPages }}</span>
      </div>
      <button @click="nextPage" :disabled="currentPage === totalPages" class="px-4 py-2 bg-white border border-gray-200 text-gray-500 hover:text-emerald-600 rounded-lg transition-all disabled:opacity-30">
        <i class="fas fa-chevron-right text-xs"></i>
      </button>
    </div>

    <FormulaireCreationStructure v-if="showFormulaire" @close="showFormulaire = false" @submitForm="fetchStructures" />

    <div v-if="alertMessage" class="fixed top-24 left-1/2 -translate-x-1/2 z-[100] px-5 py-2.5 rounded-lg shadow-lg flex items-center gap-2 transition-all" :class="isSuccess ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'">
      <i class="fas" :class="isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
      <span class="text-xs font-bold uppercase tracking-wider">{{ alertMessage }}</span>
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
      itemsPerPage: 7,
      showFormulaire: false,
      searchQuery: '',
      isLoading: false,
      alertMessage: '',
      isSuccess: false,
      file: null,
      loading: false,
      actionLoading: false,
      editableRowId: null,
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
      return this.filteredStructures.slice(start, start + this.itemsPerPage);
    },
  },
  methods: {
    handleFileUpload(event) {
      this.file = event.target.files[0];
    },
    async uploadFile() {
      if (!this.file) {
        this.showAlert("Sélectionnez un fichier.", false);
        return;
      }
      this.loading = true;
      let formData = new FormData();
      formData.append("file", this.file);
      try {
        await axios.post("/api/import-structures", formData);
        this.showAlert('Import réussi!', true);
        this.fetchStructures();
      } catch (error) {
        this.showAlert("Erreur import.", false);
      } finally {
        this.loading = false;
        this.file = null;
      }
    },
    async telechargerCanevas() {
      try {
        const response = await axios.get('/api/download-template-structures', { responseType: 'blob' });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'modele_import_structures.csv');
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (error) {
        this.showAlert("Erreur lors du téléchargement du canevas.", false);
      }
    },
    async fetchStructures() {
      try {
        const response = await axios.get('/api/structures');
        this.structures = response.data;
      } catch (error) {
        console.error(error);
      }
    },
    accederFormulaireCreation() {
      this.showFormulaire = true;
    },
    async supprimerStructure(id) {
      if (!confirm("Supprimer cette structure ?")) return;
      try {
        await axios.delete(`/api/structures/supprimer/${id}`);
        this.fetchStructures();
        this.showAlert("Supprimé", true);
      } catch (error) {
        console.error(error);
      }
    },
    nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
    previousPage() { if (this.currentPage > 1) this.currentPage--; },
    async confirmerModification(structure) {
      if (!confirm("Enregistrer les modifications ?")) return;
      this.isLoading = true;
      try {
        await axios.put(`/api/structures/${structure.id}`, {
          libelle_structure: structure.libelle_structure,
          sigle: structure.sigle,
          etat: structure.etat,
        });
        this.editableRowId = null;
        this.showAlert('Modifications enregistrées!', true);
        this.fetchStructures();
      } catch (error) {
        this.showAlert('Erreur modification.', false);
      } finally {
        this.isLoading = false;
      }
    },
    annulerModification() { this.editableRowId = null; },
    showAlert(message, success) {
      this.alertMessage = message;
      this.isSuccess = success;
      setTimeout(() => { this.alertMessage = ''; }, 3000);
    },
  },
  mounted() { this.fetchStructures(); },
};
</script>

<style scoped>
.pagination { display: flex; justify-content: center; align-items: center; }
button { transition: all 0.2s ease; }
</style>
