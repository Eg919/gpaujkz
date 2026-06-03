<template>
  <div class="gestion-historiques bg-gray-50/50 min-h-full flex flex-col print:relative print:h-auto print:overflow-visible w-full" :class="{ '': standalone }">
    <SignatureModal 
      :show="showSignatureModal" 
      @close="showSignatureModal = false" 
      @confirm="triggerPDFExport" 
    />
    <!-- En-tête officiel (UKZ) -->
    <!-- Header avec bouton d'export et Filtres -->
    <div class="print:hidden flex justify-between items-center w-full px-4 sticky z-20 bg-white/80 backdrop-blur-md py-4 border-b border-slate-100 shadow-sm transition-all text-emerald-900 top-0">
      <div class="flex items-center gap-4">
        <h2 class="text-xl font-bold tracking-tight text-slate-900 uppercase">Rapport de Structure</h2>
      </div>
      <div class="flex items-center gap-3">
        <button
          @click="exportToExcel"
          class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg hover:bg-blue-700 transition-all flex items-center gap-2 group border border-blue-700"
        >
          <i class="fa-solid fa-file-excel group-hover:scale-110 transition-transform"></i> 
          <span class="hidden md:inline">Exporter Excel</span>
        </button>
        <button
          @click="exportToPDF"
          class="bg-rose-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg hover:bg-rose-700 transition-all flex items-center gap-2 group border border-rose-700"
        >
          <i class="fa-solid fa-file-pdf group-hover:scale-110 transition-transform"></i> 
          <span class="hidden md:inline">Exporter PDF</span>
        </button>
      </div>
    </div>

    <!-- ========== ZONE D'EXPORT PDF ========== -->
    <div id="pdf-export-zone" class="pdf-export-zone flex-1 flex flex-col min-h-0 min-w-0 overflow-x-hidden bg-white shadow-xl border-b border-slate-200 w-full" style="font-family: 'Times New Roman', serif;">
      <!-- En-tête officiel (UKZ) -->

      <div class="bg-white p-6 flex justify-between items-center border-b border-slate-100 text-slate-800">
        <div class="flex items-center gap-4">
          <div class="w-2 h-8 bg-emerald-600 rounded-full shadow-sm shadow-emerald-100"></div>
          <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900 uppercase">{{ sessionsAnne }} - Rapport de Structure</h2>
            <p class="text-slate-500 text-[10px] font-medium italic opacity-70 tracking-widest uppercase">Suivi analytique par structure de rattachement</p>
          </div>
        </div>
      </div>

      <div class="flex-1 w-full overflow-x-auto overflow-y-auto scrollbar-thin bg-white print:overflow-visible">
        <table class="w-full min-w-[1200px] border-collapse">
          <thead class="sticky top-0 z-30 bg-white shadow-sm border-b border-slate-200">
            <tr class="bg-slate-50/50 text-slate-700">
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center align-top">N°</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-left min-w-[300px] align-top">OBJECTIFS / ACTIVITÉS / TÂCHES</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center hidden md:table-cell align-top">CIBLE</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-left align-top">DESCRIPTION DES TÂCHES</th>
              <th colspan="4" class="pt-4 pb-3 px-4 border-b border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center text-slate-400 bg-slate-50/30 align-top">PROGRESSION TRIMESTRIELLE</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center bg-indigo-50/30 align-top">EXÉC. (%)</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center align-top">DÉPENSES (FCFA)</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center align-top">STRUCTURE</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-slate-200 text-[10px] font-black uppercase tracking-widest text-left hidden md:table-cell align-top">OBSERVATIONS</th>
            </tr>
          <tr>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T1</th>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T2</th>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T3</th>
            <th class="py-2 px-2 sm:px-4 border text-xs sm:text-sm md:text-base">T4</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="(axe, indexAxe) in axes" :key="axe.id">
            <!-- Ligne Axe -->
            <tr class="bg-slate-100 border-l-4 border-slate-500 transition-all">
              <td colspan="12" class="py-4 px-6 font-extrabold text-slate-800 border-b border-slate-200 text-[14px] uppercase tracking-widest">
                <div class="flex items-center gap-3">
                  <span class="w-auto px-3 h-8 rounded-lg bg-slate-600 text-white flex items-center justify-center text-[12px] shadow-sm font-black">AXE {{ indexAxe + 1 }}</span>
                  AXE STRATÉGIQUE : {{ axe.libelle }}
                </div>
              </td>
            </tr>
            <template v-for="(objectif, index) in axe.objectifs" :key="objectif.id">
              <!-- Ligne Objectif global -->
              <tr class="bg-indigo-50/40 border-l-4 border-indigo-600 transition-all">
                <td colspan="12" class="py-4 px-6 font-extrabold text-blue-900 border-b border-blue-100 text-sm uppercase">
                  <div class="flex items-center gap-3 ml-4">
                    <span class="w-8 h-8 rounded-lg bg-blue-700 text-white flex items-center justify-center text-xs shadow-md">{{ indexAxe + 1 }}.{{ index + 1 }}</span>
                    OBJECTIF STRATÉGIQUE : {{ objectif.libelle }}
                  </div>
                </td>
              </tr>
              <template v-for="(effet, indexEffet) in objectif.effets" :key="effet.id">
                <!-- Ligne Effet -->
                <tr class="bg-slate-50 border-l-4 border-slate-400/50">
                  <td colspan="12" class="py-3 px-10 font-bold text-slate-700 border-b border-slate-200 text-[11px] uppercase tracking-wide italic">
                    <div class="flex items-center gap-2 ml-8">
                       <i class="fas fa-arrow-right text-[10px] text-slate-400"></i>
                      OBJECTIF OPÉRATIONNEL {{ `${indexAxe + 1}.${index + 1}.${indexEffet + 1}` }} : {{ effet.libelle }}
                    </div>
                  </td>
                </tr>
                <template v-for="(activite, indexActivite) in effet.activites" :key="activite.id">
                  <!-- Ligne Activité -->
                  <tr v-if="effet.activites.length" class="hover:bg-slate-50 transition-colors group">
                    <td class="py-3 px-4 border-r border-slate-100 text-center text-[10px] font-bold text-slate-400">
                      {{ `${indexAxe + 1}.${index + 1}.${indexEffet + 1}.${indexActivite + 1}` }}
                    </td>
                    <td class="py-3 px-4 border-r border-slate-100 text-[13px] font-black text-slate-800 leading-tight">
                      {{ activite.libelle || '' }}
                    </td>
                    <!-- Masquer cette colonne sur mobile -->
                    <td class="py-3 px-4 border-r border-slate-100 text-center text-[10px] text-slate-500 hidden md:table-cell font-mono">
                      <div v-for="indicateur in activite.indicateurs" :key="indicateur.id">
                        {{ indicateur.cible || '' }}
                      </div>
                    </td>
                    <td class="py-3 px-4 border-r border-slate-100 text-[11px] text-slate-600 leading-relaxed">
                      <div v-for="tache in activite.taches" :key="tache.id" class="mb-1 flex items-start gap-1.5 last:mb-0">
                        <span class="text-indigo-400 font-bold mt-0.5">•</span>
                        {{ tache.libelle }}
                      </div>
                    </td>
                    <td 
                      v-for="trimestre in [1, 2, 3, 4]" 
                      :key="trimestre" 
                      class="border-r border-slate-100 px-1 py-3 text-center"
                    >
                      <span v-if="activite[`trimestre_${trimestre}`]" 
                        class="text-[12px] font-bold text-slate-500"
                      >
                        X
                      </span>
                      <span v-else class="text-slate-100 text-[10px]">-</span>
                    </td>
                    <td
                      class="py-3 px-4 border-r border-slate-100 text-center text-[13px] font-black tabular-nums bg-indigo-50/20"
                      :class="calculatePercentage(activite) < 100 ? 'text-rose-500' : 'text-emerald-600'"
                    >
                      {{ calculatePercentage(activite) }}%
                    </td>
                    <td class="py-3 px-4 border-r border-slate-100 text-center text-[12px] font-black text-slate-900 tabular-nums">
                      {{ activite.etat_financier }}
                    </td>
                    <td class="py-3 px-4 border-r border-slate-100 text-center">
                      <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[10px] font-black uppercase tracking-tighter">
                        {{ activite.structure?.sigle || '' }}
                      </span>
                    </td>
                    <!-- Masquer cette colonne sur mobile -->
                    <td class="py-3 px-4 text-[11px] text-slate-400 hidden md:table-cell italic leading-tight">
                      {{ activite.observation || '-' }}
                    </td>
                  </tr>
                </template>
              </template>
            </template>
          </template>
          <tr>
            <td colspan="11" class="py-2 px-2 sm:px-4 border font-semibold text-xs sm:text-sm md:text-base">
              <span class="ml-2 text-gray-700">(Taux d'execution financier du programme d'activite: {{calculerSommeProgrammeActivite()}})</span>
            </td>
          </tr>
        </tbody>
        </table>

        <!-- Bloc de Signature -->
        <SignatureBlock :data="signatureData" />
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import * as XLSX from "xlsx";
import PDFExportService from "../../../services/PDFExportService";
import SignatureModal from "../ComposantsDeBase/SignatureModal.vue";
import SignatureBlock from "../ComposantsDeBase/SignatureBlock.vue";

export default {
  name: "GestoinRapportStructure",
  components: {
    SignatureModal,
    SignatureBlock
  },
  props: {
    standalone: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      axes: [],
      objectifs: [],
      sessionsAnne:0,
      searchQuery: "",
      showSignatureModal: false,
      signatureData: null,
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
            let objs = response.data.data.map((objectif) => ({
              ...objectif,
              effets: [],
            }));
            for (const objectif of objs) {
              objectif.effets = await this.fetchEffets(objectif.id);
            }
            
            this.objectifs = objs.filter(obj => {
              obj.effets = obj.effets.filter(eff => eff.activites && eff.activites.length > 0);
              return obj.effets.length > 0;
            });
            
            let axesMap = {};
            this.objectifs.forEach(obj => {
              if (obj.axe) {
                if (!axesMap[obj.axe.id]) {
                  axesMap[obj.axe.id] = { ...obj.axe, objectifs: [] };
                }
                axesMap[obj.axe.id].objectifs.push(obj);
              }
            });
            this.axes = Object.values(axesMap);
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
    const response = await axios.get(`/api/activites-effet/${effetId}/st`);
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
calculerSommeFinances(activite) {
  return Number(activite.etat_financier); // Convertir en nombre
},
calculerSommeFinancesEffet(effet) {
  return effet.activites.reduce(
    (somme, activite) => somme + Number(activite.etat_financier), // Convertir en nombre
    0
  );
},
calculerSommeFinancesObjectif(objectif) {
  return objectif.effets.reduce(
    (somme, effet) => somme + this.calculerSommeFinancesEffet(effet),
    0
  );
},
calculerSommeFinancesAxe(axe) {
  return axe.objectifs.reduce(
    (somme, objectif) => somme + this.calculerSommeFinancesObjectif(objectif),
    0
  );
},
calculerSommeProgrammeActivite() {
  return this.axes.reduce(
    (somme, axe) => somme + this.calculerSommeFinancesAxe(axe),
    0
  );
},


    exportToExcel() {
  try {
    // Ajouter un grand titre principal
    const titleRow = [`Rapport des Activités - Année ${this.sessionsAnne}`];

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
      "Taux(en pourcentage)",
      "Coût (en FCFA)",
      "Struct resp",
      "Observations",
    ];

    // Préparer les données à exporter
    const data = [titleRow, emptyRow, headers];
    this.axes.forEach((axe, indexAxe) => {
      data.push([`Axe Stratégique ${indexAxe + 1}: ${axe.libelle}`]);
      axe.objectifs.forEach((objectif, index) => {
        data.push([`Objectif ${indexAxe + 1}.${index + 1}: ${objectif.libelle}`]);
        objectif.effets.forEach((effet, effetIndex) => {
          data.push([`${indexAxe + 1}.${index + 1}.${effetIndex + 1}`, effet.libelle]);
          effet.activites.forEach((activite, activiteIndex) => {
            data.push([
              `${indexAxe + 1}.${index + 1}.${effetIndex + 1}.${activiteIndex + 1}`,
              activite.libelle || "",
              activite.indicateurs.map((ind) => ind.cible).join(", ") || "",
              activite.taches.map((tache) => tache.libelle).join(", ") || "",
              activite?.trimestre_1 ? 'X' : '', // T1
              activite?.trimestre_2 ? 'X' : '', // T2
              activite?.trimestre_3 ? 'X' : '', // T3
              activite?.trimestre_4 ? 'X' : '', // T4
              this.calculatePercentage(activite),
              activite?.etat_financier || "",
              activite.structure?.sigle || "",
              activite.observation || "",
            ]);
          });
        });
      });
    });

    // Ajout d'une ligne supplémentaire pour le taux d'exécution financier
    data.push([
      "",
      `Taux d'execution financier du programme d'activite : `,
      "",
      "",
      "",
      "",
      this.calculerSommeProgrammeActivite(),
      "",
      "",
      "",
      "",
      "",
    ]);

    // Créer un nouveau classeur
    const wb = XLSX.utils.book_new();

    // Convertir les données en une feuille Excel
    const ws = XLSX.utils.aoa_to_sheet(data);

    // Ajouter la feuille au classeur
    XLSX.utils.book_append_sheet(wb, ws, "Rapport");

    // Exporter le fichier Excel
    XLSX.writeFile(wb, `Rapport_activites_${this.sessionsAnne}.xlsx`);
  } catch (error) {
    console.error("Erreur lors de l'exportation Excel:", error);
    alert("Erreur d'exportation. Veuillez réessayer.");
  }
},
    exportToPDF() {
      if (this.currentUser && this.currentUser.role === 'Ordonnateur') {
        this.showSignatureModal = true;
      } else {
        this.triggerPDFExport(null);
      }
    },
    async triggerPDFExport(data) {
      this.signatureData = data;
      this.showSignatureModal = false;
      
      // Lancer l'impression native
      setTimeout(() => {
        window.onafterprint = () => {
          this.signatureData = null;
        };
        window.print();
      }, 500);
    }
  }
};
</script>

<style scoped>
/* Personnalisation de la barre de défilement */
.scrollbar-thin {
  scrollbar-width: thin;
}
</style>
