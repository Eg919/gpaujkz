<template>
  <!-- Formulaire de changement de mot de passe -->
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
    <div 
      class="bg-white p-4 sm:p-6 rounded shadow-md w-full max-w-md max-h-[calc(100vh-4rem)] overflow-y-auto relative"
    >
      <!-- Bouton de fermeture -->
      <!-- <button 
        @click.prevent="$emit('close')" 
        type="button" 
        class="absolute top-2 right-2 text-red-500 hover:text-red-700 focus:outline-none"
      >
        <i class="fas fa-times"></i>
      </button> -->

      <!-- Titre -->
      <h2 class="text-xl sm:text-2xl font-semibold text-center mb-4">Changer le mot de passe</h2>

      <!-- Formulaire -->
      <form @submit.prevent="submitForm">
        <!-- Champ de saisie du mot de passe actuel -->
        <div class="mb-4">
          <label 
            class="block text-sm sm:text-base text-gray-700 font-medium mb-2" 
            for="current_password"
          >
            Mot de passe actuel
          </label>
          <input
            v-model="form.current_password"
            type="password"
            id="current_password"
            class="w-full px-3 sm:px-4 py-2 border rounded-lg text-sm sm:text-base"
            required
          />
        </div>

        <!-- Champ de saisie du nouveau mot de passe -->
        <div class="mb-4">
          <label 
            class="block text-sm sm:text-base text-gray-700 font-medium mb-2" 
            for="new_password"
          >
            Nouveau mot de passe
          </label>
          <input
            v-model="form.new_password"
            type="password"
            id="new_password"
            class="w-full px-3 sm:px-4 py-2 border rounded-lg text-sm sm:text-base"
            required
          />
        </div>

        <!-- Champ de confirmation du nouveau mot de passe -->
        <div class="mb-4">
          <label 
            class="block text-sm sm:text-base text-gray-700 font-medium mb-2" 
            for="new_password_confirmation"
          >
            Confirmer le nouveau mot de passe
          </label>
          <input
            v-model="form.new_password_confirmation"
            type="password"
            id="new_password_confirmation"
            class="w-full px-3 sm:px-4 py-2 border rounded-lg text-sm sm:text-base"
            required
          />
        </div>

        <!-- Bouton de soumission -->
        <button
          type="submit"
          class="w-full bg-green-700 text-white px-4 py-2 rounded hover:bg-green-600 sm:hover:bg-green-500 mt-3"
        >
          Changer le mot de passe
        </button>
      </form>

      <!-- Message de confirmation -->
      <p 
        v-if="message" 
        class="mt-4 text-center text-green-500 text-sm sm:text-base"
      >
        {{ message }}
      </p>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      form: {
        current_password: "",
        new_password: "",
        new_password_confirmation: "",
      },
      message: "",
    };
  },
  methods: {
    async submitForm() {
      try {
        const response = await axios.post("/api/change-password", this.form);
        this.message = response.data.message;
        setTimeout(() => {
          this.$router.push("/admin");
        }, 2000);
      } catch (error) {
        this.message = error.response.data.message || "Une erreur est survenue.";
      }
    },
  },
};
</script>
