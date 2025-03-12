<!-- Start of Selection -->
<template>
    <div class="gestion-historiques">
      <onglets-navigation class="mt-5" :onglets="onglets"></onglets-navigation>
      
    </div>
  </template>
  
  <script>
  //import OngletsNavigation from '../ComposantsDeBase/OngletsNavigation.vue';
  
  export default {
    name: 'GestionHistoriques',
    components: {
      //OngletsNavigation,
    },
    data() {
      return {
        onglets: [
          { chemin: '/statistiques', label: 'Statistiques', icone: 'fas fa-history' },
          { chemin: '/rapports-trimestriels', label: 'Rapports', icone: 'fas fa-history' },
        ],
       
      };
    },
    methods: {
      
    },
  };
  </script>
  
  <style scoped>
  .gestion-historiques {
    padding: 20px;
  }
  .historique-item {
    margin-bottom: 10px;
  }
  </style>
  <!-- End of Selection -->
  