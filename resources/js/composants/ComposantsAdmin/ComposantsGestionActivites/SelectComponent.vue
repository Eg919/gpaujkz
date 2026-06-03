<template>
  <div class="flex flex-col h-screen">
    <!-- Header avec retour et sélection -->
    <!-- Header avec retour, Titre dynamique et sélection -->
    <div class="w-full bg-gray-50 shadow-md border-b border-gray-200 py-3 px-4 md:px-8 flex items-center mb-4 print:hidden">
      <!-- Retour -->
      <div class="w-1/4 flex items-center">
        <router-link to="/admin" class="text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-2">
          <i class="fas fa-arrow-left text-xl"></i>
          <span class="text-xs font-bold uppercase hidden md:inline">Retour</span>
        </router-link>
      </div>

      <!-- Titre Centralisé Dynamique -->
      <div class="w-2/4 text-center">
        <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter truncate">
          {{ currentTitle }}
        </h1>
      </div>

      <!-- Sélecteur -->
      <div class="w-1/4 flex justify-end items-center space-x-2">
        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest hidden lg:inline">Vue :</span>
        <select
          id="selection"
          v-model="selectedOption"
          @change="handleSelection"
          class="px-2 sm:px-3 py-1.5 bg-white border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-xs sm:text-sm font-bold text-gray-600 outline-none transition-all"
        >
          <option disabled value="">-- Sélectionner --</option>
          <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>
      </div>
    </div>

    <!-- Section avec défilement vertical -->
    <div class="flex-1 overflow-y-auto p-2 md:p-4 bg-gray-50/30">
      <div v-if="currentComponent" class="w-full">
        <component :is="currentComponent" :standalone="false" />
      </div>
      <div v-else class="text-center text-gray-500 py-8">
        <p>Sélectionnez une option pour afficher le contenu.</p>
      </div>
    </div>
  </div>
</template>
<script>
import GestionProgrammesActivites from "./GestionProgrammesActivites.vue";
import GestionMatriceDactivite from "../ComposantsGestionRapportsTrimestriels/GestionMatriceDactivite.vue";

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
    "component-b": GestionMatriceDactivite,
  },
  computed: {
    currentTitle() {
      const option = this.options.find(o => o.value === this.selectedOption);
      return option ? option.label : "Gestion des Activités";
    }
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
