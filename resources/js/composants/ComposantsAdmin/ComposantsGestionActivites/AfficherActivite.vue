<template>
  <div class="w-full mx-auto bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
    <!-- Header épuré -->
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/20">
      <h2 class="text-lg font-bold text-slate-800 uppercase tracking-tight flex items-center">
        <span class="w-1.5 h-5 bg-amber-500 rounded-full mr-3"></span>
        Détails de l'activité
      </h2>
      <div v-if="localActivite.etat_slection" 
           :class="badgeClass(localActivite.etat_slection)"
           class="px-3 py-1 rounded text-[10px] font-bold uppercase tracking-widest border">
        {{ localActivite.etat_slection }}
      </div>
    </div>

    <!-- Contenu -->
    <div class="p-6">
      <div v-if="loading && !localActivite.id" class="flex items-center justify-center py-10">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div>
        <span class="ml-3 text-gray-500 text-xs font-bold uppercase tracking-widest">Mise à jour...</span>
      </div>

      <div v-else class="space-y-10 animate-fadeIn">
        <!-- Informations Identification -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
          <div class="md:col-span-2 pb-2 border-b border-gray-50">
            <label class="text-[10px] font-bold text-amber-600 uppercase tracking-widest block mb-1">Identifiant / Libellé</label>
            <p class="text-xl font-black text-slate-800 leading-tight">{{ defaultValue(localActivite.libelle) }}</p>
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Objectif Stratégique</label>
            <p class="text-sm text-slate-700 font-medium leading-relaxed">
              {{ defaultValue(objectifStrategique.libelle || localActivite.objectif_strategique_libelle) }}
            </p>
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Effet Attendu</label>
            <p class="text-sm text-slate-700 font-medium leading-relaxed">
              {{ defaultValue(effetAttendu.libelle || localActivite.effets_attendus_libelle) }}
            </p>
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Structure Responsable</label>
            <p class="text-sm text-slate-700 font-bold uppercase">
              {{ defaultValue(structure.sigle || localActivite.structure_sigle) }}
            </p>
          </div>

          <div class="space-y-1">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Financement par Partenaires</label>
            <div v-if="localActivite.partenaires_list && localActivite.partenaires_list.length > 0" class="space-y-1.5 mt-2">
              <div v-for="(p, index) in localActivite.partenaires_list" :key="index" class="flex justify-between items-center bg-slate-50 px-3 py-1.5 rounded border border-slate-100">
                <span class="text-[13px] text-slate-700 font-bold uppercase tracking-tight">{{ p.nom }}</span>
                <span class="text-[13px] text-amber-600 font-black">{{ formatNombreAvecEspaces(p.montant) }} <small class="text-slate-400 font-bold">FCFA</small></span>
              </div>
            </div>
            <p v-else class="text-sm text-slate-700 font-bold italic">{{ defaultValue(localActivite.partenaire) }}</p>
          </div>
        </div>

        <!-- Section Finances épurée -->
        <div class="bg-slate-50/50 border-y border-slate-100 py-6 px-4 rounded-lg">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
            <div class="flex items-start space-x-3">
              <div class="text-amber-500 mt-1"><i class="fas fa-wallet"></i></div>
              <div>
                <label class="text-[9px] font-bold text-slate-400 uppercase block tracking-wider">Financement Partenaire</label>
                <span class="text-lg font-black text-slate-800">{{ formatNombreAvecEspaces(localActivite.finance_partenaire) }} <small class="text-slate-400 font-bold">FCFA</small></span>
              </div>
            </div>
            <div class="flex items-start space-x-3 sm:border-l sm:border-slate-200 sm:pl-8">
              <div class="text-amber-500 mt-1"><i class="fas fa-university"></i></div>
              <div>
                <label class="text-[9px] font-bold text-slate-400 uppercase block tracking-wider">Part État ({{ defaultValue(localActivite.etat, 'NC') }})</label>
                <span class="text-lg font-black text-slate-800">{{ formatNombreAvecEspaces(localActivite.finance_etat) }} <small class="text-slate-400 font-bold">FCFA</small></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Trimestres & Indicateurs -->
        <div class="space-y-8">
          <div class="flex items-center space-x-4">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Périodes :</label>
            <div class="flex space-x-1.5">
              <template v-for="t in 4" :key="t">
                <span :class="localActivite['trimestre_'+t] ? 'bg-amber-500 text-white border-amber-600 font-bold' : 'text-slate-200 border-slate-100 bg-white'"
                      class="h-6 w-10 rounded border flex items-center justify-center text-[10px] uppercase transition-all shadow-sm">
                  T{{ t }}
                </span>
              </template>
            </div>
          </div>

          <div class="space-y-4">
            <div class="text-slate-800 font-bold text-[10px] uppercase tracking-widest flex items-center">
              <i class="fas fa-tasks mr-2 text-amber-500"></i> Performance (Indicateurs)
            </div>
            <div v-if="indicateurs.length > 0" class="overflow-hidden border border-slate-100 rounded-lg">
              <table class="w-full text-left text-xs bg-white">
                <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-tight">
                  <tr>
                    <th class="px-4 py-3 border-b border-slate-50">Libellé</th>
                    <th class="px-4 py-3 border-b border-slate-50 text-center">Unité</th>
                    <th class="px-4 py-3 border-b border-slate-50 text-center">Réf</th>
                    <th class="px-4 py-3 border-b border-slate-50 text-center">Cible</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="ind in indicateurs" :key="ind.id" class="hover:bg-amber-50/10 transition-colors">
                    <td class="px-4 py-3 font-semibold text-slate-700">{{ ind.indicateur }}</td>
                    <td class="px-4 py-3 text-center text-slate-500 font-bold uppercase text-[10px]">{{ ind.unite }}</td>
                    <td class="px-4 py-3 text-center font-bold text-slate-400">{{ defaultValue(ind.reference, '0') }}</td>
                    <td class="px-4 py-3 text-center font-black text-amber-600">{{ defaultValue(ind.cible, '0') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="text-slate-400 italic text-[10px] pl-4">Aucun indicateur défini pour cette activité.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  props: {
    activiteId: {
      type: [Number, String],
      required: true,
    },
    // Données initiales pour un affichage immédiat
    activite: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      // On utilise l'activite locale qui sera fusionnée
      localActivite: {},
      indicateurs: [],
      structure: {},
      objectifStrategique: {},
      effetAttendu: {}, 
      loading: false,
      error: false,
    };
  },
  methods: {
    formatNombreAvecEspaces(valeur) {
      if (!valeur) return '0'
      return valeur.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
    },
    defaultValue(valeur, fallback = 'Non renseigné') {
      return (valeur && valeur !== '0') ? valeur : fallback;
    },
    badgeClass(etat) {
      switch (etat) {
        case 'Validé': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'Rejeté': return 'bg-red-50 text-red-600 border-red-100';
        case 'Selectionné': return 'bg-amber-50 text-amber-600 border-amber-100';
        default: return 'bg-gray-50 text-gray-500 border-gray-100';
      }
    },
    async fetchDetails() {
      if (!this.activiteId) return;

      this.loading = true;
      this.error = false;

      try {
        const response = await axios.get(`/api/activites-detaille/${this.activiteId}`);
        const data = response.data;

        // Fusion des données : on garde les données initiales et on complète avec le détail
        this.localActivite = { ...this.activite, ...data.activite };
        this.indicateurs = data.indicateurs || [];
        this.structure = data.structure || data.activite?.structure || {};
        this.objectifStrategique = data.objectifStrategique || data.activite?.objectif_strategique || {};
        this.effetAttendu = data.effet_attendus || data.effetAttendu || data.activite?.effets_attendus || {}; 
      } catch (error) {
        console.error("Erreur lors de la récupération des détails :", error); 
        this.error = true; 
      } finally {
        this.loading = false;
      }
    },
  },
  watch: {
    activiteId: {
      handler(newId) {
        if (newId) {
          // Au changement d'ID, on pré-remplit avec la prop s'il y en a une
          this.localActivite = { ...this.activite };
          this.fetchDetails(); 
        }
      },
      immediate: true,
    },
    // Si la prop activite change (cas où le parent met à jour l'objet)
    activite: {
      handler(newVal) {
        this.localActivite = { ...this.localActivite, ...newVal };
      },
      deep: true
    }
  },
};
</script>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
  animation: fadeIn 0.4s ease-out forwards;
}
</style>
