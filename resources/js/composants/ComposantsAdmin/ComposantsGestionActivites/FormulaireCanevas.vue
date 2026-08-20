<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto">
    <!-- Formulaire principal -->
    <form 
      @submit.prevent="soumettreFormulaire" 
      class="bg-white sm:p-4 rounded-lg shadow-md w-full max-w-5xl mx-auto mt-4 relative"
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
      <h2 class="text-xl sm:text-2xl font-bold mb-2 text-center">Canevas des Activités</h2>

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
              placeholder ="veillez entrer le libellé de l'activité"
              class="w-full  sm:p-2 border border-gray-300 rounded"
              required
            />
          </div>
        </fieldset>

        <!-- Section Financement -->
        <fieldset class="border border-gray-300 px-4 py-1 rounded">
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

            <!-- Montant État -->
            <div>
              <label 
                for="financeEtat" 
                class="block text-sm sm:text-base text-gray-700 font-medium mb-2"
              >
                État : Montant
              </label>
              <input 
                :value="formattedFinanceEtat" 
                placeholder ="veillez entrer le montant financé par l'état"
                @input="onInputFinanceEtat($event.target.value)"
                id="financeEtat" 
                type="text" 
                class="w-full  sm:p-2 border border-gray-300 rounded"
                required
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
                :value="formatMontant(p.montant)" 
                @input="onInputPartenaireMontant(index, $event.target.value)"
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

      <!-- Section Période -->
      <div class="grid grid-cols-1 gap-4">
        <fieldset class="border border-gray-300 px-4 py-1 rounded">
          <legend class="text-base sm:text-lg font-bold px-2">Période</legend>
          <div class="grid grid-cols-1 sm:grid-cols-4 ga mt-4">
            <label 
              v-for="(trimester, index) in trimestres" 
              :key="index" 
              class="inline-flex items-center"
            >
              <input 
                v-model="formactivite[trimester.model]" 
                type="checkbox" 
                class="form-checkbox text-blue-600 focus:ring-2 focus:ring-blue-400"
              />
              <span class="ml-2 text-sm sm:text-base">{{ trimester.label }}</span>
            </label>
          </div>
        </fieldset>
      </div>

      <!-- Section Indicateurs -->
      <fieldset class="border border-gray-300 px-4 py-1 rounded mb-2">
        <legend class="text-base sm:text-lg font-bold px-2">Indicateurs</legend>
        <div 
          v-for="(indicateur, index) in Indicateur" 
          :key="index" 
          class="flex flex-col sm:flex-row items-center mb-4"
        >
          <div 
            v-for="(field, idx) in indicateurFields" 
            :key="idx" 
            class="w-full sm:w-1/4 px-1 sm:px-2 mb-2 sm:mb-0"
          >
            <label 
              :for="`${field.model}_${index}`" 
              class="block text-sm sm:text-base text-gray-700 font-medium mb-2"
            >
              {{ field.label }}
            </label>
            <input 
              v-model="indicateur[field.model]" 
              :id="`${field.model}_${index}`" 
              :placeholder="`Veuillez entrer ${field.model}`"
              type="text" 
              class="w-full sm:p-2 border border-gray-300 rounded"
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
      <div class="flex justify-between items-center mb-4">
        <!-- Checkbox Hors Programme -->
        <div class="flex items-center">
          <input 
            v-model="formactivite.hort_progamme" 
            type="checkbox" 
            class="text-blue-600 focus:ring-2 focus:ring-blue-400 mr-2"
            title="Activité hors programme"
          />
          <span class="text-sm sm:text-base">Hors programme</span>
        </div>

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
    </div>
      
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "FormulaireActivite",
  data() {
    return {
      alertMessage: '',
      isSuccess: false,
      loading: false,
      userStructure: null, // La structure de l'utilisateur connecté
      isAdmin: false, // Statut de l'utilisateur connecté
      ObjectifStrategique: [],
      effetAttendus: [],
      formactivite: {
        objectif_strategique_id: 0,
        effets_attendus_id:0,
        libelle: '',
        etat:'UJKZ',
        finance_etat: '',
        hort_progamme: 0,
        trimestre_1: 0,
        trimestre_2: 0,
        trimestre_3: 0,
        trimestre_4: 0,
      },
      partenaires: [],
      Indicateur: [{ indicateur: '', unite: '', reference: '', cible: '' }],
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
  computed: {
    formattedFinanceEtat() {
      if (!this.formactivite.finance_etat) return ''
      return this.formactivite.finance_etat.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
    }
  },
  methods: {
    formatMontant(value) {
      if (!value) return ''
      return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
    },

    onInputFinanceEtat(value) {
      const numericValue = value.replace(/\s+/g, '')
      if (/^\d*$/.test(numericValue)) {
        this.formactivite.finance_etat = numericValue
      }
    },
    onInputPartenaireMontant(index, value) {
      const numericValue = value.replace(/\s+/g, '')
      if (/^\d*$/.test(numericValue)) {
        this.partenaires[index].montant = numericValue
      }
    },
    ajouterPartenaire() {
      this.partenaires.push({ nom: '', montant: '' });
    },
    supprimerPartenaire(index) {
      this.partenaires.splice(index, 1);
    },

    async fetchObjectifs() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get("/api/objectifs-strategiques-Ouvert");
        if (response.data.status === "success") {
          this.ObjectifStrategique = response.data.data; // Récupère la liste des objectifs
        console(this.ObjectifStrategique);
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
        this.effets = []; // Réinitialiser avant de récupérer
      axios.get(`/api/effets/${this.formactivite.objectif_strategique_id}`)
        .then(response => {
          this.effetAttendus = response.data;
        })
        .catch(error => console.error(error));
      }
    },
    // Soumettre le formulaire avec formactivite et Indicateur
    async soumettreFormulaire() {
      if (this.loading) return;
      this.loading = true;
      // Filtrer les partenaires vides et inclure dans formactivite
      const partenairesFiltered = this.partenaires.filter(p => p.nom && p.nom.trim() !== '');
      const dataToSend = {
        formactivite: {
          ...this.formactivite,
          partenaires_list: partenairesFiltered.length > 0 ? partenairesFiltered : null,
          // Calculer le total partenaire pour rétro-compatibilité
          partenaire: partenairesFiltered.map(p => p.nom).join(', ') || null,
          finance_partenaire: partenairesFiltered.reduce((sum, p) => sum + (Number(p.montant) || 0), 0) || null,
        },
        Indicateur: this.Indicateur
      };

      try {
        const response = await axios.post('/api/activites', dataToSend);
        this.alertMessage = 'Activité enregistrée avec succès.';
        this.isSuccess = true;
        this.formactivite = {etat:'UJKZ',hort_progamme: 0}; // Réinitialiser le formulaire
        this.Indicateur = [{ indicateur: '', unite: '', reference: '', cible: '' }];
        this.partenaires = [];
        this.$emit('soumettreFormulaire');
        this.showAlert('Activité enregistrer avec succès', true);
      } catch (error) {
        console.error('Erreur lors de l\'enregistrement de l\'activité:', error);
        this.showAlert('Une erreur s\'est produite lors de l\'enregistrement de l\'activité.', false);
        this.isSuccess = false;
      } finally {
        this.loading = false;
      }
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
      this.Indicateur.push({ indicateur: '', unite: '', reference: '', cible: '' });
    },

    // Supprimer un indicateur
    supprimerIndicateur(index) {
      this.Indicateur.splice(index, 1);
    },

    fermerFormulaire() {
      this.$emit("close");
    },
    showAlert(message, success) {
      this.alertMessage = message;
      this.isSuccess = success;
      setTimeout(() => {
        this.alertMessage = '';
      }, 3000);
    },
  },
  mounted() {
    this.fetchObjectifs();
    this.fetchEffetsAttendus();
    this.fetchSUserInfo();
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
