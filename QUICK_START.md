# 🚀 GUIDE DE DÉMARRAGE RAPIDE

## Installation en 5 minutes

### 1️⃣ Installer XAMPP
- Télécharger : https://www.apachefriends.org/
- Installer et lancer **Apache** + **MySQL**

### 2️⃣ Créer la base de données
```
1. Ouvrir : http://localhost/phpmyadmin
2. Cliquer sur "SQL"
3. Copier le contenu de database/tournament_db.sql
4. Coller et cliquer "Exécuter"
```

### 3️⃣ Installer les fichiers
```
1. Copier le dossier "backend" dans C:\xampp\htdocs\
   (ou /Applications/XAMPP/htdocs/ sur Mac)
   
2. Le chemin final doit être :
   C:\xampp\htdocs\tournament-project\backend\
```

### 4️⃣ Tester l'API
```
Ouvrir dans le navigateur :
http://localhost/tournament-project/backend/api/index.php/tournaments

✅ Vous devez voir du JSON
```

### 5️⃣ Lancer l'application
```
Double-cliquer sur :
frontend/tournament-dashboard.html

✅ Le dashboard s'ouvre dans votre navigateur
```

---

## ⚠️ En cas de problème

### Le dashboard ne charge pas les données ?

**Solution 1 : Vérifier l'URL de l'API**
```
1. Ouvrir frontend/tournament-dashboard.html avec un éditeur
2. Chercher : const API_BASE_URL = 
3. Modifier selon votre installation
```

**Solution 2 : Vérifier Apache et MySQL**
```
1. Ouvrir le panneau XAMPP
2. S'assurer que Apache et MySQL sont VERTS (démarrés)
3. Cliquer sur "Logs" pour voir les erreurs
```

### Erreur "Access to fetch has been blocked by CORS" ?

**Solution : Vérifier l'emplacement des fichiers**
```
Les fichiers backend DOIVENT être dans htdocs :
✅ C:\xampp\htdocs\tournament-project\backend\
❌ C:\Users\...\Desktop\tournament-project\backend\
```

### La base de données n'existe pas ?

**Solution : Recréer la base**
```
1. http://localhost/phpmyadmin
2. Onglet "Bases de données"
3. Si "tournament_db" n'existe pas, refaire l'étape 2
```

---

## 📝 Configuration personnalisée

### Changer le mot de passe MySQL

Si votre MySQL a un mot de passe :
```php
// Dans backend/config/database.php
private $password = 'votre_mot_de_passe';
```

### Changer le port Apache

Si Apache utilise un autre port (ex: 8080) :
```javascript
// Dans frontend/tournament-dashboard.html
const API_BASE_URL = 'http://localhost:8080/tournament-project/backend/api';
```

### Utiliser un autre nom de dossier

Si vous renommez le dossier :
```javascript
// Dossier : C:\xampp\htdocs\mon-tournoi\backend\
const API_BASE_URL = 'http://localhost/mon-tournoi/backend/api';
```

---

## ✅ Checklist de vérification

Avant de lancer l'application, vérifier :

- [ ] XAMPP est installé
- [ ] Apache est démarré (voyant VERT)
- [ ] MySQL est démarré (voyant VERT)
- [ ] La base `tournament_db` existe dans phpMyAdmin
- [ ] Le dossier `backend` est dans `htdocs`
- [ ] L'URL de l'API est correcte dans le fichier HTML
- [ ] Le navigateur est à jour (Chrome, Firefox, Edge)

---

## 🎯 Premiers pas

### 1. Vue d'ensemble
Au démarrage, vous verrez :
- Statistiques globales
- Graphiques
- 4 tournois disponibles

### 2. Cliquer sur un tournoi
Pour voir les détails et gérer les participants

### 3. Inscrire un joueur
Utiliser le champ "Inscription Nouveau Participant"

### 4. Créer des poules
Bouton "Créer Poules" (minimum 4 joueurs en lice)

---

## 🔍 Tests de l'API

Copier ces URL dans votre navigateur pour tester :

```
✅ Liste des tournois :
http://localhost/tournament-project/backend/api/index.php/tournaments

✅ Statistiques générales :
http://localhost/tournament-project/backend/api/index.php/stats

✅ Participants d'un tournoi :
http://localhost/tournament-project/backend/api/index.php/participants?tournament_id=fortnite

✅ Poules d'un tournoi :
http://localhost/tournament-project/backend/api/index.php/pools?tournament_id=fortnite
```

Si vous voyez du JSON → ✅ L'API fonctionne !
Si vous voyez une erreur → ❌ Vérifier la configuration

---

## 🛠️ Outils utiles

### phpMyAdmin
```
http://localhost/phpmyadmin
```
Pour gérer la base de données

### Logs Apache
```
C:\xampp\apache\logs\error.log
```
Pour voir les erreurs PHP

### Console navigateur
```
Appuyer sur F12
Onglet "Console"
```
Pour voir les erreurs JavaScript

---

## 📞 Aide rapide

| Problème | Solution |
|----------|----------|
| Page blanche | Vérifier la console (F12) |
| "Cannot connect to database" | Démarrer MySQL dans XAMPP |
| "404 Not Found" | Vérifier le chemin dans htdocs |
| "CORS Error" | Mettre backend dans htdocs |
| Données vides | Tester l'URL de l'API dans le navigateur |

---

## 🎓 Ressources

- **Documentation PHP** : https://www.php.net/manual/fr/
- **Documentation React** : https://fr.react.dev/
- **Documentation MySQL** : https://dev.mysql.com/doc/
- **Guide XAMPP** : https://www.apachefriends.org/faq_windows.html

---

**Besoin d'aide ?**
1. Consulter le README.md complet
2. Vérifier la checklist ci-dessus
3. Tester les URL de l'API
4. Consulter les logs d'erreur

Bon tournoi ! 🎮🏆
