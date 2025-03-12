<template>
  <div class="flex flex-col items-center min-h-screen">
    <!-- Header avec retour et titre -->
    <div class="flex justify-between items-center w-full px-4 md:px-6 py-2 bg-gray-50 shadow-md mt-8">
      <!-- Lien avec l'icône -->
      <div class="flex items-center space-x-1">
        <router-link 
          to="/canevas-activites" 
          class="text-blue-500 flex items-center" 
          v-if="isAdmin || isPointFocal || isChefService"
        >
          <i class="fas fa-arrow-left text-lg"></i>
          <span class="ml-1 text-sm sm:text-base hidden sm:inline">Retour</span>
        </router-link>
        <router-link 
          to="/select-rapport" 
          class="text-blue-500 flex items-center" 
          v-else
        >
          <i class="fas fa-arrow-left text-lg"></i>
          <span class="ml-1 text-sm sm:text-base hidden sm:inline">Retour</span>
        </router-link>
      </div>

      <!-- Titre principal -->
      <h2 class="text-red-500 font-semibold truncate">
        <span class="block text-sm sm:text-2xl lg:text-4xl">
          Planification de l'activité
        </span>
      </h2>

      <!-- Espace vide à droite -->
      <div class="hidden sm:block mx-4 sm:mx-10"></div>
    </div>
    <!-- Section pour les tâches et les détails de l'activité -->
    <div class="w-full shadow-inner flex flex-col items-center h-[calc(100vh-150px)] sm:h-[calc(100vh-200px)] px-2 overflow-y-auto">
    <!-- Contenu principal -->
    <div class="space-y-4 sm:space-y-8 p-4 sm:p-6 bg-white rounded-lg shadow-lg w-full sm:w-[90%] mt-2 sm:mt-3">
      <!-- Titre et état activité -->
      <div class="font-bold text-gray-700 border-b pb-2 sm:pb-4 flex flex-col sm:flex-row justify-between items-center">
        <!-- Titre principal -->
        <h3 class="text-xl sm:text-2xl">Planification des tâches et détails de l'activité</h3>
        <div class="flex flex-col sm:mt-0 mt-2">
          <label class="font-medium text-gray-800">État Activité</label>
          <div 
            :disabled="isInvite || isSession"
            v-if="activite.etat_activite " 
            :class="{
              'bg-yellow-300 text-black': activite.etat_activite === 'Ouvert',
              'bg-green-500 text-white': activite.etat_activite === 'terminer',
              'bg-red-500 text-white': activite.etat_activite === 'inachever',
            }"
            class="p-2 rounded-lg border-none text-sm sm:text-base"
          >
         <span v-if="activite.etat_activite">{{ activite.etat_activite }}</span>
         <span v-else>en-attente</span>
        </div>
        </div>
      </div>

      <!-- Section des tâches et détails financiers -->
      <div class="flex flex-col sm:flex-row">
        <!-- Colonne des tâches -->
        <div class="w-full sm:w-3/4 pr-0 sm:pr-4">
          <!-- Titre des tâches -->
          <h4 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2 sm:mb-4">Tâches liées à l'activité</h4>
          <!-- Liste des tâches -->
          <ul class="space-y-2 sm:space-y-4 mb-4 sm:mb-6">
            
            <li
              v-for="tache in taches"
              :key="tache.id"
              class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4"
            >
              <div class="w-full sm:w-auto flex-1">
                <span class="ml-2 sm:ml-5 text-sm sm:text-base">Libellé de la tâche</span>
                <input
                  :disabled="isInvite || isSession"
                  v-model="tache.libelle"
                  type="text"
                  placeholder="Libellé de la tâche"
                  class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 text-sm sm:text-base"
                />
              </div>
              <div class="w-full sm:w-auto flex items-center gap-2">
              <!-- Pourcentage de la tâche -->
              <div class="w-full sm:w-auto flex-1">
              <span class="ml-7 sm:ml-10 text-sm sm:text-base">Pourcentage (%)</span>
              <div class="flex items-center gap-1">
                <input
                  :disabled="isInvite || isSession"
                  v-model.number="tache.pourcentage_tache"
                  type="number"
                  placeholder="Pourcentage %"
                  class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 text-sm sm:text-base"
                />
                <span class="text-gray-600 font-medium">% </span>
              </div>
            </div>
              <!-- Taux d'exécution de la tâche -->
              <div class="w-full sm:w-auto flex-1">
                <span class="ml-6 text-sm sm:text-base">Taux d'exécution (%)</span>
              <div class="flex items-center gap-1">
                <input
                  :disabled="isInvite || isSession"
                  v-model.number="tache.taux_execution_tache"
                  type="number"
                  placeholder="Taux d'exécution %"
                  class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 text-sm sm:text-base"
                />
                <span class="text-gray-600 font-medium">% </span>
              </div>
            </div>
              <!-- État de la tâche -->
              <div  class="flex items-center gap-2">
                <span v-if="!tache.etat" class="hidden sm:inline">En cours</span>
                <span v-else class="hidden sm:inline">Terminée</span>
              </div>

              <!-- Bouton Valider -->
              <button
                class="px-2 py-1 bg-green-300 hover:bg-green-200 border rounded-lg"
                @click="modifierTache(tache)"
                :disabled="isInvite || isSession"
                title="Modifier la tâche"
              >
                <i class="fas fa-check text-xl text-green-700"></i>
                <span class="hidden sm:inline px-2">Valider</span>
              </button>

              <!-- Bouton Supprimer -->
              <button
                @click="supprimerTache(tache.id)"
                :disabled="isInvite || isSession"
                type="button"
                class="px-2 py-1 text-red-500 bg-red-300 hover:bg-red-200 border rounded-lg"
                title="Supprimer la tâche"
              >
                <i class="fas fa-trash-alt"></i>
                <span class="hidden sm:inline px-2 text-black">Supprimer</span>
              </button>
            </div>
            </li>
          </ul>
          <!-- Formulaire pour ajouter une tâche -->
          <form
            @submit="ajouterTache"
            class="border-t pt-2 sm:pt-4 flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-4"
          >
            <div class="w-full sm:w-auto space-y-2 px-2 sm:px-4">
              <label for="ajouteTache" class="block text-gray-600 text-sm sm:text-base">Libellé de la tâche</label>
              <input
                id="ajouteTache"
                :disabled="isInvite || isSession"
                v-model="nouvelleTache.libelle"
                type="text"
                placeholder="Libellé de la tâche"
                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 text-sm sm:text-base"
              />
            </div>
            <div class="w-full sm:w-auto space-y-2 px-2 sm:px-4">
              <label for="pourcentage" class="block text-gray-600 text-sm sm:text-base">Pourcentage</label>
              <input
                :disabled="isInvite || isSession"
                v-model.number="nouvelleTache.pourcentage_tache"
                type="number"
                placeholder="Pourcentage de la tâche %"
                class="w-full p-2 border rounded-lg text-center focus:ring-2 focus:ring-blue-400 text-sm sm:text-base"
              />
            </div>
           <div class="w-full sm:w-auto space-y-2 px-2 sm:px-4">
            <label class="block text-gray-600 text-sm sm:text-base hidden sm:inline" for="">Ajouter une tâche</label>
              <input
              type="submit"
              class="w-full p-2 border rounded-lg bg-green-700  text-xl text-white hover:bg-green-500 text-center hover:text-yellow-500 focus:ring-2 focus:ring-blue-400 text-sm sm:text-base"
              :disabled="isInvite || isSession"
              title="Ajouter une tâche"
            >
             
           </input>
            </div>
            
          </form>
        </div>

        <!-- Colonne des états financiers et observations -->
        <div class="w-full sm:w-1/4 mt-4 sm:mt-0">
          <!-- Formulaire pour l'état financier -->
          <form
            @submit="mettreAjourEtatFinancier"
            class="space-y-2 sm:space-y-4"
          >
            <label class="block text-gray-700 text-sm sm:text-base">État Financier</label>
            <input
              :disabled="isInvite || isSession"
              v-model.number="activite.etat_financier"
              type="number"
              class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 text-sm sm:text-base"
            />
            <button
              :disabled="isInvite || isSession"
              class="w-full py-2 bg-green-700 text-xl text-white rounded-lg hover:bg-green-500 hover:text-yellow-500 rounded-lg hover:bg-green-500"
              title="Mettre à jour l'état financier"
            >
              Mettre à jour
            </button>
          </form>
          <!-- Formulaire pour l'observation -->
          <form
            @submit="mettreAjourObservation"
            class="space-y-2 sm:space-y-4 mt-4 sm:mt-6"
          >
            <label class="block text-gray-700 text-sm sm:text-base">Observation</label>
            <textarea
              :disabled="isInvite"
              v-model="activite.observation"
              placeholder="Observation"
              class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 text-sm sm:text-base"
            ></textarea>
         
            <button
              :disabled="isInvite || isSession"
              class="w-full py-2 bg-green-700 text-xl text-white rounded-lg hover:bg-green-500 hover:text-yellow-400 mt-6 sm:mt-0"
              title="Mettre à jour l'observation"
            >
              Mettre à jour
            </button>
          </form>
        </div>
      </div>

      
    </div>
    <!-- Section pour les statistiques -->
    <div class="p-4 sm:p-6 bg-white shadow-lg rounded-lg w-full sm:w-[90%] mt-4 sm:mt-6">
        <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2 sm:mb-4">
          Statistiques de l'activité : {{ defaultValue(activite.libelle) }}
        </h3>
        <!-- Conteneur pour les graphiques -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
          <!-- Graphique 1: Progress Chart -->
          <div class="flex justify-center items-center p-4 bg-gray-50 rounded-lg shadow-sm">
            <canvas id="progressChart" class="w-20 h-20 sm:w-32 sm:h-32"></canvas>
          </div>
          <!-- Graphique 2: Financial Chart -->
          <div class="flex justify-center items-center p-4 bg-gray-50 rounded-lg shadow-sm">
            <canvas id="financialChart" class="w-20 h-20 sm:w-32 sm:h-32"></canvas>
          </div>
        </div>
      </div>
      <div class="p-4 sm:p-6 bg-white shadow-lg rounded-lg w-full sm:w-[90%] mt-4 sm:mt-6">
        <AfficherActivite
              v-if="activiteId"
              :activite-id="activiteId"
            />
      </div>
      <!-- Alertes -->
      <div
        v-if="alertMessage"
        :class="['alert', isSuccess ? 'alert-success' : 'alert-error']"
        class="mt-4 sm:mt-6 text-sm sm:text-base"
      >
        {{ alertMessage }}
      </div>
  </div>
  </div>
</template>
  <script>
  

  import axios from "axios";
  import Chart from 'chart.js/auto';
  import AfficherActivite from './AfficherActivite.vue';
  export default {
    components: {
    AfficherActivite,
  },
    data() {
      return {
      taches: [],
      alertMessage: '',
      etatFinancier: 0,
      financeEtat: 0,
      financePartenaire: 0,
      financement: 0,
      isPointFocal: false,
      activite: {},
      isSession: false,
      isInvite: false,
      indicateurs: [],
      loading: true,
      error: false,
      nouvelleTache: {
        libelle: "",
        pourcentage_tache: 0,
      },
      activiteId: null,
      etatache:null,
    };
  },
    computed: {
      defaultValue() {
        return (value, fallback = "Non renseigné") => value || fallback;
      },
      sommePourcentages() {
      return this.taches.reduce((sum, tache) => sum + tache.pourcentage_tache, 0);
    },
    //  modifierEtat(tache) {
    //   if (!confirm("Êtes-vous sûr de vouloir modifier l'etat de cette tâche ?")) {
    //             return;
    //         }
    //     const response = axios.post(`/api/tachesEtat/${tache.id}`, {
    //       etat: tache.etat,
    //     });
    //     this.showAlert(' Etat de la Tâche Mise à jour avec succes',true);
    // },

    },

    mounted() {
      const id = this.$route.params.id;
      this.activiteId = id;  
      console.log('ID reçu:', id);  

      this.fetchData();  
      this.fetchDetails();
      this.fetchUserInfo();
      this.fetchSessionByActivite();
  },

  methods: {
   async mettreAjourObservation() {
    if (!confirm("Êtes-vous sûr de vouloir mettre à jour l'observation de l'activité ?")) {
                return;
            }
     const response = await axios.post(`/api/activites/observation/${this.activite.id}`, {
       observation: this.activite.observation,
     });
     console.log('Observation mise à jour avec succès :', response.data); 
     this.showAlert("Observation mis à jour avec succès :" ,response.data,true);
   },
    async mettreAJourEtatActivite() {
      if (!confirm("Êtes-vous sûr de vouloir mettre à jour l'État de l'activité à "+ this.activite.etat_activite +" ?")) {
                return;
            }
        const response = await axios.put(`/api/activites-etat/${this.activite.id}`, {
          etat_activite: this.activite.etat_activite,
        });
        console.log("État mis à jour avec succès :", response.data);
        this.showAlert("État mis à jour avec succès :" ,response.data,true);
        this.fetchData();
    },

    async mettreAjourEtatFinancier() {
      if (!confirm("Êtes-vous sûr de vouloir mtrer à jour l'État Financier à " +this.activite.etat_financier+" ?")) {
                return;
            }
        const response = await axios.put(`/api/activites/${this.activite.id}/etat-financier`, {
          etat_financier: this.activite.etat_financier,
        });
        this.showAlert('Mise à jour de l\'état financier réussie.', true);
        this.fetchData(id);
    },

    async fetchDetails() {
      this.loading = true;
      this.error = false;
      try {
        const response = await fetch(`/api/activites-detaille/${this.activiteId}`);
        if (!response.ok) throw new Error("Erreur lors de la récupération des données");
        const data = await response.json();
        this.activite = data.activite;
        this.indicateurs = data.indicateurs;
      } catch (error) {
        console.error("Erreur :", error);
        this.error = true;
      } finally {
        this.loading = false;
      }
    },
    async fetchSessionByActivite() {
  try {
    const response = await axios.get(`/api/activites/${this.activiteId}/session`);
    console.log('Session associée :', response.data);
    this.isSession = response.data.etat =='Clôturé';
  } catch (error) {
    console.error('Erreur lors de la récupération de la session :', error);
    return null;
  }
},
    async fetchData() {
      try {
        const { data } = await axios.get(`/api/activite-statistique/${this.activiteId}`);
        this.taches = data.taches;
        this.etatache = data.taches.etat
        this.etatFinancier = data.etat_financier;
        this.financeEtat = data.finance_etat;
        this.financePartenaire = data.finance_partenaire;
        this.financement=data.financement;
        this.etatActivite = data.etat_activite;
      } catch (error) {
        //this.showAlert("Erreur lors du chargement des données :", false);
        console.log("Erreur lors du chargement des données :, false");
      }
      this.renderCharts();
    },

    ajouterTache() {
      if (this.nouvelleTache.libelle && this.nouvelleTache.pourcentage_tache) {
        axios
          .post(`/api/activites/${this.activiteId}/taches`, this.nouvelleTache)
          .then(() => {
            this.fetchData(this.activiteId); 
            this.nouvelleTache = { libelle: "", pourcentage_tache: 0 };
          });
          this.showAlert('Tâche ajouter avec succes',true);
      }
      this.fetchData();
    },
// async modifierEtat(tache) {
//       if (!confirm("Êtes-vous sûr de vouloir modifier cette tâche ?")) {
//                 return;
//             }
//         const response = await axios.put(`/api/tachesEtat/${tache.id}`, {
//           etat: tache.etat,
//         });
//         this.showAlert(' Etat de la Tâche Mise à jour avec succes',true);
//     },

    async modifierTache(tache) {
      if (!confirm("Êtes-vous sûr de vouloir modifier cette tâche ?")) {
                return;
            }
        const response = await axios.put(`/api/taches/${tache.id}`, {
          libelle: tache.libelle,
          pourcentage_tache: tache.pourcentage_tache,
          taux_execution_tache:tache.taux_execution_tache,
          //etat: tache.etat,
        });
        this.fetchData();
        if(tache.etat) {
        const index = this.taches.findIndex(t => t.id === tache.id);
        if (index !== -1) {
          this.taches[index] = response.data.tache;
        }
        this.showAlert('Tâche Mise à jour avec succes',true);
        this.renderCharts();
      }
    },

    async supprimerTache(tacheId) {
        try {
            if (!confirm("Êtes-vous sûr de vouloir supprimer cette tâche ?")) {
                return;
            }
            const response = await axios.delete(`/api/taches/${tacheId}/supprimer`);
            if (response.status === 200) {
                this.showAlert('Tâche supprimée avec succès.',true);  
            }
            this.fetchData();
        } catch (error) {
            this.showAlert("Erreur lors de la suppression de la tâche :",false);
            if (error.response && error.response.status === 404) {
                this.showAlert("Tâche introuvable.",false);
            } else {
                this.showAlert("Une erreur est survenue lors de la suppression de la tâche.",false);
            }
        }
       
      },
      renderCharts() {
  new Chart(document.getElementById("progressChart").getContext("2d"), {
    type: "doughnut",
    data: {
      labels: this.taches.map((tache) => `${tache.libelle} (${tache.pourcentage_tache}%)`),
      datasets: [
        {
          label: "Progression des tâches",
          data: this.taches.map((tache) => tache.pourcentage_tache),
          backgroundColor: this.taches.map((tache, index) =>
            tache.etat === 1 ? "#4CAF50" : this.getCouleurUnique(index)
          ),
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "bottom",
        },
        tooltip: {
          callbacks: {
            label: function (tooltipItem) {
              return ` ${tooltipItem.raw}%`;
            },
          },
        },
        datalabels: {
          color: "#fff",
          formatter: (value) => `${value}%`,
          font: {
            size: 24, // Taille de la police en pixels
            weight: "bold",
          },
        },
      },
    },
  });

  // Graphique en barres (Coût prévisionnel vs Coût effectif)
  new Chart(document.getElementById("financialChart"), {
    type: "bar",
    data: {
      labels: ["Coût prévisionnel", "Coût Effectif"],
      datasets: [
        {
          data: [this.financement, this.etatFinancier],
          backgroundColor: ["#2196f3", "#ffeb3b"],
        },
      ],
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
        },
      },
      plugins: {
        datalabels: {
          anchor: "end",
          align: "top",
          color: "#000",
          formatter: (value) => `${value} FCFA`,
          font: {
            weight: "bold",
          },
        },
      },
    },
  });
}
,



    getCouleurUnique(index) {
      const couleursParDefaut = ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF", "#FFA726"];
      return couleursParDefaut[index % couleursParDefaut.length]; 
    },

    async fetchUserInfo() {
      try {
        const response = await axios.get('/api/user-info');
        this.userInfo = response.data;
        this.isPointFocal=this.userInfo.role === 'Point-Focale';
        this.isAdmin = this.userInfo.role === 'Administrateur';
        this.isInvite = this.userInfo.role === 'Ordonateur';
      } catch (error) {
        this.showAlert('Erreur lors de la récupération des informations utilisateur :', false);
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
    verifierSommePourcentages() {
      if (this.sommePourcentages !== 100) {
        this.showAlert(
          `La somme des pourcentages des tâches est actuellement ${this.sommePourcentages}%. Veuillez ajuster les valeurs pour atteindre exactement 100%.`,
          false
        );
        return false;
      }
      return true;
    },
    beforeRouteLeave(to, from, next) {
    if (this.sommePourcentages !== 100) {
      if(!this.isInvite){
        if (confirm("La somme des pourcentages des tâches est à " + this.sommePourcentages + "%. Veuillez ajuster les valeurs pour atteindre exactement 100%. Voulez-vous vraiment quitter ?")
        ) {
          next();
        } else {
          next(false);
        }
      } else {
      next();
      } 
    } else {
      next();
    }
  },
 
  };
  </script>