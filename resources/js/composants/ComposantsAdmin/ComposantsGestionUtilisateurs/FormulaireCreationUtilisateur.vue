<template>
  <div class="formulaire-creation-utilisateur fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 mt-3">
    <!-- Formulaire principal -->
    <form 
      @submit.prevent="submitForm" 
      class="bg-white p-4 sm:p-6 rounded-lg shadow-md w-full sm:w-1/3 max-h-[calc(101vh-4rem)] overflow-y-auto relative"
    >
      <!-- Bouton de fermeture -->
      <button 
        @click.prevent="fermerFormulaire" 
        type="button" 
        class="absolute top-4 right-4 text-red-500 hover:text-red-700 focus:outline-none"
      >
        <i class="fas fa-times"></i>
      </button>

      <!-- Titre -->
      <h2 class="text-xl sm:text-2xl font-bold mb-4 text-center">Créer un Utilisateur</h2>

      <!-- Message de succès -->
      <div v-if="successMessage" class="mt-4 text-green-500 p-2 rounded bg-green-100 mb-4">
        {{ successMessage }}
      </div>

      <!-- Champ Nom -->
      <div class="mb-4">
        <label for="nom" class="block text-sm sm:text-base font-medium mb-1">Nom :</label>
        <input 
          type="text" 
          v-model="form.nom" 
          placeholder ="veillez entrer votre nom"
          required 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
        />
        <span v-if="errors.nom" class="text-red-500 text-xs sm:text-sm">{{ errors.nom }}</span>
      </div>

      <!-- Champ Prénom -->
      <div class="mb-4">
        <label for="prenom" class="block text-sm sm:text-base font-medium mb-1">Prénom :</label>
        <input 
          type="text" 
          v-model="form.prenom" 
          placeholder ="veillez entrer le prénom"
          required 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
        />
        <span v-if="errors.prenom" class="text-red-500 text-xs sm:text-sm">{{ errors.prenom }}</span>
      </div>

      <!-- Champ Rôle -->
      <div class="mb-4">
        <label for="role" class="block text-sm sm:text-base font-medium mb-1">Rôle :</label>
        <select 
          v-model="form.role" 
          required 
          placeholder ="veillez selectioner le rôle"
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
        >
          <option v-if="isAdmin" value="Administrateur">Administrateur</option>
          <option v-if="isAdmin" value="Chef-de-service">Chef-de-service</option>
          <option v-if="isAdmin" value="Responsable-de-structure">Responsable-de-structure</option>
          <option  value="Point-Focale">Point Focale</option>
          <option v-if="isAdmin" value="Ordonateur">Ordonateur</option>
        </select>
        <span v-if="errors.role" class="text-red-500 text-xs sm:text-sm">{{ errors.role }}</span>
      </div>

      <!-- Champ Structure -->
      <div class="mb-4">
        <label for="structure" class="block text-sm sm:text-base font-medium mb-1">Structure :</label>
        <select 
          v-model="form.structure_id" 
          placeholder ="veillez selectioner la structure"
          required 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base overflow-y-auto"
        >
          <option 
            v-for="structure in structures" 
            :key="structure.id" 
            :value="structure.id"
          >
            {{ structure.sigle }}
          </option>
        </select>
        <span v-if="errors.structure_id" class="text-red-500 text-xs sm:text-sm">{{ errors.structure_id }}</span>
      </div>

      <!-- Champ Email -->
      <div class="mb-4">
        <label for="email" class="block text-sm sm:text-base font-medium mb-1">Email :</label>
        <input 
          type="email" 
          v-model="form.email" 
          placeholder ="veillez entrer le mail"
          required 
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
        />
        <span v-if="errors.email" class="text-red-500 text-xs sm:text-sm">{{ errors.email }}</span>
      </div>

      <!-- Bouton de soumission -->
      <button 
        type="submit" 
        class="w-full bg-green-500 text-yellow-700 p-2 sm:p-3 rounded-lg hover:bg-green-700 mt-3"
      >
        Créer un Utilisateur
      </button>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return {
      structures: [],
      form: {
        nom: '',
        prenom: '',
        email: '',
        role: '',
        etat: 'actif',
        structure_id: '',
      
      },
      errors: {},
      successMessage: '', // Ajoutez cette ligne
      isAdmin: false,
      isInvite: false,
      isPointFocal: false,
      isChefService: false,
    };
  },
  mounted() {
    this.fetchStructures();
    this.fetchUserInfo();
  },
  methods: {
    async fetchUserInfo() {
  try {
    const response = await axios.get('/api/user-info', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}` 
      }
    });

    this.userInfo = response.data;
    this.isAdmin = this.userInfo.role === 'Administrateur';
    this.isInvite = this.userInfo.role === 'Ordonateur';
    this.isPointFocal = this.userInfo.role === 'Point-Focale';
    this.isChefService = this.userInfo.role === 'Chef-de-service';
    this.userId = this.userInfo.id;


  } catch (error) {
    console.error('Erreur lors de la récupération des informations utilisateur :', error);
  }
},
    async fetchStructures() {
      try {
        const response = await fetch('/api/structures');
        if (!response.ok) throw new Error('Erreur lors de la récupération des structures');
        this.structures = await response.json();
      } catch (error) {
        console.error(error);
      }
    },
    async submitForm() {
      this.errors = {};
      this.successMessage = ''; // Réinitialiser le message de succès
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      try {
        const response = await fetch('/api/utilisateurs', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify(this.form),
        });
        this.$emit('submitForm');
        if (!response.ok) {
          const errorData = await response.json();
          this.errors = errorData.errors || {};
          if (Object.keys(this.errors).length === 0) {
            this.errors.general = 'Une erreur est survenue lors de la création de l’utilisateur.';
          }
          throw new Error('Erreur lors de la création de l’utilisateur');
        }

        this.successMessage = 'Utilisateur créé avec succès!'; // Message de succès
        setTimeout(() => {
          this.successMessage = ''; // Masquer le message après 2 secondes
          this.fermerFormulaire(); // Fermer le formulaire
        }, 1000); // 1 secondes
      } catch (error) {
        console.error(error);
      }
    },
    fermerFormulaire() {
      this.$emit('close');
      console.log('Formulaire fermé');
    },
  },
};
</script>

<style scoped>
.formulaire-creation-utilisateur {
  padding: 20px;
}
.text-red-500 {
  color: red;
}
.text-green-500 {
  color: green;
}
</style>
