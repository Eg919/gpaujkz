<template>
  <div class="gestion-historiques bg-gray-50/50 absolute top-14 bottom-0 left-0 right-0 overflow-hidden flex flex-col print:relative print:h-auto print:overflow-visible" :class="{ '': standalone }">
    <!-- Modal de Signature -->
    <SignatureModal 
      :show="showSignatureModal" 
      @close="showSignatureModal = false" 
      @confirm="triggerPDFExport" 
    />
    <!-- Header avec bouton d'export et Filtres -->
    <div class="print:hidden flex justify-between items-center w-full max-w-[99%] mx-auto mb-4 px-4 sticky z-20 bg-white/80 backdrop-blur-md py-4 rounded-xl border border-slate-100 shadow-sm transition-all hover:bg-white text-emerald-900" :class="standalone ? 'top-12' : 'top-0'">
      <div class="flex items-center gap-4 flex-wrap">
        <!-- Sélecteur de Structure Premium -->
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

        <!-- Sélecteur de Trimestre -->
        <div class="relative group min-w-[180px]">
          <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-black text-indigo-500 uppercase tracking-widest z-10 transition-all group-focus-within:text-indigo-600">Filtrer par Trimestre</label>
          <select
            v-model="selectedTrimestre"
            @change="redirectToDedicatedTrimestriel"
            class="w-full bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50/50 transition-all appearance-none cursor-pointer"
          >
            <option value="">Tous les trimestres</option>
            <option value="1">T1</option>
            <option value="2">T2</option>
            <option value="3">T3</option>
            <option value="4">T4</option>
          </select>
          <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
            <i class="fas fa-calendar-alt text-[10px]"></i>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <button
          @click="exportToExcel"
          class="bg-blue-600 text-white px-5 py-2.5 rounded-xl hover:bg-blue-700 focus:outline-none text-sm font-bold shadow-lg shadow-blue-100 transform transition-all active:scale-95 flex items-center gap-2 border border-blue-700"
        >
          <i class="fa-solid fa-file-excel"></i> 
          <span class="hidden md:inline">Exporter Excel</span>
        </button>
        <button
          @click="exportToPDF"
           class="bg-rose-600 text-white px-5 py-2.5 rounded-xl hover:bg-rose-700 focus:outline-none text-sm font-bold shadow-lg shadow-rose-100 transform transition-all active:scale-95 flex items-center gap-2 border border-rose-700"
        >
          <i class="fa-solid fa-file-pdf"></i> 
          <span class="hidden md:inline">Exporter PDF</span>
        </button>
      </div>
    </div>

    <!-- ========== ZONE D'EXPORT PDF ========== -->
    <div id="pdf-export-zone" class="pdf-export-zone flex-1 flex flex-col min-h-0 mt-4 bg-white rounded-2xl shadow-xl border border-slate-200 mx-auto w-full max-w-[99%]" style="font-family: 'Times New Roman', serif;">
      <!-- En-tête officiel (UKZ) -->
      
      <!-- Titre du rapport -->
      <div class="bg-white px-8 py-5 flex justify-between items-end" style="border-bottom: 1.5px solid #1e293b;">
        <div>
          <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase" style="letter-spacing: 0.05em;">Rapport Global d'Exécution</h2>
          <p class="text-slate-400 text-[11px] italic mt-0.5">Synthèse consolidée du programme d'activités</p>
        </div>
        <div class="text-right">
          <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Session / Année</p>
          <p class="text-2xl font-bold text-slate-900">{{ sessionsAnne }}</p>
        </div>
      </div>

      <!-- Tableau principal -->
      <div class="flex-1 overflow-auto scrollbar-thin bg-white print:overflow-visible">
        <table class="w-full bg-white" style="border-collapse: collapse; font-size: 11px;">
          
          <!-- EN-TÊTE DU TABLEAU -->
          <thead>
            <tr style="background:#ffffff; border-top: 2.5px solid #1e293b;">
              <th rowspan="1" style="padding: 6px 10px; text-align:center; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap; color:#1e293b; vertical-align:middle;">N°</th>
              <th rowspan="1" style="padding: 6px 10px; text-align:left; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.05em; min-width:220px; color:#1e293b; vertical-align:middle;">OBJECTIFS / ACTIVITÉS / TÂCHES</th>
              <th rowspan="1" style="padding: 6px 10px; text-align:center; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.05em; color:#1e293b; vertical-align:middle;">CIBLE</th>
              <th rowspan="1" style="padding: 6px 10px; text-align:left; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.05em; min-width:150px; color:#1e293b; vertical-align:middle;">DESCRIPTION DES TÂCHES</th>
              
              <!-- Ce titre a une bordure en bas pour se séparer de T1, T2, T3, T4 -->
              <th colspan="4" rowspan="1" style="padding: 6px 10px; border-bottom: 1px solid #1e293b; text-align:center; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.05em; color:#1e293b; vertical-align:middle;">PROGRESSION TRIMESTRIELLE</th>
              
              <th rowspan="1" style="padding: 6px 10px; text-align:center; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.05em; color:#1e293b; vertical-align:middle;">EXÉC. (%)</th>
              <th rowspan="1" style="padding: 6px 10px; text-align:center; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.05em; color:#1e293b; vertical-align:middle;">DÉPENSES (FCFA)</th>
              <th rowspan="1" style="padding: 6px 10px; text-align:center; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.05em; color:#1e293b; vertical-align:middle;">PARTENAIRES</th>
              <th rowspan="1" style="padding: 6px 10px; text-align:center; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.05em; color:#1e293b; vertical-align:middle;">STRUCTURE</th>
              <th rowspan="1" style="padding: 6px 10px; text-align:left; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.05em; color:#1e293b; vertical-align:middle;">OBSERVATIONS</th>
            </tr>
            
            <tr style="background:#ffffff;">
              <th style="padding: 0 10px 12px 10px; border-bottom: 1.5px solid #1e293b;"></th>
              <th style="padding: 0 10px 12px 10px; border-bottom: 1.5px solid #1e293b;"></th>
              <th style="padding: 0 10px 12px 10px; border-bottom: 1.5px solid #1e293b;"></th>
              <th style="padding: 0 10px 12px 10px; border-bottom: 1.5px solid #1e293b;"></th>
              
              <!-- T1, T2, T3, T4 -->
              <th style="padding: 5px 8px; border-bottom: 1.5px solid #1e293b; text-align:center; font-size:9px; font-weight:900; color:#1e293b;">T1</th>
              <th style="padding: 5px 8px; border-bottom: 1.5px solid #1e293b; text-align:center; font-size:9px; font-weight:900; color:#1e293b;">T2</th>
              <th style="padding: 5px 8px; border-bottom: 1.5px solid #1e293b; text-align:center; font-size:9px; font-weight:900; color:#1e293b;">T3</th>
              <th style="padding: 5px 8px; border-bottom: 1.5px solid #1e293b; text-align:center; font-size:9px; font-weight:900; color:#1e293b;">T4</th>
              
              <th style="padding: 0 10px 12px 10px; border-bottom: 1.5px solid #1e293b;"></th>
              <th style="padding: 0 10px 12px 10px; border-bottom: 1.5px solid #1e293b;"></th>
              <th style="padding: 0 10px 12px 10px; border-bottom: 1.5px solid #1e293b;"></th>
              <th style="padding: 0 10px 12px 10px; border-bottom: 1.5px solid #1e293b;"></th>
              <th style="padding: 0 10px 12px 10px; border-bottom: 1.5px solid #1e293b;"></th>
            </tr>
          </thead>

          <!-- CORPS DU TABLEAU -->
          <tbody>
            <template v-for="(axe, indexAxe) in filteredAxes" :key="axe.id">
              
              <!-- Ligne Axe Stratégique -->
              <tr style="background:#f1f5f9; border-bottom: 1px solid #cbd5e1;">
                <td colspan="13" style="padding: 12px 16px; font-weight:900; color:#1e293b; font-size:12px; text-transform:uppercase; letter-spacing:0.05em; border-left: 4px solid #64748b;">
                  <span style="display:inline-flex; align-items:center; gap:10px;">
                    <span style="display:inline-flex; align-items:center; justify-content:center; padding: 4px 8px; height:22px; background:#475569; color:#f8fafc; font-size:10px; font-weight:900; border-radius:4px; flex-shrink:0;">AXE {{ indexAxe + 1 }}</span>
                    {{ axe.libelle }}
                  </span>
                </td>
              </tr>

              <template v-for="(objectif, index) in axe.objectifs" :key="objectif.id">
                
                <!-- Ligne Objectif Stratégique -->
                <tr style="background:#eef2ff; border-bottom: 1px solid #dde4f5;">
                  <td colspan="13" style="padding: 10px 16px; font-weight:900; color:#1e3a8a; font-size:11px; text-transform:uppercase; letter-spacing:0.04em;">
                    <span style="display:inline-flex; align-items:center; gap:10px; margin-left:15px;">
                      <span style="display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; background:#3b82f6; color:white; font-size:9px; font-weight:900; border-radius:3px; flex-shrink:0;">{{ indexAxe + 1 }}.{{ index + 1 }}</span>
                      Objectif Stratégique : {{ objectif.libelle }}
                    </span>
                  </td>
                </tr>

                <template v-for="(effet, indexEffet) in objectif.effets" :key="effet.id">
                  <!-- Ligne Objectif Opérationnel -->
                  <tr style="background:#fafafa; border-bottom: 1px solid #f0f0f0;">
                    <td colspan="13" style="padding: 6px 28px; font-weight:600; color:#64748b; font-size:10px; text-transform:uppercase; letter-spacing:0.02em;">
                      <span style="margin-left:25px; color:#c4cdda; margin-right:6px;">›</span>
                      <span style="color:#94a3b8; margin-right:4px;">Obj. Opérationnel {{ `${indexAxe + 1}.${index + 1}.${indexEffet + 1}` }} :</span>
                      <span style="color:#475569;">{{ effet.libelle }}</span>
                    </td>
                  </tr>

                  <!-- Lignes Activités -->
                  <template v-for="(activite, indexActivite) in effet.activites" :key="activite.id">
                    <tr style="border-bottom: 1px solid #f5f5f5; background:#ffffff;">
                      <!-- N° -->
                      <td style="padding: 12px 10px; text-align:center; color:#b0bac7; font-size:10px; font-weight:600; vertical-align:top; white-space:nowrap;">
                        {{ `${indexAxe + 1}.${index + 1}.${indexEffet + 1}.${indexActivite + 1}` }}
                      </td>
                      <!-- Libellé activité -->
                      <td style="padding: 12px 10px; font-weight:700; color:#1e293b; font-size:11px; vertical-align:top; line-height:1.4;">
                        {{ activite.libelle || '' }}
                      </td>
                      <!-- Cible -->
                      <td style="padding: 12px 10px; text-align:center; color:#64748b; font-size:10px; vertical-align:top;">
                        <div v-for="indicateur in activite.indicateurs" :key="indicateur.id">{{ indicateur.cible || '-' }}</div>
                      </td>
                      <!-- Description tâches -->
                      <td style="padding: 12px 10px; color:#475569; font-size:10px; vertical-align:top; line-height:1.5;">
                        <div v-for="tache in activite.taches" :key="tache.id" style="margin-bottom:2px;">
                          <span style="color:#c4cdda; margin-right:4px;">•</span>{{ tache.libelle }}
                        </div>
                      </td>
                      <!-- Trimestres -->
                      <td v-for="trimestre in [1, 2, 3, 4]" :key="trimestre" 
                          style="padding: 12px 6px; text-align:center; vertical-align:middle;">
                        <span v-if="activite[`trimestre_${trimestre}`]" style="font-size:10px; font-weight:700; color:#1e293b;">
                          X
                        </span>
                        <span v-else style="color:#e5e7eb; font-size:10px;">—</span>
                      </td>
                      <!-- Exéc. % -->
                      <td style="padding: 12px 10px; text-align:center; font-weight:900; color:#1e293b; font-size:12px; vertical-align:middle;">
                        {{ calculerExecutionGlobale(activite) }}%
                      </td>
                      <!-- Dépenses -->
                      <td style="padding: 12px 10px; text-align:right; font-weight:600; color:#1e293b; font-size:10px; vertical-align:middle; font-variant-numeric:tabular-nums;">
                        {{ formatNumber(calculerSommeActualActivite(activite)) }}
                      </td>
                      <!-- Partenaires -->
                      <td style="padding: 12px 10px; text-align:center; vertical-align:middle;">
                        <template v-if="activite.partenaires_details && activite.partenaires_details.length">
                          <div v-for="part in activite.partenaires_details" :key="part.nom"
                               style="font-size:9px; font-weight:700; color:#334155; text-transform:uppercase;">
                            {{ part.nom }}
                          </div>
                        </template>
                        <span v-else style="color:#d4d9e0; font-size:9px;">—</span>
                      </td>
                      <!-- Structure -->
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
                      <!-- Observations -->
                      <td style="padding: 12px 10px; color:#94a3b8; font-size:10px; font-style:italic; vertical-align:top; line-height:1.4;">
                        {{ activite.observation || '—' }}
                      </td>
                    </tr>
                  </template>

                  <!-- Ligne Sous-Total Opérationnel -->
                  <tr style="background:#f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <td colspan="9" style="padding: 8px 16px; text-align:right; font-size:9px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.06em;">
                      SOUS-TOTAL OPÉRATIONNEL {{ `${indexAxe + 1}.${index + 1}.${indexEffet + 1}` }} :
                    </td>
                    <td style="padding: 8px 10px; text-align:center; font-weight:800; font-size:11px; color:#1e293b;">
                      {{ calculerTauxMoyenEffet(effet) }}%
                    </td>
                    <td style="padding: 6px 10px; text-align:right; font-weight:700; font-size:10px; color:#1e293b;">
                      {{ formatNumber(calculerSommeActualEffet(effet)) }}
                    </td>
                    <td colspan="3"></td>
                  </tr>
                </template>

                <!-- Ligne Total Objectif Stratégique -->
                <tr style="background:#f1f5f9; border-bottom: 1.5px solid #cbd5e1;">
                  <td colspan="9" style="padding: 10px 16px; text-align:right; font-size:10px; font-weight:900; color:#475569; text-transform:uppercase; letter-spacing:0.08em;">
                    TOTAL OBJECTIF STRATÉGIQUE {{ `${indexAxe + 1}.${index + 1}` }} :
                  </td>
                  <td style="padding: 8px 10px; text-align:center; font-weight:900; font-size:13px; color:#1e293b;">
                    {{ calculerTauxMoyenObjectif(objectif) }}%
                  </td>
                  <td style="padding: 8px 10px; text-align:right; font-weight:800; font-size:11px; color:#1e293b;">
                    {{ formatNumber(calculerSommeFinancesObjectif(objectif)) }}
                  </td>
                  <td colspan="3"></td>
                </tr>
              </template>
              
              <!-- Ligne Total Axe Stratégique -->
              <tr style="background:#e2e8f0; border-bottom: 2px solid #94a3b8;">
                <td colspan="9" style="padding: 12px 16px; text-align:right; font-size:11px; font-weight:900; color:#0f172a; text-transform:uppercase; letter-spacing:0.08em;">
                  TOTAL AXE STRATÉGIQUE {{ indexAxe + 1 }} :
                </td>
                <td style="padding: 10px 10px; text-align:center; font-weight:900; font-size:14px; color:#0f172a;">
                  {{ calculerTauxMoyenAxe(axe) }}%
                </td>
                <td style="padding: 10px 10px; text-align:right; font-weight:900; font-size:13px; color:#0f172a;">
                  {{ formatNumber(calculerSommeFinancesAxe(axe)) }}
                </td>
                <td colspan="3"></td>
              </tr>
            </template>
          </tbody>

        </table>

        <!-- RÉCAPITULATIF GLOBAL (hors tableau pour éviter la répétition par page PDF) -->
        <div style="background:#1e293b; border-top:2px solid #1e293b; padding:16px 32px; margin-top:0; break-inside:avoid; page-break-inside:avoid;">
          <div style="display:flex; justify-content:space-between; align-items:center; max-width:700px; margin-left:auto;">
            <!-- Dépenses -->
            <div style="display:flex; align-items:center; gap:14px;">
              <div style="text-align:right;">
                <p style="font-size:9px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 2px;">Dépenses Réalisées</p>
                <p style="font-size:9px; color:#64748b; font-style:italic; margin:0;">Consolidation annuelle</p>
              </div>
              <div style="width:1px; height:32px; background:#334155;"></div>
              <div style="font-size:22px; font-weight:900; color:#f1f5f9; font-variant-numeric:tabular-nums; letter-spacing:-0.02em;">
                {{ formatNumber(calculerSommeActual()) }} <span style="font-size:10px; font-weight:600; color:#64748b;">FCFA</span>
              </div>
            </div>
            <!-- Performance -->
            <div style="display:flex; align-items:center; gap:14px;">
              <div style="text-align:right;">
                <p style="font-size:9px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 2px;">Performance Globale</p>
                <p style="font-size:9px; color:#64748b; font-style:italic; margin:0;">Taux d'exécution physique</p>
              </div>
              <div style="width:1px; height:32px; background:#334155;"></div>
              <div :style="`font-size:22px; font-weight:900; font-variant-numeric:tabular-nums; color:${calculerTauxExecutionGlobal() < 50 ? '#fca5a5' : '#86efac'};`">
                {{ calculerTauxExecutionGlobal() }}%
              </div>
            </div>
          </div>
        </div>

        <!-- Bloc de Signature (Uniquement pour le PDF) -->
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
  name: "GestoinRapport",
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
      searchQuery: "",
      showSignatureModal: false,
      signatureData: null,
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      structures: [],
      selectedStructureId: "",
      selectedTrimestre: "",
      currentUser: null,
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
  watch: {
    targetSessionId: {
      handler(newVal) {
        if (newVal) {
          this.initReport();
        }
      },
      immediate: false
    }
  },
  mounted() {
    this.initReport();
  },
  methods: {
    redirectToDedicatedTrimestriel() {
      const routeByTrimester = {
        "1": "GestoinRapportTrimestriel1",
        "2": "GestoinRapportTrimestriel2",
        "3": "GestoinRapportTrimestriel3",
        "4": "GestoinRapportTrimestriel4",
      };

      const targetRouteName = routeByTrimester[this.selectedTrimestre];
      if (!targetRouteName) {
        return;
      }

      this.$router.push({ name: targetRouteName });
    },
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
    formatNumber(valeur) {
      if (valeur == null || valeur === "") return "0";
      return valeur.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    },
    calculerSommeActualActivite(activite) {
      return (
        (+activite.coute_t1 || 0) +
        (+activite.coute_t2 || 0) +
        (+activite.coute_t3 || 0) +
        (+activite.coute_t4 || 0)
      );
    },
    calculerExecutionGlobale(activite) {
        if (activite.taux_exécution_globale !== undefined && activite.taux_exécution_globale !== null) {
            return activite.taux_exécution_globale;
        }
        // Calcul manuel si non fourni (somme des taux des trimestres programmés)
        const trimestres = [1, 2, 3, 4];
        let totalTaux = 0;
        trimestres.forEach(t => {
            if (activite[`trimestre_${t}`]) {
                totalTaux += (+activite[`taux_t${t}`] || 0);
            }
        });
        return Math.min(100, Math.round(totalTaux));
    },
    calculerSommeActualEffet(effet) {
      if (!effet.activites) return 0;
      return effet.activites.reduce((somme, a) => somme + this.calculerSommeActualActivite(a), 0);
    },
    calculerSommeFinancesObjectif(objectif) {
      if (!objectif.effets) return 0;
      return objectif.effets.reduce((somme, e) => somme + this.calculerSommeActualEffet(e), 0);
    },
    calculerSommeFinancesAxe(axe) {
      if (!axe.objectifs) return 0;
      return axe.objectifs.reduce((somme, o) => somme + this.calculerSommeFinancesObjectif(o), 0);
    },
    calculerSommeActual() {
      if (!this.filteredAxes) return 0;
      return this.filteredAxes.reduce((somme, a) => somme + this.calculerSommeFinancesAxe(a), 0);
    },
    calculerTauxMoyenEffet(effet) {
      if (!effet.activites || !effet.activites.length) return 0;
      const somme = effet.activites.reduce((s, a) => s + (this.calculerExecutionGlobale(a) || 0), 0);
      return Math.round(somme / effet.activites.length);
    },
    calculerTauxMoyenObjectif(objectif) {
      if (!objectif.effets || !objectif.effets.length) return 0;
      const somme = objectif.effets.reduce((s, e) => s + this.calculerTauxMoyenEffet(e), 0);
      return Math.round(somme / objectif.effets.length);
    },
    calculerTauxMoyenAxe(axe) {
      if (!axe.objectifs || !axe.objectifs.length) return 0;
      const somme = axe.objectifs.reduce((s, o) => s + this.calculerTauxMoyenObjectif(o), 0);
      return Math.round(somme / axe.objectifs.length);
    },
    calculerTauxExecutionGlobal() {
      if (!this.filteredAxes || !this.filteredAxes.length) return 0;
      const somme = this.filteredAxes.reduce((s, a) => s + this.calculerTauxMoyenAxe(a), 0);
      return Math.round(somme / this.filteredAxes.length);
    },
    exportToExcel() {
      try {
        const data = [
          [`RAPPORT GLOBAL D'EXÉCUTION - SESSION ${this.sessionsAnne}`],
          [`Généré le ${new Date().toLocaleDateString('fr-FR')}`],
          [],
          ["N°", "Programmes / Activités", "T1", "T2", "T3", "T4", "Exéc. (%)", "Dépenses (FCFA)", "Partenaires", "Structure", "Observations"]
        ];

        this.filteredAxes.forEach((axe, axeIdx) => {
          data.push([`${axeIdx + 1}`, `AXE STRATÉGIQUE : ${axe.libelle}`, "", "", "", "", this.calculerTauxMoyenAxe(axe) + "%", this.calculerSommeFinancesAxe(axe)]);
          axe.objectifs.forEach((obj, idx) => {
            data.push([`${axeIdx + 1}.${idx + 1}`, `Objectif Stratégique : ${obj.libelle}`, "", "", "", "", this.calculerTauxMoyenObjectif(obj) + "%", this.calculerSommeFinancesObjectif(obj)]);
            obj.effets.forEach((eff, effIdx) => {
              data.push([`${axeIdx + 1}.${idx + 1}.${effIdx + 1}`, `Objectif Opérationnel : ${eff.libelle}`, "", "", "", "", this.calculerTauxMoyenEffet(eff) + "%", this.calculerSommeActualEffet(eff)]);
              eff.activites.forEach((act, actIdx) => {
                data.push([
                  `${axeIdx + 1}.${idx + 1}.${effIdx + 1}.${actIdx + 1}`,
                  act.libelle,
                  (act.trimestre_1 ? (act.taux_t1 || 0) + "%" : "-"),
                  (act.trimestre_2 ? (act.taux_t2 || 0) + "%" : "-"),
                  (act.trimestre_3 ? (act.taux_t3 || 0) + "%" : "-"),
                  (act.trimestre_4 ? (act.taux_t4 || 0) + "%" : "-"),
                  this.calculerExecutionGlobale(act) + "%",
                  this.calculerSommeActualActivite(act),
                  (act.partenaires_details || []).map(p => p.nom).join(', '),
                  act.structure_sigle || "",
                  act.observations || ""
                ]);
              });
            });
          });
        });

        data.push([]);
        data.push(["", "TOTAL GÉNÉRAL DU PROGRAMME", "", "", "", "", this.calculerTauxExecutionGlobal() + "%", this.calculerSommeActual()]);

        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Rapport Global");
        XLSX.writeFile(wb, `Rapport_Global_${this.sessionsAnne}.xlsx`);
      } catch (error) {
        console.error("Erreur Export Excel:", error);
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
      
      // Laisser le temps au SignatureBlock de s'afficher avec l'image
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
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: #f8fafc;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
}

</style>
