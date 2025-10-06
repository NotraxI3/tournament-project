# 🔄 MIGRATION DE LA BASE DE DONNÉES

## Contexte

Ce fichier vous guide pour mettre à jour votre base de données existante afin d'ajouter les nouveaux champs pour les participants :
- **Prénom**
- **Nom** 
- **Âge**
- **Téléphone**

## 📋 Options de migration

### Option 1 : Migration automatique (RECOMMANDÉ si vous avez des données)

Cette option conserve vos données existantes en séparant le champ `name` en `prenom` et `nom`.

#### Étapes :

1. **Ouvrir phpMyAdmin**
   ```
   http://localhost/phpmyadmin
   ```

2. **Sélectionner la base `tournament_db`**

3. **Aller dans l'onglet "SQL"**

4. **Copier tout le contenu du fichier** `migration_add_participant_fields.sql`

5. **Coller dans la zone SQL et cliquer "Exécuter"**

✅ Vos données seront préservées !

---

### Option 2 : Nouvelle installation (si vous n'avez pas de données importantes)

Cette option recrée complètement la base de données avec la nouvelle structure.

#### Étapes :

1. **Ouvrir phpMyAdmin**
   ```
   http://localhost/phpmyadmin
   ```

2. **Sélectionner la base `tournament_db`**

3. **Cliquer sur "Opérations" → "Supprimer la base de données"**

4. **Confirmer la suppression**

5. **Aller dans l'onglet "SQL"**

6. **Copier tout le contenu du fichier** `tournament_db_v2.sql`

7. **Coller et cliquer "Exécuter"**

✅ Nouvelle base de données créée avec des données de test !

---

## 🧪 Vérification de la migration

Après la migration, vérifiez que tout fonctionne :

### 1. Vérifier la structure de la table

Dans phpMyAdmin → Base `tournament_db` → Table `participants` → Onglet "Structure"

Vous devriez voir ces colonnes :
- ✅ `id`
- ✅ `tournament_id`
- ✅ `prenom`
- ✅ `nom`
- ✅ `age`
- ✅ `telephone`
- ✅ `status`
- ✅ `inscription_date`
- ✅ `position_attente`
- ✅ `created_at`
- ✅ `updated_at`

### 2. Tester l'API

Ouvrir dans le navigateur :
```
http://192.168.1.182/tournament-project/backend/api/index.php/participants?tournament_id=fortnite
```

Vous devriez voir un JSON avec les champs `prenom`, `nom`, `age`, `telephone`.

### 3. Tester l'interface

1. Ouvrir `tournament-dashboard.html`
2. Cliquer sur un tournoi
3. Inscrire un nouveau participant avec tous les champs
4. Vérifier que le participant s'affiche avec son nom complet

---

## ⚠️ Problèmes courants

### Erreur : "Column 'name' doesn't exist"

✅ **Solution** : Exécuter le script de migration `migration_add_participant_fields.sql`

### Erreur : "Unknown column 'prenom' in field list"

✅ **Solution** : La migration n'a pas été effectuée. Relancer le script SQL.

### Les anciens participants n'ont pas de téléphone

✅ **Normal** : Le script de migration met des valeurs par défaut. Vous pouvez les modifier manuellement dans phpMyAdmin ou via l'interface.

---

## 🔙 Rollback (annuler la migration)

Si vous voulez revenir à l'ancienne structure :

```sql
USE tournament_db;

ALTER TABLE participants 
ADD COLUMN name VARCHAR(255) AFTER tournament_id;

UPDATE participants 
SET name = CONCAT(prenom, ' ', nom);

ALTER TABLE participants 
DROP COLUMN prenom,
DROP COLUMN nom,
DROP COLUMN age,
DROP COLUMN telephone,
DROP COLUMN position_attente;

ALTER TABLE participants 
MODIFY COLUMN status ENUM('En lice', 'Éliminé') DEFAULT 'En lice';
```

---

## 📞 Support

Si vous rencontrez des problèmes :

1. Vérifier les logs Apache : `sudo tail -50 /var/log/apache2/error.log`
2. Vérifier les logs MariaDB : `sudo tail -50 /var/log/mysql/error.log`
3. Consulter la console du navigateur (F12)

---

**Date de migration** : Octobre 2024  
**Version** : 2.0
