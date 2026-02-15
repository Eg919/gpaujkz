<template>
    <div class="flex flex-col items-center min-h-screen mt-2">
      <!-- Conteneur principal -->
      <div class="w-full max-h-screen overflow-y-auto md:overflow-visible">
        <!-- Header -->
        <div class="flex justify-between items-center w-full px-4 md:px-6 py-2 bg-gray-50 shadow-md mt-8">
          <!-- Retour -->
          <div class="flex items-center space-x-1">
            <router-link to="/admin" class="text-blue-500 flex items-center">
              <i class="fas fa-arrow-left text-lg"></i>
              <span class="ml-1 text-xs sm:text-sm md:text-base hidden sm:inline">Retour</span>
            </router-link>
          </div>
          <!-- Titre centralisé -->
          <div class="flex-grow text-center">
            <h1 class="text-red-500 font-semibold truncate">
              <span class="block text-xs sm:text-sm md:text-xl lg:text-4xl">
                Confirmer Activités
              </span>
            </h1>
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
                <h1 class="text-yellow-500 text-lg font-bold">Confirmer Activité</h1>
                <div class="flex items-center space-x-4">
                  <!-- Barre de recherche -->
                  
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
                 <div>
                    <div v-if="activite.etat_session === 'Ouvert'">
                        <div v-if="activite.confirmation_president === 1">
                            <span> activité confirmée</span>
                            <input type="checkbox" v-model="activite.confirmer" @change="confirmerActivite(activite.id)" title="Confirmer cette activité" />

                        </div>
                        <div v-else>
                            <span> activité non confirmée</span>
                        </div>
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
                <h1 class="text-yellow-500 text-lg font-bold">Confirmer activités</h1>
                <div class="flex items-center space-x-4">
                  <!-- Barre de recherche -->
                  <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher une session..."
                    class="w-3/4 border border-gray-300 px-4 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white text-gray-700"
                  />
                
                    <button 
                        @click="tousConfirmer" 
                        class="bg-green-500 text-white px-1 py-1.5 rounded shadow-md flex items-center w-full md:w-1/2"
                        title="Confirmer toutes les activités"
                    >
                        <i class="fas fa-check text-lg"></i> 
                        <span class="hidden sm:inline text-xs sm:text-sm md:text-base"> Tous confirmer</span>
                    </button>
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
                    
                        <div v-if="activite.confirmation_presi">
                            <span> activité confirmée</span>
                        </div>
                        <div v-else>
                            <span> activité non confirmée</span>&nbsp;
                            <input type="checkbox" v-model="activite.confirmer" @change="confirmerActivite(activite.id)" title="Confirmer cette activité" />
                        </div>
                </div>
                </div>
                <p v-else class="text-gray-500 mt-4">Aucune activité trouvée.</p>
            </div>
  
            <!-- Bloc droit : Composant AfficherActivite -->
            <div class="w-full md:w-4/6 flex flex-col items-center mt-4 md:mt-0">
              <AfficherActivite
                v-if="activiteIdSelectionne"
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
      </div>
  
      <!-- Formulaire Modification Activité -->
      <FormulaireModificationActivite 
        v-if="showFormulaire" 
        @close="showFormulaire = false" 
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
    name: 'GestionActiviteHorsProgramme',
    components: {
      AfficherActivite,
      FormulaireModificationActivite,
    },
    data() {
      return {
        activites: [],
        activiteIdSelectionne: null,
        searchQuery: '',
        alertMessage: '',
        isSuccess: true,
        showFormulaire: false,
        isMobile: false,
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      };
    },
    mounted() {
    console.log("Activités filtrées :", this.filteredActivites); //  Correction
    console.log("Activités validées récupérées :", this.activites);
    
    this.isMobile = window.innerWidth <= 768;
    window.addEventListener('resize', () => {
      this.isMobile = window.innerWidth <= 768;
    });
    
    this.getActivitesValidees(); //  Assurez-vous de charger les activités
  },
    computed: {
      filteredActivites() {
        return this.activites.filter((activite) => {
          const searchQuery = this.searchQuery.toLowerCase();
          return (
            activite.libelle?.toLowerCase().includes(searchQuery) ||
            activite.structure_sigle?.toLowerCase().includes(searchQuery)
          );
        });
      },
    },
    methods: {
    selectionnerActivite(activiteId) {
      this.activiteIdSelectionne = activiteId;
    },
      retournerAuBlocGauche() {
        this.activiteIdSelectionne = null;
      },
      async getActivitesValidees() {
        try {
            const response = await axios.get('/api/activites-validees'); // Remplacez avec votre route API correcte
            this.activites = response.data; // Stocker les données dans une variable réactive
            console.log("Activités validées récupérées :", this.activites);
        } catch (error) {
            console.error("Erreur lors de la récupération des activités :", error);
            if (error.response) {
                alert("Erreur : " + error.response.data.message);
            } else {
                alert("Une erreur inconnue s'est produite.");
            }
        }
    },
      async confirmerActivite(activiteId) {
        if(!confirm("Voulez-vous vraiment confirmer cette activitée ?")) {
            return;
        }
        try {
          const response = await axios.post(`/api/activites/${activiteId}/confirmer`, {
            _token: this.csrfToken,
          });
          if (response.status === 200) {
            this.activites = this.activites.map(activite =>
              activite.id === activiteId ? { ...activite, confirmer: true } : activite
            );
            this.showAlert('Activité confirmée avec succès.');
            this.getActivitesValidees(); // Recharger les activités pour mettre à jour les données  
          }
        } catch (error) {
          this.showAlert("Erreur lors de la confirmation.", false);
        }
      },
      async tousConfirmer() {
        if(!confirm("Voulez-vous vraiment confirmer toutes les activités ?")) {
            return;
        }
      
    this.isLoading = true; // Activer un état de chargement

    try {
        const response = await axios.post('/api/activites-confirmer', {
            _token: this.csrfToken,
        });

        if (response.status === 200) {
            this.showAlert(response.data.message || 'Toutes les activités ont été confirmées.');
            this.getActivitesValidees(); // Recharger les activités pour mettre à jour les données  
        } else {
            this.showAlert('Une erreur inconnue est survenue.', false);
        }
    } catch (error) {
        if (error.response) {
            // Erreur côté serveur (ex: 404, 500)
            this.showAlert(error.response.data.message || "Erreur lors de la confirmation.", false);
        } else if (error.request) {
            // Erreur réseau (ex: serveur inaccessible)
            this.showAlert("Le serveur ne répond pas. Vérifiez votre connexion.", false);
        } else {
            // Autre type d'erreur
            this.showAlert("Une erreur inattendue est survenue.", false);
        }
    } finally {
        this.isLoading = false; // Désactiver l'état de chargement
    }
}
,
      showAlert(message, isSuccess = true) {
        this.alertMessage = message;
        this.isSuccess = isSuccess;
        setTimeout(() => (this.alertMessage = ''), 3000);
      },
    },
    
  };
  </script>
  