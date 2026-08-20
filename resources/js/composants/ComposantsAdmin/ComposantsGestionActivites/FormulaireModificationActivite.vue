<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto">
    <!-- Formulaire principal -->
    <form 
      @submit.prevent="ModifierFormulaire" 
      class="bg-white sm:p-6 rounded-lg shadow-md w-full max-w-5xl mx-auto mt-3 relative"
    >
      <!-- Bouton de fermeture -->
      <button 
        @click.prevent="fermerFormulaire" 
        type="button" 
        class="absolute top-4 right-6 text-red-500 hover:text-red-700 focus:outline-none"
      >
        <i class="fas fa-times"></i>
      </button>

      <!-- Message d'alerte -->
      <div 
        v-if="alertMessage" 
        class="mb-4 alert" 
        :class="isSuccess ? 'alert-success' : 'alert-error'"
      >
        {{ alertMessage }}
      </div>

      <!-- Titre -->
      <h2 class="text-xl sm:text-2xl font-bold mb-4 text-center">Modification de l'Activité</h2>

      <!-- Section Activité -->
      <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <fieldset class="border border-gray-300 px-4 rounded">
          <legend class="text-base sm:text-lg font-bold px-2">Activité</legend>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Objectif Stratégique -->
            <div>
              <label 
                for="ObjectifStrategique" 
                class="block text-sm sm:text-base text-gray-700 font-medium mb-2"
              >
                Objectif Stratégique
              </label>
              <select 
                v-model="formactivite.objectif_strategique_id" 
                @change="fetchEffetsAttendus" 
                id="objectifStrategique" 
                class="w-full  sm:p-2 border border-gray-300 rounded"
                required
              >
                <option value="" disabled>-- Sélectionner un objectif --</option>
                <option 
                  v-for="objectif in ObjectifStrategique" 
                  :key="objectif.id" 
                  :value="objectif.id"
                >
                  {{ objectif.libelle }}
                </option>
              </select>
            </div>

            <!-- Effet Attendu -->
            <div>
              <label 
                for="effetAttendu" 
                class="block text-sm sm:text-base text-gray-700 font-medium mb-2"
              >
                Effet Attendu
              </label>
              <select 
                v-model="formactivite.effets_attendus_id" 
                id="effetAttendu" 
                class="w-full  sm:p-2 border border-gray-300 rounded"
                required
              >
                <option value="" disabled>-- Sélectionner un effet --</option>
                <option 
                  v-for="effet in effetAttendus" 
                  :key="effet.id" 
                  :value="effet.id"
                >
                  {{ effet.libelle }}
                </option>
              </select>
            </div>
          </div>

          <!-- Libellé de l'Activité -->
          <div class="mt-4">
            <label 
              for="activite" 
              class="block text-sm sm:text-base text-gray-700 font-medium mb-2"
            >
              Libellé de l'Activité
            </label>
            <input 
              v-model="formactivite.libelle" 
              id="activite" 
              type="text" 
              class="w-full  sm:p-2 border border-gray-300 rounded"
              required
            />
          </div>
        </fieldset>

        <!-- Section Financement -->
        <fieldset class="border border-gray-300 px-4 rounded">
          <legend class="text-base sm:text-lg font-bold px-2">Financement</legend>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <!-- État -->
            <div>
              <label 
                for="etat" 
                class="block text-sm sm:text-base text-gray-700 font-medium mb-2"
              >
                État
              </label>
              <input 
                v-model="formactivite.etat" 
                id="etat" 
                type="text" 
                class="w-full  sm:p-2 border border-gray-300 rounded"
                readonly
              />
            </div>

            <div>
              <label 
                for="financeEtat" 
                class="block text-sm sm:text-base text-gray-700 font-medium mb-2"
              >
                État : Montant
              </label>
              <input 
                :value="formatValue(formactivite.finance_etat)"
                @input="formactivite.finance_etat = $event.target.value.replace(/\s/g, '')"
                id="financeEtat" 
                type="text" 
                class="w-full  sm:p-2 border border-gray-300 rounded"
              />
            </div>

          </div>

          <!-- Partenaires (liste dynamique) -->
          <div class="mt-2">
            <div class="flex justify-between items-center mb-1">
              <label class="block text-sm sm:text-base text-gray-700 font-medium">Partenaires</label>
              <button 
                @click.prevent="ajouterPartenaire" 
                type="button" 
                class="text-xs border border-green-500 bg-green-500 text-white px-2 py-0.5 rounded hover:bg-green-700"
              >
                <i class="fas fa-plus"></i> Ajouter
              </button>
            </div>
            <div 
              v-for="(p, index) in partenaires" 
              :key="index" 
              class="grid grid-cols-[1fr_1fr_auto] gap-2 mb-1 items-center"
            >
              <input 
                v-model="p.nom" 
                type="text" 
                placeholder="Nom du partenaire"
                class="w-full sm:p-2 border border-gray-300 rounded text-sm"
              />
              <input 
                :value="formatValue(p.montant)" 
                @input="p.montant = $event.target.value.replace(/\s/g, '')"
                type="text" 
                placeholder="Montant"
                class="w-full sm:p-2 border border-gray-300 rounded text-sm"
              />
              <button 
                @click.prevent="supprimerPartenaire(index)" 
                type="button" 
                class="text-red-500 hover:text-red-700"
              >
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </div>
        </fieldset>
      </div>

      <!-- Section Périodes -->
      <div class="grid grid-cols-1 gap-4">
        <fieldset class="border border-gray-300 px-4 rounded">
          <legend class="text-base sm:text-lg font-bold px-2">Période</legend>
          <div class="grid grid-cols-1 sm:grid-cols-4 ga mt-4">
            <label v-for="(trimester, index) in trimestres" :key="index" class="inline-flex items-center">
                  <input v-model="formactivite[trimester.model]":checked="formactivite[trimester.model]" type="checkbox" class="form-checkbox text-blue-600" />
                  <span class="ml-2">{{ trimester.label }}</span>
                </label>
          </div>
        </fieldset>
      </div>

     <!-- Section Indicateurs -->
     <fieldset class="border border-gray-300 px-4   rounded mt-4">
        <legend class="text-lg sm:text-xl font-bold px-2">Indicateurs</legend>
        <div 
          v-for="(indicateur, index) in indicateur" 
          :key="index" 
          class="flex flex-col sm:flex-row items-start sm:items-center mb-4"
        >
          <div 
            v-for="(field, idx) in indicateurFields" 
            :key="idx" 
            class="w-full sm:w-1/4 px-1 sm:px-2 mb-2 sm:mb-0"
          >
            <label class="block text-sm sm:text-base font-medium text-gray-700 mb-2">{{ field.label }}</label>
            <input 
              v-model="indicateur[field.model]" 
              type="text" 
              class="w-full p-2 sm:p-2 border border-gray-300 rounded"
              required
            />
          </div>
          <button 
            @click.prevent="supprimerIndicateur(index)" 
            type="button" 
            class="text-red-500 hover:text-red-700 mt-2 sm:mt-0"
          >
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
        <button 
          @click.prevent="ajouterIndicateur" 
          type="button"
          class="text-xs border border-green-500 bg-green-500 text-white px-2 py-0.5 rounded hover:bg-green-700"
        >
          <i class="fas fa-plus"></i> Ajouter
        </button>
      </fieldset>
      <!-- Bouton Enregistrer -->
      <div class="text-end">
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
  name: "FormulaireActivite",
  props: {
    activiteId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      alertMessage: '',
      isSuccess: false,
      loading: false,
      isAdmin: false, // Statut de l'utilisateur connecté
      ObjectifStrategique: [],
      effetAttendus: [],
      formactivite: {},
      indicateur: [],
      partenaires: [],
      trimestres: [
        { label: 'Ttrimestre 1', model: 'trimestre_1' },
        { label: 'Ttrimestre 2', model: 'trimestre_2' },
        { label: 'Ttrimestre 3', model: 'trimestre_3' },
        { label: 'Ttrimestre 4', model: 'trimestre_4' },
      ],
      indicateurFields: [
        { label: 'Indicateur', model: 'indicateur' },
        { label: 'Unité', model: 'unite' },
        { label: 'Référence', model: 'reference' },
        { label: 'Cible', model: 'cible' },
      ]
    };
  },
  computed: {},
  methods: {
    async fetchObjectifs() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get("/api/objectifs-strategiques-Ouvert");
        if (response.data.status === "success") {
          this.ObjectifStrategique = response.data.data; // Récupère la liste des objectifs
          console.log(this.ObjectifStrategique);
        } else {
          this.error = "Erreur lors de la récupération des objectifs.";
        }

      } catch (err) {
        this.error = err.message || "Une erreur est survenue.";
      } finally {
        this.loading = false;
      }
    },

    async fetchEffetsAttendus() {
      if (this.formactivite.objectif_strategique_id) {
      
      axios.get(`/api/effets/${this.formactivite.objectif_strategique_id}`)
        .then(response => {
          this.effetAttendus = response.data;
        })
        .catch(error => console.error(error));
      }
    },
    async fetchDetails() {
      this.loading = true;
      this.error = false;
      try {
        const response = await axios.get(`/api/activites-detaille/${this.activiteId}`);
        const data = response.data;
        this.formactivite = data.activite;
        if (data.activite.structures_partenaires) {
          this.formactivite.structures_partenaires_ids = data.activite.structures_partenaires.map(s => s.id);
        } else {
          this.formactivite.structures_partenaires_ids = [];
        }
        this.indicateur = data.indicateurs;
        this.structure = data.structure;
        this.objectifStrategique = data.objectifStrategique;
        this.effetAttendu = data.effet_attendu;
        if (data.activite.partenaires_list && Array.isArray(data.activite.partenaires_list)) {
          this.partenaires = data.activite.partenaires_list.map(p => ({ nom: p.nom || '', montant: p.montant || '' }));
        } else if (data.activite.partenaire) {
          this.partenaires = [{ nom: data.activite.partenaire, montant: data.activite.finance_partenaire || '' }];
        } else {
          this.partenaires = [];
        }
      } catch (error) {
        console.error("Erreur :", error);
        this.error = true;
      } finally {
        this.loading = false;
        this.fetchEffetsAttendus();
      }
    },

    
async ModifierFormulaire() {
  if (this.loading) return;
  this.loading = true;
  // Filtrer les partenaires vides et inclure dans formactivite
  const partenairesFiltered = this.partenaires.filter(p => p.nom && p.nom.trim() !== '');
  const dataToSend = {
    formactivite: {
      ...this.formactivite,
      partenaires_list: partenairesFiltered.length > 0 ? partenairesFiltered : null,
      partenaire: partenairesFiltered.map(p => p.nom).join(', ') || null,
      finance_partenaire: partenairesFiltered.reduce((sum, p) => sum + (Number(p.montant) || 0), 0) || null,
    },
    Indicateur: this.indicateur,
  };

  console.log('Données envoyées:', dataToSend); // Vérifiez les données

  try {
    const response = await axios.put(
      `/api/activites/${this.activiteId}/modification`,
      dataToSend,
      {
        headers: {
          'Content-Type': 'application/json',
          // Ajoutez d'autres en-têtes si nécessaire
        }
      }
    );

    console.log('Réponse du serveur:', response); // Vérifiez la réponse

    if (response.status === 200) {
      this.alertMessage = 'Activité enregistrée avec succès.';
      this.isSuccess = true;
      this.$emit('soumettreFormulaire');
      this.showAlert('Activité enregistrée avec succès', true);
    } else {
      console.error('Réponse inattendue:', response);
      this.showAlert('Une erreur s\'est produite lors de l\'enregistrement de l\'activité.', false);
      this.isSuccess = false;
    }
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement de l\'activité:', error);
    this.showAlert('Une erreur s\'est produite lors de l\'enregistrement de l\'activité.', false);
    this.isSuccess = false;

    if (error.response && error.response.data && error.response.data.message) {
      console.error('Détail de l\'erreur:', error.response.data.message);
    }
  } finally {
    this.loading = false;
  }
},
    formatValue(val) {
      if (val === null || val === undefined || val === '') return '';
      // Retirer tout ce qui n'est pas un chiffre pour le formatage
      const cleanVal = val.toString().replace(/\s/g, '').replace(/[^\d]/g, '');
      return cleanVal.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    },
    async fetchSUserInfo() {
  try {
    // Récupérer les informations de l'utilisateur
    const response = await axios.get('/api/user-info');
    const userInfo = response.data;

    // Déterminer si l'utilisateur est administrateur
    this.isAdmin = userInfo.role === 'Administrateur';
    this.userStructure = userInfo.structure; // Structure associée à l'utilisateur connecté
  } catch (error) {
    console.error("Erreur lors de la récupération des informations utilisateur :", error);
  }
},

    // Ajouter un indicateur
    ajouterIndicateur() {
      this.indicateur.push({ indicateur: '', unite: '', reference: '', cible: '' });
    },

    // Supprimer un indicateur
    supprimerIndicateur(index) {
      this.indicateur.splice(index, 1);
    },

    // Ajouter un partenaire
    ajouterPartenaire() {
      this.partenaires.push({ nom: '', montant: '' });
    },

    // Supprimer un partenaire
    supprimerPartenaire(index) {
      this.partenaires.splice(index, 1);
    },

    fermerFormulaire() {
      this.$emit("close");
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
    this.fetchObjectifs();
    this.fetchSUserInfo();
    this.fetchDetails();
  }
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
