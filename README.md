

# Boutique e-commerce (Symfony 8.1)

## Developper par God-Louange THOMBET-KENDE et Djennefing SISSOKO


Projet EEMI : boutique en ligne avec panier, historique de commandes et rôles ADMIN / SUPER_ADMIN.
Paiement fictif. Interface en français et en anglais.

## Installation (procédure binôme)

```bash
git clone https://github.com/GodLouange/project.git
cd project
composer install
cp .env .env.local          # puis mettre à jour DATABASE_URL dans .env.local
php bin/console d:d:c
php bin/console d:m:m
symfony serve -d            # ou : php -S 127.0.0.1:8000 -t public
```

## Créer les comptes administrateurs

1. S'inscrire normalement sur le site (`/inscription`).
2. Donner le rôle en ligne de commande :

```bash
php bin/console app:changer-role admin@exemple.fr ROLE_ADMIN
php bin/console app:changer-role super@exemple.fr ROLE_SUPER_ADMIN
```

Se déconnecter / reconnecter pour que le nouveau rôle soit pris en compte.

## Pages

| URL | Accès | Rôle |
|---|---|---|
| `/` | Liste des produits (+ formulaire d'ajout pour un ADMIN) | tous |
| `/produit/{id}` | Fiche produit, ajout au panier (+ modifier / supprimer pour un ADMIN) | tous |
| `/panier` | Panier en cours, suppression de lignes, achat | connecté |
| `/compte` | Modification du profil + historique des commandes | connecté |
| `/compte/commande/{id}` | Contenu d'une commande | propriétaire |
| `/super-admin/paniers` | Paniers non achetés | SUPER_ADMIN |
| `/super-admin/utilisateurs` | Inscrits du jour (du plus récent au plus ancien) | SUPER_ADMIN |
| `/connexion`, `/inscription`, `/deconnexion` | Authentification | tous |
| `/langue/fr`, `/langue/en` | Changement de langue | tous |

## Organisation du code

- `src/Entity` : Produit, Utilisateur, Panier, ContenuPanier
- `src/Controller` : un contrôleur par espace (Accueil, Produit, Panier, Compte, SuperAdmin, Security, Langue)
- `src/Form` : formulaires avec validation (contraintes `#[Assert\...]`)
- `src/Service/PhotoUploader.php` : upload des photos dans `public/uploads/produits`
- `src/EventSubscriber/LocaleSubscriber.php` : applique la langue choisie
- `src/Command/ChangerRoleCommand.php` : attribution des rôles
- `templates/bundles/TwigBundle/Exception` : pages d'erreur personnalisées (403, 404, autres)
- `translations` : textes FR / EN (`messages`) et messages de validation (`validators`)

## Tester les pages d'erreur en développement

En mode `dev`, Symfony affiche sa page de débogage. Pour voir les pages personnalisées :
`/_error/404`, `/_error/403`, `/_error/500`.
