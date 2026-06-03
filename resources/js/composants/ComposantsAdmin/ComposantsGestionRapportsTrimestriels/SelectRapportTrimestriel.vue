<template>
  <div class="flex flex-col h-screen">
    
    <!-- Header Premium avec retour, Titre dynamique et sélection -->
    <div class="w-full bg-gray-50 shadow-md border-b border-gray-200 py-3 px-4 md:px-8 flex items-center mb-4 print:hidden">
      <!-- Retour -->
      <div class="w-1/4 flex items-center">
        <router-link to="/admin" class="text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-2" title="Retour à l'accueil">
          <i class="fas fa-arrow-left text-xl"></i>
          <span class="text-xs font-bold uppercase hidden md:inline">Retour</span>
        </router-link>
      </div>

      <!-- Titre Centralisé Dynamique -->
      <div class="w-2/4 text-center">
        <h1 class="text-xl md:text-2xl font-black text-amber-500 uppercase tracking-tighter truncate px-2">
          {{ currentTitle }}
        </h1>
      </div>

      <!-- Sélecteur -->
      <div class="w-1/4 flex justify-end items-center space-x-2">
        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest hidden lg:inline">Rapport :</span>
        <select
          id="selection"
          v-model="selectedOption"
          @change="handleSelection"
          class="px-2 sm:px-3 py-1.5 bg-white border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-xs sm:text-sm font-bold text-gray-600 outline-none transition-all cursor-pointer"
        >
          <option disabled value="">-- Sélectionner --</option>
          <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>
      </div>
    </div>

    <!-- Section avec défilement vertical -->
    <div class="flex-1 overflow-y-auto p-2 bg-gray-50/30">
      <div v-if="currentComponent" >
        <component :is="currentComponent" :standalone="false" />
      </div>
    </div>
  </div>
</template>

<script>
import GestoinRapport from "./GestoinRapport.vue";
import GestoinRapportTrimestriel1 from "./GestoinRapportTrimestriel1.vue";
import GestoinRapportTrimestriel2 from "./GestoinRapportTrimestriel2.vue";
import GestoinRapportTrimestriel3 from "./GestoinRapportTrimestriel3.vue";
import GestoinRapportTrimestriel4 from "./GestoinRapportTrimestriel4.vue";


export default {
  data() {
    return {
      selectedOption: "component", // Valeur initiale définissant le composant A comme sélectionné par défaut
      currentComponent: "component", // Composant affiché par défaut
      options: [
        { value: "component", label: "Rapport" },
        { value: "component-1", label: "Rapport 1er Trimestre " },
        { value: "component-2", label: "Rapport 2eme Trimestre" },
        { value: "component-3", label: "Rapport 3eme Trimestre" },
        { value: "component-4", label: "Rapport 4eme Trimestre" },
       
      ],
    };
  },
  components: {
    "component": GestoinRapport,
    "component-1": GestoinRapportTrimestriel1,
    "component-2": GestoinRapportTrimestriel2,
    "component-3": GestoinRapportTrimestriel3,
    "component-4": GestoinRapportTrimestriel4,
  },
  computed: {
    currentTitle() {
      const option = this.options.find(o => o.value === this.selectedOption);
      if (option) {
        if (this.selectedOption === 'component') return "Rapport Général des Activités";
        return option.label.trim();
      }
      return "Rapports d'Activités";
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
