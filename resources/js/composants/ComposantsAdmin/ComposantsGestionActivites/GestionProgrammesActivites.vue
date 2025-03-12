<template>
  <div class="">
    <!-- Contenu principal -->
    <div class="flex w-full px-2 justify-center items-center h-full overflow-y-auto">
      <!-- Bloc gauche : Liste des sessions et activités -->
       
      <div class="w-full md:w-3/5 overflow-y-auto md:overflow-visible px-4 py-4">

        <!-- Barre de recherche et sélecteur de sessions -->
        <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4">
          <!-- Barre de recherche -->
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Rechercher une session..."
            class="w-full sm:w-3/4 border border-gray-300 px-4 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white text-gray-700"
          />

          <!-- Sélecteur de sessions -->
          <select
            v-model="sessionsId"
            class="w-full sm:w-auto border border-gray-300 rounded px-4 py-2"
            @change="fetchActivites(sessionsId)"
          >
            <option value="" disabled selected>Sélectionnez une session</option>
            <option v-for="session in sessions" :key="session.id" :value="session.id">
              {{ session.annee }}
            </option>
          </select>
        </div>

        <!-- Liste des activités -->
        <div v-if="filteredActivites.length" class="mt-4">
          <div
            v-for="activite in filteredActivites"
            :key="activite.id"
            class="rounded shadow-md py-5 px-4 mt-4 cursor-pointer transition w-full"
            :class="{
              'bg-red-200': activite.etat_slection === 'Rejeté',
              'bg-yellow-200': activite.etat_slection === 'Selectionné',
              'bg-green-200': activite.etat_slection === 'Validé',
              'ring-2 ring-blue-400': activite.id === activiteIdSelectionne,
            }"
            @click="redirectToActivite(activite.id)"
          >
            <p class="text-sm md:text-base text-gray-600">{{ activite.structure_sigle }}</p>
            <p class="font-bold text-base md:text-lg truncate">
              {{ activite.libelle || 'Libellé indisponible' }}
            </p>
          </div>
        </div>
        <p v-else class="text-gray-500 mt-4 text-sm md:text-base text-center">
          Aucune activité trouvée.
        </p>
      </div>
    </div>

    <!-- Alertes -->
    <div
      v-if="alertMessage"
      :class="['alert', isSuccess ? 'alert-success' : 'alert-error']"
      class="fixed bottom-4 left-4 right-4 py-2 px-4 rounded-lg text-sm md:text-base"
    >
      {{ alertMessage }}
    </div>
  </div>
</template>
  
  <script>
  import axios from 'axios';
  import AfficherActivite from './AfficherActivite.vue';
  import FormulaireModificationActivite from './FormulaireModificationActivite.vue';
  
  export default {
    name: 'GestionProgrammesActivites',
    components: {
      AfficherActivite,
      FormulaireModificationActivite,
    },
    data() {
      return {
        activites: [],
        sessions: [],
        activiteIdSelectionne: null,
        searchQuery: '',
        alertMessage: '',
        isSuccess: true,
        showFormulaire: false,
        // sessionsId: null,
      };
    },
    computed: {
      filteredActivites() {
    return this.activites.filter((activite) => {
      const searchQuery = this.searchQuery.toLowerCase();
      // Vérifier à la fois le libellé et le sigle de la structure
      return (
        activite.libelle?.toLowerCase().includes(searchQuery) ||
        activite.structure_sigle?.toLowerCase().includes(searchQuery)
      );
    });
  }
  
  
    },
    methods: {
       redirectToActivite(id) {
      this.$router.push({ name: 'GestionActivites', params: { id } });

    },
      async fetchActivites(sessionsId) {
        if (!sessionsId) {
          this.showAlert('Veuillez sélectionner une session.', false);
          return;
        }
        try {
          const response = await axios.get(`/api/activites/session/${sessionsId}/pa`);
          this.activites = response.data || [];
          //this.showAlert('Activités chargées avec succès.', true);
        } catch (error) {
          console.error('Erreur lors de la récupération des activités :', error);
          this.activites = [];
          //this.showAlert('Erreur lors de la récupération des activités.', false);
        }
      },
      async fetchSessions() {
        try {
          const response = await axios.get('/api/sessions-activites');
          this.sessions = response.data;
        } catch (error) {
          console.error('Erreur lors de la récupération des sessions :', error);
          //this.showAlert('Erreur lors de la récupération des sessions.', false);
        }
      },
      
      async fetchSessionEnCours() {
        try {
          const response = await axios.get('/api/session-Ouvert');
          this.sessionsId = this.$route.params.id 
          ? this.$route.params.id 
          : (response.data?.id ? response.data.id : null);

          
          if (this.sessionsId) {this.$route.params.id;
            await this.fetchActivites(this.sessionsId);
          } else {
            this.showAlert('Aucune session en cours trouvée.', false);
          }
        } catch (error) {
          console.error('Erreur lors de la récupération de la session en cours :', error);
         // this.showAlert('Erreur lors de la récupération de la session en cours.', false);
        }
      },
      showAlert(message, isSuccess = true) {
        this.alertMessage = message;
        this.isSuccess = isSuccess;
        setTimeout(() => {
          this.alertMessage = '';
        }, 3000);
      },
    },
    async mounted() {
      await this.fetchSessions();
      await this.fetchSessionEnCours();
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
  