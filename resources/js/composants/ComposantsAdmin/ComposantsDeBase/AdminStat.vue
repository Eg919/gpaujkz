<template>
  <div class="flex flex-col min-h-screen bg-gray-50/50 pb-12">
    <!-- Header Premium -->
    <div v-if="userInfo" class="w-full bg-gray-50 border-b border-gray-200 py-6 px-4 md:px-8 mb-8 shadow-sm transition-all animate-fade-in relative z-10">
      <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter text-center">Tableau de Bord</h1>
      <div class="flex flex-col items-center mt-3">
        <p class="text-gray-600 font-bold text-center">
          Ravi de vous revoir, <span class="text-emerald-600 uppercase tracking-tight">{{ userInfo.nom && userInfo.prenom ? (userInfo.nom + ' ' + userInfo.prenom) : userInfo.email }}</span> ! 🚀
        </p>
        <span class="inline-flex items-center px-3 py-1 mt-2 rounded-full bg-white border border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest shadow-sm">
          <i class="far fa-calendar-alt mr-2 text-amber-500"></i> {{ formattedDate }}
        </span>
      </div>
    </div>

    <!-- SECTION 1: KPIs Administratifs (Uniquement pour DSI) -->
    <div v-if="isAdminDSI" class="max-w-[99%] mx-auto w-full px-4 md:px-8 mb-10">
      <h2 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-1">Administration Système</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="stat-card group border-l-4 border-l-indigo-600">
             <div class="stat-link">
                <div class="stat-icon bg-indigo-50 text-indigo-600">
                   <i class="fas fa-users-cog"></i>
                </div>
                <div class="stat-content">
                   <h3 class="stat-label">Utilisateurs Totaux</h3>
                   <p class="stat-value text-indigo-700">{{ dsiStats.total_users }}</p>
                </div>
             </div>
          </div>
          <div class="stat-card group border-l-4 border-l-blue-600">
             <div class="stat-link">
                <div class="stat-icon bg-blue-50 text-blue-600">
                   <i class="fas fa-sitemap"></i>
                </div>
                <div class="stat-content">
                   <h3 class="stat-label">Structures Gérées</h3>
                   <p class="stat-value text-blue-700">{{ dsiStats.total_structures }}</p>
                </div>
             </div>
          </div>
      </div>
    </div>

    <!-- SECTION 2: KPIs Activités & Finances (Tous rôles sauf DSI pure, mais DSI isAdminDSI peut aussi voir) -->
    <div class="max-w-[99%] mx-auto w-full px-4 md:px-8">
      <h2 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-1">Performances des Activités</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
        
        <!-- Budget Card -->
        <div class="stat-card group border-l-4" :class="(isAdmin || isChefService || isAdminDSI || isInvite) ? 'border-l-emerald-500' : 'border-l-indigo-500'">
           <div class="stat-link">
              <div class="stat-icon" :class="(isAdmin || isChefService || isAdminDSI || isInvite) ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-indigo-600'">
                 <i class="fas fa-money-bill-wave"></i>
              </div>
              <div class="stat-content">
                 <h3 class="stat-label">{{ (isAdmin || isChefService || isAdminDSI || isInvite) ? 'Budget Global' : 'Budget Structure' }}</h3>
                 <p class="stat-value" :class="(isAdmin || isChefService || isAdminDSI || isInvite) ? 'text-emerald-700' : 'text-indigo-700'">
                   {{ (isAdmin || isChefService || isAdminDSI || isInvite) ? formatCurrency(globalStats.financial_stats?.total_budget) : formatCurrency(structureStats.financials?.budget) }}
                 </p>
              </div>
           </div>
        </div>

        <!-- Programme Card -->
        <div class="stat-card group border-l-4 border-l-teal-500">
           <div class="stat-link">
              <div class="stat-icon bg-teal-50 text-teal-600">
                 <i class="fas fa-calendar-check"></i>
              </div>
              <div class="stat-content">
                 <h3 class="stat-label">Programme d'activités</h3>
                 <p class="stat-value text-teal-700">{{ (isAdmin || isChefService || isAdminDSI || isInvite) ? globalStats.confirmed_count : structureStats.confirmed_count }}</p>
              </div>
           </div>
        </div>

        <!-- Terminées Card -->
        <div class="stat-card group border-l-4 border-l-indigo-600">
           <div class="stat-link">
              <div class="stat-icon bg-indigo-50 text-indigo-600">
                 <i class="fas fa-flag-checkered"></i>
              </div>
              <div class="stat-content">
                 <h3 class="stat-label">Activités Terminées</h3>
                 <p class="stat-value text-indigo-800">{{ (isAdmin || isChefService || isAdminDSI || isInvite) ? globalStats.total_finished : structureStats.total_finished }}</p>
              </div>
           </div>
        </div>

        <!-- Taux Card -->
        <div class="stat-card group border-l-4 border-l-amber-500">
           <div class="stat-link">
              <div class="stat-icon bg-amber-50 text-amber-600">
                 <i class="fas fa-percentage"></i>
              </div>
              <div class="stat-content">
                 <h3 class="stat-label">{{ (isAdmin || isChefService || isAdminDSI || isInvite) ? 'Exécution Globale' : 'Taux de Planification' }}</h3>
                 <p class="stat-value text-amber-700">{{ (isAdmin || isChefService || isAdminDSI || isInvite) ? Math.round(globalStats.financial_stats?.avg_execution_rate || 0) : structureStats.planning_rate }}%</p>
              </div>
           </div>
        </div>

        <!-- Alertes Card -->
        <div class="stat-card group border-l-4 border-l-rose-500">
          <router-link to="/adminstat" class="stat-link">
            <div class="stat-icon bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-all">
              <i class="fas fa-bell"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-label">Alertes</h3>
              <p class="stat-value">{{ notificationsCount }}</p>
            </div>
          </router-link>
        </div>
      </div>
    </div>

    <!-- SECTION 3: Graphiques Dynamiques -->
    <div class="max-w-[99%] mx-auto w-full px-4 md:px-8 mt-8">
        <h2 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-1">Analyses Graphiques</h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
          
          <!-- Graphique 1: Statuts de Validation (Admin/Structure) OU Répartition Rôles (DSI) -->
          <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-sm font-black text-gray-800 uppercase tracking-tighter">
                {{ isAdminDSI ? 'Répartition des Utilisateurs par Rôle' : 'Statuts de Validation' }}
              </h3>
              <i class="fas fa-chart-pie" :class="isAdminDSI ? 'text-indigo-500' : 'text-emerald-500'"></i>
            </div>
            <div class="relative flex-grow min-h-[280px] flex items-center justify-center">
              <canvas ref="roleOrSelectionChart"></canvas>
            </div>
          </div>

          <!-- Graphique 2 (Admin: Volume par Structure | Structure: Execution Status) -->
          <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-sm font-black text-gray-800 uppercase tracking-tighter">
                {{ (isAdmin || isChefService || isAdminDSI || isInvite) ? 'Volume par Structure' : 'État d\'Exécution' }}
              </h3>
              <i class="fas text-blue-500" :class="(isAdmin || isChefService || isAdminDSI || isInvite) ? 'fa-chart-bar' : 'fa-spinner fa-pulse'"></i>
            </div>
            <div class="relative flex-grow min-h-[280px]">
              <canvas ref="executionChartOrStructure"></canvas>
            </div>
          </div>

          <!-- Graphique 3 (Admin: Réussites par Structure | Structure: Tâches Répartition) -->
          <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-sm font-black text-gray-800 uppercase tracking-tighter">
                {{ (isAdmin || isChefService || isAdminDSI || isInvite) ? 'Réussites par Structure' : 'Avancement des Tâches' }}
              </h3>
              <i class="fas text-amber-500" :class="(isAdmin || isChefService || isAdminDSI || isInvite) ? 'fa-trophy' : 'fa-tasks'"></i>
            </div>
            <div class="relative flex-grow min-h-[280px]">
              <canvas ref="finishedOrTasksChart"></canvas>
            </div>
          </div>
        </div>
    </div>

    <!-- Section Taux Recaps -->
    <div class="max-w-[99%] mx-auto w-full px-4 md:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Taux Financier -->
      <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-md font-black text-gray-800 uppercase tracking-tighter mb-1">Efficacité Budgétaire</h2>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Dépenses par rapport aux prévisions</p>
            </div>
            <span class="text-2xl font-black text-blue-600">{{ roleFinancialRate }}%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden mb-6 flex items-center p-0.5">
            <div class="bg-blue-600 h-full rounded-full transition-all duration-1000" :style="{ width: roleFinancialRate + '%' }"></div>
        </div>
        <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-gray-400">
           <span>Dépensé: {{ (isAdmin || isChefService || isAdminDSI || isInvite) ? formatCurrency(globalStats.financial_stats?.total_executed) : formatCurrency(structureStats.financials?.executed) }}</span>
           <span>Budget: {{ (isAdmin || isChefService || isAdminDSI || isInvite) ? formatCurrency(globalStats.financial_stats?.total_budget) : formatCurrency(structureStats.financials?.budget) }}</span>
        </div>
      </div>

      <!-- Taux de Validation/Planification -->
      <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-md font-black text-gray-800 uppercase tracking-tighter mb-1">
                    {{ (isAdmin || isChefService || isAdminDSI || isInvite) ? 'Validation Globale' : 'Taux de Planification' }}
                </h2>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Progression de la session en cours</p>
            </div>
            <span class="text-2xl font-black text-emerald-600">{{ (isAdmin || isChefService || isAdminDSI || isInvite) ? progressPercentage : structureStats.planning_rate }}%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden mb-6 flex items-center p-0.5">
            <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000" :style="{ width: ((isAdmin || isChefService || isAdminDSI || isInvite) ? progressPercentage : structureStats.planning_rate) + '%' }"></div>
        </div>
        <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-gray-400">
           <template v-if="isAdmin || isChefService || isAdminDSI || isInvite">
              <span>Validées: {{ validatedActivitiesCount }}</span>
              <span>Total: {{ activiteCount }}</span>
           </template>
           <template v-else>
              <span>Activités Planifiées: {{ structureStats.planned_activites }}</span>
              <span>Total structure: {{ structureStats.total_activites }}</span>
           </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: "Statistiques",
  data() {
    return {
      userCount: 0, structureCount: 0, sessionCount: 0, activityCount: 0, activiteCount: 0, 
      validatedActivitiesCount: 0, notificationsCount: 0, userInfo: null,
      isAdmin: false, isInvite: false, isPointFocal: false, isChefService: false, 
      isAdminDSI: false, isResponsable: false, isPlanificateur: false,
      loadingStats: true, charts: {},
      dsiStats: { total_users: 0, total_structures: 0, role_distribution: [], structure_types: [] },
      globalStats: {
        selection_distribution: [], execution_distribution: [], structure_distribution: [], 
        finished_by_structure: [], confirmed_count: 0, total_finished: 0,
        financial_stats: { total_budget: 0, total_executed: 0, avg_execution_rate: 0 }
      },
      structureStats: {
        total_activites: 0, planned_activites: 0, planning_rate: 0, confirmed_count: 0, total_finished: 0,
        taches_distribution: [], selection_distribution: [], execution_distribution: [],
        financials: { budget: 0, executed: 0 }
      }
    };
  },
  computed: {
    formattedDate() {
      return new Intl.DateTimeFormat('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }).format(new Date());
    },
    progressPercentage() {
      if (!this.activiteCount || this.activiteCount === 0) return 0;
      return Math.round((this.validatedActivitiesCount / this.activiteCount) * 100);
    },
    roleFinancialRate() {
       const budget = (this.isAdmin || this.isChefService || this.isAdminDSI || this.isInvite) ? parseFloat(this.globalStats.financial_stats?.total_budget) : parseFloat(this.structureStats.financials?.budget);
       const executed = (this.isAdmin || this.isChefService || this.isAdminDSI || this.isInvite) ? parseFloat(this.globalStats.financial_stats?.total_executed) : parseFloat(this.structureStats.financials?.executed);
       if (!budget || budget === 0) return 0;
       return Math.round((executed / budget) * 100);
    }
  },
  methods: {
    formatCurrency(value) {
       if (!value) return '0 FCFA';
       return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(value).replace('XOF', 'FCFA');
    },
    async fetchUserInfo() {
      try {
        const response = await axios.get('/api/user-info');
        this.userInfo = response.data;
        const role = this.userInfo.role;
        this.isAdmin = role === 'Administrateur';
        this.isChefService = role === 'Chef-de-service';
        this.isResponsable = role === 'Responsable-de-structure';
        this.isAdminDSI = role === 'Administrateur_DSI';
        this.isPlanificateur = role === 'Planificateur';
        this.isPointFocal = role === 'Point-Focale';
        this.isInvite = role === 'Ordonnateur';
        this.notificationsCount = (this.userInfo.notifications && this.userInfo.notifications.length) || 0;
        this.loadAdvancedStats();
      } catch (e) { console.error(e); }
    },
    async loadAdvancedStats() {
      this.loadingStats = true;
      try {
        if (this.isAdminDSI) {
           const [resGlobal, resDsi] = await Promise.all([ axios.get('/api/stats/global'), axios.get('/api/stats/dsi') ]);
           this.globalStats = resGlobal.data;
           this.dsiStats = resDsi.data;
           this.initDsiSpecificCharts();
        } else if (this.isAdmin || this.isChefService || this.isInvite) {
          const res = await axios.get('/api/stats/global');
          this.globalStats = res.data;
          this.initGlobalCharts();
        } else {
          const res = await axios.get('/api/stats/structure');
          this.structureStats = res.data;
          this.initStructureUserCharts();
        }
      } catch (e) { console.error(e); } finally { this.loadingStats = false; }
    },
    initDsiSpecificCharts() {
       this.initDsiRoleDoughnut();
       this.initBarChart('executionChartOrStructure', this.globalStats.structure_distribution.map(s => s.sigle), this.globalStats.structure_distribution.map(s => s.activites_count), '#3b82f6', 'Activités');
       this.initBarChart('finishedOrTasksChart', this.globalStats.finished_by_structure.map(s => s.sigle), this.globalStats.finished_by_structure.map(s => s.finished_count), '#fbbf24', 'Terminées');
    },
    initGlobalCharts() {
      this.initSelectionDoughnut(this.globalStats.selection_distribution);
      this.initBarChart('executionChartOrStructure', this.globalStats.structure_distribution.map(s => s.sigle), this.globalStats.structure_distribution.map(s => s.activites_count), '#3b82f6', 'Activités');
      this.initBarChart('finishedOrTasksChart', this.globalStats.finished_by_structure.map(s => s.sigle), this.globalStats.finished_by_structure.map(s => s.finished_count), '#fbbf24', 'Terminées');
    },
    initStructureUserCharts() {
      this.initSelectionDoughnut(this.structureStats.selection_distribution);
      this.initBarChart('executionChartOrStructure', this.structureStats.execution_distribution.map(s => s.etat_activite), this.structureStats.execution_distribution.map(s => s.total), '#3b82f6', 'Activités');
      this.initBarChart('finishedOrTasksChart', this.structureStats.taches_distribution.map(s => s.etat_tache), this.structureStats.taches_distribution.map(s => s.total), '#fbbf24', 'Tâches');
    },
    initDsiRoleDoughnut() {
       if (this.charts.selection) this.charts.selection.destroy(); // reuse Ref roleOrSelectionChart
       const ctx = this.$refs.roleOrSelectionChart;
       if (!ctx) return;
       const labels = this.dsiStats.role_distribution.map(r => r.role);
       const data = this.dsiStats.role_distribution.map(r => r.total);
       this.charts.selection = new Chart(ctx, {
          type: 'doughnut', data: {
             labels: labels,
             datasets: [{ data: data, backgroundColor: ['#6366f1', '#10b981', '#fbbf24', '#f43f5e', '#3b82f6', '#ec4899'], borderWidth: 0, cutout: '75%' }]
          },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
       });
    },
    initSelectionDoughnut(dist) {
      if (this.charts.selection) this.charts.selection.destroy();
      const ctx = this.$refs.roleOrSelectionChart;
      if (!ctx) return;
      const dataMap = { 'Validé': 0, 'Rejeté': 0, 'Selectionné': 0, 'En attente': 0 };
      if (dist) {
        dist.forEach(s => {
          if (dataMap.hasOwnProperty(s.etat_slection)) dataMap[s.etat_slection] = s.total;
          else dataMap['En attente'] += s.total;
        });
      }
      this.charts.selection = new Chart(ctx, {
        type: 'doughnut', data: {
          labels: Object.keys(dataMap),
          datasets: [{ data: Object.values(dataMap), backgroundColor: ['#10b981', '#f43f5e', '#fbbf24', '#cbd5e1'], borderWidth: 0, cutout: '75%' }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
      });
    },
    initBarChart(refName, labels, values, color, label) {
       if (this.charts[refName]) this.charts[refName].destroy();
       const ctx = this.$refs[refName];
       if (!ctx) return;
       this.charts[refName] = new Chart(ctx, {
          type: 'bar', data: {
             labels: labels,
             datasets: [{ label: label, data: values, backgroundColor: color, borderRadius: 4 }]
          },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
       });
    },
    async fetchData() {
      try {
        const [actsOpen, actsVal] = await Promise.all([ axios.get('/api/activites/count-Ouvert'), axios.get('/api/activites/count-valide') ]);
        this.activiteCount = actsOpen.data.count;
        this.validatedActivitiesCount = actsVal.data.count;
      } catch (e) { console.error(e); }
    }
  },
  mounted() { this.fetchUserInfo(); this.fetchData(); },
  beforeUnmount() { Object.values(this.charts).forEach(c => c.destroy()); }
};
</script>

<style scoped>
.stat-card { @apply bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300; }
.stat-link { @apply flex items-center p-5 w-full h-full; }
.stat-icon { @apply w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0; }
.stat-content { @apply ml-3 flex-grow; }
.stat-label { @apply text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5; }
.stat-value { @apply text-lg font-black tracking-tight; }
.animate-fade-in { animation: fade-in 0.6s ease-out forwards; }
@keyframes fade-in { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>
