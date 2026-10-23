# Plan d'implémentation - Module M1 « Accès et utilisateurs » (Eventia)

## 1. Identification

| Élément | Valeur |
|---|---|
| Projet | Eventia, application web de gestion des événements d'une agence événementielle |
| Module | M1 - Accès et utilisateurs (premier module du socle, répertoire `app/Modules/Access/`) |
| Sources | Cahier des charges v2.0 (F1, F2, RG17, US1, US2, T1, T2) ; Document d'architecture v2.0 (§4.2, §5.2.1, §7, §8.1, §12.2, ADR-001, ADR-002) |
| Version du plan | 0.2 |
| Date | 22 octobre 2026 |
| Auteur | Brichton |
| Statut | **Brouillon à valider** (étape 0 en cours) |
| Validateur | À renseigner (enseignant / référent de l'agence) |
| Charge indicative | Environ 5 à 6 jours (le planning du cahier des charges prévoit 2 semaines pour tout le socle M1 à M4) |

Légende des statuts d'étape : à faire, en cours, bloquée, à valider, validée. « Codé », « testé » et « validé » sont des états distincts.

## 2. Contexte, problème et objectifs

**Utilisateurs concernés.** Le personnel de l'agence, avec un compte nominatif et un rôle parmi trois : administrateur, chargé d'événements, consultant. L'application est strictement interne.

**Besoin observable.** Aucune donnée de l'agence ne doit être lisible sans session valide, et chaque collaborateur ne doit pouvoir faire que ce que son rôle autorise. L'administrateur doit pouvoir créer, modifier, désactiver et réactiver des comptes sans jamais voir un mot de passe.

**Parcours principal.** Un collaborateur ouvre l'application, arrive sur l'écran de connexion, saisit son identifiant et son mot de passe, puis atteint le tableau de bord avec des menus adaptés à son rôle. Un administrateur ouvre « Utilisateurs » et gère les comptes.

**Résultat attendu.** M1 est la porte d'entrée et le socle de sécurité. Il fournit aux autres modules trois choses : l'utilisateur courant, la décision « cet utilisateur a-t-il le droit de faire cette action ? », et la liste des utilisateurs actifs. Il ne dépend d'aucun module métier. Seule dépendance sortante prévue : M11 (journal), sous réserve de la question Q2.

**Objectifs, règles, choix techniques et critères sont séparés** (voir §5, §6, §7) pour qu'aucun ne soit confondu avec un autre.

## 3. Périmètre

### Inclus

- Connexion et déconnexion (F1, US1), avec message d'échec indifférencié, limitation des essais et session sécurisée.
- Gestion des comptes par l'administrateur (F2, US2) : création, modification (nom, identifiant, rôle), désactivation, réactivation, réinitialisation du mot de passe.
- Matrice des droits des trois rôles (RG17) appliquée côté serveur à deux niveaux (middleware de route puis service métier), avec refus par défaut.
- Les cinq services exposés aux autres modules : `Authentifier`, `UtilisateurCourant`, `VerifierDroit`, `UtilisateurParIdentifiantTechnique`, `UtilisateursActifs`.
- Socle d'interface minimal nécessaire à M1 : gabarit de page accessible, menu filtré par rôle, écrans Connexion et Utilisateurs, page provisoire « Tableau de bord » (voir §6.4).
- Premier compte administrateur (amorçage), car personne ne peut le créer depuis l'interface.
- En-têtes de sécurité, pages d'erreur neutres et journal technique des échecs d'authentification.

### Exclus explicitement

- Suppression d'un compte (interdite : un compte désactivé reste référencé, cf. RG15 et §8.1 de l'architecture).
- Réinitialisation de mot de passe par e-mail, « mot de passe oublié », double authentification, connexion via un service tiers (aucun appel sortant, ADR-002).
- Création de rôles personnalisés : les trois rôles sont figés en version 2.0.
- Tableau de bord réel, alertes, calendrier (M9, M10). M1 ne fournit qu'une page provisoire.
- Contenu du journal d'historique et son écran (M11). M1 n'en est que producteur, via un contrat (voir Q2).
- Gestion des lieux, clients, prestataires, événements : M1 ne fait que **fournir** les droits qui les protègent.
- Déploiement en production (action distincte, autorisation explicite).

## 4. Existant, stack et contraintes

**Constat important : aucun dépôt de code n'a été fourni avec les documents.** Rien n'est donc « déjà présent ». Tout est classé « à créer » ou « à confirmer ». Les versions exactes sont à relever à l'étape 0 et à inscrire ici avant d'écrire du code.

| Élément | Source | État |
|---|---|---|
| PHP, framework Laravel | Cahier des charges §7, architecture §7.2 | Poste du développeur : PHP 8.4.23, Laravel framework 13.38.0 (squelette 13.11.0), confirmés par `php artisan --version` (Laravel Framework 13.38.0) le 22 oct. 2026 ; projet créé dans `C:\dev\eventia` |
| MySQL | idem | Version **à confirmer** (Q1) ; les tests de concurrence et d'unicité tournent sur la vraie base MySQL, pas sur SQLite |
| Rendu serveur Blade, sans application monopage | Architecture §7.2 | Retenu |
| Authentification et sessions Laravel, hachage à coût ajustable, CSRF intégré | Architecture §7.2, §8.1 | Retenu : on réutilise le mécanisme du framework, on n'en construit pas un second |
| Monolithe modulaire, un répertoire par module sous `app/Modules/` | ADR-001 | Retenu |
| Dépendances : couches présentation → application → domaine → infrastructure | Architecture §4.2, §7.1 | Retenu |
| Aucun service externe, aucun appel réseau sortant | ADR-002 | Retenu |
| Git, branches `main` / `develop` / `feature/...`, tests obligatoires avant fusion | Architecture §10.2 | Retenu ; branche proposée : `feature/m1-acces-utilisateurs` |
| Accessibilité WCAG 2.2 AA, exigence de recette | Architecture §4.2 | Retenu (critères 2.2.1, 3.3.1, 3.3.3, 3.3.8 directement concernés) |
| Intégration continue (GitHub Actions) | Non exigée par les documents | **Proposition** : exécuter les tests sur un service MySQL (Q13) |

**Emplacements prévus** (à adapter à la structure réellement constatée) :

```
app/Modules/Access/                 domaine et services du module (enum Role, matrice, services)
app/Modules/Shared/                 contrats de service et DTO partagés (UtilisateurDTO, interfaces)
app/Http/Controllers/Access/        contrôleurs minces
app/Http/Middleware/                authentification, permission, compte actif, en-têtes de sécurité
app/Http/Requests/Access/           validation de la forme des formulaires
database/migrations/                table des utilisateurs
database/seeders/ , factories/      administrateur initial, données de démonstration, fabriques de test
resources/views/access/ , layouts/  gabarits Blade
routes/                             routes et permissions déclarées
tests/Unit/Access/ , tests/Feature/Access/
docs/adr/                           décisions issues des questions ouvertes
```

## 5. Règles métier et autorisations

### 5.1 Règles du module

Chaque règle est vérifiable. La colonne « Source » indique d'où elle vient. Les règles marquées **(proposée)** ne figurent pas dans le cahier des charges : elles complètent un trou et doivent être validées (voir §8).

| Réf. | Règle | Source |
|---|---|---|
| R01 | Toute route est protégée par une authentification, sauf `GET /connexion` et `POST /connexion`. Une route sans permission explicitement déclarée est refusée (refus par défaut). Un utilisateur non connecté qui demande une autre page est renvoyé vers la connexion. | US1, arch. §4.2, §8.1 |
| R02 | L'identifiant de connexion est obligatoire, unique dans toute la base, **insensible à la casse**, à la création comme à la modification. Un doublon est refusé avec un message clair. | US2, arch. §5.2.1 |
| R03 | Un compte désactivé ne peut plus se connecter. Ses sessions déjà ouvertes sont invalidées. Il n'est jamais supprimé : il reste référencé (événements, tâches, journal). | US2, arch. §5.2.1, §8.1 |
| R04 | L'échec de connexion renvoie **toujours le même message**, quel que soit le motif (identifiant inconnu, mot de passe faux, compte désactivé). Le temps de réponse ne doit pas non plus révéler l'existence du compte. | US1, arch. §8.1 |
| R05 | Le nombre d'essais de connexion est limité par couple identifiant/adresse, avec temporisation progressive. Le blocage s'applique aussi aux identifiants inexistants (sinon il révélerait les comptes existants). Les valeurs exactes sont à fixer (Q5). | arch. §8.1 (A07) |
| R06 | Le mot de passe est haché avec un algorithme à coût ajustable, jamais affiché, jamais journalisé, jamais renvoyé dans une réponse, y compris à un administrateur. Une politique de longueur minimale s'applique à la création et au changement (valeur à fixer, Q4). | Cahier §5 (sécurité), §6.7, arch. §8.1 |
| R07 | Le rôle est l'une de ces trois valeurs : administrateur, chargé d'événements, consultant. Toute autre valeur est refusée. | cahier §3, §6.7 |
| R08 | Les droits de chaque rôle suivent exactement la matrice du §5.2. Ils sont vérifiés côté serveur à **deux niveaux** : middleware de route, puis service métier. Masquer un bouton n'est jamais une protection. | RG17, arch. §4.2 |
| R09 | Le rôle consultant n'ouvre droit à aucune écriture. | arch. §5.2.1 |
| R10 | Seul un administrateur accède à la gestion des comptes (et, plus tard, des lieux). Un non-administrateur reçoit un refus 403. | cahier §3, US2 |
| R11 | Toute requête qui modifie l'état exige un jeton anti-CSRF valide. | arch. §8.1 |
| R12 | Cookie de session `Secure`, `HttpOnly`, `SameSite` ; identifiant de session renouvelé à la connexion ; déconnexion qui invalide la session **côté serveur** ; expiration après inactivité avec avertissement préalable. | arch. §8.1 (A02, A07, WCAG 2.2.1) |
| R13 | Aucune donnée personnelle sensible dans les journaux techniques : ni mot de passe, ni identifiant en clair, ni contenu de formulaire. | arch. §8.2 |
| R14 | Désactiver un compte ne modifie pas les événements et tâches qui lui sont affectés. Le compte n'est plus proposé par `UtilisateursActifs`. | arch. §5.2.1 |
| R15 **(proposée)** | Le dernier administrateur actif ne peut être ni désactivé ni rétrogradé. Un administrateur ne peut pas se désactiver lui-même. Cela évite qu'il n'existe plus aucun compte capable de gérer les comptes. | Hypothèse (Q8) |
| R16 **(proposée)** | La création, la modification (nom, identifiant, rôle), la désactivation, la réactivation et la réinitialisation d'un compte sont tracées. Le support exact dépend de la décision Q2. | arch. §5.2.1 (dépendance M11) |

### 5.2 Matrice des droits (RG17), à implémenter telle quelle

Reprise du tableau du cahier des charges §3. Chaque ligne devient une **permission nommée** dans le code.

| Permission (nom proposé) | Administrateur | Chargé d'événements | Consultant |
|---|---|---|---|
| `consulter` (événements, clients, lieux, prestataires, calendrier, tableau de bord) | Oui | Oui | Oui |
| `exporter` | Oui | Oui | Oui |
| `evenements.ecrire` (créer, modifier, confirmer, annuler) | Oui | Oui | Non |
| `affectations_checklist.gerer` | Oui | Oui | Non |
| `clients_prestataires.gerer` | Oui | Oui | Non |
| `journal.consulter` | Oui | Oui | Non |
| `lieux.gerer` | Oui | Non | Non |
| `utilisateurs.gerer` | Oui | Non | Non |

⚠ **Incohérence à trancher (Q3).** Le document d'architecture (§4.2) range la consultation du journal parmi les trois actions « réservées à l'administrateur », alors que le cahier des charges (§3, US21) et l'architecture elle-même (§8.1, §12.2) l'ouvrent aussi au chargé d'événements. Le plan suit le **cahier des charges** (référence fonctionnelle) tant que le point n'est pas tranché.

## 6. Données, API et interface

### 6.1 Données : entité Utilisateur

Table des utilisateurs (nom de table et de colonnes à confirmer, Q9) :

| Attribut | Type | Contraintes |
|---|---|---|
| id | entier auto-incrémenté | clé primaire, non modifiable |
| nom | texte | obligatoire, 100 caractères maximum |
| identifiant | texte | obligatoire, **unique**, insensible à la casse (normalisé en minuscules à l'enregistrement + index unique) |
| mot_de_passe | texte | obligatoire, empreinte hachée, jamais exposé |
| role | liste (enum) | obligatoire : administrateur, chargé d'événements, consultant |
| actif | booléen | obligatoire, vrai par défaut |
| derniere_connexion | date-heure | facultatif, renseigné à chaque connexion réussie |
| created_at, updated_at | date-heure | automatiques |

Points de conception :

- Aucune suppression physique : aucune route ni méthode de suppression. Clés étrangères des autres modules vers cette table en `RESTRICT`.
- La table des sessions est en base (pilote de session « database ») pour pouvoir invalider les sessions d'un compte désactivé (R03) et rendre la déconnexion effective côté serveur (R12).
- Un index unique SQL sur `identifiant` est le **dernier rempart** de R02 ; l'application intercepte la violation et la transforme en erreur de validation, jamais en erreur technique.
- Les colonnes Laravel par défaut non prévues par le cahier des charges (e-mail, vérification d'e-mail, jeton « se souvenir de moi ») sont **retirées** : ne pas inventer de champ. « Se souvenir de moi » est exclu (contraire à l'expiration par inactivité).

### 6.2 Services exposés (façade, contrats dans `app/Modules/Shared/`)

Les autres modules n'accèdent jamais à la table ni au modèle : ils passent par ces contrats et reçoivent un objet de transfert (`UtilisateurDTO` : id, nom, rôle, actif), jamais le modèle Eloquent.

| Service | Entrée → sortie | Remarque |
|---|---|---|
| `Authentifier` | identifiant + mot de passe → succès (DTO) ou échec indifférencié | applique R04, R05, R12 ; met à jour la dernière connexion |
| `UtilisateurCourant` | - → DTO ou rien | lit l'utilisateur de la session |
| `VerifierDroit` | utilisateur + permission → booléen | seule source de vérité de la matrice (R08) |
| `UtilisateurParIdentifiantTechnique` | id → nom et rôle | pour afficher un auteur ou un responsable |
| `UtilisateursActifs` | filtre optionnel de rôles → liste (id, nom) | filtre de rôles ajouté pour M5/M7 (Q12) |

Cas d'usage internes à M1 (non exposés) : création, modification, désactivation, réactivation, réinitialisation du mot de passe.

### 6.3 Routes

Reprise de §12.2 de l'architecture, complétée par les routes strictement nécessaires (marquées *). Convention retenue par l'architecture : les **changements d'état passent par un verbe explicite**, pas par un champ du formulaire de modification.

| Méthode | Route | Rôles | Finalité |
|---|---|---|---|
| GET | `/connexion` | Public | Écran de connexion |
| POST | `/connexion` | Public | Vérifier les identifiants, ouvrir la session (F1) |
| POST | `/deconnexion` | Tous | Fermer la session, l'invalider côté serveur |
| GET | `/tableau-de-bord` | Tous | **Page provisoire** (remplacée par M10) |
| GET | `/utilisateurs` | Administrateur | Liste des comptes avec rôle et état |
| GET | `/utilisateurs/creer` * | Administrateur | Formulaire de création |
| POST | `/utilisateurs` | Administrateur | Créer un compte |
| GET | `/utilisateurs/{id}/modifier` * | Administrateur | Formulaire prérempli |
| PUT | `/utilisateurs/{id}` | Administrateur | Modifier nom, identifiant, rôle |
| POST | `/utilisateurs/{id}/desactiver` * | Administrateur | Désactiver (R03, R15) |
| POST | `/utilisateurs/{id}/reactiver` * | Administrateur | Réactiver |
| POST | `/utilisateurs/{id}/mot-de-passe` * | Administrateur | Réinitialiser le mot de passe |
| POST | `/session/prolonger` * | Tous | Prolonger la session après l'avertissement (WCAG 2.2.1) |

Écart à documenter (Q10) : §12.2 écrit « Modifier un compte **ou le désactiver** » sur `PUT /utilisateurs/{id}`. Le plan sépare la désactivation en route dédiée, par cohérence avec la convention de nommage de l'architecture (un champ caché ne doit pas pouvoir changer un état). À acter dans un ADR puis reporter dans le document d'architecture.

**Codes de réponse.** Interface rendue côté serveur : succès par redirection avec message ; 422 pour une saisie invalide (erreurs rattachées au champ, valeurs conservées) ; 403 pour une action interdite ; 429 pour trop de tentatives de connexion ; redirection vers `/connexion` pour une session absente ou expirée ; message neutre pour toute erreur inattendue (« Une erreur est survenue. Veuillez réessayer. Si le problème persiste, contactez l'assistance. »), détail dans le journal applicatif uniquement.

### 6.4 Écrans et états à gérer

| Écran | États obligatoires |
|---|---|
| Connexion | repos ; envoi en cours ; échec (message unique) ; trop de tentatives (délai indiqué, sans révéler si le compte existe) ; session expirée ; champ mot de passe compatible avec les gestionnaires de mots de passe, avec option d'affichage (WCAG 3.3.8) |
| Utilisateurs (liste) | liste vide ; liste paginée ; comptes inactifs marqués par un **libellé texte** (pas par la couleur seule) ; accès refusé pour un non-administrateur |
| Utilisateurs (formulaire) | champs obligatoires signalés ; erreurs au niveau du champ + résumé en tête avec liens ; saisie conservée en cas d'erreur ; confirmation avant désactivation (WCAG 3.3.4) |
| Tableau de bord provisoire | titre, nom de l'utilisateur, rôle, menu adapté ; mention claire qu'il sera remplacé par M10 |
| Gabarit commun | `lang="fr"`, lien d'évitement, un seul titre de niveau 1, menu identique sur tous les écrans n'affichant que les entrées autorisées, focus visible, cibles d'au moins 24 × 24 px, contact d'assistance toujours au même endroit |
| Avertissement d'expiration | boîte de dialogue accessible au clavier avant la fin de la session, bouton « Prolonger » ; sans JavaScript, redirection vers la connexion avec message |

## 7. Qualité et risques

| Domaine | Exigence vérifiable |
|---|---|
| Sécurité | Matrice appliquée à deux niveaux ; test qui parcourt **toutes** les routes et échoue si l'une n'a ni authentification ni permission déclarée (refus par défaut) ; requêtes préparées uniquement ; échappement automatique ; CSRF ; HTTPS et HSTS ; en-têtes CSP, X-Content-Type-Options, Referrer-Policy ; mode debug désactivé en production |
| Concurrence | Deux créations simultanées du même identifiant : un seul compte, l'autre reçoit une erreur de validation (index unique). Deux administrateurs qui se désactivent simultanément : le dernier administrateur actif reste actif (transaction avec verrou sur les administrateurs actifs) |
| Performance | Liste des comptes paginée ; connexion sous 2 secondes ; coût de hachage choisi pour rester raisonnable sur le serveur cible (mesuré, pas supposé) |
| Accessibilité | Critères 1.3.1, 2.1.1, 2.2.1, 2.4.1, 2.4.7, 3.3.1, 3.3.3, 3.3.4, 3.3.8 vérifiés écran par écran |
| Tests | Règles dans `tests/Unit/Access/`, scénarios dans `tests/Feature/Access/`, nommés d'après la règle ou le scénario vérifié (ex. `R04_message_indifferencie`, `T1_connexion`). Tests sur MySQL réel pour l'unicité, la concurrence et les migrations |
| Documentation | ADR pour chaque décision de §8 ; mise à jour de `docs/architecture.md` pour les écarts (Q3, Q10) ; fichier `.env.example` sans secret |
| Risques propres à M1 | (1) verrouillage de l'administration (R15) ; (2) compte désactivé avec session encore active (R03) ; (3) énumération des comptes par message ou par temps de réponse (R04, R05) ; (4) dérive : ajouter « mot de passe oublié » ou « se souvenir de moi » sans décision (exclus) |

## 8. Questions ouvertes et décisions

Une question qui touche les droits, les données ou une règle métier **bloque l'étape indiquée** ; les autres travaux continuent. « Reco. » est la proposition de départ, à valider, pas une décision.

| N° | Question | Impact | Décideur | Bloque | Reco. |
|---|---|---|---|---|---|
| Q1 | Quelles versions exactes de PHP, Laravel et MySQL, et le dépôt existe-t-il déjà ? Aucun code n'a été fourni. | Syntaxe, fonctions d'authentification disponibles, colonnes générées | Enseignant / développeur | Étape 0 | Relever dans le dépôt, sinon créer un projet vierge et figer les versions ici |
| Q2 | Comment tracer les actions sur les comptes (R16) ? L'entrée du journal (cahier §6.8) exige un **événement** obligatoire, et RG16 ne liste que des actions sur événements. Une action sur un compte n'a pas d'événement. | Donnée (M11), RG16 | Enseignant / référent | Étape 3 (partie traçabilité uniquement) | Définir un contrat `EnregistreurJournal` dans `Shared`, avec en attendant une implémentation qui écrit dans le journal applicatif ; décider ensuite entre événement facultatif dans M11 ou table d'audit distincte |
| Q3 | Le journal est-il réservé à l'administrateur (arch. §4.2) ou ouvert aussi au chargé d'événements (cahier §3, US21, arch. §8.1) ? | Matrice des droits | Référent de l'agence | Étape 1 (ligne `journal.consulter`) | Suivre le cahier des charges : administrateur et chargé |
| Q4 | Politique de mot de passe : longueur minimale et règles de complexité « raisonnables » ? | Validation, écrans | Référent | Étape 3 | 12 caractères minimum, liste de mots de passe courants refusée, pas de composition imposée |
| Q5 | Limitation des essais et durée de session : combien d'essais, quelle temporisation, quelle durée d'inactivité, quand avertir ? | Sécurité, ergonomie | Référent | Étape 2 | 5 essais par couple identifiant/adresse, temporisation doublée de 1 à 15 minutes ; session 60 minutes d'inactivité, avertissement 2 minutes avant |
| Q6 | Un compte désactivé reçoit-il le même message que les autres échecs ? | R04 | Enseignant | Étape 2 | Oui, message identique (évite l'énumération) |
| Q7 | Réinitialisation du mot de passe sans e-mail : l'administrateur saisit-il le nouveau mot de passe ? L'utilisateur doit-il le changer à sa première connexion (ajouterait une colonne) ? L'utilisateur peut-il changer lui-même son mot de passe (écran non prévu) ? | Données, écrans | Référent | Étape 3 | L'administrateur saisit le nouveau mot de passe ; ni changement forcé ni changement en libre-service en version 2.0 |
| Q8 | Valider R15 (protection du dernier administrateur) et l'amorçage du premier administrateur (commande ou seeder, mot de passe lu dans l'environnement) | Règle métier, exploitation | Enseignant | Étapes 1 et 3 | Valider R15 ; amorçage par commande, jamais de mot de passe dans le dépôt |
| Q9 | Convention de nommage du code : français ou anglais pour tables, colonnes, classes ? L'architecture mélange (« Access », « Lieux », « Evenements ») | Toute la base de code | Développeur | Étape 1 | Domaine métier en français (cohérent avec les routes), répertoire du module `Access` tel que dans le document |
| Q10 | Route dédiée pour désactiver/réactiver (écart avec §12.2) | Contrat de routes | Développeur | Étape 3 | Oui, consigner par ADR |
| Q11 | HTTPS en développement : cookies `Secure` désactivés en local seulement, activés en test et en production ? | Configuration | Développeur | Étape 2 | Oui, par variable d'environnement, avec un test qui vérifie l'attribut en environnement de test |
| Q12 | `UtilisateursActifs` : un consultant peut-il être chargé responsable d'un événement ou d'une tâche ? | Signature du service, M5, M7 | Référent | Étape 3 (signature) | Paramètre de filtre par rôle, valeur par défaut tous les rôles actifs, à trancher avec M5 |
| Q13 | Intégration continue : mettre en place GitHub Actions avec un service MySQL ? | Livrables | Développeur | Aucune | Oui, car les tests d'unicité et de concurrence exigent MySQL |

**Décisions validées (22 oct. 2026) :**

- **Q1** : pas de dépôt existant, création d'un projet vierge.
- **Q3** : `journal.consulter` accordé à l'administrateur et au chargé d'événements (ADR-003).
- **Q8** : R15 validée ; premier administrateur créé par commande artisan, mot de passe lu dans l'environnement (ADR-004).

Le journal des décisions est en fin de document.

---

## Étape 0 - Valider le cadrage et préparer le socle du projet

**Statut :** en cours (bloquée sur la création du projet Laravel)

### Objectif et prérequis

Rendre ce plan applicable au projet réel et créer une base de travail saine. Prérequis : accès au dépôt (ou autorisation d'en créer un), cahier des charges et architecture v2.0.

### Travaux à réaliser et fichiers concernés

1. Relever (ou fixer) les versions de PHP, Laravel et MySQL ; les inscrire au §4 (Q1).
2. Vérifier ce qui existe : projet Laravel, authentification d'exemple installée (à retirer ou à réutiliser), conventions, `.env.example`. Classer chaque élément en « présent », « à créer » ou « à confirmer ».
3. Créer ou nettoyer le projet, la structure `app/Modules/{Access,Shared}` et la branche `feature/m1-acces-utilisateurs` depuis `develop`.
4. Résoudre ou assigner les questions Q1 à Q13 ; rédiger les ADR des décisions prises.
5. Jouer le plan **sur papier** : une connexion valide, une connexion refusée, un accès interdit à `/utilisateurs` pour un chargé, une désactivation de compte.
6. Si retenu (Q13) : workflow d'intégration continue qui lance les tests sur MySQL.

Fichiers : `docs/modules/m1-acces-utilisateurs.md` (ce plan), `docs/adr/`, `.env.example`, `.github/workflows/` (si Q13).

### Règles applicables et points de vigilance

Toutes les règles R01 à R16 doivent être lues et comprises avant l'étape 1. Ne pas considérer un prérequis comme disponible sans l'avoir vérifié dans le dépôt. Ne rien inventer d'existant.

### Livrables

Plan validé avec versions renseignées ; questions bloquantes tranchées ou datées ; squelette de projet et branche ; matrice d'accès (§5.2) validée.

### Tests et cas limites

Le jeu sur papier doit couvrir au moins un cas nominal, un refus d'accès et un cas limite (désactivation du dernier administrateur).

### Critères de sortie

- [ ] Versions PHP, Laravel et MySQL inscrites au §4.
- [ ] Q1, Q3 et Q8 tranchées ; les autres ont une décision ou une hypothèse datée.
- [ ] Matrice des droits validée par le validateur.
- [ ] Le projet démarre et un test vide s'exécute sur MySQL.

### Bilan d'exécution

Statut : en cours, 22 oct. 2026.

**Fait.**
- Q1, Q3 et Q8 tranchées par le développeur (voir §8) ; ADR-003 et ADR-004 rédigés.
- Dépôt Git initialisé sur la branche `develop` ; plan copié dans `docs/modules/`. Aucun commit n'a encore été fait.
- Environnement relevé : PHP 8.3.6, Git 2.43, Docker 29.9, Node 22 ; MySQL non installé localement (prévu via Docker).
- Jeu sur papier (voir ci-dessous).

**Non fait, et pourquoi.**
- **Projet Laravel** : refusé dans l'environnement de travail de Claude (accès à `repo.packagist.org` bloqué, 403), puis **créé avec succès sur le poste du développeur** (`C:\dev\eventia`, Laravel 13.38.0, PHP 8.4.23, migrations par défaut exécutées sur SQLite). Reste à vérifier MySQL et à pousser le dépôt. Ancien constat : la création a échoué ici, l'accès à Packagist est refusé par le proxy. Le blocage est une règle de l'environnement, il n'a pas été contourné. Conséquence : la version de Laravel n'est pas relevée, la structure `app/Modules/` et la branche `feature/m1-acces-utilisateurs` ne sont pas créées, et le critère « le projet démarre et un test vide s'exécute sur MySQL » n'est pas vérifié.
- Q2, Q4 à Q7, Q9 à Q13 non tranchées (recommandations au §8). Seules Q1, Q3 et Q8 conditionnaient l'étape 0.
- Matrice des droits : non encore validée par le validateur (§5.2, ligne journal alignée sur ADR-003).

**Jeu sur papier (matrice §5.2 et règles R01 à R15).**

| Cas | Scénario | Résultat attendu | Règles |
|---|---|---|---|
| Nominal | Un chargé d'événements saisit un identifiant et un mot de passe valides | Session ouverte, identifiant de session renouvelé, arrivée sur le tableau de bord, menu sans « Utilisateurs » | R01, R12, R08 |
| Refus | Même utilisateur avec un mauvais mot de passe, puis identifiant inconnu, puis compte désactivé | Trois fois le même message ; 6e essai bloqué avec délai, y compris sur l'identifiant inconnu | R04, R05, R03 |
| Refus d'accès | Ce chargé ouvre `/utilisateurs` par URL directe | 403, refus au middleware, et refus à nouveau au service si le middleware était oublié | R10, R08 |
| Cas limite | L'unique administrateur actif demande sa propre désactivation | Refusée avec message explicite ; avec deux administrateurs qui se désactivent en même temps, l'un des deux reste actif | R15 |
| Cas limite | Création d'un compte `Marie` alors que `marie` existe | Refus de validation ; si le contrôle applicatif est contourné, l'index unique refuse et l'erreur reste un message utilisateur | R02 |

Le jeu n'a révélé aucun défaut de règle. Il confirme que les cas limites de R15 exigent une transaction avec verrou.

### Validation

Validateur, date et décision de passage à l'étape 1 : ...

---

## Étape 1 - Construire les données et les droits

**Statut :** à faire

### Objectif et prérequis

Disposer d'un socle intègre : table des utilisateurs, rôles, matrice de droits et refus par défaut. Prérequis : étape 0 validée ; Q3 et Q9 tranchées.

### Travaux à réaliser et fichiers concernés

1. Migration de la table des utilisateurs et de la table des sessions (§6.1), avec index unique sur l'identifiant, clés étrangères prévues en `RESTRICT`.
2. Enum `Role` (trois valeurs) et enum `Permission` (§5.2) ; classe de matrice `role → permissions`, source unique de vérité.
3. Modèle, fabrique et service `VerifierDroit` (contrat dans `Shared`, implémentation dans `Access`) ; intégration au mécanisme d'autorisation du framework (une habilitation par permission).
4. Middleware de permission, appliqué à toutes les routes par groupe ; **test de parcours des routes** qui échoue si une route n'a ni authentification ni permission déclarée.
5. Commande d'amorçage du premier administrateur et jeu de données de démonstration (un compte par rôle, un compte inactif) ; aucun mot de passe en dur dans le dépôt.

Fichiers : `database/migrations/`, `app/Modules/Access/`, `app/Modules/Shared/`, `app/Http/Middleware/`, `database/factories/`, `database/seeders/`, `routes/`, `tests/Unit/Access/`, `tests/Feature/Access/`.

### Règles applicables et points de vigilance

R01 (refus par défaut, test de parcours), R02 (unicité insensible à la casse, vérifiée en base), R03 (colonne `actif`, pas de suppression), R07 (enum fermée), R08, R09, R10 (matrice). Vigilance : la matrice est testée **ligne par ligne et rôle par rôle** (24 combinaisons) ; ne pas dupliquer la matrice dans les gabarits.

### Livrables

Schéma migrable et rejouable ; matrice de droits ; middleware et test de parcours des routes ; amorçage de l'administrateur ; fabriques.

### Tests et cas limites

- Nominal : chaque permission est accordée aux rôles prévus (8 permissions × 3 rôles).
- Refus : un consultant n'a aucune permission d'écriture ; un chargé n'a ni `lieux.gerer` ni `utilisateurs.gerer`.
- Cas limites : deux identifiants qui ne diffèrent que par la casse (`Marie` et `marie`) sont refusés par la base ; une route ajoutée sans permission fait échouer le test de parcours ; une valeur de rôle inconnue est rejetée.
- La migration est exécutée, annulée puis rejouée sur MySQL réel.

### Critères de sortie

- [ ] Migrations vérifiées sur MySQL de test (montée, retour arrière, rejeu).
- [ ] Les 24 combinaisons permission × rôle sont testées et réussissent.
- [ ] Le test de parcours des routes échoue volontairement sur une route sans permission, puis réussit une fois corrigée.
- [ ] L'amorçage crée un administrateur sans mot de passe stocké dans le dépôt.

### Bilan d'exécution

Statut : à faire. Fichiers modifiés : ... Vérifications : ... Preuves : ... Limites : ...

### Validation

Validateur, date et décision de passage à l'étape 2 : ...

---

## Étape 2 - Authentification et sessions

**Statut :** à faire

### Objectif et prérequis

Permettre de se connecter et se déconnecter en toute sécurité. Prérequis : étape 1 validée ; Q5, Q6 et Q11 tranchées.

### Travaux à réaliser et fichiers concernés

1. Service `Authentifier` : recherche du compte (identifiant normalisé), compte actif, comparaison du mot de passe avec un temps constant (comparaison à un hachage factice si le compte n'existe pas), mise à jour de `derniere_connexion`.
2. Contrôleur de connexion et de déconnexion minces, Form Request de connexion ; message unique en cas d'échec (R04).
3. Limitation des essais par couple identifiant/adresse avec temporisation progressive, y compris pour les identifiants inconnus (R05).
4. Session : pilote en base, cookie `Secure`/`HttpOnly`/`SameSite`, renouvellement de l'identifiant à la connexion, invalidation serveur à la déconnexion, expiration par inactivité (R12).
5. Middleware « compte actif » : à chaque requête, un compte désactivé est déconnecté (R03) ; service `UtilisateurCourant`.
6. Journal technique des échecs d'authentification et des accès refusés, sans donnée sensible (R13), par exemple un condensé non réversible de l'identifiant saisi plutôt que l'identifiant.

Fichiers : `app/Modules/Access/`, `app/Http/Controllers/Access/`, `app/Http/Requests/Access/`, `app/Http/Middleware/`, `config/` (session, limitation), `tests/Feature/Access/`.

### Règles applicables et points de vigilance

R01, R03, R04, R05, R11, R12, R13. Vigilance : le temps de réponse d'un identifiant inconnu doit rester comparable à celui d'un identifiant connu ; le blocage doit s'appliquer aussi aux identifiants inexistants ; la redirection après connexion ne doit pas pouvoir mener vers un site externe.

### Livrables

Connexion et déconnexion fonctionnelles (sans habillage final) ; service d'authentification et service `UtilisateurCourant` ; configuration de session ; journal technique.

### Tests et cas limites

- Nominal (**T1**) : identifiants valides → accès accordé, session renouvelée, dernière connexion mise à jour.
- Refus : mot de passe faux, identifiant inconnu, compte désactivé → **même message** ; accès sans session à une page protégée → redirection vers la connexion ; requête sans jeton CSRF → refusée.
- Cas limites : 6e essai consécutif bloqué (y compris sur un identifiant inexistant) ; déconnexion puis rejeu de l'ancien cookie → refusé ; compte désactivé pendant une session ouverte → la requête suivante est refusée ; connexion avec l'identifiant en majuscules → acceptée ; vérification que les journaux ne contiennent ni mot de passe ni identifiant en clair.

### Critères de sortie

- [ ] Scénario T1 automatisé et réussi sur MySQL.
- [ ] Les trois motifs d'échec produisent une réponse identique (texte et code).
- [ ] L'ancien cookie ne permet plus d'accéder à l'application après déconnexion.
- [ ] Attributs du cookie vérifiés en environnement de test (Q11).
- [ ] Aucune donnée sensible dans les journaux (contrôle manuel d'un extrait réel).

### Bilan d'exécution

Statut : à faire. Fichiers modifiés : ... Vérifications : ... Preuves : ... Limites : ...

### Validation

Validateur, date et décision de passage à l'étape 3 : ...

---

## Étape 3 - Gestion des comptes et services exposés

**Statut :** à faire

### Objectif et prérequis

Permettre à l'administrateur de gérer les comptes et exposer aux autres modules les services de M1. Prérequis : étape 2 validée ; Q2, Q4, Q7, Q8, Q10 et Q12 tranchées (Q2 ne bloque que la traçabilité).

### Travaux à réaliser et fichiers concernés

1. Services de gestion : créer, modifier (nom, identifiant, rôle), désactiver, réactiver, réinitialiser le mot de passe. Chaque service **revérifie** la permission `utilisateurs.gerer` (double barrière, R08).
2. Form Requests : nom obligatoire (100 caractères max.), identifiant unique insensible à la casse, rôle dans la liste, mot de passe conforme à la politique (R02, R06, R07).
3. Protection du dernier administrateur actif, dans une transaction avec verrou (R15). Invalidation des sessions du compte désactivé (R03).
4. Contrôleur `utilisateurs` mince et routes de §6.3. Aucune route de suppression.
5. Services `UtilisateurParIdentifiantTechnique` et `UtilisateursActifs` et leurs contrats.
6. Traçabilité R16 : contrat `EnregistreurJournal` dans `Shared` ; implémentation provisoire dans le journal applicatif tant que Q2 n'est pas tranchée. Le branchement sur M11 est un travail de l'étape M11, noté dans les limites.

Fichiers : `app/Modules/Access/`, `app/Modules/Shared/`, `app/Http/Controllers/Access/`, `app/Http/Requests/Access/`, `routes/`, `tests/Unit/Access/`, `tests/Feature/Access/`.

### Règles applicables et points de vigilance

R02, R03, R06, R07, R08, R10, R14, R15, R16. Vigilance : jamais de mot de passe dans une réponse ni un journal ; l'erreur d'unicité vient de la base si le contrôle applicatif est contourné et doit rester un message utilisateur.

### Livrables

Gestion des comptes complète côté serveur ; cinq services exposés avec contrats ; tests unitaires des règles et tests fonctionnels des routes.

### Tests et cas limites

- Nominal (**US2**) : un administrateur crée un compte avec nom, identifiant, mot de passe et rôle ; le compte peut se connecter.
- Refus : un chargé et un consultant reçoivent 403 sur toutes les routes `/utilisateurs` (lecture et écriture) ; identifiant déjà utilisé refusé à la création **et** à la modification.
- Cas limites : désactivation du dernier administrateur refusée ; auto-désactivation refusée ; deux créations simultanées du même identifiant (un seul compte) ; deux désactivations croisées de deux administrateurs (au moins un reste actif) ; compte désactivé absent de `UtilisateursActifs` mais toujours retrouvé par `UtilisateurParIdentifiantTechnique` ; le mot de passe n'apparaît dans aucune réponse.

### Critères de sortie

- [ ] Tous les critères d'acceptation d'US2 sont couverts par des tests réussis.
- [ ] Les tests de concurrence réussissent sur MySQL réel.
- [ ] Aucune route de suppression de compte n'existe (vérifié par le test de parcours).
- [ ] Les cinq contrats de service existent dans `Shared`, et aucun module n'a besoin du modèle Eloquent.
- [ ] La limite sur la traçabilité (Q2) est consignée dans le bilan.

### Bilan d'exécution

Statut : à faire. Fichiers modifiés : ... Vérifications : ... Preuves : ... Limites : ...

### Validation

Validateur, date et décision de passage à l'étape 4 : ...

---

## Étape 4 - Réaliser les écrans et le socle d'interface accessible

**Statut :** à faire

### Objectif et prérequis

Rendre M1 utilisable et accessible depuis un navigateur. Prérequis : étape 3 validée.

### Travaux à réaliser et fichiers concernés

1. Gabarit commun Blade : langue, lien d'évitement, structure sémantique, menu filtré par rôle à partir de `VerifierDroit` (jamais d'une copie de la matrice), emplacement fixe du contact d'assistance.
2. Écrans Connexion et Utilisateurs (liste, création, modification, confirmation de désactivation, réinitialisation du mot de passe) avec tous les états du §6.4.
3. Page provisoire `/tableau-de-bord` (accessible à tous les rôles) pour rendre vérifiable le critère « l'utilisateur accède au tableau de bord ».
4. Avertissement d'expiration de session et route `POST /session/prolonger` ; comportement correct sans JavaScript.
5. En-têtes de sécurité (CSP, HSTS, X-Content-Type-Options, Referrer-Policy) ; pages d'erreur neutres (403, 404, 419, 429, 500) sans trace technique ; débogage désactivé hors développement.

Fichiers : `resources/views/layouts/`, `resources/views/access/`, `resources/css/`, `resources/js/`, `app/Http/Middleware/`, `routes/`, `tests/Feature/Access/`.

### Règles applicables et points de vigilance

R01, R04, R08, R09, R10, R12. Le frontend **reflète** les droits du serveur, il ne les décide pas : tester qu'une action masquée est quand même refusée si l'on forge la requête. Les erreurs sont écrites en texte (pas seulement en couleur) et rattachées au champ.

### Livrables

Écrans de M1 fonctionnels et accessibles ; gabarit réutilisable par M2 à M11 ; pages d'erreur ; en-têtes de sécurité.

### Tests et cas limites

- Nominal : parcours complet au navigateur : connexion, arrivée sur le tableau de bord, création d'un compte, déconnexion.
- Refus : un consultant ne voit ni le menu « Utilisateurs » ni les boutons d'écriture, et un accès direct par URL reçoit 403.
- Cas limites : formulaire invalide → valeurs conservées et résumé d'erreurs avec liens ; navigation au clavier seule du début à la fin ; zoom à 200 % sans perte ; lecteur d'écran sur le champ mot de passe ; session expirée pendant la saisie d'un formulaire ; réponse 500 simulée sans trace affichée.

### Critères de sortie

- [ ] Parcours exercés dans un vrai navigateur (nominal, refus, expiration de session).
- [ ] Contrôle d'accessibilité des écrans de M1 sur les critères listés au §7, sans défaut bloquant.
- [ ] Les en-têtes de sécurité sont présents dans les réponses (vérifié par test ou inspection).
- [ ] Aucune page d'erreur n'affiche de trace, de requête SQL ou de chemin de fichier.

### Bilan d'exécution

Statut : à faire. Fichiers modifiés : ... Vérifications : ... Preuves : ... Limites : ...

### Validation

Validateur, date et décision de passage à l'étape 5 : ...

---

## Étape 5 - Recetter M1 et préparer la livraison

**Statut :** à faire

### Objectif et prérequis

Prouver que M1 répond au besoin et qu'il peut servir de socle aux modules suivants. Prérequis : étapes 1 à 4 validées ; environnement de test isolé de la base de développement.

### Travaux à réaliser et fichiers concernés

1. Exécuter la recette de M1 (§ « Recette globale » ci-dessous), corriger les défauts.
2. Passer en revue la conformité OWASP des points concernés par M1 (A01, A02, A03, A05, A07, A09) et consigner un résultat par point.
3. Mettre à jour ce plan, rédiger les ADR et reporter les écarts dans `docs/architecture.md` (Q3, Q10).
4. Documenter l'installation, l'amorçage de l'administrateur, la procédure de migration et le retour arrière.
5. Fusion de la branche vers `develop` par demande de fusion qui cite T1 et T2 (partie M1) et les règles vérifiées.

### Règles applicables et points de vigilance

R01 à R16. Aucune donnée de démonstration en production, aucun mot de passe de démonstration conservé ; aucune livraison annoncée sans exécution réelle ; le déploiement reste une action distincte.

### Livrables

Rapport de recette ; commandes de vérification ; limites connues ; procédure de migration et de retour arrière ; ADR ; demande de fusion.

### Tests et cas limites

Scénario complet avec un compte de chaque rôle ; concurrence sur MySQL ; contrôle des journaux et des données exposées ; rejeu depuis une base vierge (migrations + amorçage).

### Critères de sortie

- [ ] Toute la recette globale est acceptée, défauts bloquants résolus.
- [ ] Les tests automatisés passent en intégration continue sur MySQL.
- [ ] Le module se reconstruit depuis une base vierge avec les seules commandes documentées.
- [ ] Les écarts avec les documents d'origine sont reportés ou consignés comme limites.
- [ ] Validation du validateur.

### Bilan d'exécution

Statut : à faire. Fichiers modifiés : ... Vérifications : ... Preuves : ... Limites : ...

### Validation

Validateur, date et décision de clôture de M1 : ...

---

## Recette globale du module

| Réf. | Scénario | Résultat attendu | Origine |
|---|---|---|---|
| CA01 | Connexion avec identifiant et mot de passe valides, puis avec un identifiant ou un mot de passe incorrect | Accès au tableau de bord dans le premier cas ; refus avec message unique, sans préciser le champ fautif, dans le second | US1, **T1** |
| CA02 | Page protégée ouverte sans être connecté | Redirection vers la connexion | US1 |
| CA03 | Un compte de chaque rôle parcourt les routes de M1 | Chaque rôle ne voit et ne fait que ce que la matrice §5.2 autorise ; un consultant ne voit aucune action d'écriture | US1, **T2** (partie M1) |
| CA04 | L'administrateur crée un compte (nom, identifiant unique, mot de passe, rôle) | Compte créé, utilisable pour se connecter | US2 |
| CA05 | Création ou modification avec un identifiant déjà utilisé (y compris à la casse près) | Refus avec message clair | US2 |
| CA06 | Désactivation d'un compte connecté | Il ne peut plus se connecter, sa session est coupée, ses données restent, il n'est plus proposé comme responsable | US2, R03, R14 |
| CA07 | Un chargé d'événements ou un consultant tente d'ouvrir `/utilisateurs` | Refus 403 | US2, R10 |
| CA08 | Tentatives de connexion répétées | Blocage temporisé, y compris sur un identifiant inexistant | R05 |
| CA09 | Désactivation du dernier administrateur actif | Refusée (si R15 validée) | R15 |
| CA10 | Inspection des journaux et des réponses | Aucun mot de passe, aucun identifiant en clair, aucune trace technique visible | R06, R13 |

## Limites connues (à compléter à l'étape 5)

- Aucune réinitialisation par e-mail ni double authentification : exclus par le périmètre de la version 2.0.
- La traçabilité des actions sur les comptes dépend de la décision Q2 ; tant que M11 n'existe pas, elle reste dans le journal applicatif.
- Le tableau de bord est provisoire jusqu'à la réalisation de M10.
- Les versions de la pile ne sont pas confirmées tant que l'étape 0 n'est pas validée.

## Consignes de livraison

- Une branche `feature/m1-acces-utilisateurs`, des commits qui expliquent l'intention, des tests dans le même lot que le code.
- Une demande de fusion vers `develop` qui indique le besoin, le changement, la façon de le vérifier et les scénarios T concernés. La revue contrôle : règle au bon endroit, permission présente, traçabilité déclenchée, accessibilité préservée.
- Aucune fusion sur `main` ni déploiement sans autorisation explicite.

## Consignes pour l'IA

Premier message : « Lis `docs/modules/m1-acces-utilisateurs.md`, le cahier des charges et le document d'architecture. Vérifie leur cohérence avec le code existant. N'invente aucun champ, route ou comportement existant. Présente les décisions à prendre avant l'étape 0. »

Ensuite, une étape à la fois : « Le cadrage est validé. Implémente **uniquement** l'étape N. Respecte les règles référencées et les conventions du projet. Exécute les tests prévus sur MySQL. Mets à jour le bilan avec les fichiers modifiés, les commandes lancées, les résultats réels et les limites. N'entame pas l'étape suivante avant ma validation. »

## Journal des décisions

| Date | Décision | Décideur | ADR |
|---|---|---|---|
| 2026-10-22 | Plan rédigé en version 0.1 | - | - |
| 2026-10-22 | Q1 : création d'un projet vierge | Brichton | - |
| 2026-10-22 | Q3 : journal consultable par administrateur et chargé d'événements | Brichton | ADR-003 |
| 2026-10-22 | Q8 : R15 validée, amorçage du premier administrateur par commande | Brichton | ADR-004 |
