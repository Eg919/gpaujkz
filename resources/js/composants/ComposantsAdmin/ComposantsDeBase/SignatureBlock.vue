<template>
  <div v-if="data" class="signature-block-container" style="padding: 28px 40px 20px; border-top: 1.5px solid #1e293b; background:#ffffff; font-family: 'Times New Roman', serif;">
    
    <table class="w-full border-none border-collapse signature-table-roc">
      <tbody>
        <tr>
          <!-- Gauche : Lieu et Date -->
          <td class="align-top text-left" style="width:33%; padding: 0; vertical-align:top;">
            <p style="font-size:9px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.12em; margin:0 0 4px 0;">Fait à Ouagadougou,</p>
            <p style="font-size:13px; font-weight:700; color:#1e293b; margin:0;">Le {{ formattedDate }}</p>
          </td>

          <!-- Centre : Espace vide -->
          <td style="width:34%;"></td>

          <!-- Droite : Signataire -->
          <td class="align-top text-center signature-box-protected-roc" style="width:33%; padding: 0; vertical-align:top;">
            <p style="font-size:9px; font-weight:900; color:#1e293b; text-transform:uppercase; letter-spacing:0.08em; margin:0 0 8px 0;">{{ data.titre }}</p>
            
            <!-- Zone Signature/Cachet (espace réservé pour tampon physique) -->
            <div :style="{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '160px', marginBottom: data.signatureImage ? '10px' : '20px', marginTop: data.signatureImage ? '0' : '15px' }">
              <img 
                v-if="data.signatureImage"
                :src="data.signatureImage" 
                class="object-contain mix-blend-multiply block" 
                style="max-height:160px; width:auto; max-width:300px;"
                alt="Cachet et Signature"
              />
              <!-- Aucun symbole, juste de l'espace vide correspondant au diamètre réel d'un cachet -->
            </div>

            <!-- Nom (Sans trait de signature) -->
            <div style="padding-top:5px; min-width:180px; display:inline-block; text-align:center;">
              <p style="font-size:11px; font-weight:900; color:#1e293b; text-transform:uppercase; letter-spacing:0.04em; margin:0;">{{ data.nom }}</p>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pied de document institutionnel -->
    <div style="margin-top: 24px; padding-top: 10px; border-top: 1px solid #f0f0f0; text-align:center;" class="footer-branding">
      <div style="display:flex; height:2px; overflow:hidden; margin-bottom:6px;">
        <div style="flex:1; background:#E4002B; opacity:0.4;"></div>
        <div style="flex:1; background:#EFC050; opacity:0.4;"></div>
        <div style="flex:1; background:#009E49; opacity:0.4;"></div>
      </div>
      <p style="font-size:8px; font-weight:600; color:#b0bac7; text-transform:uppercase; letter-spacing:0.18em; margin:0;">
        Document Officiel &mdash; Université Joseph Ki-Zerbo &mdash; GPA &copy; 2026
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: "SignatureBlock",
  props: {
    data: { type: Object, default: null }
  },
  computed: {
    formattedDate() {
      return new Date().toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });
    }
  }
};
</script>

<style scoped>
.signature-block-container {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
}

.signature-table-roc {
    border: none !important;
}

.signature-table-roc td {
    border: none !important;
}

.signature-box-protected-roc {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
}
</style>
