<template>
  <div class="gestion-historiques bg-gray-50/50 absolute top-14 bottom-0 left-0 right-0 overflow-hidden flex flex-col print:relative print:h-auto print:overflow-visible" :class="{ '': standalone }">
    <SignatureModal 
      :show="showSignatureModal" 
      @close="showSignatureModal = false" 
      @confirm="triggerPDFExport" 
    />
    <!-- Header avec bouton d'export et Filtres -->
    <div class="print:hidden flex justify-between items-center w-full max-w-[99%] mx-auto mb-4 px-4 sticky z-20 bg-white/80 backdrop-blur-md py-4 rounded-xl border border-slate-100 shadow-sm transition-all hover:bg-white text-emerald-900" :class="standalone ? 'top-12' : 'top-0'">
      <div class="flex items-center gap-4">
        <!-- Sélecteur de Structure -->
        <div class="relative group min-w-[300px]">
          <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-black text-indigo-500 uppercase tracking-widest z-10 transition-all group-focus-within:text-indigo-600">Filtrer par Structure</label>
          <select 
            v-model="selectedStructureId"
            :disabled="!canSelectAnyStructure"
            class="w-full bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50/50 transition-all appearance-none cursor-pointer disabled:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400"
          >
            <option value="">Toutes les structures</option>
            <option v-for="struct in structures" :key="struct.id" :value="struct.id">
              {{ struct.sigle }} - {{ struct.libelle }}
            </option>
          </select>
          <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
            <i class="fas fa-chevron-down text-[10px]"></i>
          </div>
        </div>
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

    <div id="pdf-export-zone" class="pdf-export-zone flex-1 flex flex-col min-h-0 mt-4 bg-white rounded-2xl shadow-xl border border-slate-200 mx-auto w-full max-w-[99%]" style="font-family: 'Outfit', sans-serif;">
      <!-- En-tête officiel (UKZ) -->
      <!-- Header du Tableau Sobre -->
      <div class="bg-white p-6 flex justify-between items-center border-b border-slate-100 text-slate-800">
        <div class="flex items-center gap-4">
          <div class="w-2 h-8 bg-blue-600 rounded-full shadow-sm"></div>
          <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900 uppercase">Rapport d'Exécution : 1er Trimestre</h2>
            <p class="text-slate-500 text-[10px] font-medium italic opacity-80">Suivi des activités et performances physiques (T1)</p>
          </div>
        </div>
        <div class="px-5 py-2.5 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col items-end">
          <span class="text-slate-400 text-[9px] uppercase tracking-widest block font-bold mb-0.5">Année Budgétaire</span>
          <span class="text-slate-900 font-extrabold text-xl leading-tight">{{ sessionsAnne }}</span>
        </div>
      </div>

      <div class="flex-1 overflow-auto scrollbar-thin print:overflow-visible">
        <table class="w-full border-collapse">
          <thead class="sticky top-0 z-30 bg-white shadow-sm border-b border-slate-200">
            <tr class="bg-slate-50 text-slate-700">
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center align-top">N°</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-left min-w-[300px] align-top">OBJECTIFS / ACTIVITÉS / TÂCHES</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center hidden md:table-cell align-top">CIBLE</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-left align-top">DESCRIPTION DES TÂCHES</th>
              <th colspan="4" class="pt-4 pb-3 px-4 border-r border-b border-slate-200 text-[10px] font-black uppercase tracking-widest text-center text-slate-400 bg-slate-50/30 align-top">PROGRESSION TRIMESTRIELLE</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center align-top">Avancement T1 (%)</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center align-top">Budget T1 (FCFA)</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center align-top">Partenaire(s)</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-r border-slate-200 text-[10px] font-black uppercase tracking-widest text-center align-top">Resp.</th>
              <th rowspan="2" class="pt-4 pb-2 px-4 border-slate-200 text-[10px] font-black uppercase tracking-widest text-left hidden md:table-cell align-top">Observations</th>
            </tr>
            <tr class="bg-white text-slate-400 border-b border-slate-200">
              <th class="py-2 border-r border-slate-200 text-[9px] font-black">T1</th>
              <th class="py-2 border-r border-slate-200 text-[9px] font-black">T2</th>
              <th class="py-2 border-r border-slate-200 text-[9px] font-black">T3</th>
              <th class="py-2 border-r border-slate-200 text-[9px] font-black">T4</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-for="(axe, indexAxe) in filteredAxes" :key="axe.id">
              <!-- Ligne Axe Stratégique -->
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
                <tr class="bg-indigo-50/50 border-l-4 border-blue-600 transition-all">
                  <td colspan="12" class="py-4 px-6 font-extrabold text-blue-900 border-b border-blue-100 text-sm uppercase">
                    <div class="flex items-center gap-3 ml-4">
                      <span class="w-8 h-8 rounded-lg bg-blue-700 text-white flex items-center justify-center text-xs shadow-md">{{ indexAxe + 1 }}.{{ index + 1 }}</span>
                      OBJECTIF STRATÉGIQUE : {{ objectif.libelle }}
                    </div>
                  </td>
                </tr>
                <template v-for="(effet, indexEffet) in objectif.effets" :key="effet.id">
                  <!-- Ligne Objectif Opérationnel -->
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
                    <tr class="hover:bg-slate-50 transition-colors group">
                      <td class="py-3 px-4 border-r border-slate-100 text-center text-[10px] font-bold text-slate-400">
                        {{ `${indexAxe + 1}.${index + 1}.${indexEffet + 1}.${indexActivite + 1}` }}
                      </td>
                      <td class="py-3 px-4 border-r border-slate-100 text-xs font-black text-slate-800 leading-tight">
                        {{ activite.libelle || '' }}
                      </td>
                      <td class="py-3 px-4 border-r border-slate-100 text-center text-[11px] text-slate-500 hidden md:table-cell font-mono">
                        <div v-for="indicateur in activite.indicateurs" :key="indicateur.id">
                          {{ indicateur.cible || '-' }}
                        </div>
                      </td>
                      <td class="py-3 px-4 border-r border-slate-100 text-[11px] text-slate-600 leading-relaxed">
                        <div v-for="tache in activite.taches" :key="tache.id" class="mb-1 flex items-start gap-1.5 last:mb-0">
                          <span class="text-blue-400 font-bold mt-0.5">•</span>
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
                        <span v-else class="text-slate-100">-</span>
                      </td>
                      <td class="py-3 px-4 border-r border-slate-100 text-center text-[13px] font-black text-blue-900 bg-blue-50/20">
                        {{ activite.taux_t1 || 0 }}%
                      </td>
                      <td class="py-3 px-4 border-r border-slate-100 text-center text-[12px] font-black text-slate-800 tabular-nums">
                        {{ formatNumber(activite.coute_t1) }}
                      </td>
                      <td class="py-3 px-4 border-r border-slate-100 text-center">
                         <div class="flex flex-wrap gap-1 justify-center">
                          <span v-for="part in activite.partenaires_details" :key="part.nom" class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[9px] font-bold border border-blue-100 italic">
                            {{ part.nom }}
                          </span>
                          <span v-if="!activite.partenaires_details || !activite.partenaires_details.length" class="text-slate-300 text-[9px] italic font-medium">Aucun</span>
                        </div>
                      </td>
                      <td style="padding: 12px 10px; text-align:center; vertical-align:middle;">
                        <div style="display:flex; flex-direction:column; gap:4px; align-items:center;">
                          <span style="font-size:9px; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:0.04em;">
                            {{ activite.structure_sigle || 'UKZ' }}
                          </span>
                          <template v-if="activite.structures_partenaires && activite.structures_partenaires.length > 0">
                            <span v-for="partenaire in activite.structures_partenaires" :key="partenaire.id" style="font-size:8px; font-weight:700; color:#64748b; text-transform:uppercase;">
                              / {{ partenaire.sigle }}
                            </span>
                          </template>
                        </div>
                      </td>
                      <td class="py-3 px-4 text-[11px] text-slate-400 hidden md:table-cell italic leading-tight">
                        {{ activite.observation || '-' }}
                      </td>
                    </tr>
                  </template>
                  <!-- Ligne Sous-Total Objectif Opérationnel -->
                  <tr class="bg-blue-50/10">
                    <td colspan="8" class="py-4 px-10 text-[10px] font-black text-blue-400 uppercase tracking-widest text-right border-r border-blue-100">
                      Sous-Total Objectif Opérationnel :
                    </td>
                    <td class="py-4 px-4 text-center text-[13px] font-black text-blue-700 bg-blue-50/30 border-r border-blue-100 shadow-inner">
                      {{ calculerMoyenneEffet(effet) }}%
                    </td>
                    <td class="py-4 px-4 text-center text-[13px] font-black text-emerald-600 border-r border-blue-100 bg-white tabular-nums">
                      {{ formatNumber(calculerTotalEffet(effet)) }}
                    </td>
                    <td colspan="2" class="bg-slate-50/30"></td>
                  </tr>
                </template> <!-- Fin effet -->
                
                <!-- Ligne Total Objectif Stratégique -->
                <tr class="bg-blue-600/5 border-b-2 border-blue-200">
                  <td colspan="8" class="py-4 px-6 text-[10px] font-black text-blue-700 uppercase tracking-widest text-right border-r border-blue-200">
                    TOTAL RÉCAPITULATIF - {{ objectif.libelle }} :
                  </td>
                  <td class="py-4 px-4 text-center text-[14px] font-black text-blue-900 bg-blue-100/40 border-r border-blue-200">
                    {{ calculerMoyenneObjectif(objectif) }}%
                  </td>
                  <td class="py-4 px-4 text-center font-black text-blue-900 bg-blue-100/40 border-r border-blue-200 shadow-inner">
                    {{ formatNumber(calculerTotalObjectif(objectif)) }}
                  </td>
                  <td colspan="2" class="bg-blue-600/5"></td>
                </tr>
              </template> <!-- Fin objectif -->
              
              <!-- Ligne Total Axe Stratégique -->
              <tr class="bg-slate-100 border-b-2 border-slate-300">
                <td colspan="8" class="py-4 px-6 text-[11px] font-black text-slate-800 uppercase tracking-widest text-right border-r border-slate-300">
                  TOTAL AXE STRATÉGIQUE ({{ indexAxe + 1 }}) :
                </td>
                <td class="py-4 px-4 text-center text-[14px] font-black text-slate-900 bg-slate-200/50 border-r border-slate-300">
                  {{ calculerMoyenneAxe(axe) }}%
                </td>
                <td class="py-4 px-4 text-center font-black text-slate-900 bg-slate-200/50 border-r border-slate-300 shadow-inner">
                  {{ formatNumber(calculerTotalAxe(axe)) }}
                </td>
                <td colspan="2" class="bg-slate-100"></td>
              </tr>
            </template>
            
            <!-- Récapitulatif Final T1 -->
            <tr class="bg-white border-t-2 border-blue-600">
                <td colspan="12" class="py-10 px-10">
                    <div class="flex justify-between items-center max-w-4xl ml-auto">
                        <div class="flex items-center gap-8 group">
                            <div class="text-right whitespace-nowrap">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 group-hover:text-blue-600 transition-colors">Dépenses T1</span>
                                <p class="text-slate-400 text-[9px] italic font-medium leading-none">Total décaissé pour le trimestre</p>
                            </div>
                            <div class="h-10 w-px bg-slate-100 transition-all group-hover:h-12 group-hover:bg-blue-100"></div>
                            <div class="text-4xl font-black text-slate-900 tabular-nums tracking-tighter flex items-baseline gap-1.5">
                                {{ formatNumber(calculerSommeTrimestre()) }} <span class="text-sm font-bold text-slate-300">FCFA</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-8 group">
                            <div class="text-right whitespace-nowrap">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 group-hover:text-emerald-500 transition-colors">Performance Moyenne</span>
                                <p class="text-slate-400 text-[9px] italic font-medium leading-none">Taux d'exécution physique</p>
                            </div>
                            <div class="h-10 w-px bg-slate-100 transition-all group-hover:h-12 group-hover:bg-emerald-100"></div>
                            <div class="px-6 py-3 rounded-2xl text-2xl font-black bg-slate-900 text-white shadow-xl transition-all scale-100 hover:scale-105">
                                {{ calculerPerformanceGlobale() }}%
                            </div>
                        </div>
                    </div>
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
  name: "GestoinRapportTrimestriel1",
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
      sessionsAnne: 0,
      structures: [],
      selectedStructureId: "",
      currentUser: null,
      showSignatureModal: false,
      signatureData: null,
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    };
  },
  computed: {
    canSelectAnyStructure() {
        if (!this.currentUser) return false;
        const fullAccessRoles = ['Administrateur', 'Chef-de-service', 'Ordonnateur', 'Administrateur_DSI'];
        return fullAccessRoles.includes(this.currentUser.role);
    },
    filteredAxes() {
      if (!this.selectedStructureId) return this.axes;
      
      return this.axes.map(axe => {
        const filteredObjectifs = axe.objectifs.map(obj => {
          const filteredEffets = obj.effets.map(eff => {
              const filteredActivites = eff.activites.filter(act => 
                act.structure_id == this.selectedStructureId || 
                (act.structure && act.structure.id == this.selectedStructureId)
              );
              return { ...eff, activites: filteredActivites };
            }).filter(eff => eff.activites.length > 0);
          return { ...obj, effets: filteredEffets };
        }).filter(obj => obj.effets.length > 0);
        return { ...axe, objectifs: filteredObjectifs };
      }).filter(axe => axe.objectifs.length > 0);
    }
  },
  mounted() {
    this.initReport();
  },
  methods: {
    async initReport() {
        await this.fetchCurrentUser();
        this.fetchObjectifs();
        this.fetchSessionEnCours();
        this.fetchStructures();
    },
    async fetchCurrentUser() {
      try {
        const response = await axios.get('/api/user-info');
        this.currentUser = response.data;
        
        // Si l'utilisateur a un accès restreint, on force sa structure
        if (!this.canSelectAnyStructure && this.currentUser.structure_id) {
          this.selectedStructureId = this.currentUser.structure_id;
        }
      } catch (error) {
        console.error('Erreur info utilisateur:', error);
      }
    },
    async fetchStructures() {
      try {
        const response = await axios.get("/api/structures");
        this.structures = response.data;
      } catch (error) {
        console.error("Erreur structures:", error);
      }
    },
    async fetchSessionEnCours() {
      try {
        const response = await axios.get('/api/session-Ouvert');
        this.sessionsAnne = response.data?.annee || null;
      } catch (error) {
        console.error('Erreur session:', error);
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
        console.error("Erreur objectifs:", error);
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
        return [];
      }
    },
    async fetchActivites(effetId) {
      try {
        const response = await axios.get(`/api/activites-effet/${effetId}`);
        if (Array.isArray(response.data)) {
          return response.data.filter(act => 
            (act.etat_selection === 'Validé' || act.etat_slection === 'Validé') && 
            act.confirmation_presi === 1
          );
        }
        return [];
      } catch (error) {
        return [];
      }
    },
    formatNumber(value) {
      if (value === null || value === undefined || value === "") return "0";
      return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    },
    calculerTotalEffet(effet) {
      if (!effet.activites) return 0;
      return effet.activites.reduce((sum, act) => sum + Number(act.coute_t1 || 0), 0);
    },
    calculerMoyenneEffet(effet) {
      if (!effet.activites || effet.activites.length === 0) return 0;
      const sum = effet.activites.reduce((acc, act) => acc + (act.taux_t1 || 0), 0);
      return Math.round(sum / effet.activites.length);
    },
    calculerTotalObjectif(objectif) {
      if (!objectif.effets) return 0;
      return objectif.effets.reduce((sum, eff) => sum + this.calculerTotalEffet(eff), 0);
    },
    calculerMoyenneObjectif(objectif) {
      if (!objectif.effets || objectif.effets.length === 0) return 0;
      const count = objectif.effets.reduce((acc, eff) => acc + eff.activites.length, 0);
      if (count === 0) return 0;
      const sum = objectif.effets.reduce((acc, eff) => {
        return acc + eff.activites.reduce((s, act) => s + (act.taux_t1 || 0), 0);
      }, 0);
      return Math.round(sum / count);
    },
    calculerTotalAxe(axe) {
      if (!axe.objectifs) return 0;
      return axe.objectifs.reduce((sum, obj) => sum + this.calculerTotalObjectif(obj), 0);
    },
    calculerMoyenneAxe(axe) {
      if (!axe.objectifs || axe.objectifs.length === 0) return 0;
      const count = axe.objectifs.reduce((acc, obj) => {
        return acc + obj.effets.reduce((s, eff) => s + eff.activites.length, 0);
      }, 0);
      if (count === 0) return 0;
      const sum = axe.objectifs.reduce((acc, obj) => {
        return acc + obj.effets.reduce((s, eff) => {
          return s + eff.activites.reduce((ss, act) => ss + (act.taux_t1 || 0), 0);
        }, 0);
      }, 0);
      return Math.round(sum / count);
    },
    calculerSommeTrimestre() {
      return this.filteredAxes.reduce((sum, a) => sum + this.calculerTotalAxe(a), 0);
    },
    calculerPerformanceGlobale() {
      if (!this.filteredAxes.length) return 0;
      const sum = this.filteredAxes.reduce((sum, a) => sum + this.calculerMoyenneAxe(a), 0);
      return Math.round(sum / this.filteredAxes.length);
    },
    exportToExcel() {
      try {
        const data = [
            [`RAPPORT D'EXÉCUTION DU 1er TRIMESTRE - ${this.sessionsAnne}`],
            [],
            ["N°", "Activité", "Taux d'avancement T1 (%)", "Budget Décaissé T1 (FCFA)", "Partenaires", "Structure"]
        ];

        this.filteredAxes.forEach((axe, axeIdx) => {
            data.push([`AXE STRATÉGIQUE ${axeIdx + 1}: ${axe.libelle}`]);
            axe.objectifs.forEach((obj, idx) => {
                data.push([`OBJECTIF GLOBAL ${axeIdx + 1}.${idx + 1}: ${obj.libelle}`]);
                obj.effets.forEach((eff, effIdx) => {
                    data.push([`EFFET ${axeIdx + 1}.${idx + 1}.${effIdx + 1}: ${eff.libelle}`]);
                    eff.activites.forEach((act, actIdx) => {
                        data.push([
                            `${axeIdx + 1}.${idx + 1}.${effIdx + 1}.${actIdx + 1}`,
                            act.libelle,
                            `${act.taux_t1 || 0}%`,
                            act.coute_t1 || 0,
                            (act.partenaires_details || []).map(p => p.nom).join(', '),
                            act.structure_sigle || ""
                        ]);
                    });
                    data.push(["", "Sous-Total Effet", `${this.calculerMoyenneEffet(eff)}%`, this.calculerTotalEffet(eff), ""]);
                });
                data.push(["", "Total Objectif Global", `${this.calculerMoyenneObjectif(obj)}%`, this.calculerTotalObjectif(obj), ""]);
            });
        });

        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Rapport T1");
        XLSX.writeFile(wb, `Rapport_T1_${this.sessionsAnne}.xlsx`);
      } catch (error) {
        console.error("Export error:", error);
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
.scrollbar-thin {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f8fafc;
}
.scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
.scrollbar-thin::-webkit-scrollbar-track { background: #f8fafc; }
.scrollbar-thin::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
</style>
