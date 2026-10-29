<?php

namespace Tests\Feature\Access;

use App\Http\Controllers\Access\ConnexionController;
use App\Modules\Access\Utilisateur;
use Database\Factories\UtilisateurFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnexionTest extends TestCase
{
    use RefreshDatabase;

    private function connexion(string $identifiant, string $motDePasse)
    {
        return $this->post('/connexion', ['identifiant' => $identifiant, 'mot_de_passe' => $motDePasse]);
    }

    public function test_T1_connexion_valide_arrive_sur_le_tableau_de_bord(): void
    {
        $u = Utilisateur::factory()->chargeEvenements()->create(['identifiant' => 'marc']);

        $this->connexion('marc', UtilisateurFactory::MOT_DE_PASSE)->assertRedirect('/tableau-de-bord');

        $this->assertAuthenticatedAs($u);
        $this->assertNotNull($u->fresh()->derniere_connexion);
        $this->get('/tableau-de-bord')->assertOk()->assertSee('Tableau de bord');
    }

    public function test_R02_identifiant_insensible_a_la_casse(): void
    {
        Utilisateur::factory()->create(['identifiant' => 'Marie']);

        $this->connexion('MARIE', UtilisateurFactory::MOT_DE_PASSE)->assertRedirect('/tableau-de-bord');
    }

    public function test_R04_message_identique_pour_les_trois_motifs_d_echec(): void
    {
        Utilisateur::factory()->create(['identifiant' => 'actif1']);
        Utilisateur::factory()->inactif()->create(['identifiant' => 'inactif1']);

        foreach ([['actif1', 'mauvais-mot-de-passe-1'], ['inconnu', UtilisateurFactory::MOT_DE_PASSE], ['inactif1', UtilisateurFactory::MOT_DE_PASSE]] as [$id, $mdp]) {
            $reponse = $this->connexion($id, $mdp);
            $reponse->assertRedirect('/connexion');
            $reponse->assertSessionHasErrors(['connexion' => ConnexionController::MESSAGE_ECHEC]);
            $this->assertGuest();
        }
    }

    public function test_R03_un_compte_desactive_ne_peut_pas_se_connecter(): void
    {
        Utilisateur::factory()->inactif()->create(['identifiant' => 'ancien']);

        $this->connexion('ancien', UtilisateurFactory::MOT_DE_PASSE);

        $this->assertGuest();
    }

    public function test_R05_le_sixieme_essai_est_bloque_meme_avec_le_bon_mot_de_passe(): void
    {
        Utilisateur::factory()->create(['identifiant' => 'cible']);

        for ($i = 0; $i < 5; $i++) {
            $this->connexion('cible', 'mauvais-mot-de-passe-1')->assertRedirect('/connexion');
        }

        $this->connexion('cible', UtilisateurFactory::MOT_DE_PASSE)
            ->assertStatus(429)
            ->assertSee('Trop de tentatives');
        $this->assertGuest();
    }

    public function test_R05_le_blocage_s_applique_aussi_a_un_identifiant_inexistant(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->connexion('fantome', 'mauvais-mot-de-passe-1');
        }

        $this->connexion('fantome', 'mauvais-mot-de-passe-1')->assertStatus(429);
    }

    public function test_R05_le_blocage_se_leve_apres_le_delai(): void
    {
        Utilisateur::factory()->create(['identifiant' => 'cible']);
        for ($i = 0; $i < 5; $i++) {
            $this->connexion('cible', 'mauvais-mot-de-passe-1');
        }
        $this->connexion('cible', UtilisateurFactory::MOT_DE_PASSE)->assertStatus(429);

        $this->travel(61)->seconds();

        $this->connexion('cible', UtilisateurFactory::MOT_DE_PASSE)->assertRedirect('/tableau-de-bord');
    }

    public function test_R01_une_page_protegee_renvoie_vers_la_connexion(): void
    {
        $this->get('/tableau-de-bord')->assertRedirect(route('connexion', ['expiree' => 1]));
        $this->get('/utilisateurs')->assertRedirect(route('connexion', ['expiree' => 1]));
    }

    public function test_R11_une_ecriture_sans_session_est_refusee(): void
    {
        $this->post('/deconnexion')->assertRedirect(route('connexion', ['expiree' => 1]));
    }

    public function test_R12_la_deconnexion_ferme_la_session(): void
    {
        $u = Utilisateur::factory()->create(['identifiant' => 'sortie']);
        $this->connexion('sortie', UtilisateurFactory::MOT_DE_PASSE);

        $this->post('/deconnexion')->assertRedirect('/connexion');

        $this->assertGuest();
        $this->get('/tableau-de-bord')->assertRedirect();
    }

    public function test_R03_un_compte_desactive_pendant_sa_session_est_deconnecte(): void
    {
        $u = Utilisateur::factory()->chargeEvenements()->create();
        $this->actingAs($u)->get('/tableau-de-bord')->assertOk();

        $u->update(['actif' => false]);

        $this->get('/tableau-de-bord')->assertRedirect('/connexion');
        $this->assertGuest();
    }

    public function test_en_tetes_de_securite_presents(): void
    {
        $reponse = $this->get('/connexion');

        $reponse->assertOk();
        $reponse->assertHeader('X-Content-Type-Options', 'nosniff');
        $reponse->assertHeader('Referrer-Policy', 'same-origin');
        $this->assertStringContainsString("frame-ancestors 'none'", $reponse->headers->get('Content-Security-Policy'));
    }

    public function test_R06_le_mot_de_passe_est_hache_et_jamais_expose(): void
    {
        $u = Utilisateur::factory()->create(['mot_de_passe' => 'Un-mot-de-passe-solide-1']);

        $this->assertNotSame('Un-mot-de-passe-solide-1', $u->getRawOriginal('mot_de_passe'));
        $this->assertArrayNotHasKey('mot_de_passe', $u->toArray());
    }
}
