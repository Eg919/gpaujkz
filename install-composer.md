# Installer Composer sur Windows

## Méthode recommandée : Composer-Setup.exe

1. **Téléchargez l’installateur Windows**  
   https://github.com/composer/windows-setup/releases/latest/download/Composer-Setup.exe

2. **Lancez `Composer-Setup.exe`**  
   - L’assistant cherche PHP sur votre PC.  
   - Si PHP n’est pas trouvé, installez d’abord **PHP** (par ex. via [Laragon](https://laragon.org/download/) ou [XAMPP](https://www.apachefriends.org/)), puis relancez l’installateur.  
   - L’installateur peut ajouter PHP au PATH et installer Composer globalement.

3. **Fermez puis rouvrez PowerShell** (ou Cursor), puis vérifiez :  
   ```powershell
   composer --version
   ```

## Alternative : installation manuelle (si PHP est déjà installé et dans le PATH)

Dans PowerShell, à la racine du projet :

```powershell
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'c8b085408188070d5f52bcfe4ecfbee5f727afa458b2573b8eaaf77b3419b0bf2768dc67c86944da1544f06fa544fd47') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; exit(1); }"
php composer-setup.php --install-dir=.
php -r "unlink('composer-setup.php');"
```

Ensuite, utilisez Composer dans ce dossier avec :  
`php composer.phar ...`  
Par exemple : `php composer.phar install`

Pour l’avoir en global, déplacez `composer.phar` dans un dossier du PATH et créez un fichier `composer.bat` comme indiqué sur https://getcomposer.org/doc/00-intro.md#globally .
