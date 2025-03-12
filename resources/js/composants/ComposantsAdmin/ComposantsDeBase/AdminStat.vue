<template>
  <div>
    <!-- Section titre -->
    <div v-if="userInfo" class="bg-gray-100 text-center py-4 shadow-md mt-7">
      <h1 class="text-xl sm:text-2xl font-bold text-gray-700">Tableau de bord</h1>
    </div>

    <!-- Grille des statistiques avec défilement vertical -->
    <div   class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 p-4 overflow-y-auto h-[calc(100vh-100px)]">
      <!-- Bloc statistique 1 -->
      <div v-if="isAdmin || isChefService" class="bg-white shadow-md rounded-lg p-4 flex items-center w-full h-32">
        <router-link to="/utilisateurs" class="flex items-center w-full">
          <div class="flex-shrink-0 bg-blue-100 text-blue-500 rounded-full p-4">
            <i class="fas fa-users text-2xl sm:text-3xl"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-gray-600 text-sm font-medium">Utilisateurs</h3>
            <p class="text-xl font-semibold">{{ userCount }}</p>
          </div>
        </router-link>
      </div>

      <!-- Bloc statistique 2 -->
      <div  v-if="isAdmin || isChefService" class="bg-white shadow-md rounded-lg p-4 flex items-center w-full h-32">
        <router-link to="/structures" class="flex items-center w-full">
          <div class="flex-shrink-0 bg-green-100 text-green-500 rounded-full p-4">
            <i class="fa-solid fa-building text-2xl sm:text-3xl"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-gray-600 text-sm font-medium">Structures</h3>
            <p class="text-xl font-semibold">{{ structureCount }}</p>
          </div>
        </router-link>
      </div>

      <!-- Bloc statistique 3 -->
      <div  v-if="isAdmin"class="bg-white shadow-md rounded-lg p-4 flex items-center w-full h-32">
        <router-link to="/sessions-activites" class="flex items-center w-full" title="Session d'Activités">
          <div class="flex-shrink-0 bg-yellow-100 text-yellow-500 rounded-full p-4">
            <i class="fas fa-tasks text-2xl sm:text-3xl"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-gray-600 text-sm font-medium">Sessions d'activités</h3>
            <p class="text-xl font-semibold">{{ sessionCount }}</p>
          </div>
        </router-link>
      </div>

      <!-- Bloc statistique 4 -->
      <div v-if="isAdmin || isChefService ||isPointFocal||isInvite" class="bg-white shadow-md rounded-lg p-4 flex items-center w-full h-32">
        <router-link to="/adminstat" class="flex items-center w-full" title="Notifications">
          <div class="flex-shrink-0 bg-red-100 text-red-500 rounded-full p-4">
            <i class="fas fa-bell text-2xl sm:text-3xl"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-gray-600 text-sm font-medium">Notifications</h3>
            <p class="text-xl font-semibold">{{notificationsCount }}</p>
          </div>
        </router-link>
      </div>

      <!-- Bloc statistique 5 -->
      <div v-if="isAdmin || isChefService || isPointFocal" class="bg-white shadow-md rounded-lg p-4 flex items-center w-full h-32">
        <router-link to="/canevas-activites" class="flex items-center w-full">
          <div class="flex-shrink-0 bg-indigo-100 text-indigo-500 rounded-full p-4">
            <i class="fas fa-lightbulb text-2xl sm:text-3xl"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-gray-600 text-sm font-medium">Proposition d'activités</h3>
            <p class="text-xl font-semibold">{{ activityCount }}</p>
          </div>
        </router-link>
      </div>

      <!-- Bloc statistique 6 -->
      <div v-if="isAdmin || isChefService"class="bg-white shadow-md rounded-lg p-4 flex items-center w-full h-32">
        <router-link to="/projets-activites" class="flex items-center w-full">
          <div class="flex-shrink-0 bg-purple-100 text-purple-500 rounded-full p-4">
            <i class="fas fa-project-diagram text-2xl sm:text-3xl"></i>
          </div>
          <div class="ml-4" >
            <h3 class="text-gray-600 text-sm font-medium">Projets de Programme d'activités</h3>
            <p class="text-xl font-semibold">{{ activiteCount }}</p>
          </div>
        </router-link>
      </div>

      <!-- Bloc statistique 7 -->
      <div v-if="isAdmin || isChefService ||isInvite"class="bg-white shadow-md rounded-lg p-4 flex items-center w-full h-32">
        <router-link to="/select-component" class="flex items-center w-full">
          <div class="flex-shrink-0 bg-teal-100 text-teal-500 rounded-full p-4">
            <i class="fas fa-calendar-alt text-2xl sm:text-3xl"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-gray-600 text-sm font-medium">Programmes d'activités</h3>
            <p class="text-xl font-semibold">{{ validatedActivitiesCount }}</p>
          </div>
        </router-link>
      </div>

      <!-- Bloc statistique 8 -->
      <div v-if="isAdmin || isChefService||isInvite"class="bg-white shadow-md rounded-lg p-4 flex items-center w-full h-32">
        <router-link to="/rapports-trimestriels" class="flex items-center w-full">
          <div class="flex-shrink-0 bg-purple-100 text-purple-500 rounded-full p-4">
            <i class="fas fa-file-alt text-2xl sm:text-3xl"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-gray-600 text-sm font-medium">Rapports d'activités</h3>
            <p class="text-xl font-semibold"></p>
          </div>
        </router-link>
      </div>

      <!-- Dernier bloc sur toute la largeur -->
      <div v-if="isAdmin || isChefService"class="bg-white shadow-md rounded-lg p-6 flex flex-col col-span-1 sm:col-span-2 lg:col-span-4 w-full h-auto mb-6">
        
        <div  class="grid grid-cols-2 sm:grid-cols-4 gap-4 ">
          <div class="flex-shrink-0 bg-purple-100 text-purple-500 rounded-full p-4">
            <p class="text-blue-500  text-2xl">DEPS</p>
            
          </div>
          <div class="flex-shrink-0 bg-purple-100 rounded-full p-4">
            <p class="text-2xl">     Activités validées {{ validatedActivitiesCount }} / {{ activiteCount }} </p>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>




<script>
import axios from 'axios';
export default {
  name: "Statistiques",
  data() {
    return {
      userCount: 0, 
      structureCount: 0,
      sessionCount: 0,
      activityCount: 0,
      activiteCount: 0,
      validatedActivitiesCount: 0,
      notificationsCount :0,
      userInfo: null,
      isAdmin: false,
      isInvite: false,
      isPointFocal: false,
      isChefService: false,
    };
  },
  methods: {
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
    this.userId = this.userInfo.id;

    // Ajouter le nombre de notifications non lues
    this.notificationsCount = this.userInfo.notificationsCount || 0;

  } catch (error) {
    console.error('Erreur lors de la récupération des informations utilisateur :', error);
  }
},
    // Méthode pour récupérer le nombre d'utilisateurs
    async fetchUserCount() {
      try {
        const response = await axios.get('/api/users/count');
        this.userCount = response.data.count; // Met à jour la valeur
      } catch (error) {
        console.error('Erreur lors de la récupération du nombre d’utilisateurs :', error);
      }
    },
    // Méthode pour récupérer le nombre de structures
    async fetchStructureCount() {
      try {
        const response = await axios.get('/api/structures/count');
        this.structureCount = response.data.count; // Met à jour la valeur
      } catch (error) {
        console.error('Erreur lors de la récupération du nombre de structures :', error);
      }
    },
    // Méthode pour récupérer le nombre de sessions d'activités
    async fetchSessionCount() {
      try {
        const response = await axios.get('/api/sessions-activites/count');
        this.sessionCount = response.data.count; // Met à jour la valeur
      } catch (error) {
        console.error('Erreur lors de la récupération du nombre de sessions d\'activités :', error);
      }
    },
    // Méthode pour récupérer le nombre d'activités
    async fetchActivityCount() {
      try {
        const response = await axios.get('/api/activites/count');
        this.activityCount = response.data.count; // Met à jour la valeur
      } catch (error) {
        console.error('Erreur lors de la récupération du nombre d\'activités :', error);
      }
    },
    // Méthode pour récupérer le nombre d'activités pour la session en cours
    async fetchActiviteCount() {
      try {
        const response = await axios.get('/api/activites/count-Ouvert');
        this.activiteCount = response.data.count; // Met à jour la valeur
      } catch (error) {
        console.error('Erreur lors de la récupération du nombre d\'activités :', error);
      }
    },
    // Méthode pour récupérer le nombre d'activités validées
    async fetchValidatedActivitiesCount() {
      try {
        const response = await axios.get('/api/activites/count-valide');
        this.validatedActivitiesCount = response.data.count; // Met à jour la valeur
      } catch (error) {
        console.error('Erreur lors de la récupération des données :', error);
      }
    },
   
  },
  mounted() {
    this.fetchUserCount();
    this.fetchUserInfo();
    this.fetchSessionCount();
    this.fetchActiviteCount();
    this.fetchActivityCount();
    this.fetchStructureCount();
    this.fetchValidatedActivitiesCount();
  },
};
</script>


<style scoped>

</style>
