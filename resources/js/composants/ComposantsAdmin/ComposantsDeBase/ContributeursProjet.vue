<template>
    <div class="contributeurs-container p-6 bg-gray-100 mt-7">
      <h2 class="titre text-2xl font-bold text-center mb-6 text-gray-800">Contributeurs du Projet</h2>
  
      <!-- Liste des contributeurs avec défilement vertical -->
      <div class="contributeurs-list h-96 overflow-y-auto">
        <div class="flex flex-wrap justify-center gap-4">
          <!-- Boucle sur la liste des contributeurs -->
          <div
            v-for="contributeur in contributeurs"
            :key="contributeur.id"
            class="contributeur-card bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 w-72"
          >
            <!-- Image du contributeur -->
            <div class="contributeur-image">
              <img
                :src="contributeur.image"
                :alt="`Photo de ${contributeur.prenom} ${contributeur.nom}`"
                class="w-full h-48 object-cover"
              />
            </div>
  
            <!-- Informations du contributeur -->
            <div class="contributeur-info p-4 text-center">
              <p class="nom-prenom text-xl font-semibold text-gray-800">
                {{ contributeur.prenom }} {{ contributeur.nom }}
              </p>
              <p class="role text-lg text-gray-600">{{ contributeur.role }}</p>
              <p class="email text-gray-700">
                <i class="fas fa-envelope mr-2 text-blue-500"></i>
                {{ contributeur.email }}
              </p>
              <p class="telephone text-gray-700">
                <i class="fas fa-phone mr-2 text-blue-500"></i>
                {{ contributeur.telephone }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'ContributeursProjet',
    data() {
      return {
        // Liste des contributeurs (exemple)
        contributeurs: [
          {
            id: 1,
            image: 'images/ujkz.jpg', // URL de l'image
            nom: 'DA',
            prenom: 'Sié Egis',
            role: 'Consepteur et Développeur de GPAUJKZ (Stagire à la DSI  du 8 Aûte au 7 Novembre 2024) ',
            email: 'egisroi@gmail.com',
            telephone: '+226 70 78 76 11',
          },
          {
            id: 2,
            image: 'images/ujkz.jpg',
            nom: 'KONATE ',
            prenom: 'Mohamadou',
            role: "Responsable Pôle d'excellence Africa Multiple (PEA/UJKZ) : Maitre de stage ",
            email: '',
            telephone: '+226 71 84 48 83',
          },
          {
            id: 3,
            image: 'images/ujkz.jpg',
            nom: 'TINDANO ',
            prenom: 'Olivier',
            role: 'Enseignant à l’ESTA : Professeur de suivi',
            email: 'tindanolivier83@gmail.com',
            telephone: '+226 66 92 23 22',
          },
          
          {
            id: 4,
            image: 'images/ujkz.jpg',
            nom: 'ROUAMBA ',
            prenom: 'Salifou',
            role: "Docteur en Sciences du Langages, Directeur des Etudes, de la Planification et des Statistiques de l'UJKZ ",
            email: '',
            telephone: '+226 70 04 25 33',
          },
          {
            id: 5,
            image: 'images/ujkz.jpg', // URL de l'image
            nom: 'YANOGO',
            prenom: 'Jean Armand',
            role: 'Directeur des Services Informatiques de l’Université Joseph KI ZERBO (UJKZ) ',
            email: 'jeanarmand.yanogo@ujkz.bf',
            telephone: '+226 70 24 57 72',
          },
          {
            id: 6,
            image: 'images/ujkz.jpg',
            nom: 'KONSIMBO',
            prenom: '',
            role: '',
            email: 'konsimbo@ujkz.bf',
            telephone: '+226 76 54 25 52',
          },
          {
            id: 5,
            image: 'images/ujkz.jpg', // URL de l'image
            nom: 'GUIRA  ',
            prenom: 'Seydou',
            role: 'chef de service Planification et Coordination des projets et Programmes à la DEPS (UJKZ) ',
            email: '',
            telephone: '+226 71 27 30 92',
          },
          {
            id: 6,
            image: 'images/ujkz.jpg',
            nom: 'PACODE',
            prenom: ' Salifou',
            role: 'chef de service Statistiques, Archives et Documentation à la DEPS (UJKZ)',
            email: '',
            telephone: '+226 ',
          },
        ],
      };
    },
  };
  </script>
  
  <style scoped>
  /* Styles spécifiques si nécessaire */
  </style>