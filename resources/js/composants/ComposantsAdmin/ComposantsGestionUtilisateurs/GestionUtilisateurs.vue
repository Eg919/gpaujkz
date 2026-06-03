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
        <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter">Gestion des Utilisateurs</h1>
      </div>
      <div class="w-1/4 flex justify-end">
        <button 
          @click="accederFormulaireCreation" 
          class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg shadow-sm transition-all active:scale-95 flex items-center gap-2 font-bold text-sm"
        >
          <i class="fas fa-user-plus text-xs"></i> 
          <span class="hidden md:inline">Nouvel Utilisateur</span>
          <span class="md:hidden">Utilisateur</span>
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
            placeholder="Rechercher par email, rôle..." 
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
              <th class="px-6 py-4 text-left">Utilisateur (Email)</th>
              <th class="px-6 py-4 text-left">Structure</th>
              <th class="px-6 py-4 text-left">Rôle</th>
              <th class="px-4 py-4 text-center">État</th>
              <th class="px-6 py-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr 
              v-for="(user, index) in paginatedUtilisateurs" 
              :key="user.id" 
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-4 text-[10px] font-bold text-gray-400">
                {{ (currentPage - 1) * itemsPerPage + index + 1 }}
              </td>
              <td class="px-6 py-4">
                <input 
                  v-model="user.email" 
                  class="bg-transparent text-sm font-bold text-gray-800 w-full outline-none focus:ring-1 focus:ring-amber-400 rounded px-1"
                  :disabled="editableRowId !== user.id"
                />
              </td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 bg-gray-50 rounded text-[9px] font-bold text-emerald-700 border border-emerald-100 uppercase">{{ user.structure?.sigle || 'N/A' }}</span>
              </td>
              <td class="px-6 py-4">
                <select 
                  v-model="user.role" 
                  class="bg-transparent text-[10px] font-black uppercase text-indigo-600 tracking-tighter w-full outline-none disabled:opacity-75"
                  :disabled="editableRowId !== user.id"
                >
                  <option value="Administrateur">Administrateur DEPS</option>
                  <option value="Chef-de-service">Chef de service</option>
                  <option value="Responsable-de-structure">Responsable</option>
                  <option value="Point-Focale">Point Focal</option>
                  <option value="Ordonnateur">Ordonnateur</option>
                  <option value="Administrateur_DSI">Admin DSI</option>
                </select>
              </td>
              <td class="px-4 py-4 text-center">
                <select 
                  v-model="user.etat" 
                  class="bg-transparent text-[9px] font-black uppercase tracking-tighter outline-none"
                  :class="user.etat === 'Actif' ? 'text-emerald-600' : 'text-gray-400'"
                  :disabled="editableRowId !== user.id"
                >
                  <option value="Actif">Actif</option>
                  <option value="Inactif">Inactif</option>
                </select>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <template v-if="editableRowId !== user.id">
                    <button @click="editableRowId = user.id" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded transition-all" title="Modifier">
                      <i class="fas fa-edit text-xs"></i>
                    </button>
                    <button @click="resetPassword(user.id)" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded transition-all" title="Reset Pass">
                      <i class="fas fa-key text-xs"></i>
                    </button>
                    <button @click="supprimerUtilisateur(user.id)" class="p-1.5 text-gray-400 hover:text-rose-500 hover:bg-rose-50 rounded transition-all" title="Supprimer">
                      <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                  </template>
                  <template v-else>
                    <button @click="confirmerModification(user)" class="px-3 py-1 bg-emerald-600 text-white rounded text-[10px] font-bold uppercase tracking-widest shadow-sm">
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
    <div v-if="filteredUtilisateurs.length > 0" class="flex justify-center items-center gap-4 py-8">
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

    <FormulaireCreationUtilisateur v-if="showFormulaire" @close="showFormulaire = false" @submitForm="fetchUtilisateurs" />

    <div v-if="alertMessage" class="fixed top-24 left-1/2 -translate-x-1/2 z-[100] px-5 py-2.5 rounded-lg shadow-lg flex items-center gap-2 transition-all" :class="isSuccess ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'">
      <i class="fas" :class="isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
      <span class="text-xs font-bold uppercase tracking-wider">{{ alertMessage }}</span>
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
      itemsPerPage: 7,
      showFormulaire: false,
      searchQuery: '',
      alertMessage: '',
      isSuccess: false,
      file: null,
      message: '',
      success: false,
      loading: false,
      actionLoading: {},
      editableRowId: null,
    };
  },
  mounted() {
    this.fetchUtilisateurs();
  },
  computed: {
    filteredUtilisateurs() {
      return this.utilisateurs.filter(utilisateur => 
        utilisateur.role.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
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
        this.showAlert("Veuillez sélectionner un fichier.", false);
        return;
      }
      this.loading = true;
      let formData = new FormData();
      formData.append('file', this.file);
      try {
        const response = await axios.post('/api/import-users', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        this.showAlert(response.data.message, true);
        this.fetchUtilisateurs();
      } catch (error) {
        this.showAlert(error.response?.data?.message || "Erreur d'importation.", false);
      } finally {
        this.loading = false;
        this.file = null;
      }
    },
    async telechargerCanevas() {
      try {
        const response = await axios.get('/api/download-template-users', { responseType: 'blob' });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'modele_import_utilisateurs.csv');
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (error) {
        this.showAlert("Erreur lors du téléchargement du canevas.", false);
      }
    },
    async fetchUtilisateurs() {
      try {
        const response = await axios.get('/api/utilisateurs');
        this.utilisateurs = response.data;
      } catch (error) {
        console.error('Erreur lors de la récupération des utilisateurs:', error);
      }
    },
    accederFormulaireCreation() {
      this.showFormulaire = true;
    },
    nextPage() {
      if (this.currentPage < this.totalPages) this.currentPage++;
    },
    previousPage() {
      if (this.currentPage > 1) this.currentPage--;
    },
    async confirmerModification(utilisateur) {
      if (!confirm('Êtes-vous sûr de vouloir enregistrer ces modifications ?')) return;
      try {
        const response = await axios.put(`/api/utilisateurs/${utilisateur.id}`, {
          email: utilisateur.email,
          role: utilisateur.role,
          etat: utilisateur.etat,
        });
        if (response.status === 200) {
          this.showAlert('Modifications enregistrées!', true);
          this.fetchUtilisateurs();
        }
        this.editableRowId = null;
      } catch (error) {
        this.showAlert('Erreur lors de la modification.', false);
      }
    },
    annulerModification() {
      this.editableRowId = null;
    },
    async supprimerUtilisateur(id) {
      if (confirm("Supprimer cet utilisateur ?")) {
        try {
          await axios.delete(`/api/utilisateurs/supprimer/${id}`);
          this.utilisateurs = this.utilisateurs.filter(u => u.id !== id);
          this.showAlert("Utilisateur supprimé", true);
        } catch (error) {
          console.error(error);
        }
      }
    },
    async resetPassword(userId) {
      if (!confirm('Réinitialiser le mot de passe ?')) return;
      try {
        const response = await axios.put(`/api/utilisateurs/${userId}/reset-password`, {});
        if (response.status === 200) this.showAlert('Réinitialisé avec succès!', true);
      } catch (error) {
        this.showAlert('Erreur de réinitialisation.', false);
      }
    },
    showAlert(message, success) {
      this.alertMessage = message;
      this.isSuccess = success;
      setTimeout(() => { this.alertMessage = ''; }, 3000);
    },
  },
};
</script>

<style scoped>
.pagination { display: flex; justify-content: center; align-items: center; }
button { transition: all 0.2s ease; }
</style>
