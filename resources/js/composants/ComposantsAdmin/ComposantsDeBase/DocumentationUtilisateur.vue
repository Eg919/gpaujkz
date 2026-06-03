<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-green-50/30 pb-16 pt-20">

    <!-- Header -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 mb-10">
      <div class="flex items-center gap-4 mb-2">
        <div class="w-12 h-12 rounded-2xl bg-green-700 flex items-center justify-center shadow-lg">
          <i class="fas fa-book-open text-white text-xl"></i>
        </div>
        <div>
          <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Documentation</h1>
          <p class="text-sm text-slate-500 font-medium">
            Guide d'utilisation — Rôle :
            <span class="text-green-700 font-black uppercase">{{ roleLabel }}</span>
          </p>
        </div>
      </div>
      <div class="h-1 w-24 bg-green-700 rounded-full mt-3"></div>
    </div>

    <!-- Tabs par section -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 mb-6">
      <div class="flex gap-2 flex-wrap">
        <button
          v-for="section in visibleSections"
          :key="section.id"
          @click="activeSection = section.id"
          :class="[
            'px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all',
            activeSection === section.id
              ? 'bg-green-700 text-white shadow-md'
              : 'bg-white text-slate-500 border border-slate-200 hover:border-green-300 hover:text-green-700'
          ]"
        >
          <i :class="section.icon + ' mr-1.5'"></i>{{ section.label }}
        </button>
      </div>
    </div>

    <!-- Contenu de la section active -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
      <div v-for="section in visibleSections" :key="section.id">
        <transition name="fade">
          <div v-if="activeSection === section.id" :id="'section-' + section.id">

            <!-- Carte principale -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
              <div class="bg-gradient-to-r from-green-700 to-green-600 px-8 py-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <i :class="section.icon + ' text-white text-2xl'"></i>
                  <h2 class="text-lg font-black text-white uppercase tracking-wider">{{ section.title }}</h2>
                </div>
                <button
                  @click="telechargerPDF(section)"
                  class="flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition-all"
                >
                  <i class="fas fa-file-pdf"></i> Télécharger PDF
                </button>
              </div>

              <div class="p-8">
                <!-- Introduction -->
                <div class="mb-8 p-5 bg-green-50 rounded-xl border-l-4 border-green-600">
                  <p class="text-sm text-slate-700 leading-relaxed" v-html="section.intro"></p>
                </div>

                <!-- Étapes / Fonctionnalités -->
                <div v-for="(bloc, bi) in section.blocs" :key="bi" class="mb-8">
                  <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-green-700 text-white rounded-full flex items-center justify-center text-xs font-black">{{ bi + 1 }}</span>
                    {{ bloc.titre }}
                  </h3>
                  <div class="grid gap-3">
                    <div
                      v-for="(etape, ei) in bloc.etapes"
                      :key="ei"
                      class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100"
                    >
                      <span class="w-6 h-6 bg-slate-200 text-slate-600 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">{{ ei + 1 }}</span>
                      <div>
                        <p class="text-sm font-bold text-slate-700" v-html="etape.titre"></p>
                        <p v-if="etape.detail" class="text-xs text-slate-500 mt-1 leading-relaxed" v-html="etape.detail"></p>
                      </div>
                    </div>
                  </div>
                  <!-- Notes / Alertes -->
                  <div v-if="bloc.note" class="mt-3 p-3 bg-amber-50 rounded-lg border border-amber-200 flex items-start gap-2">
                    <i class="fas fa-exclamation-triangle text-amber-500 text-xs mt-0.5 flex-shrink-0"></i>
                    <p class="text-xs text-amber-700" v-html="bloc.note"></p>
                  </div>
                </div>

                <!-- Tableau des accès si disponible -->
                <div v-if="section.tableauAcces" class="mt-6">
                  <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-4">Récapitulatif des accès</h3>
                  <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                      <thead>
                        <tr class="bg-slate-50">
                          <th class="px-4 py-3 text-left font-black text-slate-600 uppercase tracking-wider">Fonctionnalité</th>
                          <th class="px-4 py-3 text-center font-black text-slate-600 uppercase tracking-wider">Accès</th>
                          <th class="px-4 py-3 text-left font-black text-slate-600 uppercase tracking-wider">Description</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100">
                        <tr v-for="(ligne, li) in section.tableauAcces" :key="li" class="hover:bg-slate-50">
                          <td class="px-4 py-3 font-bold text-slate-700">{{ ligne.feature }}</td>
                          <td class="px-4 py-3 text-center">
                            <span :class="ligne.acces === 'Oui' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-50 text-red-500'" class="px-2 py-0.5 rounded-full font-bold text-[10px] uppercase">
                              {{ ligne.acces }}
                            </span>
                          </td>
                          <td class="px-4 py-3 text-slate-500">{{ ligne.description }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </transition>
      </div>
    </div>

    <!-- Bouton télécharger toute la doc -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 mt-4 flex justify-end">
      <button
        @click="telechargerToutePDF"
        class="flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded-xl font-bold uppercase tracking-widest text-xs shadow-md transition-all"
      >
        <i class="fas fa-file-pdf"></i> Télécharger toute la documentation (PDF)
      </button>
    </div>

  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'DocumentationUtilisateur',
  data() {
    return {
      userRole: null,
      activeSection: 'connexion',
      allSections: this.buildSections(),
    };
  },
  computed: {
    roleLabel() {
      const labels = {
        'Administrateur': 'Administrateur DEPS',
        'Administrateur_DSI': 'Administrateur DSI',
        'Responsable': 'Responsable',
        'Responsable-de-structure': 'Responsable de structure',
        'Chef-de-service': 'Chef de service',
        'Point-Focale': 'Point Focal',
        'Ordonnateur': 'Ordonnateur',
        'Planificateur': 'Planificateur',
        'Utilisateur': 'Utilisateur',
      };
      return labels[this.userRole] || this.userRole || '...';
    },
    visibleSections() {
      return this.allSections.filter(s => {
        if (!s.roles || s.roles.length === 0) return true;
        return s.roles.includes(this.userRole);
      });
    },
  },
  async mounted() {
    try {
      const res = await axios.get('/api/user-info');
      this.userRole = res.data.role;
    } catch (e) {
      console.error('Impossible de récupérer le rôle:', e);
    }
    if (this.visibleSections.length > 0) {
      this.activeSection = this.visibleSections[0].id;
    }
  },
  methods: {
    buildSections() {
      // ============================================================
      // SECTION COMMUNE : CONNEXION
      // ============================================================
      const connexion = {
        id: 'connexion',
        icon: 'fas fa-sign-in-alt',
        label: 'Connexion',
        title: 'Connexion à la plateforme GPA-UJKZ',
        roles: [], // visible par tous
        intro: 'La plateforme <strong>GPA-UJKZ</strong> est accessible via un navigateur web (Chrome, Firefox, Edge). Ce guide vous explique comment vous connecter et gérer votre session.',
        blocs: [
          {
            titre: 'Se connecter',
            etapes: [
              { titre: 'Ouvrez votre navigateur web et accédez à l\'adresse de la plateforme.', detail: 'Saisissez l\'URL fournie par votre administrateur système.' },
              { titre: 'Saisissez votre <strong>adresse email</strong> dans le premier champ.', detail: 'L\'email est celui qui vous a été communiqué lors de la création de votre compte.' },
              { titre: 'Saisissez votre <strong>mot de passe</strong> dans le second champ.', detail: 'Si c\'est votre première connexion, utilisez le mot de passe temporaire reçu par email.' },
              { titre: 'Cliquez sur le bouton <strong>Se connecter</strong>.', detail: 'Vous serez automatiquement redirigé vers votre tableau de bord.' },
            ],
            note: 'En cas d\'oubli de mot de passe, contactez votre administrateur DSI pour une réinitialisation.'
          },
          {
            titre: 'Changer son mot de passe',
            etapes: [
              { titre: 'Cliquez sur votre <strong>email</strong> en haut à droite de la barre de navigation.' },
              { titre: 'Sélectionnez <strong>Changer mon mot de passe</strong>.' },
              { titre: 'Saisissez votre ancien mot de passe, puis le nouveau deux fois.', detail: 'Le nouveau mot de passe doit contenir au moins 8 caractères.' },
              { titre: 'Cliquez sur <strong>Valider</strong>.' },
            ],
          },
          {
            titre: 'Se déconnecter',
            etapes: [
              { titre: 'Cliquez sur le bouton <strong>rouge de déconnexion</strong> (icône porte) en haut à droite.' },
              { titre: 'Confirmez la déconnexion dans la fenêtre qui s\'ouvre.' },
            ],
            note: 'Pour la sécurité de vos données, déconnectez-vous toujours en fin de session, surtout sur un ordinateur partagé.'
          },
        ],
      };

      // ============================================================
      // SECTION ACTIVITÉS (Point Focal, Chef de service, Responsable)
      // ============================================================
      const activites = {
        id: 'activites',
        icon: 'fas fa-tasks',
        label: 'Activités',
        title: 'Gestion des Activités',
        roles: ['Administrateur', 'Chef-de-service', 'Point-Focale', 'Responsable', 'Responsable-de-structure', 'Ordonnateur'],
        intro: 'Les activités représentent les tâches planifiées par votre structure dans le cadre du programme d\'activités de l\'université. Vous pouvez créer, modifier et suivre leur état d\'exécution.',
        blocs: [
          {
            titre: 'Créer une nouvelle activité',
            etapes: [
              { titre: 'Cliquez sur <strong>Activités</strong> dans la barre de navigation, puis <strong>Nouvelle Activité</strong>.' },
              { titre: 'Remplissez le formulaire : Libellé, Type, Objectif, Structure, Session.' },
              { titre: 'Ajoutez les <strong>tâches</strong> liées à l\'activité avec leurs dates et taux d\'exécution.' },
              { titre: 'Cliquez sur <strong>Soumettre</strong> pour enregistrer.' },
            ],
            note: 'Une activité ne peut être soumise que si elle appartient à une session active. Vérifiez auprès de l\'administrateur que la session est ouverte.'
          },
          {
            titre: 'Suivre l\'exécution d\'une activité',
            etapes: [
              { titre: 'Accédez à votre <strong>Programme d\'activités</strong> depuis le menu Suivi.' },
              { titre: 'Cliquez sur une activité pour voir le détail de ses tâches.' },
              { titre: 'Mettez à jour le <strong>taux d\'exécution</strong> de chaque tâche.' },
              { titre: 'Le taux global de l\'activité est calculé automatiquement.' },
            ],
          },
        ],
        tableauAcces: [
          { feature: 'Créer une activité', acces: 'Oui', description: 'Via le menu Activités > Nouvelle Activité' },
          { feature: 'Modifier une activité', acces: 'Oui', description: 'Dans la fiche détail de l\'activité' },
          { feature: 'Supprimer une activité', acces: 'Non', description: 'Réservé à l\'administrateur' },
          { feature: 'Valider une activité', acces: 'Oui', description: 'Si vous êtes responsable de structure' },
        ],
      };

      // ============================================================
      // SECTION RAPPORTS (Admin, Chef de service, Ordonnateur)
      // ============================================================
      const rapports = {
        id: 'rapports',
        icon: 'fas fa-file-contract',
        label: 'Rapports',
        title: 'Rapports Trimestriels',
        roles: ['Administrateur', 'Chef-de-service', 'Ordonnateur', 'Responsable', 'Responsable-de-structure', 'Point-Focale'],
        intro: 'Les rapports trimestriels récapitulent les activités exécutées sur chaque trimestre. Ils sont générés automatiquement à partir des données saisies dans le système.',
        blocs: [
          {
            titre: 'Consulter un rapport trimestriel',
            etapes: [
              { titre: 'Cliquez sur <strong>Suivi</strong> dans la barre de navigation.' },
              { titre: 'Sélectionnez <strong>Rapports de Gestion</strong>.' },
              { titre: 'Choisissez la structure et le trimestre concerné.' },
              { titre: 'Le rapport s\'affiche avec les taux d\'exécution par activité.' },
            ],
          },
          {
            titre: 'Exporter un rapport en PDF',
            etapes: [
              { titre: 'Ouvrez le rapport trimestriel souhaité.' },
              { titre: 'Cliquez sur le bouton <strong>Exporter PDF</strong> en haut à droite.' },
              { titre: 'Le fichier PDF se télécharge automatiquement sur votre ordinateur.' },
            ],
          },
        ],
      };

      // ============================================================
      // SECTION ADMINISTRATION (Admin DSI seulement)
      // ============================================================
      const administration = {
        id: 'administration',
        icon: 'fas fa-users-cog',
        label: 'Administration',
        title: 'Administration du Système',
        roles: ['Administrateur_DSI'],
        intro: 'En tant qu\'<strong>Administrateur DSI</strong>, vous gérez les comptes utilisateurs, les structures et l\'historique des actions (audits) sur la plateforme.',
        blocs: [
          {
            titre: 'Gérer les utilisateurs',
            etapes: [
              { titre: 'Accédez à <strong>Paramètres > Utilisateurs</strong>.' },
              { titre: 'Consultez la liste de tous les utilisateurs enregistrés.' },
              { titre: 'Modifiez le rôle ou l\'état d\'un utilisateur en cliquant sur l\'icône <strong>Modifier</strong>.' },
              { titre: 'Pour créer plusieurs comptes d\'un coup, cliquez sur <strong>Télécharger Canevas</strong>, remplissez le fichier CSV, puis importez-le avec le bouton <strong>Choisir fichier → Importer</strong>.' },
            ],
            note: 'Les rôles disponibles sont : Administrateur, Administrateur_DSI, Chef-de-service, Responsable-de-structure, Point-Focale, Ordonnateur, Planificateur, Utilisateur.'
          },
          {
            titre: 'Gérer les structures',
            etapes: [
              { titre: 'Accédez à <strong>Paramètres > Structures</strong>.' },
              { titre: 'Consultez la liste de toutes les structures.' },
              { titre: 'Ajoutez une structure via le bouton <strong>Nouvelle Structure</strong>.' },
              { titre: 'Importez plusieurs structures en masse : téléchargez le canevas CSV, renseignez les données, puis importez.' },
            ],
          },
          {
            titre: 'Consulter les audits',
            etapes: [
              { titre: 'Accédez à <strong>Paramètres > Historique Audits</strong>.' },
              { titre: 'Filtrez par date, utilisateur ou type d\'action.' },
              { titre: 'Chaque ligne indique : qui a fait quoi, quand et depuis quelle adresse IP.' },
            ],
          },
        ],
        tableauAcces: [
          { feature: 'Gestion des utilisateurs', acces: 'Oui', description: 'Créer, modifier, importer, réinitialiser mot de passe' },
          { feature: 'Gestion des structures', acces: 'Oui', description: 'Créer, modifier, importer' },
          { feature: 'Historique des audits', acces: 'Oui', description: 'Consultation uniquement' },
          { feature: 'Plans stratégiques', acces: 'Non', description: 'Réservé à l\'Administrateur DEPS' },
        ],
      };

      // ============================================================
      // SECTION PARAMÈTRES (Administrateur DEPS)
      // ============================================================
      const parametres = {
        id: 'parametres',
        icon: 'fas fa-cog',
        label: 'Paramètres',
        title: 'Paramètres & Plans Stratégiques',
        roles: ['Administrateur'],
        intro: 'L\'<strong>Administrateur DEPS</strong> gère les sessions d\'activités et les plans stratégiques qui servent de cadre à toutes les activités de l\'université.',
        blocs: [
          {
            titre: 'Créer une session d\'activités',
            etapes: [
              { titre: 'Cliquez sur <strong>Sessions d\'Activités</strong> dans la barre de navigation.' },
              { titre: 'Cliquez sur <strong>Nouvelle Session</strong>.' },
              { titre: 'Renseignez le libellé, les dates de début et de fin, puis enregistrez.' },
              { titre: 'Les utilisateurs peuvent saisir des activités dès que la session est active.' },
            ],
            note: 'Une seule session peut être active à la fois. Fermez la session précédente avant d\'en ouvrir une nouvelle.'
          },
          {
            titre: 'Gérer les plans stratégiques',
            etapes: [
              { titre: 'Accédez à <strong>Paramètres > Plans Stratégiques</strong>.' },
              { titre: 'Créez un plan avec ses objectifs stratégiques et indicateurs.' },
              { titre: 'Les activités sont liées à ces objectifs lors de leur création.' },
            ],
          },
        ],
      };

      // ============================================================
      // SECTION ORDONNATEUR (Confirmations)
      // ============================================================
      const ordonnateur = {
        id: 'confirmation',
        icon: 'fas fa-check-circle',
        label: 'Confirmations',
        title: 'Confirmation des Activités',
        roles: ['Ordonnateur'],
        intro: 'L\'<strong>Ordonnateur</strong> valide les activités soumises par les différentes structures avant leur intégration officielle dans le programme.',
        blocs: [
          {
            titre: 'Confirmer une activité',
            etapes: [
              { titre: 'Cliquez sur <strong>Activités > À confirmer</strong> dans le menu.' },
              { titre: 'Consultez la liste des activités en attente de confirmation.' },
              { titre: 'Ouvrez une activité pour en voir le détail.' },
              { titre: 'Cliquez sur <strong>Confirmer</strong> pour valider, ou <strong>Rejeter</strong> en indiquant un motif.' },
            ],
            note: 'Seules les activités confirmées apparaissent dans les rapports officiels.'
          },
        ],
      };

      // ============================================================
      // SECTION MATRICE D'ACTIVITÉ (Admin, Chef de service, Ordonnateur, Planificateur)
      // ============================================================
      const matrice = {
        id: 'matrice',
        icon: 'fas fa-table',
        label: 'Matrice d\'activité',
        title: 'Matrice d\'activité & Statistiques',
        roles: ['Administrateur', 'Chef-de-service', 'Ordonnateur', 'Planificateur', 'Administrateur_DSI'],
        intro: 'La <strong>Matrice d\'activité</strong> est un tableau de bord visuel qui présente une synthèse globale de l\'état d\'avancement de toutes les activités par structure, par trimestre et par objectif stratégique. C\'est l\'outil central de pilotage et de suivi de la performance.',
        blocs: [
          {
            titre: 'Accéder à la matrice d\'activité',
            etapes: [
              { titre: 'Dans la barre de navigation, cliquez sur <strong>Statistiques</strong> ou accédez directement via le menu de votre tableau de bord.' },
              { titre: 'La matrice s\'affiche avec toutes les structures et leurs activités regroupées par trimestre.' },
              { titre: 'Utilisez les <strong>filtres</strong> (session, structure, trimestre) pour affiner la vue.' },
            ],
          },
          {
            titre: 'Lire la matrice',
            etapes: [
              { titre: '<strong>Colonnes</strong> : représentent les trimestres (T1, T2, T3, T4) et les indicateurs globaux.' },
              { titre: '<strong>Lignes</strong> : représentent les activités regroupées par structure et par objectif stratégique.', detail: 'Chaque ligne indique le libellé de l\'activité, son type et son taux d\'exécution.' },
              { titre: '<strong>Couleur des cellules</strong> : indique le niveau d\'avancement.', detail: 'Vert = exécuté à 100% | Jaune = en cours | Rouge = non démarré ou en retard.' },
              { titre: 'Le <strong>taux global</strong> de chaque structure est calculé automatiquement en bas de chaque groupe.' },
            ],
            note: 'Les données de la matrice sont mises à jour en temps réel dès qu\'un utilisateur modifie le taux d\'exécution d\'une tâche.'
          },
          {
            titre: 'Exporter la matrice',
            etapes: [
              { titre: 'Cliquez sur le bouton <strong>Exporter PDF</strong> en haut de la page matrice.' },
              { titre: 'Le PDF généré reprend fidèlement la mise en page de la matrice avec les couleurs et les totaux.' },
              { titre: 'Vous pouvez également utiliser <strong>Ctrl+P</strong> (ou Cmd+P sur Mac) depuis le navigateur pour imprimer directement.' },
            ],
          },
        ],
        tableauAcces: [
          { feature: 'Consulter la matrice', acces: 'Oui', description: 'Vue complète de toutes les structures et activités' },
          { feature: 'Filtrer par structure', acces: 'Oui', description: 'Afficher uniquement une structure donnée' },
          { feature: 'Filtrer par trimestre', acces: 'Oui', description: 'Afficher uniquement les données d\'un trimestre' },
          { feature: 'Exporter en PDF', acces: 'Oui', description: 'Génère un document PDF de la matrice complète' },
          { feature: 'Modifier les données', acces: 'Non', description: 'La matrice est en lecture seule — modifiez les taux via les activités' },
        ],
      };

      return [connexion, activites, matrice, rapports, ordonnateur, parametres, administration];

    },

    telechargerPDF(section) {
      this.genererPDF([section], `documentation_${section.id}.pdf`);
    },

    telechargerToutePDF() {
      this.genererPDF(this.visibleSections, `documentation_gpa_${this.userRole || 'utilisateur'}.pdf`);
    },

    genererPDF(sections, filename) {
      // Construction du contenu HTML pour l'impression
      const contenu = sections.map(section => `
        <div style="page-break-after: always; padding: 40px;">
          <div style="border-left: 6px solid #15803d; padding-left: 20px; margin-bottom: 30px;">
            <h1 style="font-size: 22px; font-weight: 900; color: #15803d; text-transform: uppercase; letter-spacing: 2px; margin: 0 0 6px 0;">${section.title}</h1>
            <p style="font-size: 12px; color: #64748b; margin: 0;">Rôle : ${this.roleLabel} — Plateforme GPA-UJKZ</p>
          </div>

          <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
            <p style="font-size: 13px; color: #374151; line-height: 1.7; margin: 0;">${section.intro.replace(/<[^>]*>/g, '')}</p>
          </div>

          ${section.blocs.map((bloc, bi) => `
            <div style="margin-bottom: 24px;">
              <h2 style="font-size: 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #1e293b; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="display: inline-block; width: 24px; height: 24px; background: #15803d; color: white; border-radius: 50%; text-align: center; line-height: 24px; font-size: 12px;">${bi + 1}</span>
                ${bloc.titre}
              </h2>
              ${bloc.etapes.map((etape, ei) => `
                <div style="display: flex; gap: 12px; margin-bottom: 8px; padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                  <span style="width: 22px; height: 22px; background: #e2e8f0; color: #475569; border-radius: 50%; text-align: center; line-height: 22px; font-size: 11px; font-weight: bold; flex-shrink: 0;">${ei + 1}</span>
                  <div>
                    <p style="font-size: 12px; font-weight: 700; color: #374151; margin: 0;">${etape.titre.replace(/<[^>]*>/g, '')}</p>
                    ${etape.detail ? `<p style="font-size: 11px; color: #94a3b8; margin: 4px 0 0 0;">${etape.detail}</p>` : ''}
                  </div>
                </div>
              `).join('')}
              ${bloc.note ? `<div style="margin-top: 8px; padding: 10px 14px; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px;"><p style="font-size: 11px; color: #92400e; margin: 0;">⚠️ ${bloc.note}</p></div>` : ''}
            </div>
          `).join('')}

          ${section.tableauAcces ? `
            <table style="width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 12px;">
              <thead>
                <tr style="background: #f1f5f9;">
                  <th style="text-align: left; padding: 8px 12px; font-weight: 900; text-transform: uppercase; color: #64748b; border: 1px solid #e2e8f0;">Fonctionnalité</th>
                  <th style="text-align: center; padding: 8px 12px; font-weight: 900; text-transform: uppercase; color: #64748b; border: 1px solid #e2e8f0;">Accès</th>
                  <th style="text-align: left; padding: 8px 12px; font-weight: 900; text-transform: uppercase; color: #64748b; border: 1px solid #e2e8f0;">Description</th>
                </tr>
              </thead>
              <tbody>
                ${section.tableauAcces.map(ligne => `
                  <tr>
                    <td style="padding: 8px 12px; border: 1px solid #e2e8f0; font-weight: 700; color: #374151;">${ligne.feature}</td>
                    <td style="padding: 8px 12px; border: 1px solid #e2e8f0; text-align: center;">
                      <span style="padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; background: ${ligne.acces === 'Oui' ? '#dcfce7' : '#fee2e2'}; color: ${ligne.acces === 'Oui' ? '#15803d' : '#dc2626'};">${ligne.acces}</span>
                    </td>
                    <td style="padding: 8px 12px; border: 1px solid #e2e8f0; color: #64748b;">${ligne.description}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          ` : ''}
        </div>
      `).join('');

      const html = `<!DOCTYPE html>
      <html lang="fr">
      <head>
        <meta charset="UTF-8">
        <title>${filename}</title>
        <style>
          * { box-sizing: border-box; }
          body { font-family: Arial, sans-serif; margin: 0; padding: 0; color: #1e293b; }
          @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
          }
        </style>
      </head>
      <body>
        <div style="background: #15803d; color: white; padding: 24px 40px; display: flex; align-items: center; gap: 16px;">
          <div>
            <h1 style="margin: 0; font-size: 20px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase;">Documentation GPA-UJKZ</h1>
            <p style="margin: 4px 0 0 0; font-size: 12px; opacity: 0.8;">Rôle : ${this.roleLabel} — Généré le ${new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' })}</p>
          </div>
        </div>
        ${contenu}
      </body>
      </html>`;

      const fenetre = window.open('', '_blank');
      fenetre.document.write(html);
      fenetre.document.close();
      fenetre.focus();
      setTimeout(() => {
        fenetre.print();
        fenetre.document.title = filename;
      }, 500);
    },
  },
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
