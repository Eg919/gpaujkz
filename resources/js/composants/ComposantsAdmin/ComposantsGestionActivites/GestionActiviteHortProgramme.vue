<template>
  <div class="flex flex-col items-center min-h-screen">
    <!-- Header avec retour et titre (Sober Style) -->
    <div class="w-full bg-gray-50 shadow-md border-b border-gray-200 py-3 px-4 md:px-8 flex items-center mb-8">
      <div class="w-1/4">
        <router-link to="/admin" class="text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-2">
          <i class="fas fa-arrow-left text-xl"></i>
          <span class="text-xs font-bold uppercase hidden md:inline">Retour</span>
        </router-link>
      </div>
      <div class="w-2/4 text-center">
        <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter">Activités Hors Programme</h1>
      </div>
      <div class="w-1/4 flex justify-end">
        <button 
          @click="accederFormulaireModification" 
          class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg shadow-sm transition-all active:scale-95 flex items-center gap-2 font-bold text-sm disabled:opacity-50"
          :disabled="!pouvonsModifier"
          :title="pouvonsModifier ? 'Modifier l\'activité' : 'Non autorisé'"
        >
          <i class="fas fa-edit text-xs"></i> 
          <span class="hidden md:inline">Modifier</span>
        </button>
      </div>
    </div>

      <!-- Contenu principal -->
      <div class="w-full mt-4 px-4 md:px-6">
        <!-- Affichage conditionnel pour mobile : Liste des activités ou Détails -->
        <div v-if="isMobile">
          <!-- Bloc gauche : Liste des sessions et activités (visible par défaut) -->
          <div v-if="!activiteIdSelectionne" class="w-full h-[calc(100vh-150px)] overflow-y-auto px-4">
            <div class="flex flex-col items-center w-full space-y-4">
              <!-- Titre -->
              <h1 class="text-yellow-500 text-lg font-bold">Activités hors programme</h1>
              <div class="flex items-center space-x-4">
                <!-- Barre de recherche -->
                <input
                  type="text"
                  v-model="searchQuery"
                  placeholder="Rechercher une session..."
                  class="w-full border border-gray-300 px-4 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white text-gray-700"
                />
                <!-- Sélecteur de sessions -->
                <select
                  v-model="sessionsId"
                  class="border border-gray-300 rounded px-4 py-2"
                  @change="fetchActivites(sessionsId)"
                >
                  <option v-for="session in sessions" :key="session.id" :value="session.id">
                    {{ session.annee }}
                  </option>
                </select>
              </div>
            </div>
            <!-- Liste des activités -->
            <div v-if="filteredActivites.length" class="w-full">
              <div
                v-for="activite in filteredActivites"
                :key="activite.id"
                class="rounded shadow-md py-5 px-4 mt-5 cursor-pointer transition w-full bg-green-200"
                @click="selectionnerActivite(activite.id)"
              >
                <p class="text-sm text-gray-600">{{ activite.structure_sigle }}</p>
                <p class="font-bold">{{ activite.libelle || 'Libellé indisponible' }}</p>
                <span>Hors programme</span>
              </div>
            </div>
            <p v-else class="text-gray-500 mt-4">Aucune activité trouvée.</p>
          </div>

          <!-- Bloc droit : Détails de l'activité (avec bouton Retour en haut à gauche) -->
          <div v-if="activiteIdSelectionne" class="w-full h-[calc(100vh-150px)] flex flex-col items-center mt-4">
            <!-- Bouton Retour en haut à gauche -->
            <div class="fixed left-2 mt-0">
              <button 
                @click="retournerAuBlocGauche" 
                class="bg-gray-100 text-blue-500 px-3 py-2 rounded shadow-md flex items-center"
              >
                <i class="fas fa-arrow-left text-lg"></i>
                <span class="ml-1 text-sm">Retour</span>
              </button>
            </div>
            <!-- Composant AfficherActivite -->
            <AfficherActivite
              v-if="activiteIdSelectionne"
              :key="activiteIdSelectionne + '-' + refreshCount"
              class="mt-7"
              :activite-id="activiteIdSelectionne"
              :activite="activites.find(a => a.id === activiteIdSelectionne)"
            />
          </div>
        </div>

        <!-- Affichage pour PC et tablette -->
        <div v-if="!isMobile" class="flex w-full h-[calc(100vh-200px)] flex-col md:flex-row">
          <!-- Bloc gauche : Liste des sessions et activités -->
          <div class="w-full md:w-2/5 overflow-y-auto px-4 h-full mb-4 md:mb-0">
            <div class="flex flex-col items-center w-full space-y-4">
              <!-- Titre -->
              <h1 class="text-yellow-500 text-lg font-bold">Activités hors programme</h1>
              <div class="flex items-center space-x-4">
                <!-- Barre de recherche -->
                <input
                  type="text"
                  v-model="searchQuery"
                  placeholder="Rechercher une session..."
                  class="w-3/4 border border-gray-300 px-4 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white text-gray-700"
                />
                <!-- Sélecteur de sessions -->
                <select
                  v-model="sessionsId"
                  class="border border-gray-300 rounded px-4 py-2"
                  @change="fetchActivites(sessionsId)"
                >
                  <option v-for="session in sessions" :key="session.id" :value="session.id">
                    {{ session.annee }}
                  </option>
                </select>
              </div>
            </div>
            <!-- Liste des activités -->
            <div v-if="filteredActivites.length" class="w-full overflow-x-auto md:overflow-visible">
              <div
                v-for="activite in filteredActivites"
                :key="activite.id"
                class="rounded shadow-md py-5 px-4 mt-5 cursor-pointer transition w-full bg-green-200"
                @click="selectionnerActivite(activite.id)"
              >
                <p class="text-sm text-gray-600">{{ activite.structure_sigle }}</p>
                <p class="font-bold">{{ activite.libelle || 'Libellé indisponible' }}</p>
                <span>Hors programme</span>
              </div>
            </div>
            <p v-else class="text-gray-500 mt-4">Aucune activité trouvée.</p>
          </div>

          <!-- Bloc droit : Composant AfficherActivite -->
          <div class="w-full md:w-4/6 flex flex-col items-center mt-4 md:mt-0 h-full overflow-y-auto">
            <AfficherActivite
              v-if="activiteIdSelectionne"
              :key="activiteIdSelectionne + '-' + refreshCount"
              :activite-id="activiteIdSelectionne"
              :activite="activites.find(a => a.id === activiteIdSelectionne)"
            />
            <div v-else  class="flex items-center justify-center h-full">
                <span class="text-xl">Veuillez sélectionner une activité pour afficher les informations</span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="alertMessage" :class="['alert', isSuccess ? 'alert-success' : 'alert-error']">
        {{ alertMessage }}
      </div>
    
    <!-- Formulaire Modification Activité -->
    <FormulaireModificationActivite
      v-if="showFormulaire"
      @close="showFormulaire = false"
      @soumettreFormulaire="handleUpdate"
      :activiteId="activiteIdSelectionne"
      :activite="activites.find(a => a.id === activiteIdSelectionne)"
    />
  </div>
</template>


<script>
import axios from 'axios';
import AfficherActivite from './AfficherActivite.vue';
import FormulaireModificationActivite from './FormulaireModificationActivite.vue';

export default {
  name: 'GestionActiviteHortProgramme',
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
      isChefService:false,
      isAdmin: false,
      userInfo: null,
      refreshCount: 0,
    };
  },
  mounted() {
     this.fetchSessions();
    this.fetchSessionEnCours();
   // Vérification de l'activité à recomduire
    const activiteId = this.activites.length ? this.activites[0].id : null;
    if (activiteId) {
      this.recomduireActivite(activiteId); // Appel avec l'ID d'une activité existante
  }
  this.fetchUserInfo();
  // Détecter si l'écran est en mode mobile
  this.isMobile = window.innerWidth <= 768;
    window.addEventListener('resize', () => {
      this.isMobile = window.innerWidth <= 768;
    });
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
    },
    pouvonsModifier() {
      if (!this.activiteIdSelectionne) return false;
      const activite = this.activites.find(a => a.id === this.activiteIdSelectionne);
      if (!activite) return false;

      // Si l'activité est soumise, seuls Admin et Chef de service peuvent modifier
      if (activite.soumi) {
        return this.isAdmin || this.isChefService;
      }
      return true;
    },
    anneeEnCours() {
      const currentSession = this.sessions.find(s => s.id === this.sessionsId);
      return currentSession ? currentSession.annee : null;
    },
   
    async reconduireActivite(id) {
  if (!id) {
    this.showAlert('ID de l\'activité invalide.', false);
    return;
  }

  this.isLoading = true;
  try {
    const response = await axios.put(`/api/activites/${id}/reconduire`);
    const updatedActivite = response.data.activite;

    // Mettre à jour les données localement
    const index = this.activites.findIndex((activite) => activite.id === id);
    if (index !== -1) {
      this.activites[index] = updatedActivite;
    }

    this.showAlert(response.data.message, true);
  } catch (error) {
    console.error('Erreur lors de la reconduction:', error);
    this.showAlert(
      error.response?.data?.message || 'Erreur lors de la reconduction.',
      false
    );
  } finally {
    this.isLoading = false;
  }
},


  },
  methods: {
    
    retournerAuBlocGauche() {
      this.activiteIdSelectionne = null;
    },
    handleUpdate() {
      this.fetchActivites(this.sessionsId);
      this.refreshCount++;
    },

    accederFormulaireModification() {
      if (!this.activiteIdSelectionne) {
        this.showAlert('Veuillez sélectionner une activité.', false);
        return;
      }
      this.showFormulaire = true;
    },
    selectionnerActivite(activiteId) {
      this.activiteIdSelectionne = activiteId;
    },
    async fetchActivites(sessionsId) {
      if (!sessionsId) {
        this.showAlert('Veuillez sélectionner une session.', false);
        return;
      }
      try {
        const response = await axios.get(`/api/activites/session/${sessionsId}/hort/programme`);
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
        this.showAlert('Erreur lors de la récupération des sessions.', false);
      }
    },
    
    async fetchSessionEnCours() {
        try {
          const response = await axios.get('/api/session-Ouvert');
          this.sessionsId = response.data?.id || null;
          if (this.sessionsId) {
            await this.fetchActivites(this.sessionsId);
          } else {
            this.showAlert('Aucune session en cours trouvée.', false);
          }
        } catch (error) {
          console.error('Erreur lors de la récupération de la session en cours :', error);
          this.showAlert('Erreur lors de la récupération de la session en cours.', false);
        }
      },
     
    showAlert(message, isSuccess = true) {
      this.alertMessage = message;
      this.isSuccess = isSuccess;
      setTimeout(() => {
        this.alertMessage = '';
      }, 3000);
    },
    async fetchUserInfo() {
      try {
        const response = await axios.get('/api/user-info');
        const user = response.data;
        this.userInfo = user;
        this.isAdmin = user.role === 'Administrateur';
        this.isChefService = user.role === 'Chef-de-service';
      } catch (error) {
        console.error('Erreur lors de la récupération des informations utilisateur :', error);
      }
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
