<template>
  <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-300">
      <!-- Header -->
      <div class="bg-emerald-600 p-6 text-white relative">
        <h2 class="text-xl font-black uppercase tracking-tight">Signature de l'Ordonnateur</h2>
        <p class="text-emerald-100 text-xs font-bold uppercase tracking-widest mt-1 opacity-80">Validation du Rapport</p>
        <button @click="$emit('close')" class="absolute top-6 right-6 text-white/80 hover:text-white transition-colors">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>

      <!-- Content -->
      <div class="p-8 space-y-6">
        <!-- Titre Honorifique -->
        <div>
          <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Titre Honorifique</label>
          <input 
            v-model="formData.titre" 
            type="text" 
            placeholder="Ex: Professeur, Docteur..." 
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all font-bold text-slate-700"
          />
        </div>

        <!-- Nom Complet -->
        <div>
          <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nom de l'Ordonnateur</label>
          <input 
            v-model="formData.nom" 
            type="text" 
            placeholder="Prénoms et Nom" 
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all font-bold text-slate-700"
          />
        </div>

        <!-- Upload Cachet/Signature -->
        <div>
          <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Cachet & Signature (Optionnel)</label>
          <div 
            class="relative border-2 border-dashed border-slate-200 rounded-2xl p-6 transition-all hover:bg-slate-50 flex flex-col items-center justify-center gap-3 cursor-pointer group"
            @click="$refs.fileInput.click()"
          >
            <template v-if="!formData.signatureImage">
              <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-image text-xl"></i>
              </div>
              <p class="text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Cliquez pour ajouter un cachet numérique<br><span class="text-[9px] lowercase opacity-60">(Facultatif)</span></p>
            </template>
            <template v-else>
              <img :src="formData.signatureImage" class="max-h-24 w-auto object-contain rounded shadow-sm" />
              <button @click.stop="formData.signatureImage = null" class="text-[9px] font-black text-rose-500 uppercase tracking-tighter hover:underline transition-all">Retirer le cachet</button>
            </template>
            <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleFileUpload" />
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
        <button 
          @click="$emit('close')" 
          class="flex-1 px-4 py-3 border border-slate-200 text-slate-500 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-white transition-all active:scale-95"
        >
          Annuler
        </button>
        <button 
          @click="submit" 
          :disabled="!isValid"
          class="flex-1 px-8 py-3 bg-slate-900 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-black transition-all active:scale-95 shadow-lg shadow-slate-200 disabled:opacity-20 translate-y-0"
        >
          Générer PDF
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SignatureModal",
  props: {
    show: { type: Boolean, default: false }
  },
  data() {
    return {
      formData: {
        titre: "",
        nom: "",
        signatureImage: null
      }
    };
  },
  computed: {
    isValid() {
      return this.formData.titre && this.formData.nom;
    }
  },
  methods: {
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.formData.signatureImage = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    submit() {
      if (this.isValid) {
        this.$emit('confirm', { ...this.formData });
      }
    }
  }
};
</script>
