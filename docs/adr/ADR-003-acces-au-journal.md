# ADR-003 - Le journal d'historique est consultable par l'administrateur et le chargé d'événements

- Date : 2026-10-22
- Statut : acceptée
- Décideurs : Brichton (développeur)

Contexte. Le document d'architecture (§4.2) range la consultation du journal parmi les actions réservées à l'administrateur. Le cahier des charges (§3, US21) et l'architecture (§8.1, §12.2) l'ouvrent aussi au chargé d'événements.

Décision. La permission journal.consulter est accordée aux rôles administrateur et chargé d'événements, conformément au cahier des charges.

Conséquences. Le §4.2 de docs/architecture.md doit être corrigé : seules la gestion des lieux et celle des utilisateurs sont strictement réservées à l'administrateur.

Vérification. Test de la matrice des droits (étape 1 du plan M1) ; scénario T18.
