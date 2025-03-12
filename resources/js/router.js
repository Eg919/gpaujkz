import { createRouter, createWebHashHistory } from 'vue-router'; // Utilisation du mode hash
// Importation des composants
import Admin from './composants/ComposantsAdmin/Admin.vue';
import GestionStructures from './composants/ComposantsAdmin/ComposantsGestionStructures/GestionStructures.vue';
import GestionUtilisateurs from './composants/ComposantsAdmin/ComposantsGestionUtilisateurs/GestionUtilisateurs.vue';
import GestionPlanStrategiques from './composants/ComposantsAdmin/ComposantsGestionPlanStrategiques/GestionPlanStrategiques.vue';
import GestionSessionsActivites from './composants/ComposantsAdmin/ComposantsGestionSessionsActivites/GestionSessionsActivites.vue';
import FormulaireConnexion from './composants/ComposantsConnexion/FormulaireConnexion.vue';
import FormulaireChangerMotDePasse from './composants/ComposantsConnexion/FormulaireChangerMotDePasse.vue';
import GestionCanevasActivites from './composants/ComposantsAdmin/ComposantsGestionActivites/GestionCanevasActivites.vue';
import GestionProjetsActivites from './composants/ComposantsAdmin/ComposantsGestionActivites/GestionProjetsActivites.vue';
import GestionProgrammesActivites from './composants/ComposantsAdmin/ComposantsGestionActivites/GestionProgrammesActivites.vue';
import GestionProgrammesActivitesStructure from './composants/ComposantsAdmin/ComposantsGestionActivites/GestionProgrammesActivitesStructure.vue';
import GestionProgrammesActivitesHistorique from './composants/ComposantsAdmin/ComposantsGestionActivites/GestionProgrammesActivitesHistorique.vue';
import GestionActivites from './composants/ComposantsAdmin/ComposantsGestionActivites/GestionActivites.vue';
import AdminStat from './composants/ComposantsAdmin/ComposantsDeBase/AdminStat.vue';
import ContributeursProjet from './composants/ComposantsAdmin/ComposantsDeBase/ContributeursProjet.vue';
import GestionDesStatistiques from './composants/ComposantsAdmin/ComposantsGestionRapportsTrimestriels/GestionDesStatistiques.vue';
import GestoinRapport from './composants/ComposantsAdmin/ComposantsGestionRapportsTrimestriels/GestoinRapport.vue';
import GestoinRapportStructure from './composants/ComposantsAdmin/ComposantsGestionRapportsTrimestriels/GestoinRapportStructure.vue';
import GestoinRapportTrimestriel1 from './composants/ComposantsAdmin/ComposantsGestionRapportsTrimestriels/GestoinRapportTrimestriel1.vue';
import GestoinRapportTrimestriel2 from './composants/ComposantsAdmin/ComposantsGestionRapportsTrimestriels/GestoinRapportTrimestriel2.vue';
import GestoinRapportTrimestriel3 from './composants/ComposantsAdmin/ComposantsGestionRapportsTrimestriels/GestoinRapportTrimestriel3.vue';
import GestoinRapportTrimestriel4 from './composants/ComposantsAdmin/ComposantsGestionRapportsTrimestriels/GestoinRapportTrimestriel4.vue';
import SelectRapportTrimestriel from './composants/ComposantsAdmin/ComposantsGestionRapportsTrimestriels/SelectRapportTrimestriel.vue';
import SelectComponent from './composants/ComposantsAdmin/ComposantsGestionActivites/SelectComponent.vue';
import GestionActiviteHortProgramme from './composants/ComposantsAdmin/ComposantsGestionActivites/GestionActiviteHortProgramme.vue';
const routes = [
  
  {
    path: '/admin',
    name:' Admin',
    component: Admin,
    redirect: '/adminstat', // Redirection automatique
    children: [
      {
        path: '/adminstat', // Route par défaut
        component: AdminStat,
      },
    ],
  },
  {
    path: '/activite-hort-programme',
    name:'GestionActiviteHortProgramme',
    component: GestionActiviteHortProgramme,
    
  },
  {
    path: '/contributeursProjet',
    name:' ContributeursProjet',
    component: ContributeursProjet ,
    
  },
 
  {
    path: '/select-component',
    name:'SelectComponent',
   component: SelectComponent,
    
  },
  {
    path: '/select-rapport',
    name:'SelectRapportTrimestriel',
    component:SelectRapportTrimestriel,
  },
  {
    path: '/rapport',
    name:'GestoinRapport',
    component: GestoinRapport,
  },
  {
    path: '/rapport-structure',
    name:'GestoinRapportStructure',
    component: GestoinRapportStructure,
  },

  {
    path: '/rapports-trimestriels',
    name:'GestoinRapportTrimestriel1',
    component:GestoinRapportTrimestriel1,
  },
  {
    path: '/rapports-trimestriels',
    name:'GestoinRapportTrimestriel2',
    component:GestoinRapportTrimestriel2,
  },
  {
    path: '/rapports-trimestriels',
    name:'GestoinRapportTrimestriel3',
    component:GestoinRapportTrimestriel3,
  },
  {
    path: '/rapports-trimestriels',
    name:'GestoinRapportTrimestriel4',
    component:GestoinRapportTrimestriel4,
  },
  {
    path: '/statistiques',
    name:'GestionDesStatistiques',
    component: GestionDesStatistiques,
  },
  {
    path: '/adminstat',
    name: 'AdminStat',
    component: AdminStat,
    
  },
 
  {
    path: '/structures',
    name: 'GestionStructures',
    component: GestionStructures,
  },
  
  {
    path: '/utilisateurs',
    name: 'GestionUtilisateurs',
    component: GestionUtilisateurs,
  },
  {
    path: '/plans-strategiques',
    name: 'GestionPlanStrategiques',
    component: GestionPlanStrategiques,
  },
  {
    path: '/projets-activites',
    name: 'GestionProjetsActivites',
    component: GestionProjetsActivites,
  },
  {
    path: '/programmes-activites',
    name: 'GestionProgrammesActivites',
    component: GestionProgrammesActivites,
  },
  {
    path: '/programmes-activites-structure',
    name: 'GestionProgrammesActivitesStructure',
    component: GestionProgrammesActivitesStructure,
  },
  {
    path: '/programmes-activites-historique/:id',
    name: 'GestionProgrammesActivitesHistorique',
    component: GestionProgrammesActivitesHistorique,
  },
  
  {
    path: '/sessions-activites/:id?',
    name: 'GestionSessionsActivites',
    component: GestionSessionsActivites,
  },
  {
    path: '/suivi-activites/:id',
    name: 'GestionActivites',
    component: GestionActivites,
    props: true,
  },
  {
    path: '/login',
    name: 'FormulaireConnexion',
    component: FormulaireConnexion,
  },
  {
    path: '/changer-mot-de-passe',
    name: 'FormulaireChangerMotDePasse',
    component: FormulaireChangerMotDePasse,
  },
  {
    path: '/canevas-activites',
    name: 'GestionCanevasActivites',
    component: GestionCanevasActivites,
   
  },
  {
    path: '/programmes-activites/:id?',
    name: 'GestionProgrammesActivites',
    component: GestionProgrammesActivites,
    props: true,
  },
];

const router = createRouter({
  history: createWebHashHistory(), // Mode hash activé ici
  routes,
});


export default router;
