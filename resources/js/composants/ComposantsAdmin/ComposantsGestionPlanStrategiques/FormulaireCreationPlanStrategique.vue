<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
    <!-- Formulaire principal -->
    <form 
      @submit.prevent="createPlan" 
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
      <h2 class="text-xl sm:text-2xl font-bold mb-4 text-center">Créer un Plan Stratégique</h2>

      <!-- Message d'erreur général -->
      <div v-if="errors.general" class="mt-4 text-red-500 text-sm sm:text-base p-2 rounded bg-red-100 mb-4">
        {{ errors.general }}
      </div>

      <!-- Champ Période -->
      <div class="mb-4">
        <label for="titre" class="block text-sm sm:text-base font-medium text-gray-700">Période</label>
        <input 
          v-model="form.titre" 
          type="text" 
          id="titre" 
          placeholder ="veillez entrer la période exemple: 2025-2029" 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
          required 
        />
        <span v-if="errors.titre" class="text-red-500 text-xs sm:text-sm">{{ errors.titre }}</span>
      </div>

      <!-- Champ Date de début -->
      <div class="mb-4">
        <label for="debut" class="block text-sm sm:text-base font-medium text-gray-700">Date de début</label>
        <input 
          v-model="form.debut" 
          type="date" 
          id="debut" 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
          required 
        />
        <span v-if="errors.debut" class="text-red-500 text-xs sm:text-sm">{{ errors.debut }}</span>
      </div>

      <!-- Champ Date de fin -->
      <div class="mb-4">
        <label for="fin" class="block text-sm sm:text-base font-medium text-gray-700">Date de fin</label>
        <input 
          v-model="form.fin" 
          type="date" 
          id="fin" 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
          required 
        />
        <span v-if="errors.fin" class="text-red-500 text-xs sm:text-sm">{{ errors.fin }}</span>
      </div>

      <!-- Champ État -->
      <div class="mb-4">
        <label for="etat" class="block text-sm sm:text-base font-medium text-gray-700">État</label>
        <select 
          v-model="form.etat" 
          id="etat" 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
          required
        >
          <option value="Ouvert">Ouvert</option>
          <option value="En_Cours">En_Cours</option>
          <option value="Clôturé">Clôturé</option>
        </select>
        <span v-if="errors.etat" class="text-red-500 text-xs sm:text-sm">{{ errors.etat }}</span>
      </div>

      <!-- Bouton de soumission -->
      <button 
        type="submit" 
        class="w-full bg-green-500 text-white p-2 sm:p-3 rounded-lg hover:bg-green-700 mt-3"
        :disabled="isSubmitting"
      >
        <span v-if="isSubmitting">Chargement...</span>
        <span v-else>Créer un Plan</span>
      </button>
    </form>

    <!-- Alertes -->
    <div 
      v-if="alertMessage" 
      :class="['alert', isSuccess ? 'alert-success' : 'alert-error']"
      class="fixed bottom-4 left-4 right-4 py-2 px-4 rounded-lg text-sm sm:text-base"
    >
      {{ alertMessage }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormulaireCreationPlanStrategique',
  data() {
    return {
      alertMessage: '',
      form: {
        titre: '',
        debut: '',
        fin: '',
        etat: 'en cours', // Valeur par défaut
      },
      errors: {},
      isSubmitting: false,
    };
  },
  methods: {
    async createPlan() {
      this.errors = {};

      // Validation côté client
      if (!this.form.titre) this.errors.titre = 'Le titre est requis.';
      if (this.form.debut > this.form.fin) {
        this.errors.fin = 'La date de fin doit être postérieure à la date de début.';
        return;
      }

      this.isSubmitting = true;
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      try {
        const response = await fetch('/api/plans-strategiques', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify(this.form),
        });

        if (!response.ok) throw new Error('Erreur serveur');
        this.$emit('createPlan');
        this.showAlert('Plan stratégique créé avec succès!', true);
        this.fermerFormulaire();
      } catch (error) {
        console.error(error);
        this.showAlert('Une erreur est survenue, veuillez réessayer.', true);
      } finally {
        this.isSubmitting = false;
      }
    },
    showAlert(message, success) {
      this.alertMessage = message;
      this.isSuccess = success;
      setTimeout(() => {
        this.alertMessage = '';
      }, 1500);
    },
    fermerFormulaire() {
      this.$emit('fermer');
    },
  },
};
</script>

<style scoped>
.absolute {
  position: absolute;
}
.inset-0 {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
.flex {
  display: flex;
}
.items-center {
  align-items: center;
}
.justify-center {
  justify-content: center;
}
.text-red-500 {
  color: red;
}
</style>
