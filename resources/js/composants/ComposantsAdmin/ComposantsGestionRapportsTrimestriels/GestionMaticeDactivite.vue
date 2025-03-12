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
      <table class="min-w-full bg-white border border-gray-300 shadow-md">
        <thead class="bg-gray-200">
          <tr>
            <th rowspan="3" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">N°</th>
            <th rowspan="3" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
              PROGRAMMES/<br>OBJECTIFS/ACTIVITES
            </th>
            <th rowspan="3" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base hidden md:table-cell">
              Indicateurs
            </th>
            <th rowspan="3" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base hidden md:table-cell">
              Unité de<br>l'indicateur
            </th>
            <th rowspan="3" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base hidden md:table-cell">
              Référence
            </th>
            <th rowspan="3" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base hidden md:table-cell">
              Cible (Résultat attendu)
            </th>
            <th rowspan="3" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
              Coûts estimatifs<br>(en millions)
            </th>
            <th colspan="4" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
              PÉRIODE DE RÉALISATION
            </th>
            <th rowspan="3" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
              STRUCTURE RESPONSABLE
            </th>
          </tr>
          <tr>
            <th colspan="4" class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base"></th>
          </tr>
          <tr>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T1</th>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T2</th>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T3</th>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T4</th>
          </tr>
        </thead>
        <tbody>
          <!-- Boucle sur les objectifs -->
          <template v-for="(objectif, index) in objectifs" :key="objectif.id">
            <!-- Ligne Objectif stratégique -->
            <tr class="bg-yellow-100">
              <td colspan="11" class="py-2 px-2 sm:px-4 border font-semibold text-xs sm:text-sm md:text-base">
                Objectif stratégique {{ index + 1 }}: {{ objectif.libelle }}
              </td>
            </tr>

            <!-- Boucle sur les effets de l'objectif -->
            <template v-for="(effet, indexEffet) in objectif.effets" :key="effet.id">
              <!-- Ligne Objectif opérationnel -->
              <tr class="bg-orange-100">
                <td colspan="11" class="py-2 px-2 sm:px-4 border font-semibold text-xs sm:text-sm md:text-base">
                  Objectif opérationnel {{ `${index + 1}.${indexEffet + 1}` }}: {{ effet.libelle }}
                </td>
              </tr>

              <!-- Boucle sur les activités de l'effet -->
              <template v-for="(activite, indexActivite) in effet.activites" :key="activite.id">
                <tr>
                  <td class="py-2 px-2 sm:px-4 border text-center text-xs sm:text-sm md:text-base">
                    {{ `${index + 1}.${indexEffet + 1}.${indexActivite + 1}` }}
                  </td>
                  <td class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
                    {{ activite.libelle || '' }}
                  </td>
                  <td class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base hidden md:table-cell">
                    {{ activite.indicateurs[0]?.indicateur || '' }}
                  </td>
                  <td class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base hidden md:table-cell">
                    {{ activite.indicateurs[0]?.unite || '' }}
                  </td>
                  <td class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base hidden md:table-cell">
                    {{ activite.indicateurs[0]?.reference || '' }}
                  </td>
                  <td class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base hidden md:table-cell">
                    {{ activite.indicateurs[0]?.cible || '' }}
                  </td>
                  <td class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">
                    {{ calculerSommeFinances(activite) }}
                  </td>
                  <td 
                    v-for="trimestre in [1, 2, 3, 4]" 
                    :key="trimestre" 
                    class="border px-2 sm:px-4 py-2 text-gray-600 text-center text-xs sm:text-sm md:text-base"
                  >
                    {{ activite[`trimestre_${trimestre}`] ? 'X' : '' }}
                  </td>
                  <td class="py-2 px-2 sm:px-4 border text-center text-xs sm:text-sm md:text-base">
                    {{ activite.structure.sigle || 'UKZ' }}
                  </td>
                </tr>
              </template>

              <!-- Ligne Total pour l'objectif opérationnel -->
              <tr class="bg-orange-100">
                <td colspan="11" class="py-2 px-2 sm:px-4 border font-semibold text-xs sm:text-sm md:text-base">
                  Objectif opérationnel {{ `${index + 1}.${indexEffet + 1}` }}:
                  <span class="ml-2 text-gray-700">(Total: {{ calculerSommeFinancesEffet(effet) }})</span>
                </td>
              </tr>
            </template>

            <!-- Ligne Total pour l'objectif stratégique -->
            <tr class="bg-yellow-100">
              <td colspan="11" class="py-2 px-2 sm:px-4 border font-semibold text-xs sm:text-sm md:text-base">
                Objectif stratégique {{ index + 1 }}:
                <span class="ml-2 text-gray-700">(Total: {{ calculerSommeFinancesObjectif(objectif) }})</span>
              </td>
            </tr>
          </template>

          <!-- Ligne Total général du programme -->
          <tr>
            <td colspan="11" class="py-2 px-2 sm:px-4 border font-semibold text-xs sm:text-sm md:text-base">
              <span class="ml-2 text-gray-700">(Total du programme d'activite: {{ calculerSommeProgrammeActivite() }})</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>


<script>
import axios from "axios";
import * as XLSX from "xlsx";

export default {
  name: "GestionMaticeDuProgrammeDactivités",
  data() {
    return {
      objectifs: [],
      sessionsAnne: 0,
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
      calculerSommeFinances(activite) {
  return (+activite.finance_partenaire || 0) + (+activite.finance_etat || 0);
},
calculerSommeFinancesEffet(effet) {
  return effet.activites.reduce(
    (somme, activite) =>
      somme + (+activite.finance_partenaire || 0) + (+activite.finance_etat || 0),
    0
  );
},
calculerSommeFinancesObjectif(objectif) {
  return objectif.effets.reduce(
    (somme, effet) => somme + this.calculerSommeFinancesEffet(effet),
    0
  );
},
calculerSommeProgrammeActivite() {
  return this.objectifs.reduce(
    (somme, objectif) => somme + this.calculerSommeFinancesObjectif(objectif),
    0
  );
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
        activite.reconduire_annee === this.sessionEnCours // 'sessionEnCours' représentant l'année de la session en cours
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
  
exportToExcel() {
  try {
    // Ajouter un grand titre principal
    const titleRow = [`Matice du programme d'activités ${this.sessionsAnne}`];

    // Créer une feuille de calcul
    const worksheetData = [titleRow, []]; // Ajout du titre et d'une ligne vide

    // Ajouter l'en-tête du tableau
    worksheetData.push([
      "N°",
      "PROGRAMMES/OBJECTIFS/ACTIVITES",
      "Indicateurs",
      "Unité de l'indicateur",
      "Référence",
      "Cible (Résultat attendu)",
      "Coûts estimatifs (en millions)",
      "T1",
      "T2",
      "T3",
      "T4",
      "STRUCTURE RESPONSABLE",
    ]);

    // Parcourir les objectifs pour remplir les données
    this.objectifs.forEach((objectif, index) => {
      worksheetData.push([`Objectif stratégique ${index + 1}: ${objectif.libelle}`]);

      objectif.effets.forEach((effet, effetIndex) => {
        worksheetData.push([
          `Objectif opérationnel ${index + 1}.${effetIndex + 1}: ${effet.libelle}`,
        ]);

        effet.activites.forEach((activite, activiteIndex) => {
          worksheetData.push([
            `${index + 1}.${effetIndex + 1}.${activiteIndex + 1}`,
            activite.libelle || "",
            activite.indicateurs?.[0]?.indicateur || "",
            activite.indicateurs?.[0]?.unite || "",
            activite.indicateurs?.[0]?.reference || "",
            activite.indicateurs?.[0]?.cible || "",
            this.calculerSommeFinances(activite),
            activite.trimestre_1 ? "X" : "",
            activite.trimestre_2 ? "X" : "",
            activite.trimestre_3 ? "X" : "",
            activite.trimestre_4 ? "X" : "",
            activite.structure?.sigle || "UKZ",
          ]);
        });

        // Calcul des sommes pour l'effet
        worksheetData.push([
          "",
          `Total Effet ${index + 1}.${effetIndex + 1}`,
          "",
          "",
          "",
          "",
          this.calculerSommeFinancesEffet(effet),
          "",
          "",
          "",
          "",
          "",
        ]);
      });

      // Calcul des sommes pour l'objectif
      worksheetData.push([
        "",
        `Total Objectif ${index + 1}`,
        "",
        "",
        "",
        "",
        this.calculerSommeFinancesObjectif(objectif),
        "",
        "",
        "",
        "",
        "",
      ]);
    });
    worksheetData.push([
        "",
        `Total du programme d'activite: `,
        "",
        "",
        "",
        "",
        this.calculerSommeProgrammeActivite() ,
        "",
        "",
        "",
        "",
        "",
      ]);

    // Convertir les données en feuille de calcul
    const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);

    // Créer un classeur
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Matice du programme");

    // Corriger le nom du fichier pour éviter l'apostrophe
    const fileName = `Matice_du_programme_d_activites_${this.sessionsAnne}.xlsx`;

    // Exporter le fichier Excel
    XLSX.writeFile(workbook, fileName);

  } catch (error) {
    console.error("Erreur lors de l'exportation des données :", error);
    alert("Une erreur est survenue lors de l'exportation des données.");
  }
}



  },
};
</script>

<style scoped>
.scrollbar-thin {
  scrollbar-width: thin;
}
.scrollbar-thumb-gray-300 {
  scrollbar-color: #d1d5db #f3f4f6;
}
.scrollbar-track-gray-100 {
  scrollbar-color: #f3f4f6;
}
</style>
