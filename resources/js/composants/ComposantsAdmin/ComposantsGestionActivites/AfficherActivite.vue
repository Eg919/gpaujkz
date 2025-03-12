<template>
  <div class="min-w-full mx-auto max-h-full overflow-y-auto box-border">
    <!-- Conteneur principal -->
    <div class="overflow-y-auto">
      <div v-if="loading" class="text-center py-8">
        <p class="text-gray-600 animate-pulse text-base sm:text-lg md:text-xl">
          Chargement des détails de l'activité...
        </p>
      </div>
      <div v-else-if="error" class="text-center text-red-500 py-8">
        <p class="text-base sm:text-lg md:text-xl">
          Erreur lors du chargement des données. Veuillez réessayer plus tard.
        </p>
      </div>
      <div v-else class="p-4 sm:p-6 md:p-8 bg-white rounded-lg shadow-lg">
        <!-- Titre -->
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4 border-b pb-3">
          Détails de l'activité
        </h2>

        <!-- Liste des détails -->
        <ul class="space-y-2 sm:space-y-3">
          <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <span class="font-medium text-gray-800">Etat Activité :</span>
            <span
              :class="{
                'text-yellow-300': activite.etat_activite === 'Ouvert',
                'text-green-500': activite.etat_activite === 'terminer',
                'text-red-500': activite.etat_activite === 'abandonner',
              }"
              class="text-gray-700 mt-1 sm:mt-0"
            >
              {{ activite.etat_activite || 'État inconnu' }}
            </span>
          </li>
          <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <span class="font-medium text-gray-800">Objectif Stratégique :</span>
            <span class="text-gray-700 mt-1 sm:mt-0">
              {{ defaultValue(objectifStrategique.libelle) }}
            </span>
          </li>
          <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <span class="font-medium text-gray-800">Effet Attendu :</span>
            <span class="text-gray-700 mt-1 sm:mt-0">
              {{ defaultValue(effetAttendu.libelle) }}
            </span>
          </li>
          <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <span class="font-medium text-gray-800">Structure :</span>
            <span class="text-gray-700 mt-1 sm:mt-0">
              {{ defaultValue(structure.sigle) }}
            </span>
          </li>
          <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <span class="font-medium text-gray-800">Libellé :</span>
            <span class="text-gray-700 mt-1 sm:mt-0">
              {{ defaultValue(activite.libelle) }}
            </span>
          </li>
          <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <span class="font-medium text-gray-800">Partenaire :</span>
            <span class="text-gray-700 mt-1 sm:mt-0">
              {{ defaultValue(activite.partenaire) }}
            </span>
          </li>
          <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <span class="font-medium text-gray-800">Montant Partenaire :</span>
            <span class="text-gray-700 mt-1 sm:mt-0">
              {{ defaultValue(activite.finance_partenaire) }}
            </span>
          </li>
          <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <span class="font-medium text-gray-800">État :</span>
            <span class="text-gray-700 mt-1 sm:mt-0">
              {{ defaultValue(activite.etat) }}
            </span>
          </li>
          <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <span class="font-medium text-gray-800">Montant État :</span>
            <span class="text-gray-700 mt-1 sm:mt-0">
              {{ defaultValue(activite.finance_etat) }}
            </span>
          </li>
          <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <span class="font-medium text-gray-800">Trimestres :</span>
            <span class="text-gray-700 mt-1 sm:mt-0">
              <template v-for="(trimestre, index) in [activite.trimestre_1, activite.trimestre_2, activite.trimestre_3, activite.trimestre_4]" :key="index">
                <p v-if="trimestre" class="inline-block">
                  Trimestre {{ index + 1 }}{{ index < 3 && [activite.trimestre_1, activite.trimestre_2, activite.trimestre_3, activite.trimestre_4].slice(index + 1).some(Boolean) ? ',' : '' }}
                </p>
              </template>
            </span>
          </li>
        </ul>

        <!-- Indicateurs associés -->
        <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mt-6 mb-4 border-b pb-2">
          Indicateurs associés
        </h3>
        <div v-if="indicateurs.length > 0" class="w-full overflow-x-auto">
          <table class="table-auto w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
              <tr>
                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-sm sm:text-base">
                  Indicateur
                </th>
                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-sm sm:text-base">
                  Unité
                </th>
                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-sm sm:text-base">
                  Référence
                </th>
                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-sm sm:text-base">
                  Cible
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="indicateur in indicateurs"
                :key="indicateur.id"
                class="even:bg-gray-50"
              >
                <td class="border border-gray-300 px-2 sm:px-4 py-2 text-sm sm:text-base">
                  {{ indicateur.indicateur }}
                </td>
                <td class="border border-gray-300 px-2 sm:px-4 py-2 text-sm sm:text-base">
                  {{ indicateur.unite }}
                </td>
                <td class="border border-gray-300 px-2 sm:px-4 py-2 text-sm sm:text-base">
                  {{ defaultValue(indicateur.reference, 'N/A') }}
                </td>
                <td class="border border-gray-300 px-2 sm:px-4 py-2 text-sm sm:text-base">
                  {{ defaultValue(indicateur.cible, 'N/A') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="text-gray-500 mt-4 text-sm sm:text-base">
          Aucun indicateur associé à cette activité.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    activiteId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      activite: {},
      structure:{},
      objectifStrategique:{},
      effetAttendu:{},
      indicateurs: [],
      loading: true,
      error: false,
    };
  },
  computed: {
    defaultValue() {
      return (value, fallback = "Non renseigné") => value || fallback;
    },
  },
  methods: {
    async fetchDetails() {
  this.loading = true; // Indique que les données sont en cours de chargement
  this.error = false; // Réinitialise l'état d'erreur

  try {
    // Effectue une requête API pour récupérer les détails de l'activité
    const response = await fetch(`/api/activites-detaille/${this.activiteId}`);
    
    // Vérifie si la réponse est valide
    if (!response.ok) throw new Error("Erreur lors de la récupération des données");

    const data = await response.json();

    // Assigne les données aux variables locales
    this.activite = data.activite || {}; 
    this.indicateurs = data.indicateurs || [];
    this.structure = data.structure || {};
    this.objectifStrategique = data.objectifStrategique || {};
    this.effetAttendu = data.effetAttendu || {}; 
  } catch (error) {
    console.error("Erreur :", error); 
    this.error = true; 
  } finally {
    this.loading = false;
  }
},
  },
  created() {
    this.fetchDetails();
  },
  watch: {
    activiteId: {
      handler(newId, oldId) {
        if (newId !== oldId) {
          this.fetchDetails(); // Actualiser les données lorsque l'ID change
        }
      },
      immediate: true, // Facultatif : déclenche le handler dès la première valeur
    },
  },
};
</script>

<style scoped>
h2 {
  color: #2d3748;
  border-bottom: 2px solid #e2e8f0;
}
ul {
  color: #2d3748;
  line-height: 1.6;
}
</style>

