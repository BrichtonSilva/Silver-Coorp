<?php

use App\Http\Controllers\Access\ConnexionController;
use App\Http\Controllers\Access\SessionController;
use App\Http\Controllers\Access\UtilisateurController;
use App\Http\Controllers\TableauDeBordController;
use Illuminate\Support\Facades\Route;

/*
| Toute route est protégée par « auth » + « permission:... » (refus par défaut, R01).
| Seules les routes de connexion sont publiques. Un test parcourt toutes les routes pour le vérifier.
*/

Route::redirect('/', '/tableau-de-bord');

Route::get('/connexion', [ConnexionController::class, 'afficher'])->name('connexion');
Route::post('/connexion', [ConnexionController::class, 'connecter'])->name('connexion.envoyer');

Route::middleware(['auth', 'permission:consulter'])->group(function () {
    Route::post('/deconnexion', [ConnexionController::class, 'deconnecter'])->name('deconnexion');
    Route::post('/session/prolonger', [SessionController::class, 'prolonger'])->name('session.prolonger');
    Route::get('/tableau-de-bord', [TableauDeBordController::class, 'afficher'])->name('tableau-de-bord');
});

Route::middleware(['auth', 'permission:utilisateurs.gerer'])->prefix('utilisateurs')->name('utilisateurs.')->group(function () {
    Route::get('/', [UtilisateurController::class, 'index'])->name('index');
    Route::get('/creer', [UtilisateurController::class, 'creer'])->name('creer');
    Route::post('/', [UtilisateurController::class, 'enregistrer'])->name('enregistrer');
    Route::get('/{utilisateur}/modifier', [UtilisateurController::class, 'modifier'])->name('modifier');
    Route::put('/{utilisateur}', [UtilisateurController::class, 'mettreAJour'])->name('mettre-a-jour');
    Route::post('/{utilisateur}/desactiver', [UtilisateurController::class, 'desactiver'])->name('desactiver');
    Route::post('/{utilisateur}/reactiver', [UtilisateurController::class, 'reactiver'])->name('reactiver');
    Route::get('/{utilisateur}/mot-de-passe', [UtilisateurController::class, 'formulaireMotDePasse'])->name('mot-de-passe');
    Route::post('/{utilisateur}/mot-de-passe', [UtilisateurController::class, 'reinitialiserMotDePasse'])->name('mot-de-passe.reinitialiser');
});
