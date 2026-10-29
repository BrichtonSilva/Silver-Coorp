<?php

namespace App\Modules\Shared;

/** Permissions nommées issues du tableau des droits (cahier des charges §3, RG17). */
enum Permission: string
{
    case Consulter = 'consulter';
    case Exporter = 'exporter';
    case EvenementsEcrire = 'evenements.ecrire';
    case AffectationsChecklistGerer = 'affectations_checklist.gerer';
    case ClientsPrestatairesGerer = 'clients_prestataires.gerer';
    case JournalConsulter = 'journal.consulter';
    case LieuxGerer = 'lieux.gerer';
    case UtilisateursGerer = 'utilisateurs.gerer';
}
