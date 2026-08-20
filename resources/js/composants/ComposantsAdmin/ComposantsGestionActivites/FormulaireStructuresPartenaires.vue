<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto">
    <form @submit.prevent="soumettreFormulaire" class="bg-white sm:p-6 rounded-lg shadow-md w-full max-w-3xl mx-auto mt-3 relative">
      <button
        @click.prevent="fermerFormulaire"
        type="button"
        class="absolute top-4 right-6 text-red-500 hover:text-red-700 focus:outline-none"
      >
        <i class="fas fa-times"></i>
      </button>

      <div v-if="alertMessage" class="mb-4 alert" :class="isSuccess ? 'alert-success' : 'alert-error'">
        {{ alertMessage }}
      </div>

      <h2 class="text-xl sm:text-2xl font-bold mb-4 text-center">Structures partenaires</h2>

      <fieldset class="border border-gray-300 px-4 rounded">
        <legend class="text-base sm:text-lg font-bold px-2">Sélection des structures</legend>

        <div class="flex flex-col gap-2 mt-2 mb-2">
          <input
            v-model="rechercheStructure"
            type="text"
            placeholder="Rechercher une structure..."
            class="w-full p-2 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-400"
          />

          <div class="h-56 overflow-y-auto border border-gray-200 rounded p-2 bg-gray-50">
            <label
              v-for="structure in structuresFiltrees"
              :key="structure.id"
              class="flex items-center space-x-2 mb-1 cursor-pointer hover:bg-gray-100 p-1 rounded"
            >
              <input
                type="checkbox"
                :value="structure.id"
                v-model="structuresPartenairesIds"
                class="form-checkbox h-4 w-4 text-blue-600 focus:ring-blue-400 rounded"
              />
              <span class="text-sm font-medium text-gray-700">{{ structure.sigle }}</span>
            </label>
            <div v-if="structuresFiltrees.length === 0" class="text-xs text-gray-500 italic p-1">
              Aucune structure trouvée.
            </div>
          </div>
        </div>
      </fieldset>

      <div class="flex items-center justify-between mt-4">
        <span class="text-xs text-gray-500 font-semibold">{{ structuresPartenairesIds.length }} structure(s) sélectionnée(s)</span>
        <button
          type="submit"
          :disabled="loading"
          class="px-4 mt-2 sm:px-6 sm:py-1 border-2 border-green-500 bg-green-500 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ loading ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'FormulaireStructuresPartenaires',
  props: {
    activiteId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      alertMessage: '',
      isSuccess: false,
      rechercheStructure: '',
      structures: [],
      structuresPartenairesIds: [],
      structureOwnerId: null,
    };
  },
  computed: {
    structuresFiltrees() {
      const structuresDisponibles = this.structures.filter(
        (s) => Number(s.id) !== Number(this.structureOwnerId)
      );

      if (!this.rechercheStructure) {
        return structuresDisponibles;
      }

      const terme = this.rechercheStructure.toLowerCase();
      return structuresDisponibles.filter(
        (s) =>
          (s.sigle && s.sigle.toLowerCase().includes(terme)) ||
          (s.libelle_structure && s.libelle_structure.toLowerCase().includes(terme))
      );
    },
  },
  methods: {
    async fetchStructures() {
      try {
        const response = await axios.get('/api/structures');
        this.structures = response.data || [];
      } catch (error) {
        console.error('Erreur lors du chargement des structures :', error);
        this.showAlert('Erreur lors du chargement des structures.', false);
      }
    },
    async fetchDetailsActivite() {
      try {
        const response = await axios.get(`/api/activites-detaille/${this.activiteId}`);
        const activite = response.data?.activite || {};
        this.structureOwnerId = activite.structure_id || null;
        this.structuresPartenairesIds = (activite.structures_partenaires || []).map((s) => s.id);
      } catch (error) {
        console.error('Erreur lors du chargement de l\'activité :', error);
        this.showAlert('Erreur lors du chargement de l\'activité.', false);
      }
    },
    async soumettreFormulaire() {
      if (this.loading) return;
      this.loading = true;

      try {
        await axios.put(`/api/activites/${this.activiteId}/structures-partenaires`, {
          structures_partenaires_ids: this.structuresPartenairesIds,
        });

        this.showAlert('Structures partenaires mises à jour avec succès.', true);
        this.$emit('soumettreFormulaire');
      } catch (error) {
        console.error('Erreur lors de la mise à jour des structures partenaires :', error);
        this.showAlert('Une erreur est survenue lors de la mise à jour.', false);
      } finally {
        this.loading = false;
      }
    },
    fermerFormulaire() {
      this.$emit('close');
    },
    showAlert(message, success) {
      this.alertMessage = message;
      this.isSuccess = success;
      setTimeout(() => {
        this.alertMessage = '';
      }, 2000);
    },
  },
  async mounted() {
    await this.fetchStructures();
    await this.fetchDetailsActivite();
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
