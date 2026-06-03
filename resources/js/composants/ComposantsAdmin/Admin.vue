<template>
  <div class="flex flex-col h-screen overflow-hidden print:h-auto print:overflow-visible">
    <!-- Barre de navigation en haut -->
    <BarreDeNavigation class="print:hidden" />

    <!-- Vue principale rendue par le routeur avec scroll vertical -->
    <main class="flex-grow relative pt-14 overflow-y-auto custom-scrollbar bg-gray-50/50 print:pt-0 print:overflow-visible print:h-auto">
      <router-view v-slot="{ Component }">
        <component :is="Component" v-if="Component" />
        <Bienvenue v-else />
      </router-view>
    </main>

    <!-- Bas de page en bas -->
    <BasDePage class="print:hidden" />
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
/* Scrollbar personnalisée pour le conteneur principal */
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #fbbf24; /* Couleur amber pour matcher le thème */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #f59e0b;
}

/* Fallback Firefox */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #fbbf24 #f1f1f1;
}
</style>