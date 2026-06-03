<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="mb-8">
      <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">Historique des Audits</h1>
      <p class="text-slate-500 text-sm mt-2">Suivi des créations, modifications et suppressions des activités.</p>
    </div>

    <!-- Filtres -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-wrap gap-4 items-center">
      <div class="flex flex-col">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Action</label>
        <select v-model="filters.action" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none w-48" @change="fetchAudits(1)">
          <option value="">Toutes les actions</option>
          <option value="création">Création</option>
          <option value="modification">Modification</option>
          <option value="suppression">Suppression</option>
        </select>
      </div>

      <div class="flex flex-col">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Email Utilisateur</label>
        <input type="text" v-model="filters.email" placeholder="Rechercher..." class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none w-64" @keyup.enter="fetchAudits(1)" />
      </div>

      <div class="flex items-end mt-5">
        <button @click="fetchAudits(1)" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors text-sm">
          Filtrer
        </button>
        <button @click="resetFilters" class="ml-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2 px-4 rounded-lg shadow-sm transition-colors text-sm">
          Réinitialiser
        </button>
      </div>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
              <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Activité ID</th>
              <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Utilisateur</th>
              <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Action</th>
              <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Détails</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-if="loading" class="bg-white">
              <td colspan="5" class="px-6 py-10 text-center text-slate-500 italic">Chargement des données...</td>
            </tr>
            <tr v-else-if="audits.length === 0" class="bg-white">
              <td colspan="5" class="px-6 py-10 text-center text-slate-500 italic">Aucun audit trouvé.</td>
            </tr>
            <tr v-else v-for="audit in audits" :key="audit.id" class="hover:bg-slate-50 transition-colors bg-white">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 font-medium">
                {{ formatDate(audit.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                #{{ audit.record_id || '?' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-slate-900">
                  {{ audit.user_role ? audit.user_role.replace(/-/g, ' ') : 'Système' }} 
                  <span v-if="audit.structure_sigle" class="text-indigo-600">({{ audit.structure_sigle }})</span>
                </div>
                <div class="text-xs text-slate-500">{{ audit.user_email || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest" :class="{
                  'bg-emerald-100 text-emerald-800': audit.action === 'création',
                  'bg-amber-100 text-amber-800': audit.action === 'modification',
                  'bg-rose-100 text-rose-800': audit.action === 'suppression'
                }">
                  {{ audit.action }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-slate-700">
                <button @click="viewDetails(audit)" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-tight flex items-center gap-1">
                  <i class="fas fa-eye"></i> Voir les détails
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="bg-white px-6 py-4 border-t border-slate-200 flex items-center justify-between">
        <div class="text-sm text-slate-500">
          Affichage de <span class="font-bold text-slate-700">{{ pagination.from || 0 }}</span> à <span class="font-bold text-slate-700">{{ pagination.to || 0 }}</span> sur <span class="font-bold text-slate-700">{{ pagination.total }}</span> résultats
        </div>
        <div class="flex gap-2">
          <button @click="fetchAudits(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
            Précédent
          </button>
          <button @click="fetchAudits(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
            Suivant
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de détails -->
    <div v-if="selectedAudit" class="fixed inset-0 bg-slate-900/50 z-[2000] flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
          <h3 class="text-lg font-black text-slate-800 uppercase tracking-tighter">Détails de l'audit #{{ selectedAudit.id }}</h3>
          <button @click="selectedAudit = null" class="text-slate-400 hover:text-rose-500 transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        
        <div class="p-6 overflow-y-auto flex-grow bg-slate-100">
          
          <!-- Informations de contexte -->
          <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Activité ID</div>
              <div class="text-sm font-bold text-indigo-700">#{{ selectedAudit.record_id || 'Non spécifié' }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">URL (Endpoint API)</div>
              <div class="text-sm text-slate-700 truncate font-mono bg-slate-50 p-1 rounded border border-slate-100" :title="selectedAudit.url">
                {{ selectedAudit.url || 'Non spécifiée' }}
              </div>
            </div>
            <div class="md:col-span-2">
              <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Navigateur (User-Agent)</div>
              <div class="text-xs font-bold text-slate-700 bg-slate-50 p-2 rounded border border-slate-100 flex items-center gap-2">
                <i :class="getBrowserIcon(selectedAudit.user_agent)" class="text-slate-400 text-lg"></i>
                <div>
                  <div>{{ formatUserAgent(selectedAudit.user_agent) }}</div>
                  <div class="text-[9px] font-normal text-slate-400 font-mono truncate" :title="selectedAudit.user_agent">
                    {{ selectedAudit.user_agent || 'Non spécifié' }}
                  </div>
                </div>
              </div>
            </div>
            <div class="md:col-span-2 flex flex-col md:flex-row gap-4">
              <div class="flex-1">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Adresse IP</div>
                <div class="text-sm font-mono text-slate-700">{{ selectedAudit.address_mail || 'Inconnue' }}</div>
              </div>
              <div class="flex-1">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Localisation approximative (GeoIP)</div>
                <div v-if="geoIpLoading" class="text-xs text-slate-500 italic"><i class="fas fa-spinner fa-spin mr-1"></i> Recherche en cours...</div>
                <div v-else-if="geoIpData && geoIpData.status === 'success'" class="text-sm text-slate-700 flex items-center gap-2">
                  <span class="text-xl" :title="geoIpData.country">{{ getCountryFlag(geoIpData.countryCode) }}</span>
                  <span>{{ geoIpData.city }}, {{ geoIpData.country }}</span>
                  <a :href="`https://www.google.com/maps/search/?api=1&query=${geoIpData.lat},${geoIpData.lon}`" target="_blank" class="text-indigo-500 hover:text-indigo-700 ml-2" title="Voir sur la carte">
                    <i class="fas fa-map-marker-alt"></i>
                  </a>
                </div>
                <div v-else class="text-xs text-slate-400 italic">
                  Impossible de localiser cette IP (Réseau local ou API bloquée).
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="grid grid-cols-2 gap-px bg-slate-200">
              <!-- Anciennes valeurs -->
              <div class="bg-white p-5">
                <h4 class="text-xs font-black text-rose-500 uppercase tracking-widest mb-4 border-b border-rose-100 pb-2">Anciennes Valeurs</h4>
                <div v-if="!selectedAudit.old_values || Object.keys(selectedAudit.old_values).length === 0" class="text-sm text-slate-400 italic">Aucune ancienne valeur</div>
                <div v-else class="space-y-3">
                  <div v-for="(value, key) in selectedAudit.old_values" :key="'old_'+key">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ key }}</span>
                    <div class="text-sm text-slate-700 break-words bg-slate-50 p-2 rounded border border-slate-100 mt-1">{{ formatValue(value) }}</div>
                  </div>
                </div>
              </div>
              
              <!-- Nouvelles valeurs -->
              <div class="bg-white p-5">
                <h4 class="text-xs font-black text-emerald-500 uppercase tracking-widest mb-4 border-b border-emerald-100 pb-2">Nouvelles Valeurs</h4>
                <div v-if="!selectedAudit.new_values || Object.keys(selectedAudit.new_values).length === 0" class="text-sm text-slate-400 italic">Aucune nouvelle valeur</div>
                <div v-else class="space-y-3">
                  <div v-for="(value, key) in selectedAudit.new_values" :key="'new_'+key">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ key }}</span>
                    <div class="text-sm text-slate-800 break-words bg-emerald-50/30 p-2 rounded border border-emerald-100 mt-1" :class="{'font-bold text-emerald-700': isChanged(key)}">
                      {{ formatValue(value) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'GestionAudits',
  data() {
    return {
      audits: [],
      loading: true,
      filters: {
        action: '',
        email: ''
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0,
        from: 0,
        to: 0
      },
      selectedAudit: null,
      geoIpData: null,
      geoIpLoading: false
    }
  },
  mounted() {
    this.fetchAudits(1);
  },
  methods: {
    async fetchAudits(page) {
      this.loading = true;
      try {
        const response = await axios.get('/api/audits', {
          params: {
            page: page,
            action: this.filters.action,
            email: this.filters.email
          }
        });
        
        this.audits = response.data.data;
        this.pagination = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          total: response.data.total,
          from: response.data.from,
          to: response.data.to
        };
      } catch (error) {
        console.error("Erreur lors de la récupération des audits", error);
      } finally {
        this.loading = false;
      }
    },
    resetFilters() {
      this.filters = {
        action: '',
        email: ''
      };
      this.fetchAudits(1);
    },
    formatDate(dateString) {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fr-FR', { 
        year: 'numeric', 
        month: 'short', 
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    },
    viewDetails(audit) {
      this.selectedAudit = audit;
      this.geoIpData = null;
      if (audit.address_mail && audit.address_mail !== '127.0.0.1' && audit.address_mail !== '::1') {
        this.fetchGeoIp(audit.address_mail);
      }
    },
    async fetchGeoIp(ip) {
      this.geoIpLoading = true;
      try {
        const response = await axios.get(`http://ip-api.com/json/${ip}`);
        this.geoIpData = response.data;
      } catch (error) {
        console.error("Erreur GeoIP", error);
        this.geoIpData = { status: 'fail' };
      } finally {
        this.geoIpLoading = false;
      }
    },
    getCountryFlag(countryCode) {
      if (!countryCode) return '🌍';
      const codePoints = countryCode.toUpperCase().split('').map(char => 127397 + char.charCodeAt());
      return String.fromCodePoint(...codePoints);
    },
    formatUserAgent(ua) {
      if (!ua) return 'Système inconnu';
      
      let browser = "Navigateur Inconnu";
      if (ua.includes("Edg/")) browser = "Microsoft Edge";
      else if (ua.includes("OPR/") || ua.includes("Opera/")) browser = "Opera";
      else if (ua.includes("Firefox/")) browser = "Mozilla Firefox";
      else if (ua.includes("Chrome/")) browser = "Google Chrome";
      else if (ua.includes("Safari/") && !ua.includes("Chrome")) browser = "Apple Safari";
      
      let os = "OS Inconnu";
      if (ua.includes("Windows NT 10.0")) os = "Windows 10 / 11";
      else if (ua.includes("Windows NT 6.")) os = "Windows 7 / 8";
      else if (ua.includes("Windows")) os = "Windows";
      else if (ua.includes("Mac OS X")) os = "macOS";
      else if (ua.includes("Linux")) os = "Linux";
      else if (ua.includes("Android")) os = "Android";
      else if (ua.includes("iPhone") || ua.includes("iPad")) os = "iOS";
      
      return `${browser} sur ${os}`;
    },
    getBrowserIcon(ua) {
      if (!ua) return 'fas fa-desktop';
      if (ua.includes("Edg/")) return 'fab fa-edge text-blue-600';
      if (ua.includes("OPR/") || ua.includes("Opera/")) return 'fab fa-opera text-red-500';
      if (ua.includes("Firefox/")) return 'fab fa-firefox text-orange-500';
      if (ua.includes("Chrome/")) return 'fab fa-chrome text-green-500';
      if (ua.includes("Safari/") && !ua.includes("Chrome")) return 'fab fa-safari text-blue-400';
      if (ua.includes("Android") || ua.includes("iPhone") || ua.includes("iPad")) return 'fas fa-mobile-alt';
      return 'fas fa-desktop';
    },
    formatValue(value) {
      if (value === null || value === undefined) return 'null';
      if (typeof value === 'boolean') return value ? 'Oui' : 'Non';
      if (typeof value === 'object') return JSON.stringify(value);
      return value;
    },
    isChanged(key) {
      if (!this.selectedAudit || !this.selectedAudit.old_values) return false;
      return this.selectedAudit.old_values[key] != this.selectedAudit.new_values[key];
    }
  }
}
</script>
