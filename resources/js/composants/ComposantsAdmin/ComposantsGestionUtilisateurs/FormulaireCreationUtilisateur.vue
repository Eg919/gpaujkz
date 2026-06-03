<template>
  <div class="fixed inset-0 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm z-[2000]">
    <!-- Main Modal Card -->
    <div class="relative w-full max-w-lg mx-4">
      
      <form 
        @submit.prevent="submitForm" 
        class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200"
      >
        <!-- Header -->
        <div class="bg-gray-50 border-b border-gray-200 py-6 px-8 flex items-center justify-between">
          <div>
            <h2 class="text-xl font-black text-amber-500 uppercase tracking-tighter">Nouvel Utilisateur</h2>
            <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-widest">Accès et Habilitations</p>
          </div>
          <button @click.prevent="fermerFormulaire" class="text-gray-400 hover:text-rose-500 transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <div class="p-8 max-h-[70vh] overflow-y-auto">
          <!-- Status Messages -->
          <div v-if="successMessage" class="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-700 p-3 rounded-lg text-xs font-bold flex items-center gap-2">
            <i class="fas fa-check-circle"></i> {{ successMessage }}
          </div>
          <div v-if="errors.general" class="mb-6 bg-rose-50 border border-rose-100 text-rose-700 p-3 rounded-lg text-xs font-bold flex items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i> {{ errors.general }}
          </div>

          <div class="space-y-5">
            <!-- Email Field -->
            <div>
              <label class="block text-[11px] font-black uppercase text-gray-500 tracking-wider mb-1.5">Adresse Email</label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-amber-400 focus:bg-white transition-all outline-none"
                placeholder="nom.prenom@ujkz.bf"
              />
              <p v-if="errors.email" class="mt-1.5 text-[10px] font-bold text-rose-600">{{ errors.email }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <!-- Role Field -->
              <div>
                <label class="block text-[11px] font-black uppercase text-gray-500 tracking-wider mb-1.5">Rôle</label>
                <select
                  v-model="form.role"
                  required
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-amber-400 transition-all outline-none"
                >
                  <option value="">Sélectionner...</option>
                  <option value="Administrateur">Administrateur DEPS</option>
                  <option value="Chef-de-service">Chef de service</option>
                  <option value="Responsable-de-structure">Responsable</option>
                  <option value="Point-Focale">Point Focal</option>
                  <option value="Ordonnateur">Ordonnateur</option>
                  <option value="Administrateur_DSI">Admin DSI</option>
                  <option value="Planificateur">Planificateur</option>
                </select>
              </div>

              <!-- State Field -->
              <div>
                <label class="block text-[11px] font-black uppercase text-gray-500 tracking-wider mb-1.5">État Initial</label>
                <select
                  v-model="form.etat"
                  required
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-amber-400 transition-all outline-none"
                >
                  <option value="Actif">Actif</option>
                  <option value="Inactif">Inactif</option>
                </select>
              </div>
            </div>

            <!-- Structure Field -->
            <div>
              <label class="block text-[11px] font-black uppercase text-gray-500 tracking-wider mb-1.5">Structure de rattachement</label>
              <select
                v-model="form.structure_id"
                required
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-amber-400 transition-all outline-none"
              >
                <option value="">Veuillez sélectionner...</option>
                <option v-for="structure in structures" :key="structure.id" :value="structure.id">
                  {{ structure.sigle }} - {{ structure.libelle_structure }}
                </option>
              </select>
              <p v-if="errors.structure_id" class="mt-1.5 text-[10px] font-bold text-rose-600">{{ errors.structure_id }}</p>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="bg-gray-50 border-t border-gray-200 py-6 px-8 flex items-center justify-end gap-3">
          <button 
            type="button"
            @click.prevent="fermerFormulaire"
            class="px-6 py-2.5 text-gray-500 font-bold text-xs uppercase tracking-widest hover:text-gray-800 transition-colors"
          >
            Annuler
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-8 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs uppercase tracking-widest shadow-lg shadow-emerald-100 transition-all active:scale-95 disabled:opacity-50"
          >
            {{ loading ? 'En cours...' : 'Créer' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return {
      structures: [],
      form: {
        email: '',
        role: '',
        etat: 'Actif',
        structure_id: '',
      },
      errors: {},
      successMessage: '',
      loading: false,
    };
  },
  mounted() {
    this.fetchStructures();
  },
  methods: {
    async fetchStructures() {
      try {
        const response = await axios.get('/api/structures');
        this.structures = response.data;
      } catch (error) {
        console.error(error);
      }
    },
    async submitForm() {
      if (this.loading) return;
      this.errors = {};
      this.successMessage = '';

      const emailRegex = /^[a-zA-Z0-9._%+-]+@ujkz\.bf$/;
      if (!emailRegex.test(this.form.email)) {
        this.errors.email = "L'adresse email doit se terminer par @ujkz.bf";
        return;
      }

      this.loading = true;
      try {
        const response = await axios.post('/api/utilisateurs', this.form);
        this.successMessage = 'Utilisateur créé !';
        this.$emit('submitForm');
        setTimeout(() => { this.fermerFormulaire(); }, 1000);
      } catch (error) {
        this.errors = error.response?.data?.errors || { general: 'Une erreur est survenue.' };
      } finally {
        this.loading = false;
      }
    },
    fermerFormulaire() { this.$emit('close'); }
  },
};
</script>
