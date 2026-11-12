<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Event;
use App\Models\Provider;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- M1: Create demo users ---

        // Admin
        $admin = User::create([
            'name' => 'Admin Eventia',
            'login' => 'admin',
            'email' => 'admin@eventia.fr',
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make('admin123'),
            'is_active' => true,
        ]);

        // Charge d'evenements
        $charge = User::create([
            'name' => 'Marie Dupont',
            'login' => 'marie.dupont',
            'email' => 'marie.dupont@eventia.fr',
            'role' => User::ROLE_CHARGE,
            'password' => Hash::make('charge123'),
            'is_active' => true,
        ]);

        // Consultant
        $consultant = User::create([
            'name' => 'Pierre Martin',
            'login' => 'pierre.martin',
            'email' => 'pierre.martin@eventia.fr',
            'role' => User::ROLE_CONSULTANT,
            'password' => Hash::make('consult123'),
            'is_active' => true,
        ]);

        // Inactive user (for testing)
        User::create([
            'name' => 'Jean Inactif',
            'login' => 'jean.inactif',
            'email' => 'jean.inactif@eventia.fr',
            'role' => User::ROLE_CONSULTANT,
            'password' => Hash::make('inactive123'),
            'is_active' => false,
        ]);

        // --- M2: Create demo clients ---
        Client::create([
            'name' => 'Société Tech Solutions',
            'type' => Client::TYPE_ENTREPRISE,
            'email' => 'contact@techsolutions.fr',
            'phone' => '01 23 45 67 89',
            'address' => '12 rue de l\'Innovation, 75001 Paris',
            'created_by' => $charge->id,
        ]);

        Client::create([
            'name' => 'Sophie Bernard',
            'type' => Client::TYPE_PARTICULIER,
            'email' => 'sophie.bernard@email.fr',
            'phone' => '06 78 90 12 34',
            'address' => '45 avenue des Fleurs, 33000 Bordeaux',
            'created_by' => $charge->id,
        ]);

        Client::create([
            'name' => 'Restaurant Le Gourmet',
            'type' => Client::TYPE_ENTREPRISE,
            'email' => 'contact@legourmet.fr',
            'phone' => '02 34 56 78 90',
            'address' => '8 place du Marché, 44000 Nantes',
            'created_by' => $admin->id,
        ]);

        Client::create([
            'name' => 'Lucas Petit',
            'type' => Client::TYPE_PARTICULIER,
            'email' => null,
            'phone' => '07 11 22 33 44',
            'address' => null,
            'created_by' => $charge->id,
        ]);

        // --- M4: Create demo providers ---
        Provider::create([
            'name' => 'Les Délices de Marie',
            'category' => Provider::CATEGORY_TRAITEUR,
            'contact' => 'Marie Lefebvre',
            'phone' => '01 40 20 30 40',
            'email' => 'contact@delicesmarie.fr',
            'indicative_price' => 2500.00,
            'created_by' => $charge->id,
        ]);

        Provider::create([
            'name' => 'SoundEvent Pro',
            'category' => Provider::CATEGORY_SONORISATION,
            'contact' => 'Thomas Bernard',
            'phone' => '06 12 34 56 78',
            'email' => 'booking@soundevent.fr',
            'indicative_price' => 1200.00,
            'created_by' => $charge->id,
        ]);

        Provider::create([
            'name' => 'Fleurs et Décors',
            'category' => Provider::CATEGORY_DECORATION,
            'contact' => 'Sophie Moreau',
            'phone' => '02 98 76 54 32',
            'email' => null,
            'indicative_price' => 800.00,
            'created_by' => $admin->id,
        ]);

        Provider::create([
            'name' => 'Pixel Studio',
            'category' => Provider::CATEGORY_PHOTOGRAPHE,
            'contact' => 'Lucas Durand',
            'phone' => '07 55 44 33 22',
            'email' => 'hello@pixelstudio.fr',
            'indicative_price' => 1500.00,
            'created_by' => $charge->id,
        ]);

        Provider::create([
            'name' => 'Location Mobilier Express',
            'category' => Provider::CATEGORY_AUTRE,
            'contact' => null,
            'phone' => '01 99 88 77 66',
            'email' => 'info@mobilier-express.fr',
            'indicative_price' => 600.00,
            'created_by' => $admin->id,
        ]);

        // --- M3: Create demo venues ---
        Venue::create([
            'name' => 'Salle des fêtes Bordeaux',
            'address' => '15 cours de l\'Intendance, 33000 Bordeaux',
            'capacity' => 200,
            'type' => Venue::TYPE_INTERNE,
            'rental_price' => 500.00,
            'created_by' => $admin->id,
        ]);

        Venue::create([
            'name' => 'Centre de congrès Paris',
            'address' => '2 place de la Porte Maillot, 75017 Paris',
            'capacity' => 1000,
            'type' => Venue::TYPE_EXTERNE,
            'rental_price' => 3500.00,
            'created_by' => $charge->id,
        ]);

        Venue::create([
            'name' => 'Espace rooftop Nantes',
            'address' => '10 quai de la Fosse, 44000 Nantes',
            'capacity' => 80,
            'type' => Venue::TYPE_EXTERNE,
            'rental_price' => 1200.00,
            'created_by' => $charge->id,
        ]);

        Venue::create([
            'name' => 'Salle de réunion A',
            'address' => 'Bâtiment principal, 1er étage',
            'capacity' => 20,
            'type' => Venue::TYPE_INTERNE,
            'rental_price' => 0.00,
            'created_by' => $admin->id,
        ]);

        // --- M5: Create demo events ---
        $client1 = Client::where('name', 'Société Tech Solutions')->first();
        $client2 = Client::where('name', 'Sophie Bernard')->first();
        $venue1 = Venue::where('name', 'Salle des fêtes Bordeaux')->first();
        $venue2 = Venue::where('name', 'Centre de congrès Paris')->first();

        Event::create([
            'client_id' => $client1->id,
            'type' => Event::TYPE_SEMINAIRE,
            'date' => now()->addMonth()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'venue_id' => $venue2->id,
            'guest_count' => 150,
            'budget' => 25000.00,
            'status' => Event::STATUS_CONFIRME,
            'charged_user_id' => $charge->id,
        ]);

        Event::create([
            'client_id' => $client2->id,
            'type' => Event::TYPE_MARIAGE,
            'date' => now()->addWeeks(2)->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '23:00:00',
            'venue_id' => $venue1->id,
            'guest_count' => 80,
            'budget' => 12000.00,
            'status' => Event::STATUS_BROUILLON,
            'charged_user_id' => $charge->id,
        ]);

        Event::create([
            'client_id' => $client1->id,
            'type' => Event::TYPE_CONFERENCE,
            'date' => now()->subWeek()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '16:00:00',
            'venue_id' => $venue2->id,
            'guest_count' => 200,
            'budget' => 30000.00,
            'status' => Event::STATUS_TERMINE,
            'charged_user_id' => $admin->id,
        ]);

        Event::create([
            'client_id' => $client2->id,
            'type' => Event::TYPE_CONCERT,
            'date' => now()->addDays(5)->toDateString(),
            'start_time' => '20:00:00',
            'end_time' => '23:00:00',
            'venue_id' => $venue1->id,
            'guest_count' => 60,
            'budget' => 8000.00,
            'status' => Event::STATUS_ANNULE,
            'cancellation_reason' => 'Changement de programme par le client.',
            'charged_user_id' => $charge->id,
        ]);
    }
}
