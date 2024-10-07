"# E-commerce-Mall-WebApp" 

Ce dépôt contient une application web de centre commercial (Mall) développée avec le framework Symfony. L'application permet aux utilisateurs de naviguer et d'acheter des produits de différents magasins virtuels, offrant une expérience de gestion centralisée des boutiques en ligne.

### Fonctionnalités principales :
- **Gestion des utilisateurs** : Inscription, connexion, et gestion des comptes utilisateurs.
- **Système de panier** : Ajout, suppression et mise à jour des articles dans le panier d'achat.
- **Paiement en ligne** : Intégration de passerelles de paiement sécurisées pour des transactions en ligne.
- **Interface d'administration** : Gestion des produits, des commandes et des utilisateurs pour les administrateurs du site.
- **Catalogue multi-boutiques** : Présentation de produits de plusieurs magasins avec des filtres et des catégories.

### Technologies utilisées :
- Symfony (framework PHP)
- MySQL (base de données)
- Twig (moteur de templates)
- Bootstrap (design responsive)
- API REST pour certaines fonctionnalités

### Instructions d'installation :
1. Cloner le dépôt :
    ```bash
    git clone https://github.com/votre-utilisateur/e-commerce-mall-webapp.git
    ```

2. Installer les dépendances :
    ```bash
    composer install
    ```

3. Configurer la base de données dans le fichier `.env` et exécuter les migrations :
    ```bash
    php bin/console doctrine:migrations:migrate
    ```

4. Lancer le serveur local :
    ```bash
    symfony server:start
    ```

### Contribution :
Les contributions sont les bienvenues. Veuillez soumettre une demande d'extraction avec une description détaillée des modifications proposées.
