<template>
  <div class="flex flex-col min-h-screen">
    <!-- Barre de navigation en haut -->
    <BarreDeNavigation />

    <!-- Vue principale rendue par le routeur -->
    <main class="flex-grow relative">
      <router-view v-slot="{ Component }">
        <component :is="Component" v-if="Component" />
        <Bienvenue v-else />
      </router-view>
    </main>

    <!-- Bas de page en bas -->
    <BasDePage />
  </div>
</template>

<script>
import BarreDeNavigation from './ComposantsDeBase/BarreDeNavigation.vue';
import BasDePage from './ComposantsDeBase/BasDePage.vue';
import Bienvenue from './ComposantsDeBase/Bienvenue.vue';

export default {
  name: 'Admin',
  components: {
    BarreDeNavigation,
    BasDePage,
    Bienvenue,
  },
};
</script>


<style scoped>
.main {
  padding: 1rem;
}
</style>