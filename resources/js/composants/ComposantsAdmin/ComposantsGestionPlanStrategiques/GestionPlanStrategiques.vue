<template>
  <div class="flex flex-col md:flex-row h-screen overflow-hidden">
    <!-- Alertes -->
    <div v-if="alertMessage" :class="['alert', isSuccess ? 'alert-success' : 'alert-error']" class="w-full md:w-1/5 mx-auto md:mx-0 mb-4 md:mb-0">
      {{ alertMessage }}
    </div>

    <!-- Section Gauche: Plans Stratégiques -->
    <div class="w-full md:w-1/5 p-4 bg-gray-200 overflow-y-auto md:overflow-visible mt-6 md:mt-0">
      <h2 class="text-lg font-bold mb-5">Plans Stratégiques</h2>
      <div v-for="plan in plans" :key="plan.id" class="mb-2">
        <div
          class="border-2 p-2 rounded-lg cursor-pointer"
          :class="{
            'bg-blue-500 text-white': planIdSelectionne === plan.id,
            'bg-green-500 hover:bg-green-700 text-white': planIdSelectionne !== plan.id,
          }"
          @click="selectionnerPlan(plan.id)"
        >
          <div class="flex justify-between items-center">
            <div>
              <h3 class="text-base md:text-lg font-semibold">{{ plan.titre }}</h3>
            </div>
            <button
              @click="ouvrirFormulaireModificationPlan"
              class="text-yellow-500 text-sm ml-auto hidden md:block"
              aria-label="Modifier le plan"
            >
              <i class="fas fa-edit" title="Modifier"></i>
            </button>
            <button 
                @click="supprimerPlan(plan.id)"
                class=" text-red-700 px-3 py-1 rounded hover:bg-red-200 flex flex-col items-center justify-center">
                <i class="fas fa-trash-alt"></i> <!-- Icône de suppression -->
              
            </button>
          </div>
        </div>
      </div>
      <div v-if="errorMessage" class="text-red-500 text-sm">{{ errorMessage }}</div>
    </div>

    <!-- Section Centre: Axes, Objectifs, Effets -->
    <div class="w-full md:flex-1 p-4 md:p-6 mt-4 md:mt-0 overflow-y-auto md:overflow-visible">
      <!-- Sélecteurs d'Axe et Objectif -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 mt-4">
        <div>
          <label for="axes" class="text-base md:text-lg font-bold">Axe Stratégique</label>
          <select
            v-model="axeIdSelectionne"
            id="axes"
            @change="fetchObjectifs(axeIdSelectionne)"
            class="w-full p-2 border border-gray-300 rounded text-sm md:text-base"
          >
            <option v-if="!axes.length" disabled>Pas d'axes disponibles</option>
            <option v-for="axe in axes" :key="axe.id" :value="axe.id">
              {{ axe.libelle }}
            </option>
          </select>
        </div>
        <div>
          <label for="objectifs" class="text-base md:text-lg font-bold">Objectif Stratégique</label>
          <select
            v-model="objectifIdSelectionne"
            id="objectifs"
            @change="fetchEffets(objectifIdSelectionne)"
            class="w-full p-2 border border-gray-300 rounded text-sm md:text-base"
          >
            <option v-if="!objectifs.length" disabled>Pas d'objectifs disponibles</option>
            <option v-for="objectif in objectifs" :key="objectif.id" :value="objectif.id">
              {{ objectif.libelle }}
            </option>
          </select>
        </div>
      </div>

      <!-- Tableau des Effets Attendus -->
      <table v-if="effets.length" class="w-full text-sm md:text-base text-gray-500 border-collapse border border-gray-200 shadow-md sm:rounded-lg mb-6">
        <thead class="text-xs md:text-sm text-gray-700 uppercase bg-gray-50">
          <tr>
            <th class="border border-gray-300 px-4 py-2">#</th>
            <th class="border border-gray-300 px-4 py-2">Effet Attendu</th>
            <th class="border border-gray-300 px-4 py-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(effet, index) in effets" :key="effet.id">
            <td class="border border-gray-300 px-4 py-2">{{ index + 1 }}</td>
            <td class="border border-gray-300 px-4 py-2">
              <input
                v-model="effet.libelle"
                type="text"
                class="bg-transparent px-4 py-2 w-full text-gray-900 dark:text-white text-sm md:text-base"
                :disabled="editableRowId !== effet.id"
              />
            </td>
            <td class="px-2 flex items-center justify-center space-x-6 border border-gray-300">
              <button 
                v-if="editableRowId !== effet.id"
                @click="editableRowId = effet.id"
                class="text-yellow-500 py-1 rounded hover:bg-yellow-100 flex flex-col items-center justify-center"
                title="Modifier"
              >
                <i class="fas fa-edit"></i>
                <span class="text-xs hidden sm:inline">Modifier</span>
              </button>
              <button
               v-if="editableRowId === effet.id"
                @click.stop="sauvegarderEffet(effet)"
                class="text-green-700 px-2 py-2 rounded hover:bg-green-200 flex flex-col items-center justify-center"
                title="Valider la modification"
              >
                <i class="fas fa-check"></i>
                <span class="hidden sm:inline text-xs md:text-sm">Valider</span>
              </button>
              <!-- Bouton Annuler -->
              <button 
                v-if="editableRowId === effet.id"
                @click="annulerModification" 
                class="text-gray-700 py-1 rounded hover:bg-gray-200 flex flex-col items-center justify-center"
                title="Annuler"
              >
                <i class="fas fa-times"></i>
                <span class="text-xs hidden sm:inline">Annuler</span>
              </button>
              <button 
                v-if="editableRowId !== effet.id"
                  @click="supprimerEffet(effet.id)" 
                  class=" text-red-700 px-3 py-1 rounded hover:bg-red-200 flex flex-col items-center justify-center">
                  <i class="fas fa-trash-alt"></i> <!-- Icône de suppression -->
                  <span class="text-red-700 text-xs hidden sm:inline">Supprimer</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Tableau des Objectifs Stratégiques -->
      <table v-if="objectifs.length && !effets.length" class="w-full text-sm md:text-base text-gray-500 border-collapse border border-gray-200 shadow-md sm:rounded-lg mb-6">
        <thead class="text-xs md:text-sm text-gray-700 uppercase bg-gray-50">
          <tr>
            <th class="border border-gray-300 px-4 py-2">#</th>
            <th class="border border-gray-300 px-4 py-2">Objectifs Stratégiques</th>
            <th class="border border-gray-300 px-4 py-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(objectif, index) in objectifs" :key="objectif.id">
            <td class="border border-gray-300 px-4 py-2">{{ index + 1 }}</td>
            <td class="border border-gray-300 px-4 py-2">
              <input
                v-model="objectif.libelle"
                type="text"
                class="bg-transparent px-4 py-2 w-full text-gray-900 dark:text-white text-sm md:text-base"
                :disabled="editableRowId !== objectif.id"
              />
            </td>
            <td class="px-2 flex items-center justify-center space-x-6 border border-gray-300">
              <button 
                v-if="editableRowId !== objectif.id"
                @click="editableRowId = objectif.id" 
                class="text-yellow-500 py-1 rounded hover:bg-yellow-100 flex flex-col items-center justify-center"
                title="Modifier"
              >
                <i class="fas fa-edit"></i>
                <span class="text-xs hidden sm:inline">Modifier</span>
              </button>
              <button
                v-if="editableRowId === objectif.id"
                @click.stop="sauvegarderObjectif(objectif)"
                class="text-green-700 px-2 py-2 rounded hover:bg-green-200 flex flex-col items-center justify-center"
                title="Valider la modification"
              >
                <i class="fas fa-check"></i>
                <span class="hidden sm:inline text-xs md:text-sm">Valider</span>
              </button>
              <!-- Bouton Annuler -->
            <button 
              v-if="editableRowId === objectif.id"
              @click="annulerModification" 
              class="text-gray-700 py-1 rounded hover:bg-gray-200 flex flex-col items-center justify-center"
              title="Annuler"
            >
              <i class="fas fa-times"></i>
              <span class="text-xs hidden sm:inline">Annuler</span>
            </button>
            <button 
            v-if="editableRowId !== objectif.id"
              @click="supprimerObjectif(objectif.id)" 
              class=" text-red-700 px-3 py-1 rounded hover:bg-red-200 flex flex-col items-center justify-center">
              <i class="fas fa-trash-alt"></i> <!-- Icône de suppression -->
              <span class="text-red-700 text-xs hidden sm:inline">Supprimer</span>
          </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Tableau des Axes Stratégiques -->
      <table v-if="axes.length && !objectifs.length" class="w-full text-sm md:text-base text-gray-500 border-collapse border border-gray-200 shadow-md sm:rounded-lg mb-6">
        <thead class="text-xs md:text-sm text-gray-700 uppercase bg-gray-50">
          <tr>
            <th class="border border-gray-300 px-4 py-2">#</th>
            <th class="border border-gray-300 px-4 py-2">Axes Stratégiques</th>
            <th class="border border-gray-300 px-4 py-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(axe, index) in axes" :key="axe.id">
            <td class="border border-gray-300 px-4 py-2">{{ index + 1 }}</td>
            <td class="border border-gray-300 px-4 py-2">
              <input
                v-model="axe.libelle"
                type="text"
                class="bg-transparent px-4 py-2 w-full text-gray-900 dark:text-white text-sm md:text-base"
                :disabled="editableRowId !== axe.id"
              />
            </td>
            <td class="px-2 flex items-center justify-center space-x-6 border border-gray-300">
              <button 
                v-if="editableRowId !== axe.id"
                @click="editableRowId = axe.id" 
                class="text-yellow-500 py-1 rounded hover:bg-yellow-100 flex flex-col items-center justify-center"
                title="Modifier"
              >
                <i class="fas fa-edit"></i>
                <span class="text-xs hidden sm:inline">Modifier</span>
              </button>
              <button
               v-if="editableRowId === axe.id"
                @click.stop="sauvegarderAxe(axe)"
                class="text-green-700 px-2 py-2 rounded hover:bg-green-200 flex flex-col items-center justify-center"
                title="Valider la modification"
              >
                <i class="fas fa-check"></i>
                <span class="hidden sm:inline text-xs md:text-sm">Valider</span>
              </button>
              <!-- Bouton Annuler -->
              <button 
                v-if="editableRowId === axe.id"
                @click="annulerModification" 
                class="text-gray-700 py-1 rounded hover:bg-gray-200 flex flex-col items-center justify-center"
                title="Annuler"
              >
                <i class="fas fa-times"></i>
                <span class="text-xs hidden sm:inline">Annuler</span>
              </button>
              <button 
                v-if="editableRowId !== axe.id"
                  @click="supprimerAxe(axe.id)"  
                  class=" text-red-700 px-3 py-1 rounded hover:bg-red-200 flex flex-col items-center justify-center">
                  <i class="fas fa-trash-alt"></i> <!-- Icône de suppression -->
                  <span class="text-red-700 text-xs hidden sm:inline">Supprimer</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Section Droite: Actions -->
    <div class="w-full md:w-1/5 p-4 bg-gray-200 mt-4 md:mt-0 overflow-y-auto md:overflow-visible">
      <h2 class="text-base md:text-lg font-bold mb-4">Actions</h2>
      <button
        @click="ouvrirFormulaireCreationPlan"
        class="pl-4 text-left w-full bg-green-500 text-white py-2 mb-2 rounded-lg"
      >
        <i class="fas fa-plus"></i> <span class="hidden sm:inline">Plan Stratégique</span>
      </button>
      <button
        @click="ouvrirFormulaireAjoutAxe"
        class="pl-4 text-left w-full bg-green-500 text-white py-2 mb-2 rounded-lg"
        :disabled="!planIdSelectionne"
      >
        <i class="fas fa-plus"></i> <span class="hidden sm:inline">Axe Stratégique</span>
      </button>
      <button
        @click="ouvrirFormulaireAjoutObjectif"
        class="pl-4 text-left w-full bg-green-500 text-white py-2 mb-2 rounded-lg"
        :disabled="!axeIdSelectionne"
      >
        <i class="fas fa-plus"></i> <span class="hidden sm:inline">Objectif Stratégique</span>
      </button>
      <button
        @click="ouvrirFormulaireAjoutEffet"
        class="pl-4 text-left w-full bg-green-500 text-white py-2 mb-2 rounded-lg"
        :disabled="!objectifIdSelectionne"
      >
        <i class="fas fa-plus"></i> <span class="hidden sm:inline">Effet Attendu</span>
      </button>
    </div>

    <!-- Formulaires modaux -->
    <FormulaireCreationPlanStrategique
      v-if="FormulaireCreationPlanStrategique"
      @createPlan="recupererPlans"
      @fermer="fermerFormulaire"
    />
    <FormulaireAjoutAxeStrategique
      v-if="FormulaireAjoutAxeStrategique"
      :selectedPlanId="planIdSelectionne"
      @soumettreFormulaire="fetchAxes"
      @fermer="fermerFormulaire"
    />
    <FormulaireAjoutObjectifStrategique
      v-if="FormulaireAjoutObjectifStrategique"
      :axeStrategiqueId="axeIdSelectionne"
      @soumettreFormulaireObjectifs="fetchObjectifs"
      @fermer="fermerFormulaire"
    />
    <FormulaireAjoutEffetAttendu
      v-if="FormulaireAjoutEffetAttendu"
      :idObjectifSelectionne="objectifIdSelectionne"
      @soumettreFormulaireEffets="fetchEffets"
      @fermer="fermerFormulaire"
    />
    <FormulaireModificationPlanStrategique
      v-if="FormulaireModificationPlanStrategique"
      :planId="planIdSelectionne"
      @fermer="fermerFormulaire"
      @handleSubmit="recupererPlans"
    />
  </div>
</template>

<script>

import axios from 'axios';
import FormulaireAjoutAxeStrategique from './FormulaireAjoutAxeStrategique.vue';
import FormulaireAjoutObjectifStrategique from './FormulaireAjoutObjectifStrategique.vue';
import FormulaireAjoutEffetAttendu from './FormulaireAjoutEffetAttendu.vue';
import FormulaireCreationPlanStrategique from './FormulaireCreationPlanStrategique.vue';
import FormulaireModificationPlanStrategique from './FormulaireModificationPlanStrategique.vue';

export default {
  components: {
    FormulaireAjoutAxeStrategique,
    FormulaireAjoutObjectifStrategique,
    FormulaireAjoutEffetAttendu,
    FormulaireCreationPlanStrategique,
    FormulaireModificationPlanStrategique,
  },
  data() {
    return {
      FormulaireCreationPlanStrategique: false,
      FormulaireAjoutAxeStrategique: false,
      FormulaireAjoutObjectifStrategique: false,
      FormulaireAjoutEffetAttendu: false,
      FormulaireModificationPlanStrategique: false,
      plans: [],
      axes: [],
      objectifs: [],
      effets: [],
      planIdSelectionne: 0,
      axeIdSelectionne: 0,
      objectifIdSelectionne: 0,
      errorMessage: null,
      alertMessage: '',
      editableRowId : null,
    };
  },
  methods: {
    async recupererPlans() {
      try {
        const reponse = await fetch('/api/plans-strategiques');
        if (!reponse.ok) throw new Error('Erreur lors de la récupération des plans.');
        this.plans = await reponse.json();
        console.log('Plans récupérés :', this.plans);
      } catch (error) {
        console.error(error);
        this.errorMessage = error.message;
      }
    },
    selectionnerPlan(planId) {
      this.planIdSelectionne = planId;
    },
    fetchAxes() {
      axios.get(`/api/axes/${this.planIdSelectionne}`)
        .then(response => {
          this.axes = response.data;
        })
        .catch(error => console.error(error));
    },
    fetchObjectifs() {
      this.objectifs = []; // Réinitialiser avant de récupérer
      axios.get(`/api/objectifs/${this.axeIdSelectionne}`)
        .then(response => {
          this.objectifs = response.data;
        })
        .catch(error => console.error(error));
    },
    fetchEffets() {
      this.effets = []; // Réinitialiser avant de récupérer
      axios.get(`/api/effets/${this.objectifIdSelectionne}`)
        .then(response => {
          this.effets = response.data;
        })
        .catch(error => console.error(error));
    },
 

    selectionnerPlan(planId) {
      this.planIdSelectionne = planId;
      this.fetchAxes();
    },
    selectionnerAxe(axeId) {
      this.axeIdSelectionne = axeId;
    },
    selectionnerObjectif(objectifId) {
      this.objectifIdSelectionne = objectifId;
    },
    ouvrirFormulaireCreationPlan() {
    console.log("Ouverture du formulaire de création de plan stratégique");
    this.FormulaireCreationPlanStrategique = true
  },
  ouvrirFormulaireModificationPlan() {
    console.log("Ouverture du formulaire de création de plan stratégique");
    this.FormulaireModificationPlanStrategique = true
  },
  ouvrirFormulaireAjoutAxe() {
    if (this.planIdSelectionne) {
      console.log("Ouverture du formulaire d'ajout d'axe stratégique");
      this. FormulaireAjoutAxeStrategique = true
    }
  },
  ouvrirFormulaireAjoutObjectif() {
    if (this.axeIdSelectionne) {
      console.log("Ouverture du formulaire d'ajout d'objectif stratégique");
      this.FormulaireAjoutObjectifStrategique = true
    }
  },
  ouvrirFormulaireAjoutEffet() {
      console.log("Ouverture du formulaire d'ajout d'effet attendu");
      this.FormulaireAjoutEffetAttendu = true
  },
    fermerFormulaire() {
      this.FormulaireCreationPlanStrategique = false,
      this.FormulaireAjoutAxeStrategique = false,
      this.FormulaireAjoutObjectifStrategique = false,
      this.FormulaireAjoutEffetAttendu = false,
      this.FormulaireModificationPlanStrategique = false
    },
    
    
    sauvegarderEffet(effet) {
      if (confirm('Êtes-vous sûr de vouloir modifier cet effet ?')) {
      axios.put(`/api/effets-attendus/${effet.id}/modifier`, { libelle: effet.libelle })
        .then(() => {
          effet.isEditing = false; 
          this.showAlert('Effet modifier avec sucesss', true);
        })
        .catch(error => {
          this.showAlert("Erreur lors de la sauvegarde de l'effet:", true);
        });
        this.fetchEffets();
        }
        this.editableRowId = null; // Quitte le mode édition après validation
    },
   
    sauvegarderObjectif(objectif) {
      if (confirm('Êtes-vous sûr de vouloir modifier cet objectif stratégique ?')) {
      axios.put(`/api/objectifs-strategiques/${objectif.id}/modifier`, { libelle: objectif.libelle })
        .then(() => {
          objectif.isEditing = false; 
          this.showAlert('Objectif modifier avec sucesss', true);
        })
        .catch(error => {
          this.showAlert("Erreur lors de la sauvegarde de l'objectif:", true);
        });
        this.fetchObjectifs();
        }
        this.editableRowId = null; // Quitte le mode édition après validation
    },
   
    sauvegarderAxe(axe) {
      if (confirm('Êtes-vous sûr de vouloir modifier cet axe stratégique ?')) {
      axios.put(`/api/axes-strategiques/${axe.id}/modifier`, { libelle: axe.libelle })
        .then(() => {
          axe.isEditing = false; 
          this.showAlert('Axe modifier avec sucesss', true);
        })
        .catch(error => {
          this.showAlert("Erreur lors de la sauvegarde de l'axe:", true);
        });
        this.fetchAxes();
        }
        this.editableRowId = null; // Quitte le mode édition après validation
    },
    annulerModification() {
    this.editableRowId = null; // Annule l'édition et rétablit les valeurs initiales
  },
   async supprimerEffet(effetId) {
      if (confirm('Êtes-vous sûr de vouloir supprimer cet effet ?')) {
        axios.delete(`/api/effets-attendus/${effetId}`)
          .then(() => {
            this.showAlert('Effet supprimé avec succès', true);
            this.fetchEffets();
          })
          .catch(error => {
            this.showAlert("Erreur lors de la suppression de l'effet", false);
          });
      }
    },
 async   supprimerObjectif(objectifId) {
      if (confirm('Êtes-vous sûr de vouloir supprimer cet objectif stratégique ?')) {
        axios.delete(`/api/objectifs-strategiques/${objectifId}/supprimer`)
          .then(() => {
            this.showAlert('Objectif supprimé avec succès', true);
            this.fetchObjectifs();
          })
          .catch(error => {
            this.showAlert("Erreur lors de la suppression de l'objectif", false);
          });
      }
    },
  async  supprimerAxe(axeId) {
      if (confirm('Êtes-vous sûr de vouloir supprimer cet axe stratégique ?')) {
        axios.delete(`/api/axes-strategiques/${axeId}/supprimer`)
          .then(() => {
            this.showAlert('Axe supprimé avec succès', true);
            this.fetchAxes();
          })
          .catch(error => {
            this.showAlert("Erreur lors de la suppression de l'axe", false);
          });
      }
    },
async supprimerPlan(planId) {
      if (confirm('Êtes-vous sûr de vouloir supprimer ce plan stratégique ?')) {
        axios.delete(`/api/plans-strategiques/${planId}`)
          .then(() => {
            this.showAlert('Plan supprimé avec succès', true);
            this.recupererPlans();
          })
          .catch(error => {
            this.showAlert("Erreur lors de la suppression du plan", false);
          });
      }
    },
    showAlert(message, success) {
      this.alertMessage = message;
      this.isSuccess = success;
      setTimeout(() => {
        this.alertMessage = '';
      }, 1500);
    },
  },
  mounted() {
    this.recupererPlans();
  },
};
</script>

<style scoped>
/* Styles spécifiques pour le composant */
</style>
