<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
    <!-- Formulaire principal -->
    <form 
      @submit.prevent="submitForm" 
      class="bg-white p-4 sm:p-6 rounded-lg shadow-md w-full sm:w-1/3 max-h-[calc(100vh-4rem)] overflow-y-auto relative"
    >
      <!-- Bouton de fermeture -->
      <button 
        @click.prevent="fermerFormulaire" 
        type="button" 
        class="absolute top-2 right-2 text-red-500 hover:text-red-700 focus:outline-none"
      >
        <i class="fas fa-times"></i>
      </button>

      <!-- Titre -->
      <h2 class="text-xl sm:text-2xl font-bold mb-4 text-center">Création de Structure</h2>

      <!-- Message de succès -->
      <div v-if="successMessage" class="mt-4 text-green-500 p-2 rounded bg-green-100 mb-4">
        {{ successMessage }}
      </div>

      <!-- Message d'erreur -->
      <div v-if="errorMessage" class="mt-4 text-red-500 p-2 rounded bg-red-100 mb-4">
        {{ errorMessage }}
      </div>

      <!-- Champ Libellé de la Structure -->
      <div class="mb-4">
        <label for="libelle_structure" class="block text-sm sm:text-base font-medium text-gray-700">Libellé de la Structure</label>
        <input 
          v-model="form.libelle_structure" 
          type="text" 
          id="libelle_structure" 
          placeholder ="veillez entrer le libelle de la structure"
          required 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
        />
        <span v-if="errors.libelle_structure" class="text-red-500 text-xs sm:text-sm">{{ errors.libelle_structure }}</span>
      </div>

      <!-- Champ Sigle -->
      <div class="mb-4">
        <label for="sigle" class="block text-sm sm:text-base font-medium text-gray-700">Sigle</label>
        <input 
          v-model="form.sigle" 
          type="text" 
          id="sigle" 
          placeholder ="veillez entrer le sigle de la structure"
          required 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
        />
        <span v-if="errors.sigle" class="text-red-500 text-xs sm:text-sm">{{ errors.sigle }}</span>
      </div>

      <!-- Champ État -->
      <div class="mb-4">
        <label for="etat" class="block text-sm sm:text-base font-medium text-gray-700">État</label>
        <select 
          v-model="form.etat" 
          id="etat" 
          required 
          placeholder ="veillez selectioner l'etat"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
        >
          <option value="actif">Actif</option>
          <option value="inactif">Inactif</option>
        </select>
        <span v-if="errors.etat" class="text-red-500 text-xs sm:text-sm">{{ errors.etat }}</span>
      </div>

      <!-- Bouton de soumission -->
      <button 
        type="submit" 
        class="w-full bg-green-500 text-white p-2 sm:p-3 rounded-lg hover:bg-green-700 mt-3"
      >
        Créer une Structure
      </button>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      form: {
        libelle_structure: '',
        sigle: '',
        etat: 'actif',
      },
      errors: {},
      successMessage: '',
      errorMessage: '',
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    };
  },
  methods: {
    async submitForm() {
      this.errors = {};
      this.successMessage = '';
      this.errorMessage = '';

      // Validation côté client
      if (!this.form.libelle_structure) this.errors.libelle_structure = "Le libellé de la structure est requis.";
      if (!this.form.sigle) this.errors.sigle = "Le sigle est requis.";
      if (!this.form.etat) this.errors.etat = "L'état est requis.";

      if (Object.keys(this.errors).length === 0) {
        try {
          // Soumettre le formulaire via API
          const response = await axios.post('/api/structures', this.form, {
            headers: {
              'X-CSRF-TOKEN': this.csrfToken
            },
          });
          this.$emit('submitForm');
          console.log("Formulaire soumis avec succès:", response.data);
          this.successMessage = "Structure créée avec succès!";
          
          // Attendre 2 secondes avant de fermer le formulaire
          setTimeout(() => {
            this.fermerFormulaire(); // Fermer le formulaire
          }, 1000); // Attendre 1 secondes 
          
        } catch (error) {
          console.error("Erreur lors de la soumission du formulaire:", error);
          if (error.response) {
            if (error.response.status === 404) {
              this.errorMessage = "La route /api/structures est introuvable.";
            } else {
              this.errorMessage = `Erreur ${error.response.status}: ${error.response.data.message || 'Une erreur est survenue.'}`;
            }
          } else {
            this.errorMessage = "Une erreur de connexion est survenue.";
          }
        }
      }
    },

    fermerFormulaire() {
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
/* Styles personnalisés pour l'alerte */
.text-green-100 {
  background-color: #d1f7c4;
}
.text-red-100 {
  background-color: #f8d7da;
}
</style>
