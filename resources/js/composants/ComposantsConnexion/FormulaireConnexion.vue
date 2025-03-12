<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
    <!-- Formulaire principal -->
    <form 
      @submit.prevent="submitLogin" 
      class="bg-white p-4 sm:p-6 rounded-lg shadow-md max-w-md w-full max-h-[calc(100vh-4rem)] overflow-y-auto relative"
    >
      <!-- Titre -->
      <h2 class="text-xl sm:text-2xl font-bold mb-4 text-center">Connexion</h2>

      <!-- Message d'erreur -->
      <div 
        v-if="errorMessage" 
        class="mb-4 text-red-500 text-sm sm:text-base text-center"
      >
        {{ errorMessage }}
      </div>

      <!-- Champ Email -->
      <div class="mb-4">
        <label 
          for="email" 
          class="block text-sm sm:text-base font-medium text-gray-700 mb-2"
        >
          Email
        </label>
        <input
          v-model="form.email"
          type="email"
          id="email"
          required
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
          placeholder="exemple@domaine.com"
        />
        <span 
          v-if="errors.email" 
          class="text-red-500 text-xs sm:text-sm mt-1"
        >
          {{ errors.email[0] }}
        </span>
      </div>

      <!-- Champ Mot de passe -->
      <div class="mb-4">
        <label 
          for="password" 
          class="block text-sm sm:text-base font-medium text-gray-700 mb-2"
        >
          Mot de passe
        </label>
        <input
          v-model="form.password"
          type="password"
          id="password"
          required
          class="mt-1 block w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm sm:text-base"
          placeholder="********"
        />
        <span 
          v-if="errors.password" 
          class="text-red-500 text-xs sm:text-sm mt-1"
        >
          {{ errors.password[0] }}
        </span>
      </div>

      <!-- Bouton de soumission -->
      <button
        type="submit"
        class="w-full bg-green-700 text-white p-2 sm:p-3 rounded-lg hover:bg-green-600 transition duration-200"
      >
        Se connecter
      </button>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      form: {
        email: '',
        password: '',
      },
      errors: {},
      errorMessage: '',
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    };
  },
  methods: {
    async submitLogin() {
      this.errors = {};
      this.errorMessage = '';

      if (!this.form.email) {
        this.errors.email = ["L'email est requis."];
      } else if (!/\S+@\S+\.\S+/.test(this.form.email)) {
        this.errors.email = ["L'email n'est pas valide."];
      }

      if (!this.form.password) {
        this.errors.password = ["Le mot de passe est requis."];
      }

      if (Object.keys(this.errors).length === 0) {
        try {
          const response = await axios.post('/api/login', this.form, {
            headers: {
              'X-CSRF-TOKEN': this.csrfToken,
            },
          });
          this.$emit('submitLogin');
          if (response.data.redirect) {
            this.$router.push(response.data.redirect);
            this.fermerFormulaire();
          } else {
            this.errorMessage = response.data.message || "Erreur inconnue.";
          }
        } catch (error) {
          if (error.response && error.response.data && error.response.data.message) {
            this.errorMessage = error.response.data.message;
          } else {
            this.errorMessage = "Une erreur est survenue. Veuillez réessayer.";
          }
          console.error(error);
        }
      }
    },
    fermerFormulaire() {
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
.absolute {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
}
</style>
