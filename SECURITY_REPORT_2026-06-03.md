# Rapport de securite serveur - 2026-06-03

## Resume

Audit rapide effectue sur le serveur hebergeant l'application Laravel.

Niveau de risque actuel: **eleve** tant que les points systeme critiques ne sont pas corriges.

## Corrections appliquees maintenant

1. Durcissement applicatif Laravel:
- `LOG_LEVEL` passe de `debug` a `warning` dans le fichier d'environnement.

## Constats critiques restants

1. Pare-feu inactif/non installe.
2. Protection anti-bruteforce absente (fail2ban).
3. Port MySQL/MariaDB (3306) expose publiquement.
4. Site en HTTP uniquement (pas de TLS actif).
5. Durcissement Apache insuffisant (`ServerTokens OS`, `ServerSignature On`).
6. Permissions du fichier `.env` trop permissives.

## Plan de correction prioritaire (ordre recommande)

1. Installer protections de base:

```bash
sudo apt update
sudo apt install -y ufw fail2ban unattended-upgrades
```

2. Activer le pare-feu et fermer 3306 depuis Internet:

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw deny 3306/tcp
sudo ufw enable
sudo ufw status verbose
```

3. Verrouiller permissions du fichier d'environnement:

```bash
sudo chown www-data:www-data /var/www/gpaujkz/.env
sudo chmod 640 /var/www/gpaujkz/.env
```

4. Durcir Apache:
- Basculer vers `ServerTokens Prod`
- Basculer vers `ServerSignature Off`
- Conserver `TraceEnable Off`

5. Activer TLS (HTTPS):
- Necessite un nom de domaine pointe vers le serveur.
- Puis installer certificat Let's Encrypt avec Certbot.

## Verification post-correction

Executer ces verifications:

```bash
ss -tulpen | grep -E ':22|:80|:443|:3306'
sudo ufw status verbose
sudo fail2ban-client status
```

Resultat attendu:
- 22/80/443 autorises selon besoin.
- 3306 non accessible depuis Internet.
- fail2ban actif.

## Blocage actuel

Les corrections systeme necessitent les privileges `sudo` (mot de passe administrateur).
Sans ces privileges, impossible d'appliquer automatiquement les changements systeme depuis cette session.
