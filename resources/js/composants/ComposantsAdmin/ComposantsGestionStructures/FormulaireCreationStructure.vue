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
            <h2 class="text-xl font-black text-amber-500 uppercase tracking-tighter">Nouvelle Structure</h2>
            <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-widest">Organisation et Unités</p>
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
          <div v-if="errorMessage" class="mb-6 bg-rose-50 border border-rose-100 text-rose-700 p-3 rounded-lg text-xs font-bold flex items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i> {{ errorMessage }}
          </div>

          <div class="space-y-6">
            <!-- Libelle Field -->
            <div>
              <label class="block text-[11px] font-black uppercase text-gray-500 tracking-wider mb-1.5">Libellé complet de la structure</label>
              <input
                v-model="form.libelle_structure"
                type="text"
                required
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-amber-400 focus:bg-white transition-all outline-none"
                placeholder="Ex: Direction de l'Enseignement..."
              />
              <p v-if="errors.libelle_structure" class="mt-1.5 text-[10px] font-bold text-rose-600">{{ errors.libelle_structure }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <!-- Sigle Field -->
              <div>
                <label class="block text-[11px] font-black uppercase text-gray-500 tracking-wider mb-1.5">Sigle</label>
                <input
                  v-model="form.sigle"
                  type="text"
                  required
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-black uppercase tracking-tight focus:ring-2 focus:ring-amber-400 transition-all outline-none"
                  placeholder="EX: DEPS"
                />
              </div>

              <!-- State Field -->
              <div>
                <label class="block text-[11px] font-black uppercase text-gray-500 tracking-wider mb-1.5">État Initial</label>
                <select
                  v-model="form.etat"
                  required
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-amber-400 transition-all outline-none"
                >
                  <option value="actif">Actif</option>
                  <option value="inactif">Inactif</option>
                </select>
              </div>
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
      form: {
        libelle_structure: '',
        sigle: '',
        etat: 'actif',
      },
      errors: {},
      successMessage: '',
      errorMessage: '',
      loading: false,
    };
  },
  methods: {
    async submitForm() {
      if (this.loading) return;
      this.errors = {};
      this.successMessage = '';
      this.errorMessage = '';

      this.loading = true;
      try {
        await axios.post('/api/structures', this.form);
        this.successMessage = 'Structure créée !';
        this.$emit('submitForm');
        setTimeout(() => { this.fermerFormulaire(); }, 1000);
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors;
        } else {
          this.errorMessage = 'Une erreur est survenue lors de la création.';
        }
      } finally {
        this.loading = false;
      }
    },
    fermerFormulaire() { this.$emit('close'); }
  },
};
</script>
