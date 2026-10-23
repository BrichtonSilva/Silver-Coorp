# ADR-004 - Protection du dernier administrateur et amorçage du premier compte

- Date : 2026-10-22
- Statut : acceptée
- Décideurs : Brichton (développeur)

Contexte. Seul un administrateur gère les comptes. Si le dernier administrateur actif est désactivé ou rétrogradé, plus personne ne peut les gérer. Le premier administrateur ne peut pas être créé depuis l'interface.

Décision. (1) Règle R15 : le dernier administrateur actif ne peut être ni désactivé ni rétrogradé, et un administrateur ne peut pas se désactiver lui-même, avec contrôle dans une transaction avec verrou. (2) Le premier administrateur est créé par une commande artisan qui lit son mot de passe dans l'environnement ; aucun mot de passe dans le dépôt.

Conséquences. Règle R15 à reporter dans le document d'architecture ; commande d'exploitation à documenter.

Vérification. Tests de l'étape 3 (dernier administrateur, auto-désactivation, désactivations croisées) ; CA09 ; aucun mot de passe dans le dépôt.
