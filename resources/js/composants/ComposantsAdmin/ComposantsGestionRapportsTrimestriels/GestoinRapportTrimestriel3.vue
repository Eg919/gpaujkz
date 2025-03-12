<template>
  <div class="gestion-historiques p-2 h-[98vh] overflow-y-auto border border-gray-300 rounded-lg">
    <!-- Header avec bouton d'export -->
    <div class="flex justify-between items-center w-full mb-4">
      <div></div>
      <button
        @click="exportToExcel"
        class="bg-blue-500 text-white px-3 py-1.5 rounded hover:bg-blue-600 focus:outline-none sticky top-0 z-10 text-xs sm:text-sm md:text-base"
      >
        <i class="fa-solid fa-download"></i> 
        <span class="hidden sm:inline">Télécharger en Excel</span>
      </button>
    </div>

    <!-- Conteneur du tableau avec scroll horizontal sur mobile -->
    <div class="w-full overflow-x-auto">
      <table class="table-auto w-full border border-gray-300 text-sm sm:text-base md:text-lg text-center shadow-md rounded-md">
        <thead class="bg-gray-200">
          <tr>
            <th rowspan="2" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">N°</th>
            <th rowspan="2" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
              OBJECTIFS/<br>ACTIVITÉS
            </th>
            <th rowspan="2" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base hidden md:table-cell">
              CIBLE (RÉSULTATS)
            </th>
            <th rowspan="2" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
              TÂCHES À MENER
            </th>
            <th colspan="4" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
              TÂCHES MENÉES (RÉSULTATS ATTEINTS)
            </th>
            <th rowspan="2" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
              TAUX
            </th>
            <th rowspan="2" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
              COÛT
            </th>
            <th rowspan="2" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
              STRUCT RESP
            </th>
            <th rowspan="2" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base hidden md:table-cell">
              OBSERVATIONS
            </th>
          </tr>
          <tr>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T1</th>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T2</th>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T3</th>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T4</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="(objectif, index) in objectifs" :key="objectif.id">
            <!-- Ligne Objectif global -->
            <tr class="bg-blue-100">
              <td colspan="12" class="py-2 px-2 sm:px-4 border font-semibold text-left text-xs sm:text-sm md:text-base">
                Objectif global {{ index + 1 }} : {{ objectif.libelle }}
              </td>
            </tr>
            <template v-for="(effet, indexEffet) in objectif.effets" :key="effet.id">
              <!-- Ligne Effet -->
              <tr class="bg-green-100">
                <td colspan="12" class="py-2 px-2 sm:px-4 border font-semibold text-left text-xs sm:text-sm md:text-base">
                  {{ `${index + 1}.${indexEffet + 1}` }}. {{ effet.libelle }}
                </td>
              </tr>
              <template v-for="(activite, indexActivite) in effet.activites" :key="activite.id">
                <!-- Ligne Activité -->
                <tr v-if="effet.activites.length">
                  <td class="py-2 px-2 sm:px-4 border text-center text-xs sm:text-sm md:text-base">
                    {{ `${index + 1}.${indexEffet + 1}.${indexActivite + 1}` }}
                  </td>
                  <td class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
                    {{ activite.libelle || '' }}
                  </td>
                  <!-- Masquer cette colonne sur mobile -->
                  <td class="py-2 px-2 sm:px-4 border text-center text-xs sm:text-sm md:text-base hidden md:table-cell">
                    <div v-for="indicateur in activite.indicateurs" :key="indicateur.id">
                      {{ indicateur.cible || '' }}
                    </div>
                  </td>
                  <td class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
                    <div v-for="tache in activite.taches" :key="tache.id">
                      {{ tache.libelle }}
                    </div>
                  </td>
                  <td 
                    v-for="trimestre in [1, 2, 3, 4]" 
                    :key="trimestre" 
                    class="border px-2 sm:px-4 py-2 text-gray-600 text-center text-xs sm:text-sm md:text-base"
                  >
                    {{ activite && activite[`trimestre_${trimestre}`] ? 'X' : '' }}
                  </td>
                  <td
                    class="py-2 px-2 sm:px-4 border text-center text-xs sm:text-sm md:text-base"
                    :class="activite.taux_t1 < 100 ? 'text-red-500' : 'text-green-600'"
                  >
                    {{ activite.taux_t3|| 0 }}%
                  </td>
                  <td class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
                    {{ activite.coute_t3|| 0 }}
                  </td>
                  <td class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
                    {{ activite.structure?.sigle || '' }}
                  </td>
                  <!-- Masquer cette colonne sur mobile -->
                  <td class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base hidden md:table-cell">
                    {{ activite.observation }}
                  </td>
                </tr>
              </template>
            </template>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import * as XLSX from "xlsx";

export default {
  name: "GestionRapportT3",
  data() {
    return {
      objectifs: [],
      sessionsAnne:0,
    };
  },
  mounted() {
    this.fetchObjectifs();
    this.fetchSessionEnCours();
  },
  methods: {
    async fetchSessionEnCours() {
        try {
          const response = await axios.get('/api/session-Ouvert');
          this.sessionsAnne = response.data?.annee || null;
          
        } catch (error) {
          console.error('Erreur lors de la récupération de la session en cours :', error);
        }
      },
    async fetchObjectifs() {
        try {
          const response = await axios.get("/api/objectifs-strategiques-Ouvert-activites");
          if (response.data.status === "success") {
            this.objectifs = response.data.data.map((objectif) => ({
              ...objectif,
              effets: [],
            }));
            for (const objectif of this.objectifs) {
              objectif.effets = await this.fetchEffets(objectif.id);
            }
          }
        } catch (error) {
          console.error("Erreur lors de la récupération des objectifs:", error);
        }
      },
      async fetchEffets(objectifId) {
      try {
        const response = await axios.get(`/api/effets-activites/${objectifId}`);
       
        const effets = response.data.map((effet) => ({
          ...effet,
          activites: [],
        }));
        for (const effet of effets) {
          effet.activites = await this.fetchActivites(effet.id);
        }
        return effets;
      } catch (error) {
        console.error(`Erreur lors de la récupération des effets pour l'objectif ${objectifId}:`, error);
        return [];
      }
    },
    async fetchActivites(effetId) {
  try {
    // Envoi de la requête pour récupérer les activités liées à un effet
    const response = await axios.get(`/api/activites-effet/${effetId}`);
    console.log(`Activités pour l'effet ${effetId}:`, response.data);

    // Vérifier si la réponse est un tableau
    if (Array.isArray(response.data)) {
      // Filtrage des activités selon les critères demandés
      const activites = response.data.filter(activite => 
        activite.etat_selection === 'Validé' || 
        activite.reconduire === this.sessionEnCours // 'sessionEnCours' représentant l'année de la session en cours
      );

      // Retourner les activités filtrées
      return activites.map((activite) => ({
        ...activite,
        // Ajoutez d'autres propriétés si nécessaire
      }));
    } else if (response.data.message) {
      console.log(`Message pour l'effet ${effetId}:`, response.data.message);
      return [];
    } else {
      throw new Error("Réponse inattendue de l'API");
    }
  } catch (error) {
    console.error("Error fetching activities:", error);
    return [];
  }
},
    calculatePercentage(activite) {
  // Vérifier si l'activité et ses tâches existent
  if (!activite || !Array.isArray(activite.taches) || activite.taches.length === 0) {
    console.log("Aucune tâche disponible ou activité invalide.");
    return 0;
  }

  // Calculer la somme des pourcentages des tâches
  const totalPercentage = activite.taches.reduce((sum, tache) => {
    return sum + (tache.taux_execution_tache || 0);
  }, 0);

  // Afficher le résultat dans la console
  console.log(`Total des pourcentages pour l'activité ${activite.libelle}: ${totalPercentage}`);

  // Retourner le pourcentage total arrondi à l'entier le plus proche
  return Math.round(totalPercentage);
}
,



exportToExcel() {
  try {
    // Ajouter un grand titre principal
    const titleRow = [`Rapport des activités 3éme trimestre année ${this.sessionsAnne}`];

    // Ajouter une ligne vide pour séparer le titre du tableau
    const emptyRow = [];

    // En-têtes du tableau
    const headers = [
      "N°",
      "Objectifs/Activités",
      "Cible",
      "Tâches à mener",
      "T1",
      "T2",
      "T3",
      "T4",
      "Taux",
      "Coût",
      "Struct resp",
      "Observations",
    ];

    // Préparer les données à exporter
    const data = [titleRow, emptyRow, headers];
    this.objectifs.forEach((objectif, index) => {
      data.push([`Objectif ${index + 1}: ${objectif.libelle}`]);
      objectif.effets.forEach((effet, effetIndex) => {
        data.push([`${index + 1}.${effetIndex + 1}`, effet.libelle]);
        effet.activites.forEach((activite, activiteIndex) => {
          data.push([
            `${index + 1}.${effetIndex + 1}.${activiteIndex + 1}`,
            activite.libelle || "",
            activite.indicateurs.map((ind) => ind.cible).join(", ") || "",
            activite.taches.map((tache) => tache.libelle).join(", ") || "",
            activite?.trimestre_1 ? 'X' : '', // T1
            activite?.trimestre_2 ? 'X' : '', // T2
            activite?.trimestre_3 ? 'X' : '', // T3
            activite?.trimestre_4 ? 'X' : '', // T4
            activite?.taux_t3,
            activite?.coute_t3,
            activite.structure?.sigle || "",
            activite.observation || "",
          ]);
        });
      });
    });

    // Créer un nouveau classeur
    const wb = XLSX.utils.book_new();

    // Convertir les données en une feuille Excel
    const ws = XLSX.utils.aoa_to_sheet(data);

    // Ajouter la feuille au classeur
    XLSX.utils.book_append_sheet(wb, ws, "Rapport ");

    // Déterminer l'année actuelle
    //const currentYear = new Date().getFullYear();

    // Exporter le fichier Excel
    XLSX.writeFile(wb, `Rapport_activites_3éme_trimestre_${this.sessionsAnne}.xlsx`);
  } catch (error) {
    console.error("Erreur lors de l'exportation Excel:", error);
    alert("Erreur d'exportation. Veuillez réessayer.");
  }
},

  },
};
</script>

<style scoped>
/* Personnalisation de la barre de défilement */
.scrollbar-thin {
  scrollbar-width: thin;
}
</style>
