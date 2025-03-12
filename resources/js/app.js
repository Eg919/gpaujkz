import { createApp } from 'vue';
import router from './router'; // Assurez-vous d'avoir configuré Vue Router
import '../css/app.css'; // Styles globaux
import Toast from "vue-toastification"; // Importation de Vue Toastification
import Chart from "chart.js/auto";
import ChartDataLabels from "chartjs-plugin-datalabels";
import "vue-toastification/dist/index.css"; // Importation du style Toastification
import Admin from './composants/ComposantsAdmin/Admin.vue'; // Importation du composant principal Admin
import ChartComponent from './composants/ComposantsAdmin/ComposantsGestionActivites/GestionActivites.vue';
// Enregistrement du plugin ChartDataLabels
Chart.register(ChartDataLabels);
// Créer l'application Vue.js
createApp(Admin)
  .use(Toast) // Utiliser le plugin Toastification pour les notifications
  .use(router) // Utiliser Vue Router pour la gestion des routes
  .component('chart-component', ChartComponent)
  .mount('#app'); // Monter l'application sur l'élément ayant l'ID 'app'
  
