<template>
  <div class="flex flex-col h-screen">
    
    <!-- Header avec retour et sélection -->
    <div class="flex justify-between items-center w-full mx-6 mt-7">
      <div>
        <router-link to="/admin" class="text-blue-500" title="Retour à l'accueil">
          <i class="fas fa-arrow-left"></i>
          <span class="ml-2">Retour</span>
        </router-link>
      </div>
      <div>
        <h1 class="my-4 text-red-500 text-4xl font-semibold">Rapports d'Activités</h1>
      </div>
      <div class="mx-10">
        <select
          id="selection"
          v-model="selectedOption"
          @change="handleSelection"
          class="px-3 py-2 bg-white border rounded-md shadow-sm focus:outline-none focus:ring focus:ring-blue-300"
        >
          <option disabled value="">-- Sélectionner --</option>
          <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>
      </div>
    </div>

    <!-- Section avec défilement vertical -->
    <div class="flex-1 overflow-y-auto p-2  rounded-lg shadow-inner">
      <div v-if="currentComponent" >
        <component :is="currentComponent" />
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
  methods: {
    handleSelection() {
      this.currentComponent = this.selectedOption;
    },
  },
};
</script>

<style scoped>

</style>
