# 🎮 Dashboard Tournois Esports

Application complète de gestion de tournois esports avec base de données MySQL et API PHP.

## 📋 Prérequis

- **XAMPP** ou **WAMP** ou **MAMP** (serveur local avec PHP et MySQL)
- Un navigateur web moderne (Chrome, Firefox, Edge, Safari)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur

## 🚀 Installation

### Étape 1 : Installer XAMPP (si pas déjà fait)

1. Télécharger XAMPP : https://www.apachefriends.org/fr/index.html
2. Installer XAMPP
3. Démarrer **Apache** et **MySQL** depuis le panneau de contrôle XAMPP

### Étape 2 : Créer la base de données

1. Ouvrir votre navigateur et aller sur : http://localhost/phpmyadmin
2. Cliquer sur l'onglet **"SQL"**
3. Ouvrir le fichier `database/tournament_db.sql` avec un éditeur de texte
4. Copier tout le contenu du fichier
5. Coller dans la zone SQL de phpMyAdmin
6. Cliquer sur **"Exécuter"**

✅ La base de données `tournament_db` est maintenant créée avec des données de test !

### Étape 3 : Installer les fichiers PHP

1. Copier le dossier `backend` dans le dossier `htdocs` de XAMPP
   - Sur Windows : `C:\xampp\htdocs\`
   - Sur Mac : `/Applications/XAMPP/htdocs/`
   - Sur Linux : `/opt/lampp/htdocs/`

2. Renommer le dossier si nécessaire pour avoir : `htdocs/tournament-project/backend/`

### Étape 4 : Configurer la connexion à la base de données

1. Ouvrir le fichier `backend/config/database.php`
2. Vérifier les paramètres de connexion :
   ```php
   private $host = 'localhost';      // Hôte MySQL
   private $db_name = 'tournament_db'; // Nom de la base de données
   private $username = 'root';         // Utilisateur MySQL (par défaut: root)
   private $password = '';             // Mot de passe MySQL (par défaut: vide)
   ```
3. Modifier si nécessaire selon votre configuration

### Étape 5 : Tester l'API

1. Ouvrir votre navigateur
2. Aller sur : http://localhost/tournament-project/backend/api/index.php/tournaments
3. Vous devriez voir une liste de tournois en format JSON

✅ Si vous voyez du JSON, l'API fonctionne !

### Étape 6 : Lancer l'interface web

1. Ouvrir le fichier `frontend/tournament-dashboard.html` dans votre navigateur
   - Double-cliquer sur le fichier
   - OU faire clic droit → "Ouvrir avec" → votre navigateur

2. **IMPORTANT** : Si l'API ne se connecte pas, vérifier l'URL dans le fichier HTML
   - Ouvrir `tournament-dashboard.html` avec un éditeur de texte
   - Trouver la ligne : `const API_BASE_URL = 'http://localhost/tournament-project/backend/api';`
   - Modifier selon l'emplacement de vos fichiers

## 📁 Structure du projet

```
tournament-project/
├── database/
│   └── tournament_db.sql          # Script de création de la base de données
├── backend/
│   ├── config/
│   │   └── database.php           # Configuration de connexion à MySQL
│   ├── classes/
│   │   ├── Tournament.php         # Gestion des tournois
│   │   ├── Participant.php        # Gestion des participants
│   │   └── Pool.php               # Gestion des poules
│   └── api/
│       └── index.php              # API REST
└── frontend/
    └── tournament-dashboard.html   # Interface utilisateur
```

## 🎯 Fonctionnalités

### Vue d'ensemble
- ✅ Statistiques en temps réel (inscrits, en lice, éliminés)
- ✅ Graphiques interactifs (participants par tournoi, répartition des statuts)
- ✅ 4 tournois configurés : Fortnite, Mario Kart 8, Smash Bros Ultimate, FC 26

### Gestion des participants
- ✅ Inscription de nouveaux participants (opérateur uniquement)
- ✅ Mise à jour des statuts (En lice / Éliminé)
- ✅ Suppression de participants
- ✅ Historique des inscriptions avec dates

### Génération de poules
- ✅ Création automatique de poules aléatoires
- ✅ Répartition équitable des joueurs en lice
- ✅ Affichage visuel des poules (A, B, C...)
- ✅ Support de différentes tailles de poules

### Persistance des données
- ✅ Toutes les données sont sauvegardées en base de données MySQL
- ✅ Synchronisation automatique entre l'interface et la base
- ✅ Actualisation en temps réel

## 🔧 Résolution des problèmes

### Erreur : "Erreur de connexion à la base de données"
- Vérifier que MySQL est démarré dans XAMPP
- Vérifier les paramètres dans `backend/config/database.php`
- Vérifier que la base `tournament_db` existe dans phpMyAdmin

### Erreur : "Erreur lors du chargement des données"
- Vérifier que Apache est démarré dans XAMPP
- Vérifier l'URL de l'API dans `tournament-dashboard.html`
- Ouvrir la console du navigateur (F12) pour voir les erreurs détaillées

### Les données ne s'affichent pas
- Ouvrir http://localhost/tournament-project/backend/api/index.php/stats
- Si vous voyez du JSON, l'API fonctionne
- Vérifier la console du navigateur (F12)

### Erreur CORS
- Vérifier que les fichiers PHP sont dans le dossier `htdocs`
- Les headers CORS sont déjà configurés dans `api/index.php`

## 📊 API Endpoints

- `GET /tournaments` - Liste tous les tournois
- `GET /tournaments/{id}` - Détails d'un tournoi
- `GET /participants?tournament_id={id}` - Liste des participants d'un tournoi
- `POST /participants` - Ajouter un participant
- `PUT /participants` - Modifier le statut d'un participant
- `DELETE /participants` - Supprimer un participant
- `GET /pools?tournament_id={id}` - Liste des poules d'un tournoi
- `POST /pools` - Créer des poules aléatoires
- `GET /stats` - Statistiques générales

## 🎨 Technologies utilisées

- **Frontend** : React.js, Tailwind CSS, Recharts
- **Backend** : PHP 7.4+, PDO
- **Base de données** : MySQL 5.7+
- **Architecture** : REST API

## 📝 Modifier les données

### Ajouter un tournoi
1. Aller sur phpMyAdmin : http://localhost/phpmyadmin
2. Sélectionner la base `tournament_db`
3. Cliquer sur la table `tournaments`
4. Cliquer sur "Insérer"
5. Remplir les champs et valider

### Ajouter un participant manuellement
1. Utiliser l'interface web (section "Inscription Nouveau Participant")
2. OU via phpMyAdmin dans la table `participants`

### Modifier un tournoi existant
1. phpMyAdmin → Base `tournament_db` → Table `tournaments`
2. Cliquer sur "Modifier" sur la ligne du tournoi
3. Modifier les informations et valider

## 🔒 Sécurité

- Les données sont nettoyées avec `htmlspecialchars()` et `strip_tags()`
- Utilisation de Prepared Statements PDO pour éviter les injections SQL
- Validation des données côté serveur
- Headers CORS configurés pour autoriser uniquement les requêtes locales

## 🎓 Guide d'utilisation

### Pour inscrire un participant
1. Cliquer sur un tournoi dans la vue d'ensemble
2. Entrer le nom du joueur dans le champ "Inscription Nouveau Participant"
3. Cliquer sur "Inscrire"

### Pour modifier le statut d'un participant
1. Dans la liste des participants, utiliser le menu déroulant
2. Sélectionner "En lice" ou "Éliminé"
3. Le statut est mis à jour automatiquement

### Pour créer des poules
1. S'assurer d'avoir au moins 4 joueurs "En lice"
2. Cliquer sur "Créer Poules"
3. Les poules sont générées aléatoirement
4. Chaque création efface les anciennes poules

### Pour supprimer un participant
1. Cliquer sur "Supprimer" à côté du participant
2. Confirmer la suppression
3. Le participant est retiré de la base de données

## 📈 Améliorations futures possibles

- [ ] Système d'authentification pour les opérateurs
- [ ] Historique des matchs et résultats
- [ ] Export des poules en PDF
- [ ] Notifications en temps réel
- [ ] Gestion des brackets de tournoi
- [ ] Statistiques avancées par joueur
- [ ] Interface d'administration complète
- [ ] Support multi-langues

## 💡 Conseils

1. **Sauvegarder régulièrement** : Exporter la base de données via phpMyAdmin
2. **Tester localement** : Toujours tester les modifications sur XAMPP d'abord
3. **Console navigateur** : Utiliser F12 pour déboguer les erreurs JavaScript
4. **Logs PHP** : Consulter les logs d'erreur dans `xampp/apache/logs/error.log`

## 📞 Support

En cas de problème :
1. Vérifier que Apache et MySQL sont démarrés
2. Consulter la section "Résolution des problèmes"
3. Vérifier la console du navigateur (F12)
4. Tester les endpoints API directement dans le navigateur

## 📄 Licence

Ce projet est libre d'utilisation pour des fins éducatives et personnelles.

## 🙏 Crédits

- React.js - Framework JavaScript
- Recharts - Bibliothèque de graphiques
- Tailwind CSS - Framework CSS
- PHP & MySQL - Backend et base de données

---

**Version** : 1.0.0  
**Date** : 2024  
**Auteur** : Dashboard Tournois Esports

Bon tournoi ! 🎮🏆
# tournament-project
