<?php

namespace Tests\Feature\Access;

use App\Modules\Access\Utilisateur;
use App\Modules\Shared\Contracts\UtilisateursActifs;
use App\Modules\Shared\Role;
use Database\Factories\UtilisateurFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UtilisateursTest extends TestCase
{
    use RefreshDatabase;

    private const MDP = 'Nouveau-mot-de-passe-1';

    private function donnees(array $surcharge = []): array
    {
        return array_merge(['nom' => 'Nadia Test', 'identifiant' => 'nadia', 'mot_de_passe' => self::MDP, 'role' => 'charge_evenements'], $surcharge);
    }

    public function test_US2_l_administrateur_cree_un_compte_qui_peut_se_connecter(): void
    {
        $admin = Utilisateur::factory()->administrateur()->create();

        $this->actingAs($admin)->post('/utilisateurs', $this->donnees())->assertRedirect('/utilisateurs');

        $cree = Utilisateur::where('identifiant', 'nadia')->firstOrFail();
        $this->assertSame(Role::ChargeEvenements, $cree->role);
        $this->assertTrue($cree->actif);

        $this->post('/deconnexion');
        $this->post('/connexion', ['identifiant' => 'Nadia', 'mot_de_passe' => self::MDP])->assertRedirect('/tableau-de-bord');
    }

    public static function rolesInterdits(): array
    {
        return ['chargé' => ['chargeEvenements'], 'consultant' => ['consultant']];
    }

    #[DataProvider('rolesInterdits')]
    public function test_R10_seul_l_administrateur_gere_les_comptes(string $etat): void
    {
        $u = Utilisateur::factory()->{$etat}()->create();
        $cible = Utilisateur::factory()->create();

        $this->actingAs($u);
        $this->get('/utilisateurs')->assertForbidden();
        $this->get('/utilisateurs/creer')->assertForbidden();
        $this->post('/utilisateurs', $this->donnees())->assertForbidden();
        $this->put("/utilisateurs/{$cible->id}", $this->donnees())->assertForbidden();
        $this->post("/utilisateurs/{$cible->id}/desactiver")->assertForbidden();
        $this->post("/utilisateurs/{$cible->id}/mot-de-passe", ['mot_de_passe' => self::MDP])->assertForbidden();

        $this->assertDatabaseMissing('utilisateurs', ['identifiant' => 'nadia']);
        $this->assertTrue($cible->fresh()->actif);
    }

    public function test_R08_le_menu_utilisateurs_est_masque_aux_non_administrateurs(): void
    {
        $this->actingAs(Utilisateur::factory()->consultant()->create())->get('/tableau-de-bord')->assertOk()->assertDontSee('Utilisateurs');
        $this->actingAs(Utilisateur::factory()->administrateur()->create())->get('/tableau-de-bord')->assertSee('Utilisateurs');
    }

    public function test_R02_identifiant_deja_utilise_refuse_a_la_casse_pres(): void
    {
        $admin = Utilisateur::factory()->administrateur()->create();
        Utilisateur::factory()->create(['identifiant' => 'marie']);

        $this->actingAs($admin)->post('/utilisateurs', $this->donnees(['identifiant' => 'MARIE']))->assertSessionHasErrors('identifiant');

        $this->assertSame(1, Utilisateur::where('identifiant', 'marie')->count());
    }

    public function test_R02_modifier_en_gardant_son_identifiant_est_possible(): void
    {
        $admin = Utilisateur::factory()->administrateur()->create();
        $cible = Utilisateur::factory()->create(['identifiant' => 'paul']);

        $this->actingAs($admin)->put("/utilisateurs/{$cible->id}", ['nom' => 'Paul Modifié', 'identifiant' => 'paul', 'role' => 'consultant'])
            ->assertRedirect('/utilisateurs');

        $this->assertSame('Paul Modifié', $cible->fresh()->nom);
    }

    public function test_R02_modifier_vers_un_identifiant_deja_pris_est_refuse(): void
    {
        $admin = Utilisateur::factory()->administrateur()->create();
        Utilisateur::factory()->create(['identifiant' => 'pris']);
        $cible = Utilisateur::factory()->create(['identifiant' => 'libre']);

        $this->actingAs($admin)->put("/utilisateurs/{$cible->id}", ['nom' => 'X', 'identifiant' => 'PRIS', 'role' => 'consultant'])
            ->assertSessionHasErrors('identifiant');
    }

    public function test_R07_un_role_inconnu_est_refuse(): void
    {
        $admin = Utilisateur::factory()->administrateur()->create();

        $this->actingAs($admin)->post('/utilisateurs', $this->donnees(['role' => 'super-admin']))->assertSessionHasErrors('role');
    }

    public function test_R06_politique_de_mot_de_passe(): void
    {
        $admin = Utilisateur::factory()->administrateur()->create();

        $this->actingAs($admin)->post('/utilisateurs', $this->donnees(['mot_de_passe' => 'court']))->assertSessionHasErrors('mot_de_passe');
        $this->actingAs($admin)->post('/utilisateurs', $this->donnees(['mot_de_passe' => 'motdepasse123']))->assertSessionHasErrors('mot_de_passe');
        $this->actingAs($admin)->post('/utilisateurs', $this->donnees(['mot_de_passe' => '111111111111']))->assertSessionHasErrors('mot_de_passe');

        $this->assertDatabaseMissing('utilisateurs', ['identifiant' => 'nadia']);
    }

    public function test_R03_la_desactivation_conserve_le_compte_et_ferme_ses_sessions(): void
    {
        $admin = Utilisateur::factory()->administrateur()->create();
        $cible = Utilisateur::factory()->chargeEvenements()->create();
        DB::table('sessions')->insert(['id' => 'abc', 'user_id' => $cible->id, 'payload' => 'x', 'last_activity' => now()->timestamp]);

        $this->actingAs($admin)->post("/utilisateurs/{$cible->id}/desactiver")->assertRedirect('/utilisateurs');

        $this->assertFalse($cible->fresh()->actif);
        $this->assertDatabaseHas('utilisateurs', ['id' => $cible->id]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $cible->id]);
    }

    public function test_R14_un_compte_desactive_n_est_plus_propose_mais_reste_retrouvable(): void
    {
        $actif = Utilisateur::factory()->chargeEvenements()->create(['nom' => 'Actif']);
        $inactif = Utilisateur::factory()->chargeEvenements()->inactif()->create(['nom' => 'Inactif']);

        $liste = app(UtilisateursActifs::class)->liste();

        $this->assertSame([$actif->id], array_map(fn ($d) => $d->id, $liste));
        $this->assertSame('Inactif', app(\App\Modules\Shared\Contracts\UtilisateurParIdentifiantTechnique::class)->obtenir($inactif->id)->nom);
    }

    public function test_Q12_la_liste_des_actifs_peut_etre_filtree_par_role(): void
    {
        Utilisateur::factory()->consultant()->create();
        $charge = Utilisateur::factory()->chargeEvenements()->create();

        $liste = app(UtilisateursActifs::class)->liste([Role::ChargeEvenements]);

        $this->assertSame([$charge->id], array_map(fn ($d) => $d->id, $liste));
    }

    public function test_R15_le_dernier_administrateur_ne_peut_pas_etre_desactive_ni_retrograde(): void
    {
        $unique = Utilisateur::factory()->administrateur()->create();
        $autre = Utilisateur::factory()->chargeEvenements()->create();

        // Rétrogradation du seul administrateur (par lui-même) : refusée.
        $this->actingAs($unique)->put("/utilisateurs/{$unique->id}", ['nom' => $unique->nom, 'identifiant' => $unique->identifiant, 'role' => 'consultant'])
            ->assertSessionHasErrors('role');
        $this->assertSame(Role::Administrateur, $unique->fresh()->role);
    }

    public function test_R15_un_administrateur_ne_peut_pas_se_desactiver_lui_meme(): void
    {
        $a = Utilisateur::factory()->administrateur()->create();
        Utilisateur::factory()->administrateur()->create();

        $this->actingAs($a)->post("/utilisateurs/{$a->id}/desactiver")->assertSessionHasErrors('compte');

        $this->assertTrue($a->fresh()->actif);
    }

    public function test_R15_auto_desactivation_refusee_meme_apres_desactivation_d_un_autre_administrateur(): void
    {
        $a = Utilisateur::factory()->administrateur()->create();
        $b = Utilisateur::factory()->administrateur()->inactif()->create();

        // « a » est le seul administrateur actif : même un acteur administrateur ne peut pas le désactiver.
        $acteur = Utilisateur::factory()->administrateur()->create();
        $this->actingAs($acteur)->post("/utilisateurs/{$a->id}/desactiver")->assertRedirect('/utilisateurs'); // acteur reste actif : désactivation possible
        $this->assertFalse($a->fresh()->actif);

        $this->actingAs($acteur)->post("/utilisateurs/{$acteur->id}/desactiver")->assertSessionHasErrors('compte');
        $this->assertTrue($acteur->fresh()->actif);
    }

    public function test_deux_administrateurs_peuvent_se_retrograder_tant_qu_il_en_reste_un(): void
    {
        $a = Utilisateur::factory()->administrateur()->create();
        $b = Utilisateur::factory()->administrateur()->create();

        $this->actingAs($a)->put("/utilisateurs/{$b->id}", ['nom' => $b->nom, 'identifiant' => $b->identifiant, 'role' => 'consultant'])
            ->assertRedirect('/utilisateurs');

        $this->assertSame(Role::Consultant, $b->fresh()->role);
    }

    public function test_reactivation_et_reinitialisation_du_mot_de_passe(): void
    {
        $admin = Utilisateur::factory()->administrateur()->create();
        $cible = Utilisateur::factory()->inactif()->create(['identifiant' => 'retour']);

        $this->actingAs($admin)->post("/utilisateurs/{$cible->id}/reactiver")->assertRedirect('/utilisateurs');
        $this->post("/utilisateurs/{$cible->id}/mot-de-passe", ['mot_de_passe' => self::MDP])->assertRedirect('/utilisateurs');

        $this->assertTrue($cible->fresh()->actif);
        $this->post('/deconnexion');
        $this->post('/connexion', ['identifiant' => 'retour', 'mot_de_passe' => self::MDP])->assertRedirect('/tableau-de-bord');
    }

    public function test_R06_le_mot_de_passe_n_apparait_dans_aucune_page(): void
    {
        $admin = Utilisateur::factory()->administrateur()->create();
        $cible = Utilisateur::factory()->create();

        $hash = $cible->getRawOriginal('mot_de_passe');
        $liste = $this->actingAs($admin)->get('/utilisateurs')->assertOk();
        $liste->assertDontSee($hash);
        $liste->assertDontSee(UtilisateurFactory::MOT_DE_PASSE);
        $this->get("/utilisateurs/{$cible->id}/modifier")->assertOk()->assertDontSee($hash);
    }

    public function test_commande_d_amorcage_du_premier_administrateur(): void
    {
        putenv('EVENTIA_ADMIN_MOT_DE_PASSE=Amorcage-solide-2026');

        $this->artisan('eventia:creer-administrateur', ['identifiant' => 'Root', 'nom' => 'Premier Admin'])->assertSuccessful();

        $admin = Utilisateur::where('identifiant', 'root')->firstOrFail();
        $this->assertSame(Role::Administrateur, $admin->role);
        $this->assertNotSame('Amorcage-solide-2026', $admin->getRawOriginal('mot_de_passe'));

        putenv('EVENTIA_ADMIN_MOT_DE_PASSE');
    }

    public function test_commande_d_amorcage_refuse_un_mot_de_passe_trop_court(): void
    {
        putenv('EVENTIA_ADMIN_MOT_DE_PASSE=court');

        $this->artisan('eventia:creer-administrateur', ['identifiant' => 'root', 'nom' => 'X'])->assertFailed();

        $this->assertDatabaseCount('utilisateurs', 0);
        putenv('EVENTIA_ADMIN_MOT_DE_PASSE');
    }
}
