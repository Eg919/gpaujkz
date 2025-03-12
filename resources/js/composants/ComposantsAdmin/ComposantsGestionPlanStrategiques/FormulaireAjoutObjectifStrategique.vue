<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
    <!-- Formulaire principal -->
    <form 
      @submit.prevent="soumettreFormulaireObjectifs" 
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

      <!-- Titre du formulaire -->
      <h2 class="text-xl sm:text-2xl font-bold mb-4 text-center">Ajouter des Objectifs Stratégiques</h2>

      <!-- Erreur générale -->
      <div v-if="erreurs.general" class="mt-4 text-red-500 text-sm sm:text-base p-2 rounded bg-red-100 mb-4" role="alert">
        {{ erreurs.general }}
      </div>

      <!-- Champ caché pour l'ID de l'Axe Stratégique -->
      <input type="hidden" :value="axeStrategiqueId" name="axe_strategique_id" />

      <!-- Champs dynamiques pour les Objectifs Stratégiques -->
      <div 
        v-for="(objectif, index) in objectifs" 
        :key="index" 
        class="mb-4 flex flex-col sm:flex-row items-center sm:items-start"
      >
        <div class="flex-grow sm:w-11/12">
          <label class="block text-sm sm:text-base font-medium text-gray-700 mb-1">Libellé de l'Objectif Stratégique</label>
          <input 
            v-model="objectif.libelle" 
            type="text" 
            class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
            placeholder="Libellé de l'objectif stratégique" 
          />
          <span 
            v-if="erreurs[`objectif_${index}`]" 
            class="text-red-500 text-xs sm:text-sm mt-1"
          >
            {{ erreurs[`objectif_${index}`] }}
          </span>
        </div>
        <button 
          @click.prevent="supprimerObjectif(index)" 
          type="button" 
          class="ml-2 sm:ml-4 mt-2 sm:mt-0 text-red-500 hover:text-red-700"
        >
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>

      <!-- Bouton Ajouter un autre Objectif -->
      <button 
        @click.prevent="ajouterObjectif" 
        class="w-full border-2 border-green-500 bg-yellow-500 text-white p-2 sm:p-3 rounded-lg hover:bg-yellow-700 mb-4"
      >
        Ajouter un nouvel Objectif
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
    axeStrategiqueId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      alertMessage: '',
      isSuccess: false,
      objectifs: [{ libelle: '' }],
      erreurs: {},
      enCoursDeSoumission: false,
    };
  },
  methods: {
    ajouterObjectif() {
      this.objectifs.push({ libelle: '' });
    },
    supprimerObjectif(index) {
      this.objectifs.splice(index, 1);
    },
    async soumettreFormulaireObjectifs() {
      this.erreurs = {};
      this.enCoursDeSoumission = true;

      // Validation
      if (this.objectifs.length === 0 || this.objectifs.some(o => !o.libelle)) {
        this.erreurs.general = 'Veuillez ajouter au moins un objectif stratégique avec un libellé.';
        this.enCoursDeSoumission = false;
        return;
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      try {
        const response = await fetch('/api/objectifs-strategiques', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({
            axe_strategique_id: this.axeStrategiqueId,
            objectifs: this.objectifs,
          }),
        });

        if (!response.ok) {
          const errorData = await response.json();
          console.error('Erreur API:', errorData);
          this.erreurs.general = errorData.message || 'Erreur lors de l\'ajout des objectifs stratégiques.';
          throw new Error(this.erreurs.general);
        }

        this.showAlert('Objectifs stratégiques ajoutés avec succès!',true);
        this.fermerFormulaire();
        this.$emit('soumettreFormulaireObjectifs');
      } catch (error) {
        console.error('Erreur:', error);
        this.showAlert('Une erreur est survenue. Veuillez réessayer.',false);
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
