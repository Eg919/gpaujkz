<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
    <!-- Formulaire principal -->
    <form 
      @submit.prevent="handleSubmit" 
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

      <!-- Titre dynamique -->
      <h2 class="text-xl sm:text-2xl font-bold mb-4 text-center">
        {{ planId ? 'Modifier le Plan Stratégique' : 'Créer un Plan Stratégique' }}
      </h2>

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
          placeholder="Titre" 
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
        <span v-else>{{ planId ? 'Modifie un Plan' : 'Créer un Plan' }}</span>
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
  name: 'FormulaireModificationPlanStrategique',
  props: {
    planId: {
      type: Number,
      required: false,
      default: null,
    },
  },
  data() {
    return {
      alertMessage: '',
      isSuccess: false,
      form: {
        titre: '',
        debut: '',
        fin: '',
        etat: 'Ouvert', // Valeur par défaut
      },
      errors: {},
      isSubmitting: false,
    };
  },
  watch: {
    planId: {
      immediate: true,
      handler(newValue) {
        if (newValue) {
          this.fetchPlan(newValue);
        }
      },
    },
  },
  methods: {
    async fetchPlan() {
      try {
        const response = await fetch(`/api/plans-strategiques/${this.planId}`, {
          method: 'GET',
        });
        if (!response.ok) throw new Error('Erreur lors de la récupération des données.');
        const data = await response.json();
        this.form = {
          titre: data.titre,
          debut: data.debut,
          fin: data.fin,
          etat: data.etat,
        };
      } catch (error) {
        console.error(error);
      }
    },
    async handleSubmit() {
      this.errors = {};

      // Validation des données
      if (!this.form.titre) this.errors.titre = 'Le titre est requis.';
      if (this.form.debut > this.form.fin) {
        this.errors.fin = 'La date de fin doit être postérieure à la date de début.';
        return;
      }

      this.isSubmitting = true;
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      if (this.planId) { // Gestion spécifique pour PUT
        try {
          const response = await fetch(`/api/plans-strategiques/${this.planId}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(this.form),
          });
          this.$emit('handleSubmit');
          this.showAlert('Plan stratégique modifié avec succès!', true);
          if (!response.ok) throw new Error('Erreur serveur');
          this.$emit('updatePlan');
          this.fermerFormulaire();
        } catch (error) {
          console.error(error);
          this.showAlert('Une erreur est survenue lors de la mise à jour, veuillez réessayer.', true);
        } finally {
          this.isSubmitting = false;
        }
      } else {
        this.showAlert('La modification nécessite un ID valide.', false);
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
  this.$emit('fermer');  // Emmettre l'événement "fermer"
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
.alert {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 1rem;
  border-radius: 0.5rem;
  z-index: 1000;

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
