<template>
  <div class="fixed top-0 left-0 right-0 h-14 flex justify-between items-center bg-green-700 px-4 sm:px-6 md:px-8 z-[1000] shadow-sm">
    <!-- Logo et Titre (Optionnel, ici on garde le menu) -->
    <div class="flex items-center gap-4">
      <!-- Liens directs pour d'autres sections -->
      <router-link
        v-if="isAdmin" 
        to="/sessions-activites"
        class="text-white mr-4 sm:mr-6 md:mr-8 hover:text-yellow-500 cursor-pointer flex items-center gap-1.5 transition-colors"
        :class="{ active: isActive('/sessions-activites') }"
        title="Session d'Activités"
      >
        <i class="fas fa-calendar-alt text-sm"></i>
        <span class="hidden sm:inline font-bold uppercase tracking-wider text-[11px]">Sessions d'Activités</span>
      </router-link>

      <!-- Menu Activités avec sous-menus -->
      <div v-if="isAdmin || isPointFocal || isChefService || isResponsable || isInvite" class="relative group">
        <div
          @click.stop="toggleActiviteMenu"
          class="text-white mr-4 sm:mr-6 md:mr-8 hover:text-yellow-500 cursor-pointer flex items-center gap-1.5 transition-colors"
          :class="{ 'text-yellow-500 font-black': showActiviteMenu || isActive('/canevas-activites')|| isActive('/programmes-activites-structure') || isActive('/projets-activites') || isActive('/activite-hort-programme')|| isActive('/activites') }"
          title="Activités"
        >
          <i class="fas fa-tasks text-sm"></i>
          <span class="hidden sm:inline font-bold uppercase tracking-wider text-[11px]">Activités</span>
          <i class="fas fa-chevron-down text-[8px] transition-transform duration-300" :class="{ 'rotate-180': showActiviteMenu }"></i>
        </div>
        <div v-if="showActiviteMenu" class="absolute left-0 top-full bg-white shadow-2xl rounded-xl mt-3 py-3 w-72 border border-slate-100 z-[1100] animate-in fade-in slide-in-from-top-2 duration-200">
          <!-- Liens du menu déroulant Activités -->
          <router-link
            v-if="isAdmin || isPointFocal || isChefService || isResponsable"
            to="/canevas-activites"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all border-b border-slate-50 last:border-0"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/canevas-activites') }"
          >
            <i class="fas fa-plus-circle text-indigo-400 w-5"></i> 
            <span class="text-xs font-bold uppercase tracking-tight">Nouvelle Activité</span>
          </router-link>
          <router-link
            v-if="isChefService || isResponsable||isPointFocal"
            to="/programmes-activites-structure"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all border-b border-slate-50 last:border-0"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/programmes-activites-structure') }"
          >
            <i class="fas fa-list-alt text-indigo-400 w-5"></i> 
            <span class="text-xs font-bold uppercase tracking-tight">Programmes par structure</span>
          </router-link>
          <router-link
            v-if="isAdmin || isChefService"
            to="/projets-activites"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all border-b border-slate-50 last:border-0"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/projets-activites') }"
          >
            <i class="fas fa-project-diagram text-indigo-400 w-5"></i> 
            <span class="text-xs font-bold uppercase tracking-tight">Projets de Programme</span>
          </router-link>
          <router-link
            v-if="isAdmin || isChefService" 
            to="/activite-hort-programme"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all border-b border-slate-50 last:border-0"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/activite-hort-programme') }"
          >
            <i class="fas fa-clock text-indigo-400 w-5"></i>
            <span class="text-xs font-bold uppercase tracking-tight">Hors programme</span>
          </router-link>
          <router-link
            v-if="isInvite"
            to="/confirmation-activites"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/confirmation-activites') }"
          >
            <i class="fas fa-check text-indigo-400 w-5"></i>
            <span class="text-xs font-bold uppercase tracking-tight">À confirmer</span>
          </router-link>
        </div>
      </div>
      
      <!-- Suivi -->
      <div v-if="isAdmin || isInvite || isChefService || isResponsable || isPointFocal || isPlanificateur" class="relative group">
        <div
          @click.stop="toggleSuiviMenu"
          class="text-white mr-4 sm:mr-6 md:mr-8 hover:text-yellow-500 cursor-pointer flex items-center gap-1.5 transition-colors"
          :class="{ 'text-yellow-500 font-black': showSuiviMenu || isActive('/select-component') || isActive('/select-rapport') || isActive('/rapport-structure')}"
          title="Suivi"
        >
          <i class="fas fa-chart-line text-sm"></i>
          <span class="hidden sm:inline font-bold uppercase tracking-wider text-[11px]">Suivi</span>
          <i class="fas fa-chevron-down text-[8px] transition-transform duration-300" :class="{ 'rotate-180': showSuiviMenu }"></i>
        </div>
        <div v-if="showSuiviMenu" class="absolute left-0 top-full bg-white shadow-2xl rounded-xl mt-3 py-3 w-72 border border-slate-100 z-[1100] animate-in fade-in slide-in-from-top-2 duration-200">
          <router-link
            v-if="isAdmin || isInvite || isChefService"
            to="/select-component"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all border-b border-slate-50 last:border-0"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/select-component') }"
          >
            <i class="fas fa-list-alt text-indigo-400 w-5"></i> 
            <span class="text-xs font-bold uppercase tracking-tight">Programmes d'activités</span>
          </router-link>
          <router-link
            v-if="isAdmin || isInvite || isChefService"
            to="/select-rapport"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all border-b border-slate-50 last:border-0"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/select-rapport') }"
          >
            <i class="fas fa-file-contract text-indigo-400 w-5"></i>
            <span class="text-xs font-bold uppercase tracking-tight">Rapports de Gestion</span>
          </router-link>
          <router-link
           v-if="isChefService || isResponsable||isPointFocal"
            to="/rapport-structure"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/rapport-structure') }"
          >
            <i class="fas fa-file-contract text-indigo-400 w-5"></i>
            <span class="text-xs font-bold uppercase tracking-tight">Rapports par structure</span>
          </router-link>
          <router-link
            v-if="!isAdmin && !isAdminDSI"
            to="/programmes-activites-historique"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/programmes-activites-historique') }"
          >
            <i class="fas fa-history text-indigo-400 w-5"></i>
            <span class="text-xs font-bold uppercase tracking-tight">Historique</span>
          </router-link>
        </div>
      </div>

      <!-- Option Paramètres avec un menu déroulant -->
      <div class="relative group" v-if="isAdminDSI || isAdmin">
        <div
          @click.stop="toggleParametresMenu"
          class="text-white mr-4 sm:mr-6 md:mr-8 hover:text-yellow-500 cursor-pointer flex items-center gap-1.5 transition-colors"
          :class="{ 'text-yellow-500 font-black': showParametresMenu || isActive('/plans-strategiques') || isActive('/structures') || isActive('/utilisateurs') }"
          title="Paramètres"
        >
          <i class="fas fa-cog text-sm"></i>
          <span class="hidden sm:inline font-bold uppercase tracking-wider text-[11px]">Paramètres</span>
          <i class="fas fa-chevron-down text-[8px] transition-transform duration-300" :class="{ 'rotate-180': showParametresMenu }"></i>
        </div>
        <div v-if="showParametresMenu" class="absolute left-0 top-full bg-white shadow-2xl rounded-xl mt-3 py-3 w-72 border border-slate-100 z-[1100] animate-in fade-in slide-in-from-top-2 duration-200">
          <router-link
          v-if="isAdmin "
            to="/plans-strategiques"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all border-b border-slate-50 last:border-0"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/plans-strategiques') }"
          >
            <i class="fas fa-map text-indigo-400 w-5"></i> 
            <span class="text-xs font-bold uppercase tracking-tight">Plans Stratégiques</span>
          </router-link>
          <router-link
          v-if="isAdminDSI"
            to="/structures"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all border-b border-slate-50 last:border-0"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/structures') }"
          >
            <i class="fas fa-building text-indigo-400 w-5"></i> 
            <span class="text-xs font-bold uppercase tracking-tight">Structures</span>
          </router-link>
          <router-link
          v-if="isAdminDSI"
            to="/utilisateurs"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all border-b border-slate-50 last:border-0"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/utilisateurs') }"
          >
            <i class="fas fa-users-cog text-indigo-400 w-5"></i> 
            <span class="text-xs font-bold uppercase tracking-tight">Utilisateurs</span>
          </router-link>
          <router-link
          v-if="isAdminDSI"
            to="/audits"
            class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-all"
            :class="{ 'bg-indigo-50/50 text-indigo-700 font-black': isActive('/audits') }"
          >
            <i class="fas fa-history text-indigo-400 w-5"></i> 
            <span class="text-xs font-bold uppercase tracking-tight">Historique Audits</span>
          </router-link>
        </div>
      </div>

      <!-- Lien Documentation (visible par tous les utilisateurs connectés) -->
      <router-link
        v-if="userInfo"
        to="/documentation"
        class="text-white mr-4 sm:mr-6 md:mr-8 hover:text-yellow-500 cursor-pointer flex items-center gap-1.5 transition-colors"
        :class="{ active: isActive('/documentation') }"
        title="Documentation"
      >
        <i class="fas fa-book-open text-sm"></i>
        <span class="hidden sm:inline font-bold uppercase tracking-wider text-[11px]">Documentation</span>
      </router-link>
    </div>

    <!-- Notifications et icône utilisateur -->

    <div class="flex items-center">
      <!-- Icône Notifications -->
      <div
        v-if="userInfo"
        class="relative text-white mr-4 sm:mr-6 md:mr-8 hover:text-yellow-500 cursor-pointer"
        title="Notifications"
        @click="toggleNotification"
      >
        <i class="fas fa-bell"></i>
        <!-- Badge de notification -->
        <span
          v-if="notificationsCount > 0"
          class="absolute top-0 right-0 bg-red-500 text-white text-xs font-bold rounded-full px-1 py-0.1"
        >
          {{notificationsCount }}
        </span>
      </div>

      <!-- Icône utilisateur -->
      <div
        class="text-white hover:text-yellow-500 cursor-pointer"
        @click="gererClicUtilisateur"
        title="Utilisateur"
      >
        <i class="fas fa-user-circle"></i>
        <span v-if="userInfo" class="hidden sm:inline text-white ml-2">{{ userInfo.email }}</span>
      </div>

      <!-- Bouton de déconnexion -->
      <button
        v-if="userInfo"
        @click="seDeconnecter"
        class="ml-4 bg-yellow-500 hover:bg-yellow-700 text-red-700 font-bold px-2 rounded"
        title="Déconnexion"
      >
        <i class="fas fa-sign-out-alt"></i>
      </button>
    </div>

    <!-- Fenêtre modale de connexion -->
    <FormulaireConnexion
      v-if="formVisible"
      @close="fermerFormulaireConnexion"
      @submitLogin="fetchUserInfo"
    />
    
    <!-- Fenêtre de notification -->
    <NotificationActivite
      v-if="notificaionVisible"
      class="absolute top-16 right-6 bg-white shadow-lg rounded p-4 z-50"
      @close="fermerNotification"
      @marquerCommeLue="fetchUserInfo"
    />
  </div>
</template>

<script>
import axios from 'axios';
import FormulaireConnexion from '../../ComposantsConnexion/FormulaireConnexion.vue';
import NotificationActivite from '../ComposantsGestionNotifications/NotificationActivite.vue';

export default {
  name: 'BarreDeNavigation',
  components: {
    FormulaireConnexion,
    NotificationActivite,
  },
  data() {
    return {
      userInfo: null,
      formVisible: false,
      notificaionVisible: false,
      showParametresMenu: false,
      showActiviteMenu: false,
      showSuiviMenu: false,
      isAdmin: false,
      isInvite: false,
      isPointFocal: false,
      isChefService: false,
      isResponsable: false,
      isAdminDSI: false,
      isPlanificateur: false,
      userId:'',
      notificationsCount: 0,
    };
  },
  watch: {
    $route() {
      this.closeAllMenus();
      this.fetchUserInfo();
    }
  },
  mounted() {
    this.fetchUserInfo();
  },
  methods: {
    closeAllMenus() {
      this.showParametresMenu = false;
      this.showActiviteMenu = false;
      this.showSuiviMenu = false;
      this.notificaionVisible = false;
    },
    toggleParametresMenu() {
      const willShow = !this.showParametresMenu;
      this.closeAllMenus();
      this.showParametresMenu = willShow;
      if (this.showParametresMenu) {
        document.addEventListener('click', this.handleClickOutsideMenu);
      } else {
        document.removeEventListener('click', this.handleClickOutsideMenu);
      }
    },
    toggleActiviteMenu() {
      const willShow = !this.showActiviteMenu;
      this.closeAllMenus();
      this.showActiviteMenu = willShow;
      if (this.showActiviteMenu) {
        document.addEventListener('click', this.handleClickOutsideMenu);
      } else {
        document.removeEventListener('click', this.handleClickOutsideMenu);
      }
    },
    toggleSuiviMenu() {
      const willShow = !this.showSuiviMenu;
      this.closeAllMenus();
      this.showSuiviMenu = willShow;
      if (this.showSuiviMenu) {
        document.addEventListener('click', this.handleClickOutsideMenu);
      } else {
        document.removeEventListener('click', this.handleClickOutsideMenu);
      }
    },
    handleClickOutsideMenu(event) {
      if (!this.$el.contains(event.target)) {
        this.closeAllMenus();
        document.removeEventListener('click', this.handleClickOutsideMenu);
      }
    },
    toggleNotification() {
      const willShow = !this.notificaionVisible;
      this.closeAllMenus();
      this.notificaionVisible = willShow;
      if (this.notificaionVisible) {
        document.addEventListener('click', this.handleClickOutsideMenu);
      } else {
        document.removeEventListener('click', this.handleClickOutsideMenu);
      }
    },
    fermerNotification() {
      this.notificaionVisible = false;
    },
    gererClicUtilisateur() {
      if (!this.userInfo) {
        this.formVisible = true;
      }
    },
    fermerFormulaireConnexion() {
      this.formVisible = false;
    },
    async seDeconnecter() {
      if(!confirm('Voulez-vous vraiment vous déconnecter ?')) return;
      try {
        await axios.post('/api/logout');
        
        // Reset states locally immediately
        this.userInfo = null;
        this.isAdmin = false;
        this.isInvite = false;
        this.isPointFocal = false;
        this.isChefService = false;
        this.isResponsable = false;
        this.isAdminDSI = false;
        this.isPlanificateur = false;
        this.userId = '';
        this.notificationsCount = 0;
        this.formVisible = false;
        this.closeAllMenus();

        this.$router.push('/login');
      } catch (error) {
        console.error('Erreur lors de la déconnexion :', error.response?.data?.message || error.message);
      }
    },
    async fetchUserInfo() {
      try {
        const response = await axios.get('/api/user-info');
        this.userInfo = response.data;
        this.isAdmin = this.userInfo.role === 'Administrateur';
        this.isInvite = this.userInfo.role === 'Ordonnateur';
        this.isPointFocal = this.userInfo.role === 'Point-Focale';
        this.isChefService = this.userInfo.role === 'Chef-de-service';
        this.isResponsable = this.userInfo.role === 'Responsable-de-structure';
        this.isAdminDSI = this.userInfo.role === 'Administrateur_DSI';
        this.isPlanificateur = this.userInfo.role === 'Planificateur';
        this.userId = this.userInfo.id;
        this.notificationsCount = this.userInfo.notificationsCount || 0;
      } catch (error) {
        console.error('Erreur lors de la récupération des informations utilisateur :', error);
        this.userInfo = null;
        this.isAdmin = false;
        this.isInvite = false;
        this.isPointFocal = false;
        this.isChefService = false;
        this.isResponsable = false;
        this.isAdminDSI = false;
        this.isPlanificateur = false;
        this.userId = '';
        this.notificationsCount = 0;
      }
    },
    isActive(route) {
      return this.$route.path === route;
    },
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutsideMenu);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.handleClickOutsideMenu);
  },
};
</script>

<style scoped>
.active {
  color: rgb(253, 194, 67);
  font-weight: bold;
  text-decoration: underline;
}
</style>

