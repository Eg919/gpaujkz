<template>
  <div class="w-full sm:w-1/2 md:w-1/4">
    <h2 class="text-2xl font-semibold mb-4">Notifications d'activités</h2>
    <!-- Conteneur avec défilement et taille adaptative selon la taille de l'écran -->
    <div class="max-h-64 sm:max-h-80 md:max-h-96 overflow-y-auto">
      <ul>
        <li v-for="notification in notifications" :key="notification.id" class="mb-4">
          <!-- Bulle de notification bleue -->
          <div class="bg-blue-100 p-4 rounded-lg shadow-md">
            <p class="text-blue-900">{{ notification.message }}</p>
            <small class="text-sm text-gray-500">{{ notification.created_at }}</small>
            <div class="mt-2">
              <button
                @click="marquerCommeLue(notification.id)"
                class="text-blue-500 hover:text-blue-700 focus:outline-none"
              >
                Marquer comme lue
              </button>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>


<script>
import axios from 'axios';

export default {
  data() {
    return {
      notifications: [],
      notificationsCount: 0,
    };
  },
  async created() {
    this.fetchNotifications();
  },
  methods: {
    async fetchNotifications() {
      try {
        const response = await axios.get('/api/notifications/non-lues', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        });
        this.notifications = response.data.notifications;
        this.notificationsCount = response.data.notificationsCount;
      } catch (error) {
        console.error('Erreur lors de la récupération des notifications :', error);
      }
    },

    async marquerCommeLue(notificationId) {
      try {
        await axios.put(`/api/notifications/${notificationId}/lue`, {}, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        });
        this.notifications = this.notifications.filter(
          notification => notification.id !== notificationId
        );
        this.fetchNotifications();
        this.notificationsCount--;
        this.$emit('marquerCommeLue');
      } catch (error) {
        console.error('Erreur lors du marquage de la notification :', error);
      }
    }
  }
};
</script>

<style scoped>
/* Vous pouvez ajouter des styles supplémentaires ici si nécessaire */
</style>
