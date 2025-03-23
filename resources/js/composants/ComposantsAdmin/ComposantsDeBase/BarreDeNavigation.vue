<template>
  <div class="fixed top-0 left-0 right-0 flex justify-between items-center bg-green-700 py-2 px-4 sm:px-6 md:px-8 z-50">
    <!-- Liens de navigation -->
    <div class="flex items-center">
      <!-- Liens directs pour d'autres sections -->
      <router-link
        v-if="isAdmin" 
        to="/sessions-activites"
        class="text-white mr-4 sm:mr-6 md:mr-8 hover:text-yellow-500"
        :class="{ active: isActive('/sessions-activites') }"
        title="Session d'Activités"
      >
        <i class="fas fa-calendar-alt"></i>
        <span class="hidden sm:inline">Sessions d'Activités</span>
      </router-link>

      <!-- Menu Activités avec sous-menus -->
      <div v-if="isAdmin || isPointFocal || isChefService || isResponsable" class="relative">
        <div
          @click="toggleActiviteMenu"
          class="text-white mr-4 sm:mr-6 md:mr-8 hover:text-yellow-500 cursor-pointer"
          :class="{ active: showActiviteMenu || isActive('/canevas-activites')|| isActive('/programmes-activites-structure') || isActive('/projets-activites') || isActive('/activite-hort-programme')|| isActive('/activites') }"
          title="Activités"
        >
          <i class="fas fa-tasks"></i>
          <span class="hidden sm:inline">Activités</span>
        </div>
        <div v-if="showActiviteMenu" class="absolute bg-white shadow-lg rounded mt-2 py-2 w-48 sm:w-64 md:w-80">
          <!-- Liens du menu déroulant Activités -->
          <router-link
            v-if="isAdmin || isPointFocal || isChefService || isResponsable"
            to="/canevas-activites"
            class="block px-4 py-2 text-gray-800 hover:bg-gray-200"
            :class="{ active: isActive('/canevas-activites') }"
          >
            <i class="fas fa-plus-circle"></i> Nouvelle Activité
          </router-link>
          <router-link
            v-if="isChefService || isResponsable||isPointFocal"
            to="/programmes-activites-structure"
            class="block px-4 py-2 text-gray-800 hover:bg-gray-200"
            :class="{ active: isActive('/programmes-activites-structure') }"
          >
          <i class="fas fa-list-alt"></i> Programmes d'activités par structure
          </router-link>
          <router-link
            v-if="isAdmin || isChefService"
            to="/projets-activites"
            class="block px-4 py-2 text-gray-800 hover:bg-gray-200"
            :class="{ active: isActive('/projets-activites') }"
          >
            <i class="fas fa-project-diagram"></i> Projets de Programme d'activités
          </router-link>
          <router-link
            v-if="isAdmin || isChefService" 
            to="/activite-hort-programme"
            class="block px-4 py-2 text-gray-800 hover:bg-gray-200"
            :class="{ active: isActive('/activite-hort-programme') }"
            title="Activités hors programme"
          >
            <i class="fas fa-clock"></i>
            <span class="sm:inline">Activités hors programme</span>
          </router-link>
          <router-link
            v-if="isInvite"
            to="/confirmation-activites"
            class="block px-4 py-2 text-gray-800 hover:bg-gray-200"
            :class="{ active: isActive('/confirmation-activites') }"
            title="Activités à confirmer"
          >
            <i class="fas fa-check"></i>
            <span class="sm:inline">Activités à confirmer</span>
          </router-link>
        </div>
      </div>
      
      <!-- Suivi -->
      <div v-if="isAdmin || isInvite || isChefService || isResponsable" class="relative">
        <div
          @click="toggleSuiviMenu"
          class="text-white mr-4 sm:mr-6 md:mr-8 hover:text-yellow-500 cursor-pointer"
          :class="{ active: showSuiviMenu || isActive('/select-component') || isActive('/select-rapport') || isActive('/rapport-structure')}"
          title="Suivi"
        >
          <i class="fas fa-chart-line"></i>
          <span class="hidden sm:inline">Suivi</span>
        </div>
        <div v-if="showSuiviMenu" class="absolute bg-white shadow-lg rounded mt-2 py-2 w-48 sm:w-64 md:w-80">
          <!-- Liens du menu déroulant Suivi -->
          <router-link
            v-if="isAdmin || isInvite || isChefService"
            to="/select-component"
            class="block px-4 py-2 text-gray-800 hover:bg-gray-200"
            :class="{ active: isActive('/select-component') }"
          >
            <i class="fas fa-list-alt"></i> Programmes d'activités
          </router-link>
          <router-link
            v-if="isAdmin || isInvite || isChefService"
            to="/select-rapport"
            class="block px-4 py-2 text-gray-800 hover:bg-gray-200"
            :class="{ active: isActive('/select-rapport') }"
            title="Rapports"
          >
            <i class="fas fa-file-contract"></i>
            <span class=" sm:inline">Rapports</span>
          </router-link>
          <router-link
           v-if="isChefService || isResponsable||isPointFocal"
            to="/rapport-structure"
            class="block px-4 py-2 text-gray-800 hover:bg-gray-200"
            :class="{ active: isActive('/rapport-structure') }"
            title="Rapports"
          >
            <i class="fas fa-file-contract"></i>
            <span class=" sm:inline">Rapports par structure</span>
          </router-link>
        </div>
      </div>

      <!-- Option Paramètres avec un menu déroulant -->
      <div class="relative" v-if="isAdminDSI || isAdmin">
        <div
          
          @click="toggleParametresMenu"
          class="text-white mr-4 sm:mr-6 md:mr-8 hover:text-yellow-500 cursor-pointer"
          :class="{ active: showParametresMenu || isActive('/plans-strategiques') || isActive('/structures') || isActive('/utilisateurs') }"
          title="Paramètres"
        >
          <i class="fas fa-cog"></i>
          <span class="hidden sm:inline">Paramètres</span>
        </div>
        <div v-if="showParametresMenu" class="absolute bg-white shadow-lg rounded mt-2 py-2 w-48 sm:w-64 md:w-80">
          <!-- Liens du menu déroulant Paramètres -->
          <router-link
          v-if="isAdmin "
            to="/plans-strategiques"
            class="block px-4 py-2 text-gray-800 hover:bg-gray-200"
            :class="{ active: isActive('/plans-strategiques') }"
          >
            <i class="fas fa-map"></i> Plans Stratégiques
          </router-link>
          <router-link
          v-if="isAdminDSI"
            to="/structures"
            class="block px-4 py-2 text-gray-800 hover:bg-gray-200"
            :class="{ active: isActive('/structures') }"
          >
            <i class="fas fa-building"></i> Structures
          </router-link>
          <router-link
          v-if="isAdminDSI"
            to="/utilisateurs"
            class="block px-4 py-2 text-gray-800 hover:bg-gray-200"
            :class="{ active: isActive('/utilisateurs') }"
          >
            <i class="fas fa-users-cog"></i> Utilisateurs
          </router-link>
        </div>
      </div>
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
      userId:'',
      
    };
  },
  mounted() {
    this.fetchUserInfo();
  },
  methods: {
    toggleParametresMenu() {
      this.showParametresMenu = !this.showParametresMenu;
      if (this.showParametresMenu) {
        document.addEventListener('click', this.handleClickOutsideMenu);
      } else {
        document.removeEventListener('click', this.handleClickOutsideMenu);
      }
        this.showActiviteMenu = false;
        this.showSuiviMenu = false;
        this.notificaionVisible = false;
    },
    toggleActiviteMenu() {
      this.showActiviteMenu = !this.showActiviteMenu;
      if (this.showActiviteMenu) {
        document.addEventListener('click', this.handleClickOutsideMenu);
      } else {
        document.removeEventListener('click', this.handleClickOutsideMenu);
        this.showParametresMenu = false;
        this.showSuiviMenu = false;
        this.notificaionVisible = false;
      }
    },
    toggleSuiviMenu() {
      this.showSuiviMenu = !this.showSuiviMenu;
      if (this.showSuiviMenu) {
        document.addEventListener('click', this.handleClickOutsideMenu);
      } else {
        document.removeEventListener('click', this.handleClickOutsideMenu);
      }
      this.showParametresMenu = false;
        this.showActiviteMenu = false;
        this.notificaionVisible = false;
        
    },
    handleClickOutsideMenu(event) {
      if (!this.$el.contains(event.target)) {
        this.showParametresMenu = false;
        this.showActiviteMenu = false;
        this.showSuiviMenu = false;
        this.notificaionVisible = false;
        document.removeEventListener('click', this.handleClickOutsideMenu);
      }
    },
   
  
  beforeDestroy() {
    document.removeEventListener('click', this.handleClickOutsideMenu);
    
  },
    toggleNotification() {
      this.notificaionVisible = !this.notificaionVisible;
      if (this.notificaionVisible) {
        document.addEventListener('click', this.handleClickOutsideMenu);
      } else {
        document.removeEventListener('click', this.handleClickOutsideMenu);
      }
        this.showParametresMenu = false;
        this.showActiviteMenu = false;
        this.showSuiviMenu = false;
      
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
        this.userInfo = null;
        this.formVisible = false;
        this.fetchUserInfo();
        this.$router.push('/login');
      } catch (error) {
        console.error('Erreur lors de la déconnexion :', error.response.data.message);
      }
    },
  async fetchUserInfo() {
  try {
    const response = await axios.get('/api/user-info', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}` 
      }
    });

    this.userInfo = response.data;
    this.isAdmin = this.userInfo.role === 'Administrateur';
    this.isInvite = this.userInfo.role === 'Ordonateur';
    this.isPointFocal = this.userInfo.role === 'Point-Focale';
    this.isChefService = this.userInfo.role === 'Chef-de-service';
    this.isResponsable = this.userInfo.role === 'Responsable-de-structure';
    this.isAdminDSI = this.userInfo.role === 'Administrateur_DSI';
    this.userId = this.userInfo.id;

    // Ajouter le nombre de notifications non lues
    this.notificationsCount = this.userInfo.notificationsCount || 0;

  } catch (error) {
    console.error('Erreur lors de la récupération des informations utilisateur :', error);
  }
},
    isActive(route) {
      return this.$route.path === route;
    },
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

