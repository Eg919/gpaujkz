<template>
  <div class="flex flex-col h-screen">
    <!-- Header avec retour et sélection -->
    <div class="flex justify-between items-center w-full px-4 md:px-6 py-2 bg-gray-50 shadow-md mt-7">
      <!-- Retour -->
      <div class="flex items-center space-x-1">
        <router-link to="/admin" class="text-blue-500 flex items-center">
          <i class="fas fa-arrow-left text-lg"></i>
          <span class="ml-1 text-xs sm:text-sm md:text-base hidden sm:inline">Retour</span>
        </router-link>
      </div>

      <!-- Titre centralisé -->
      <div class="flex-grow text-center">
        <h1 class="text-red-500 font-semibold truncate">
          <span class="block text-xs sm:text-sm md:text-xl lg:text-4xl">
            Programmes d'Activités
          </span>
        </h1>
      </div>

      <!-- Sélecteur -->
      <div class="mx-2 sm:mx-4">
        <select
          id="selection"
          v-model="selectedOption"
          @change="handleSelection"
          class="px-2 sm:px-3 py-1 sm:py-2 bg-white border rounded-md shadow-sm focus:outline-none focus:ring focus:ring-blue-300 text-xs sm:text-sm md:text-base"
        >
          <option disabled value="">-- Sélectionner --</option>
          <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>
      </div>
    </div>

    <!-- Section avec défilement vertical -->
    <div class="flex-1 overflow-y-auto p-2 md:p-4 rounded-lg shadow-inner">
      <div v-if="currentComponent" class="w-full">
        <component :is="currentComponent" />
      </div>
      <div v-else class="text-center text-gray-500 py-8">
        <p>Sélectionnez une option pour afficher le contenu.</p>
      </div>
    </div>
  </div>
</template>
<script>
import GestionProgrammesActivites from "./GestionProgrammesActivites.vue";
import GestionMaticeDactivite from "../ComposantsGestionRapportsTrimestriels/GestionMaticeDactivite.vue";

export default {
  data() {
    return {
      selectedOption: "component-a", // Valeur initiale définissant le composant A comme sélectionné par défaut
      currentComponent: "component-a", // Composant affiché par défaut
      options: [
        { value: "component-a", label: "Programme d'activites" },
        { value: "component-b", label: "Matrice d'activite" },
       
      ],
    };
  },
  components: {
    "component-a": GestionProgrammesActivites,
    "component-b": GestionMaticeDactivite,
  },
  methods: {
    handleSelection() {
      this.currentComponent = this.selectedOption;
    },
  },
};
</script>

<style scoped>

</style>
