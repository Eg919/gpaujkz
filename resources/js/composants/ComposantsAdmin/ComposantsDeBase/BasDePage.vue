<template>
  <div class="bg-green-700 px-6 flex justify-between items-center fixed inset-x-0 bottom-0">
    <p class="text-white"> Copyright &copy; 
      <router-link 
      to="/contributeursProjet"
      >DSI-UJKZ</router-link>
      <span> &nbsp;{{  annee }}</span>
    </p>
    <!-- <p class="text-white">Tous droits réservés {{ new Date().getFullYear() }}</p> -->
    <p class="text-white">Tous droits réservés {{ currentDateTime }}</p>

  </div>
</template>

<script>
export default {
  name: "BasDePage",
  data() {
    return {
      currentDateTime: this.getCurrentDateTime(),
      annee: this.getCurrentyear(),
    }
  },
  methods: {
    getCurrentyear(){
      const now = new Date();
      const year = now.getFullYear();
       return `${year}`
    },
  getCurrentDateTime() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth() + 1; // Mois commence à 0, donc on ajoute 1
    const day = now.getDate();
    const hours = now.getHours();
    const minutes = now.getMinutes();
    const seconds = now.getSeconds();
    return `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')} ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
  },
  // Optionnel : mettre à jour la date et l'heure chaque seconde
  updateDateTime() {
    setInterval(() => {
      this.currentDateTime = this.getCurrentDateTime();
    }, 1000);
  }
},
mounted() {
  this.updateDateTime(); // Lancer la mise à jour de l'heure lors du montage du composant
}
};
</script>

<style scoped>
/* Styles du bas de page */
</style>
