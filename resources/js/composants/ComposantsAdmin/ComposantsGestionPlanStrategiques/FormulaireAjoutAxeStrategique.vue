<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
    <!-- Formulaire principal -->
    <form 
      @submit.prevent="soumettreFormulaire" 
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
      <h2 class="text-xl sm:text-2xl font-bold mb-4 text-center">Ajouter des Axes Stratégiques</h2>

      <!-- Message d'erreur général -->
      <div v-if="erreurs.general" class="mt-4 text-red-500 text-sm sm:text-base p-2 rounded bg-red-100 mb-4">
        {{ erreurs.general }}
      </div>

      <!-- Champ caché pour l'ID du plan stratégique -->
      <input type="hidden" :value="selectedPlanId" name="plan_strategique_id" />

      <!-- Liste des axes stratégiques -->
      <div 
        v-for="(axe, index) in axes" 
        :key="index" 
        class="mb-4 flex flex-col sm:flex-row items-center sm:items-start"
      >
        <div class="flex-grow sm:w-11/12">
          <label class="block text-sm sm:text-base font-medium text-gray-700 mb-1">Libellé de l'Axe Stratégique</label>
          <input 
            v-model="axe.libelle" 
            type="text" 
            class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
            placeholder="Libellé de l'axe stratégique" 
          />
          <span 
            v-if="erreurs[`axe_${index}`]" 
            class="text-red-500 text-xs sm:text-sm mt-1"
          >
            {{ erreurs[`axe_${index}`] }}
          </span>
        </div>
        <button 
          @click.prevent="supprimerAxe(index)" 
          type="button" 
          class="ml-2 sm:ml-4 text-red-500 hover:text-red-700 mt-2 sm:mt-0"
        >
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>

      <!-- Bouton Ajouter un autre Axe -->
      <button 
        @click.prevent="ajouterAxe" 
        class="w-full border-2 border-green-500 bg-yellow-500 text-white p-2 sm:p-3 rounded-lg hover:bg-yellow-700 mb-4"
      >
        Ajouter un autre Axe
      </button>

      <!-- Bouton Enregistrer -->
      <button 
        type="submit" 
        class="w-full bg-green-500 text-white p-2 sm:p-3 rounded-lg hover:bg-green-700"
        :disabled="enCoursDeSoumission"
      >
        {{ enCoursDeSoumission ? 'Chargement...' : 'Enregistrer' }}
      </button>
    </form>

    <!-- Message d'alerte -->
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
  props: {
    selectedPlanId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      alertMessage: '',
      isSuccess: false,
      axes: [{ libelle: '' }],
      erreurs: {},
      enCoursDeSoumission: false,
    };
  },
  methods: {
    ajouterAxe() {
      this.axes.push({ libelle: '' });
    },
    supprimerAxe(index) {
      this.axes.splice(index, 1);
    },
    async soumettreFormulaire() {
      this.erreurs = {};
      this.enCoursDeSoumission = true;

      // Validation
      if (this.axes.length === 0 || this.axes.some(a => !a.libelle)) {
        this.erreurs.general = 'Veuillez ajouter au moins un axe stratégique avec un libellé.';
        this.enCoursDeSoumission = false;
        return;
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      try {
        const response = await fetch('/api/axes-strategiques', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({
            plan_strategique_id: this.selectedPlanId,
            axes: this.axes,
          }),
        });

        if (!response.ok) {
          const errorData = await response.json();
          this.erreurs.general = errorData.message || 'Erreur lors de l\'ajout des axes stratégiques.';
          throw new Error(this.erreurs.general);
        }

        this.showAlert('Axes stratégiques ajoutés avec succès!', true);
        this.$emit('soumettreFormulaire');
        this.fermerFormulaire();
      } catch (error) {
        console.error(error);
        this.showAlert( 'Une erreur est survenue. Veuillez réessayer.',false);
      } finally {
        this.enCoursDeSoumission = false;
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