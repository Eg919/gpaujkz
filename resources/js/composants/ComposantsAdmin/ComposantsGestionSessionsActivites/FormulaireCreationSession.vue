<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
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

      <h2 class="text-xl sm:text-2xl font-bold mb-4 text-center">Création de Session</h2>

      <div v-if="successMessage" class="mt-4 text-green-500 p-2 rounded bg-green-100 mb-4">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="mt-4 text-red-500 p-2 rounded bg-red-100 mb-4">
        {{ errorMessage }}
      </div>

      <input type="hidden" v-model="form.plan_strategiques_id" />

      <!-- Champ Année -->
      <div class="mb-4">
        <label for="annee" class="block text-sm sm:text-base font-medium text-gray-700">Année</label>
        <input 
          v-model="form.annee" 
          type="text" 
          id="annee" 
          required 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
          placeholder="2024"
        />
        <span v-if="errors.annee" class="text-red-500 text-xs sm:text-sm">{{ errors.annee[0] }}</span>
      </div>

      <!-- Champ Date de Début -->
      <div class="mb-4">
        <label for="date_debut" class="block text-sm sm:text-base font-medium text-gray-700">Date de début</label>
        <input 
          v-model="form.date_debut" 
          type="date" 
          id="date_debut" 
          required 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
        />
        <span v-if="errors.date_debut" class="text-red-500 text-xs sm:text-sm">{{ errors.date_debut[0] }}</span>
      </div>

      <!-- Champ Date de Fin -->
      <div class="mb-4">
        <label for="date_fin" class="block text-sm sm:text-base font-medium text-gray-700">Date de fin</label>
        <input 
          v-model="form.date_fin" 
          type="date" 
          id="date_fin" 
          required 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
        />
        <span v-if="errors.date_fin" class="text-red-500 text-xs sm:text-sm">{{ errors.date_fin[0] }}</span>
      </div>

      <!-- Champ État -->
      <div class="mb-4">
        <label for="etat" class="block text-sm sm:text-base font-medium text-gray-700">État</label>
        <select 
          v-model="form.etat" 
          id="etat" 
          required 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
        >
          <option value="Ouvert">Ouvert</option>
          <option value="En_Cours">En_Cours</option>
          <option value="Clôturé">Clôturé</option>
        </select>
        <span v-if="errors.etat" class="text-red-500 text-xs sm:text-sm">{{ errors.etat[0] }}</span>
      </div>

      <button 
        type="submit" 
        class="w-full bg-green-500 text-white p-2 sm:p-3 rounded-lg hover:bg-green-700 mt-3"
      >
        Créer une Session
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
        annee: '',
        date_debut: '',
        date_fin: '',
        etat: 'Ouvert',
        plan_strategiques_id: '',
      },
      errors: {},
      successMessage: '',
      errorMessage: '',
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    };
  },
  mounted() {
    this.fetchPlanStrategiqueActif();
  },
  methods: {
    async fetchPlanStrategiqueActif() {
      try {
        const response = await axios.get('/api/plan-strategique-en-cour');
        this.form.plan_strategiques_id = response.data.id;
      } catch (error) {
        console.error("Erreur lors de la récupération du plan stratégique en cours:", error);
        this.errorMessage = error.response?.data?.message || "Une erreur de connexion est survenue.";
      }
    },

    async submitForm() {
      this.errors = {};
      this.successMessage = '';
      this.errorMessage = '';

      // Validation des champs
      if (!this.form.annee) {
        this.errors.annee = ["L'année est requise."];
      } else if (!/^\d{4}$/.test(this.form.annee)) {
        this.errors.annee = ["L'année doit être un nombre à 4 chiffres."];
      }

      if (!this.form.date_debut) {
        this.errors.date_debut = ["La date de début est requise."];
      }
      if (!this.form.date_fin) {
        this.errors.date_fin = ["La date de fin est requise."];
      }
      if (this.form.date_debut && this.form.date_fin && this.form.date_fin < this.form.date_debut) {
        this.errors.date_fin = ["La date de fin doit être après la date de début."];
      }

      if (!this.form.etat) {
        this.errors.etat = ["L'état est requis."];
      }

      if (Object.keys(this.errors).length === 0) {
        try {
          const response = await axios.post('/api/sessions-activites', this.form, {
            headers: {
              'X-CSRF-TOKEN': this.csrfToken
            },
          });
          this.$emit('submitForm');
          this.successMessage = 'Session créée avec succès!';
          setTimeout(() => {
            this.fermerFormulaire();
          }, 2000);
        } catch (error) {
          console.error("Erreur lors de la soumission du formulaire:", error);
          if (error.response) {
            this.errors = error.response.data.errors || {};
            this.errorMessage = error.response.data.message || 'Une erreur est survenue.';
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
