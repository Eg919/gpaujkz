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
        <!-- Sélecteur de Structure Premium -->
        <div class="relative group min-w-[320px]">
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
            <i class="fas fa-filter text-[10px]"></i>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <button
          @click="exportToExcel"
          class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all flex items-center gap-2 group border border-blue-700 active:scale-95"
        >
          <i class="fa-solid fa-file-excel group-hover:rotate-12 transition-transform"></i> 
          <span class="hidden md:inline">Exporter Excel</span>
        </button>
        <button
          @click="exportToPDF"
          class="bg-rose-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-xl shadow-rose-100 hover:bg-rose-700 transition-all flex items-center gap-2 group border border-rose-700 active:scale-95"
        >
          <i class="fa-solid fa-file-pdf group-hover:rotate-12 transition-transform"></i> 
          <span class="hidden md:inline">Exporter PDF</span>
        </button>
      </div>
    </div>

    <div id="pdf-export-zone" class="pdf-export-zone flex-1 flex flex-col min-h-0 mt-4 bg-white rounded-2xl shadow-xl border border-slate-200 mx-auto w-full max-w-[99%]" style="font-family: 'Outfit', sans-serif;">
      <!-- En-tête officiel (UKZ) -->
      <!-- Header du Tableau Sobre (Sync avec Rapport) -->
      <div class="bg-white p-8 flex flex-col items-center border-b border-slate-100">
        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-2">IV. MATRICE DE PROGRAMMATION DES ACTIVITES {{ sessionsAnne }} DE L'UJKZ</h1>
        <div class="w-24 h-1 bg-amber-500 rounded-full mb-4"></div>
      </div>

      <div class="flex-1 overflow-auto scrollbar-thin print:overflow-visible">
        <table class="w-full border-collapse">
          <thead class="sticky top-0 z-30 bg-white shadow-sm border-b border-slate-200 text-center">
            <!-- Titre Cadre Programmatique -->
            <tr class="bg-[#fee2e2]/30 text-slate-900 font-bold border-b border-slate-200">
              <th colspan="15" class="py-2.5 text-[11px] uppercase tracking-widest text-center italic">
                Cadre programmatique du programme d'activités {{ sessionsAnne }} révisé de l'UJKZ
              </th>
            </tr>
            <!-- Header Niveau 1 -->
            <tr class="bg-[#fef3c7]/40 text-slate-800 font-black uppercase text-[9px] border-b border-slate-200">
              <th rowspan="2" class="px-2 border-r border-slate-200 w-[40px]">N°</th>
              <th rowspan="2" class="px-4 border-r border-slate-200 text-left min-w-[280px]">Objectifs/Activités</th>
              <th colspan="4" class="py-2 border-r border-slate-200">Indicateurs</th>
              <th colspan="2" class="py-2 border-r border-slate-200">Coûts estimatifs</th>
              <th colspan="4" class="py-2 border-r border-slate-200">Période de réalisation</th>
              <th rowspan="2" class="px-2 border-r border-slate-200 min-w-[100px]">Struct resp</th>
              <th colspan="2" class="py-2 border-slate-200">Source de financement</th>
            </tr>
            <tr class="bg-[#fef3c7]/20 text-slate-600 font-bold uppercase text-[8px] border-b border-slate-200">
              <!-- Indicateurs -->
              <th class="py-2 border-r border-slate-200 px-2 min-w-[150px]">Indicateur</th>
              <th class="py-2 border-r border-slate-200 px-1 w-[100px]">Unité de l'indicateur</th>
              <th class="py-2 border-r border-slate-200 px-1 italic w-[80px]">Référence</th>
              <th class="py-2 border-r border-slate-200 px-1 w-[80px]">Cible</th>
              <!-- Coûts -->
              <th class="py-2 border-r border-slate-200 px-2">Etat</th>
              <th class="py-2 border-r border-slate-200 px-2">Partenaire</th>
              <!-- Période -->
              <th class="py-2 border-r border-slate-200 w-8">T1</th>
              <th class="py-2 border-r border-slate-200 w-8">T2</th>
              <th class="py-2 border-r border-slate-200 w-8">T3</th>
              <th class="py-2 border-r border-slate-200 w-8">T4</th>
              <!-- Financement -->
              <th class="py-2 border-r border-slate-200 px-2">Etat</th>
              <th class="py-2 px-2">Partenaires</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <template v-if="filteredAxes.length > 0">
              <template v-for="(axe, indexAxe) in filteredAxes" :key="axe.id">
                <!-- Ligne Axe Stratégique -->
                <tr class="bg-slate-100 border-l-4 border-slate-500 transition-all">
                  <td colspan="15" class="py-4 px-6 font-black text-slate-800 border-b border-slate-200 text-[14px] uppercase tracking-widest">
                    <div class="flex items-center gap-3">
                      <span class="w-auto px-3 h-8 rounded-lg bg-slate-600 text-white flex items-center justify-center text-[12px] shadow-sm font-black">AXE {{ indexAxe + 1 }}</span>
                      Axe Stratégique {{ indexAxe + 1 }} : {{ axe.libelle }}
                    </div>
                  </td>
                </tr>
                <template v-for="(objectif, index) in axe.objectifs" :key="objectif.id">
                  <!-- Ligne Objectif stratégique -->
                  <tr class="bg-[#dcfce7] border-l-4 border-emerald-600 transition-all">
                    <td colspan="15" class="py-4 px-6 font-black text-slate-900 border-b border-slate-100 text-[13px] uppercase tracking-tight">
                      <div class="flex items-center gap-3 ml-4">
                        <span class="w-8 h-8 rounded-lg bg-emerald-700 text-white flex items-center justify-center text-[11px] shadow-sm font-black">OBJ {{ indexAxe + 1 }}.{{ index + 1 }}</span>
                        Objectif Stratégique {{ indexAxe + 1 }}.{{ index + 1 }} : {{ objectif.libelle }}
                      </div>
                    </td>
                  </tr>
                <template v-for="(effet, indexEffet) in objectif.effets" :key="effet.id">
                  <!-- Ligne Objectif opérationnel -->
                  <tr class="bg-[#f0fdf4] border-l-4 border-slate-200">
                    <td colspan="15" class="py-3 px-10 font-bold text-slate-600 border-b border-slate-100 text-[11px] uppercase tracking-wide">
                      <div class="flex items-center gap-2.5 text-emerald-800/70 ml-8">
                        {{ `${indexAxe + 1}.${index + 1}.${indexEffet + 1}.` }} {{ effet.libelle }}
                      </div>
                    </td>
                  </tr>
                  <template v-for="(activite, indexActivite) in effet.activites" :key="activite.id">
                     <tr class="hover:bg-slate-50/50 transition-colors group">
                      <td class="py-4 px-3 border-r border-slate-50 border-b text-center text-[10px] font-bold text-slate-400 tabular-nums">
                        {{ `${indexAxe + 1}.${index + 1}.${indexEffet + 1}.${indexActivite + 1}` }}
                      </td>
                    <td class="py-4 px-4 border-r border-slate-50 border-b">
                      <p class="text-[12px] font-bold text-slate-800 leading-tight">{{ activite.libelle || '' }}</p>
                    </td>
                    <!-- Colonnes Indicateurs -->
                    <td class="py-2 px-2 border-r border-slate-50 border-b text-[10px] text-slate-600 leading-relaxed italic">
                      <div v-for="ind in activite.indicateurs" :key="ind.id" class="border-b last:border-0 border-slate-100 py-1">
                        {{ ind.libelle_indicateur || ind.indicateur || 'N/A' }}
                      </div>
                    </td>
                    <td class="py-2 px-2 border-r border-slate-50 border-b text-[10px] text-slate-600 text-center">
                      <div v-for="ind in activite.indicateurs" :key="ind.id" class="border-b last:border-0 border-slate-100 py-1">
                        {{ ind.unite_indicateur || ind.unite || '-' }}
                      </div>
                    </td>
                    <td class="py-2 px-2 border-r border-slate-50 border-b text-[10px] text-slate-600 italic text-center">
                      <div v-for="ind in activite.indicateurs" :key="ind.id" class="border-b last:border-0 border-slate-100 py-1">
                        {{ ind.reference_indicateur || ind.reference || '-' }}
                      </div>
                    </td>
                    <td class="py-2 px-2 border-r border-slate-50 border-b text-[10px] text-slate-600 font-bold text-center">
                      <div v-for="ind in activite.indicateurs" :key="ind.id" class="border-b last:border-0 border-slate-100 py-1">
                        {{ ind.cible_indicateur || ind.cible || '-' }}
                      </div>
                    </td>
                    <!-- Coûts estimatifs -->
                    <td class="py-4 px-2 border-r border-slate-50 border-b text-center text-[11px] font-bold text-slate-700 tabular-nums">
                      {{ formatNumber(activite.finance_etat) }}
                    </td>
                    <td class="py-4 px-2 border-r border-slate-50 border-b text-center text-[11px] font-bold text-slate-700 tabular-nums">
                      {{ formatNumber(activite.finance_partenaire) }}
                    </td>
                    <!-- Période -->
                    <td v-for="trimestre in [1, 2, 3, 4]" :key="trimestre" class="py-4 border-r border-slate-50 border-b text-center">
                      <div v-if="activite[`trimestre_${trimestre}`]" class="flex flex-col items-center">
                         <div class="text-[12px] font-bold text-slate-500">
                            X
                         </div>
                      </div>
                    </td>
                    <!-- Struct Resp -->
                    <td class="py-4 px-2 border-r border-slate-50 border-b text-center">
                      <div class="flex flex-col gap-1 items-center">
                        <span class="text-[10px] font-black uppercase text-slate-700">
                          {{ activite.structure_sigle || 'UKZ' }}
                        </span>
                        <template v-if="activite.structures_partenaires && activite.structures_partenaires.length > 0">
                          <span v-for="partenaire in activite.structures_partenaires" :key="partenaire.id" class="text-[9px] font-bold text-slate-500 uppercase">
                            / {{ partenaire.sigle }}
                          </span>
                        </template>
                      </div>
                    </td>
                    <!-- Source de financement -->
                    <td class="py-4 px-2 border-r border-slate-50 border-b text-center text-[11px] font-bold text-slate-400 tabular-nums">
                      {{ formatNumber(activite.finance_etat) }}
                    </td>
                    <td class="py-4 px-2 border-b text-center">
                       <div class="flex flex-col gap-0.5">
                         <span v-for="part in activite.partenaires_details" :key="part.nom" class="text-[8px] font-bold text-slate-500 truncate max-w-[80px]">
                           {{ part.nom }} ({{ formatNumber(part.montant) }})
                         </span>
                       </div>
                    </td>
                   </tr>
                </template>
                  <!-- Ligne Sous-Total Effet (Nouveau) -->
                  <tr class="bg-indigo-50/10 border-t border-indigo-100">
                    <td colspan="2" class="py-2.5 px-10 text-[9px] font-black text-indigo-400 uppercase tracking-[0.2em] text-right">
                      Sous-Total ({{ indexAxe + 1 }}.{{ index + 1 }}.{{ indexEffet + 1 }}) :
                    </td>
                    <td colspan="4"></td>
                    <td class="py-2.5 px-2 text-center text-[11px] font-black text-indigo-700 tabular-nums border-l border-indigo-50">
                      {{ formatNumber(calculerTotalEffetEtat(effet)) }}
                    </td>
                    <td class="py-2.5 px-2 text-center text-[11px] font-black text-indigo-700 tabular-nums border-l border-indigo-50">
                      {{ formatNumber(calculerTotalEffetPartenaire(effet)) }}
                    </td>
                    <td colspan="7" class="bg-indigo-50/5"></td>
                  </tr>
                </template>
                <!-- Ligne Total Objectif Stratégique (Nouveau) -->
                <tr class="bg-indigo-600/5 border-b-2 border-indigo-200">
                  <td colspan="2" class="py-3 px-6 text-[10px] font-black text-indigo-600 uppercase tracking-widest text-right">
                    TOTAL OBJECTIF STRATÉGIQUE ({{ indexAxe + 1 }}.{{ index + 1 }}) :
                  </td>
                  <td colspan="4"></td>
                  <td class="py-3 px-2 text-center text-[12px] font-black text-indigo-900 tabular-nums border-l border-indigo-200">
                    {{ formatNumber(calculerTotalObjectifEtat(objectif)) }}
                  </td>
                  <td class="py-3 px-2 text-center text-[12px] font-black text-indigo-900 tabular-nums border-l border-indigo-200">
                    {{ formatNumber(calculerTotalObjectifPartenaire(objectif)) }}
                  </td>
                  <td colspan="7" class="bg-indigo-600/5"></td>
                </tr>
              </template>
              
              <!-- Ligne Total Axe Stratégique -->
              <tr class="bg-slate-100 border-b-2 border-slate-300">
                <td colspan="2" class="py-3 px-6 text-[11px] font-black text-slate-800 uppercase tracking-widest text-right">
                  TOTAL AXE STRATÉGIQUE ({{ indexAxe + 1 }}) :
                </td>
                <td colspan="4"></td>
                <td class="py-3 px-2 text-center text-[13px] font-black text-slate-900 tabular-nums border-l border-slate-300">
                  {{ formatNumber(calculerTotalAxeEtat(axe)) }}
                </td>
                <td class="py-3 px-2 text-center text-[13px] font-black text-slate-900 tabular-nums border-l border-slate-300">
                  {{ formatNumber(calculerTotalAxePartenaire(axe)) }}
                </td>
                <td colspan="7" class="bg-slate-100"></td>
              </tr>
            </template>
          </template>

          <template v-else>
            <tr>
              <td colspan="15" class="py-20 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300">
                    <i class="fas fa-folder-open text-2xl"></i>
                  </div>
                  <p class="text-slate-500 font-medium font-serif italic text-lg">Aucune donnée validée trouvée pour cette session & structure.</p>
                </div>
              </td>
            </tr>
          </template>
            <!-- Pied de Tableau Récapitulatif Global -->
            <tr class="bg-white border-t-2 border-slate-900">
                <td colspan="15" class="py-10 px-10">
                    <div class="flex justify-between items-center max-w-5xl ml-auto">
                        <div class="flex items-center gap-8 group">
                            <div class="text-right whitespace-nowrap">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Budget Global (Etat)</span>
                                <p class="text-4xl font-black text-slate-900 tabular-nums tracking-tighter">{{ formatNumber(calculerSommeTotalEtat()) }}</p>
                            </div>
                            <div class="h-10 w-px bg-slate-100"></div>
                            <div class="text-right whitespace-nowrap">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Budget Global (Partenaires)</span>
                                <p class="text-4xl font-black text-indigo-600 tabular-nums tracking-tighter">{{ formatNumber(calculerSommeTotalPartenaire()) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-8 group bg-slate-50 px-8 py-5 rounded-2xl border border-slate-200">
                            <div class="text-right">
                                <span class="text-[10px] font-black text-slate-400 uppercase">Total Activités</span>
                                <p class="text-3xl font-black text-slate-800">{{ totalActivites }}</p>
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
  name: "GestionMatriceDactivite",
  components: {
    SignatureModal,
    SignatureBlock
  },
  props: {
    standalone: {
      type: Boolean,
      default: true
    },
    targetSessionId: {
      type: [Number, String],
      default: null
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
    },
    totalActivites() {
      return this.filteredAxes.reduce((sum, axe) => {
        return sum + axe.objectifs.reduce((s, obj) => {
          return s + obj.effets.reduce((ss, eff) => ss + eff.activites.length, 0);
        }, 0);
      }, 0);
    }
  },
  watch: {
    targetSessionId: {
      handler(newVal) {
        if (newVal) {
          this.initMatrix();
        }
      },
      immediate: false
    }
  },
  mounted() {
    this.initMatrix();
  },
  methods: {
    async initMatrix() {
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
        if (this.targetSessionId) {
          const response = await axios.get(`/api/sessions-activites`);
          const target = response.data.find(s => s.id == this.targetSessionId);
          this.sessionsAnne = target ? target.annee : null;
        } else {
          const response = await axios.get('/api/session-Ouvert');
          this.sessionsAnne = response.data?.annee || null;
        }
      } catch (error) {
        console.error('Erreur session:', error);
      }
    },
    async fetchObjectifs() {
      try {
        const params = this.targetSessionId ? { session_id: this.targetSessionId } : {};
        const response = await axios.get("/api/objectifs-strategiques-Ouvert-activites", { params });
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
        const params = this.targetSessionId ? { session_id: this.targetSessionId } : {};
        const response = await axios.get(`/api/effets-activites/${objectifId}`, { params });
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
        const params = this.targetSessionId ? { session_id: this.targetSessionId } : {};
        const response = await axios.get(`/api/activites-effet/${effetId}`, { params });
        if (Array.isArray(response.data)) {
          return response.data.filter(act => 
            (act.etat_selection === 'Validé' || act.etat_slection === 'Validé') && 
            act.confirmation_presi == 1
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
    calculerTotalActiviteBudget(act) {
      const etat = Number(act.finance_etat || 0);
      const partenaire = Number(act.finance_partenaire || 0);
      return etat + partenaire;
    },
    calculerTotalEffetBudget(effet) {
      if (!effet.activites) return 0;
      return effet.activites.reduce((sum, act) => sum + this.calculerTotalActiviteBudget(act), 0);
    },
    calculerTotalEffetEtat(effet) {
      if (!effet.activites) return 0;
      return effet.activites.reduce((sum, act) => sum + Number(act.finance_etat || 0), 0);
    },
    calculerTotalEffetPartenaire(effet) {
      if (!effet.activites) return 0;
      return effet.activites.reduce((sum, act) => sum + Number(act.finance_partenaire || 0), 0);
    },
    calculerTotalObjectifBudget(obj) {
      if (!obj.effets) return 0;
      return obj.effets.reduce((sum, eff) => sum + this.calculerTotalEffetBudget(eff), 0);
    },
    calculerTotalObjectifEtat(obj) {
      if (!obj.effets) return 0;
      return obj.effets.reduce((sum, eff) => sum + this.calculerTotalEffetEtat(eff), 0);
    },
    calculerTotalObjectifPartenaire(obj) {
      if (!obj.effets) return 0;
      return obj.effets.reduce((sum, eff) => sum + this.calculerTotalEffetPartenaire(eff), 0);
    },
    calculerTotalAxeBudget(axe) {
      if (!axe.objectifs) return 0;
      return axe.objectifs.reduce((sum, obj) => sum + this.calculerTotalObjectifBudget(obj), 0);
    },
    calculerTotalAxeEtat(axe) {
      if (!axe.objectifs) return 0;
      return axe.objectifs.reduce((sum, obj) => sum + this.calculerTotalObjectifEtat(obj), 0);
    },
    calculerTotalAxePartenaire(axe) {
      if (!axe.objectifs) return 0;
      return axe.objectifs.reduce((sum, obj) => sum + this.calculerTotalObjectifPartenaire(obj), 0);
    },
    calculerSommeTotalBudget() {
      if (!this.filteredAxes) return 0;
      return this.filteredAxes.reduce((sum, axe) => sum + this.calculerTotalAxeBudget(axe), 0);
    },
    calculerSommeTotalEtat() {
      if (!this.filteredAxes) return 0;
      return this.filteredAxes.reduce((sum, axe) => sum + this.calculerTotalAxeEtat(axe), 0);
    },
    calculerSommeTotalPartenaire() {
      if (!this.filteredAxes) return 0;
      return this.filteredAxes.reduce((sum, axe) => sum + this.calculerTotalAxePartenaire(axe), 0);
    },
    async exportToExcel() {
       try {
        const data = [
            [`MATRICE DE PROGRAMMATION DES ACTIVITÉS - SESSION ${this.sessionsAnne}`],
            [`Généré le ${new Date().toLocaleDateString('fr-FR')}`],
            [],
            ["N°", "Objectifs/Activités", "Indicateur", "Unité", "Référence", "Cible", "Coût Etat", "Coût Partenaire", "T1", "T2", "T3", "T4", "Structure", "Fin. Etat", "Fin. Partenaires"]
        ];

        this.filteredAxes.forEach((axe, axeIdx) => {
            data.push([`AXE ${axeIdx + 1}`, axe.libelle, "", "", "", "", this.calculerTotalAxeEtat(axe), this.calculerTotalAxePartenaire(axe)]);
            axe.objectifs.forEach((obj, idx) => {
                data.push([`OBJ ${axeIdx + 1}.${idx + 1}`, obj.libelle, "", "", "", "", this.calculerTotalObjectifEtat(obj), this.calculerTotalObjectifPartenaire(obj)]);
                obj.effets.forEach((eff, effIdx) => {
                    data.push([`${axeIdx + 1}.${idx + 1}.${effIdx + 1}`, eff.libelle, "", "", "", "", this.calculerTotalEffetEtat(eff), this.calculerTotalEffetPartenaire(eff)]);
                    eff.activites.forEach((act, actIdx) => {
                        const firstInd = (act.indicateurs || [])[0] || {};
                        data.push([
                            `${axeIdx + 1}.${idx + 1}.${effIdx + 1}.${actIdx + 1}`,
                            act.libelle,
                            firstInd.libelle_indicateur || firstInd.indicateur || "",
                            firstInd.unite_indicateur || firstInd.unite || "",
                            firstInd.reference_indicateur || firstInd.reference || "",
                            firstInd.cible_indicateur || firstInd.cible || "",
                            act.finance_etat,
                            act.finance_partenaire,
                            act.trimestre_1 ? "X" : "",
                            act.trimestre_2 ? "X" : "",
                            act.trimestre_3 ? "X" : "",
                            act.trimestre_4 ? "X" : "",
                            act.structure_sigle || "",
                            act.finance_etat,
                            (act.partenaires_details || []).map(p => `${p.nom} (${p.montant})`).join(', ')
                        ]);

                        // Si plusieurs indicateurs, ajouter des lignes supplémentaires
                        if ((act.indicateurs || []).length > 1) {
                          act.indicateurs.slice(1).forEach(ind => {
                            data.push([
                              "", "", 
                              ind.libelle_indicateur || ind.indicateur || "",
                              ind.unite_indicateur || ind.unite || "",
                              ind.reference_indicateur || ind.reference || "",
                              ind.cible_indicateur || ind.cible || "",
                              "", "", "", "", "", "", "", "", ""
                            ]);
                          });
                        }
                    });
                });
            });
        });

        data.push([]);
        data.push(["", "TOTAL GÉNÉRAL (ETAT)", "", "", "", "", this.calculerSommeTotalEtat()]);
        data.push(["", "TOTAL GÉNÉRAL (PARTENAIRES)", "", "", "", "", this.calculerSommeTotalPartenaire()]);

        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Matrice");
        XLSX.writeFile(wb, `Matrice_Activites_${this.sessionsAnne}.xlsx`);
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
