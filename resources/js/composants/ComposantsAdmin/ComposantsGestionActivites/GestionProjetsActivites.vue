<template>
  <div class="flex flex-col items-center min-h-screen">
    <!-- Header avec retour et titre (Sober Style) -->
    <div class="w-full bg-gray-50 border-b border-gray-200 py-3 px-4 md:px-8 flex items-center mb-8">
      <div class="w-1/4">
        <router-link to="/admin" class="text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-2">
          <i class="fas fa-arrow-left text-xl"></i>
          <span class="text-xs font-bold uppercase hidden md:inline">Retour</span>
        </router-link>
      </div>
      <div class="w-2/4 text-center">
        <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter">Projets d'Activités</h1>
      </div>
      <div class="w-1/4 flex justify-end">
        <button 
          @click="accederFormulaireCreation" 
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
              <h1 class="text-yellow-500 text-lg font-bold">Projets d'Activités</h1>
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
                  <option value="" disabled selected>Sélectionnez une session</option>
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
                class="rounded shadow-md py-5 px-4 mt-5 cursor-pointer transition w-full"
                :class="{
                  'bg-red-200': activite.etat_slection === 'Rejeté',
                  'bg-yellow-200': activite.etat_slection === 'Selectionné',
                  'bg-green-200': activite.etat_slection === 'Validé',
                }"
                @click="selectionnerActivite(activite.id)"
              >
                <p class="text-sm text-gray-600 truncate">{{ activite.structure_sigle }}</p>
                <p class="font-bold truncate">{{ activite.libelle || 'Libellé indisponible' }}</p>
                <!-- Affichage conditionnel -->
                <div v-if="activite.etat_session === 'Ouvert'" class="mt-2">
                  <select
                    v-model="activite.etat_slection"
                    class="border border-gray-300 rounded px-4 py-2 mt-2"
                    @change="updateEtatActiviteSelection(activite)"
                  >
                    <option value="Preselectionné">Préselectionné</option>
                    <option value="Selectionné">Sélectionné</option>
                    <option v-if="isAdmin" value="Validé">Validé</option>
                    <option value="Rejeté">Rejeté</option>
                  </select>
                </div>
                <div v-else class="flex justify-between items-center mt-2">
                  <p class="text-gray-500">Session Clôturée</p>
                  <div>
                    <p class="text-gray-500">Réconduire l'activité</p>
                    <label class="flex items-center space-x-2">
                      <input 
                        type="checkbox"
                        v-model="activite.reconduir" 
                        @change="reconduireActivite(activite.id)" 
                        title="Réconduire cette activité"
                      />
                      <span>{{ activite.reconduir ? 'Récondui' : 'Non récondui' }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-gray-500 mt-4">Aucune activité trouvée.</p>
          </div>

          <!-- Bloc droit : Détails de l'activité (avec bouton Retour en haut à gauche) -->
          <div v-if="activiteIdSelectionne" class="w-full h-[calc(100vh-150px)] flex flex-col items-center mt-4">
            <!-- Bouton Retour en haut à gauche -->
            <div class="fixed  left-2 mt-0">
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
              <h1 class="text-yellow-500 text-lg font-bold">Projets d'Activités</h1>
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
                  <option value="" disabled selected>Sélectionnez une session</option>
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
                class="rounded shadow-md py-5 px-4 mt-5 cursor-pointer transition w-full"
                :class="{
                  'bg-red-200': activite.etat_slection === 'Rejeté',
                  'bg-yellow-200': activite.etat_slection === 'Selectionné',
                  'bg-green-200': activite.etat_slection === 'Validé',
                  'ring-2 ring-blue-400': activite.id === activiteIdSelectionne,
                }"
                @click="selectionnerActivite(activite.id)"
              >
                <p class="text-sm text-gray-600 truncate">{{ activite.structure_sigle }}</p>
                <p class="font-bold truncate">{{ activite.libelle || 'Libellé indisponible' }}</p>
                <!-- Affichage conditionnel -->
                <div v-if="activite.etat_session === 'Ouvert'" class="mt-2 flex items-center space-x-4">
                  <select
                    v-model="activite.etat_slection"
                    class="border border-gray-300 rounded px-4 py-2 mt-2"
                    @change="updateEtatActiviteSelection(activite)"
                  >
                    <option value="Preselectionné">Préselectionnée</option>
                    <option value="Selectionné">Sélectionnée</option>
                    <option v-if="isAdmin" value="Validé">Validée</option>
                    <option value="Rejeté">Rejeté</option>
                  </select>
                  <button 
                      v-if="activite.etat_slection === 'Rejeté'"
                      @click="supprimerActivite(activite.id)" 
                      class=" text-red-700 px-3 py-1 rounded hover:bg-red-200 flex flex-col items-center justify-center">
                      <i class="fas fa-trash-alt"></i> <!-- Icône de suppression -->
                      <span class="text-red-700 text-xs hidden sm:inline">Supprimer</span>
                  </button>
                </div>
                
                <div v-else class="flex justify-between items-center mt-2">
                  <!-- Message "Session Clôturée" -->
                  <p class="text-gray-500">Session Clôturée</p>

                  <!-- Section de réconduction -->
                  <div>
                    <!-- Titre "Réconduire l'activité" -->
                    <p class="text-gray-500">Réconduire l'activité</p>

                    <!-- Case à cocher pour réconduire l'activité -->
                    <label class="flex items-center space-x-2">
                      <div v-if="activite.reconduir !== anneeEnCours">
                        <input
                        type="checkbox"
                        v-model="activite.reconduir" 
                        @change="reconduireActivite(activite.id)"
                        :disabled="isLoading"
                        title="Réconduire cette activité"
                      />
                      <!-- Texte dynamique en fonction de l'état de la case à cocher -->
                       <span >Réconduire</span>
                      </div>
                     
                       <span v-else>Reconduit</span>
                     
                    </label>
                    
                  </div>
              </div>
              </div>
            </div>
            <p v-else class="text-gray-500 mt-4">Aucune activité trouvée.</p>
          </div>

          <!-- Bloc droit : Composant AfficherActivite -->
          <div class="w-full md:w-4/6 flex flex-col items-center mt-4 md:mt-0">
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

      <!-- Alertes -->
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
  name: 'GestionProjetsActivites',
  components: {
    AfficherActivite,
    FormulaireModificationActivite,
  },
  data() {
    return {
      anneeEnCours: new Date().getFullYear(), // Récupérer l'année en cours
      activites: [],
      sessions: [],
      activiteIdSelectionne: null,
      searchQuery: '',
      alertMessage: '',
      isSuccess: true,
      showFormulaire: false,
      sessionsId: null,
      isChefService:false,
      isAdmin: false,
      actionLoading: false,
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
         // activite.libelle?.toLowerCase().includes(searchQuery) ||
          activite.structure_sigle?.toLowerCase().includes(searchQuery)
        );
      });
    },
    pouvonsModifier() {
      if (!this.activiteIdSelectionne) return false;
      const activite = this.activites.find(a => a.id === this.activiteIdSelectionne);
      if (!activite) return false;
      
      // Si l'activité est déjà soumise, seuls les admins et chefs de service peuvent modifier
      if (activite.soumi == 1) {
        return this.isAdmin || this.isChefService;
      }
      
      // Verification additionnelle si preselectionné ou selectionné (selon demande utilisateur)
      // On autorise la modification sans restriction si pas encore validé définitivement
      return true;
    }
  },
  methods: {
    
    retournerAuBlocGauche() {
      this.activiteIdSelectionne = null;
    },
    handleUpdate() {
      this.fetchActivites(this.sessionsId);
      this.refreshCount++;
    },

    accederFormulaireCreation() {
      this.showFormulaire = true;
    },
    selectionnerActivite(activiteId) {
      this.activiteIdSelectionne = activiteId;
    },
    async reconduireActivite(id) {
  // Validation de l'ID
  if (!id || isNaN(id)) {
    this.showAlert('ID de l\'activité invalide.', false);
    return;
  }

  // Activer l'état de chargement
  this.isLoading = true;

  try {
    // Envoyer la requête PUT pour reconduire l'activité
    const response = await axios.put(`/api/activites/${id}/reconduire`);

    // Vérifier la réponse du serveur
    if (!response.data || !response.data.activite || !response.data.message) {
      throw new Error('Réponse du serveur invalide.');
    }

    // Récupérer l'activité mise à jour depuis la réponse
    const updatedActivite = response.data.activite;

    // Mettre à jour les données localement
    const index = this.activites.findIndex((activite) => activite.id === id);
    if (index !== -1) {
      // Mettre à jour l'élément dans le tableau réactif
      this.activites.splice(index, 1, updatedActivite);
    }

    // Afficher un message de succès
    this.showAlert(response.data.message, true);
  } catch (error) {
    // Gestion des erreurs
    if (error.response) {
      // Erreur côté serveur (4xx ou 5xx)
      console.error('Erreur serveur:', error.response.data);
      this.showAlert(
        error.response.data?.message || 'Erreur lors de la reconduction.',
        false
      );
    } else if (error.request) {
      // Pas de réponse du serveur (problème réseau)
      console.error('Pas de réponse du serveur:', error.request);
      this.showAlert('Erreur réseau. Veuillez vérifier votre connexion.', false);
    } else {
      // Erreur inattendue
      console.error('Erreur inattendue:', error.message);
      this.showAlert('Une erreur inattendue s\'est produite.', false);
    }
  } finally {
    // Désactiver l'état de chargement
    this.isLoading = false;
  }
},
    async fetchActivites(sessionsId) {
      if (!sessionsId) {
        this.showAlert('Veuillez sélectionner une session.', false);
        return;
      }
      try {
        const response = await axios.get(`/api/activites/session/${sessionsId}`);
        this.activites = response.data || [];
        //this.showAlert('Activités chargées avec succès.', true);
      } catch (error) {
        console.error('Erreur lors de la récupération des activités :', error);
        this.activites = [];
        this.showAlert('Erreur lors de la récupération des activités.', false);
      }
    },
    async supprimerActivite(id) {
      if (this.actionLoading) return;
      if (!confirm('Êtes-vous sûr de vouloir supprimer cette activité?')) {
        console.log('Suppression annulée par l\'utilisateur.');
        return;
      }

      this.actionLoading = true;
      try {
        await axios.delete(`/api/activites/${id}/supprimer`, {
          headers: {
            'X-XSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          }
        });
        this.activites = this.activites.filter((activite) => activite.id !== id);
        this.showAlert('Activité supprimée avec succès.', true);
      } catch (error) {
        console.error('Erreur lors de la suppression de l\'activité:', error);
        this.showAlert('Une erreur est survenue lors de la suppression de l\'activité.', false);
      } finally {
        this.actionLoading = false;
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
    async updateEtatActiviteSelection(activite) {
      if (!activite || !activite.id) return;
      try {
        await axios.put(`/api/activites/${activite.id}`, {
          etat_slection: activite.etat_slection,
        });
        this.showAlert('État mis à jour avec succès.', true);
      } catch (error) {
        console.error("Erreur lors de la mise à jour de l'état :", error);
        this.showAlert("Erreur lors de la mise à jour de l'état.", false);
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
