<?php

namespace Database\Seeders;

use App\Models\Film;
use App\Models\Marker;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin kasutaja
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@hajusrakendus.ee',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Tavaline kasutaja
        $user = User::create([
            'name'     => 'Jaan Tamm',
            'email'    => 'jaan@hajusrakendus.ee',
            'password' => Hash::make('password'),
        ]);

        // Demo blogipostitused
        $posts = [
            ['title' => 'Tere tulemast Hajusrakendusse!', 'description' => "See on näidispostitus Laravel 13 + Vue 3 + Inertia.js rakenduses.\n\nRakendus sisaldab:\n- Ilmateenistuse API liidestus\n- Kaardirakendus Radar Maps abil\n- Blogi kommentaaridega\n- E-pood Stripe maksetega\n- Filmide JSON API"],
            ['title' => 'Laravel 13 uued funktsioonid', 'description' => "Laravel 13 toob mitmeid põnevaid uuendusi:\n\n1. PHP Attributes mudelitele ja kontrolleritele\n2. Laravel AI SDK\n3. JSON:API tugi\n4. Queue::route() meetod\n5. Passkey autentimine\n\nKõik need muudavad arenduse sujuvamaks."],
            ['title' => 'Vue 3 + Inertia.js kombinatsioon', 'description' => "Inertia.js on sild serveri-poolse Laravel ruutimise ja kliendipoolse Vue 3 renderdamise vahel.\n\nEelised:\n- Pole vaja REST API-t ehitada\n- Serveripoolne andmete jagamine on lihtne\n- SPA-sarnane kasutajakogemus\n- Ziggy integratsioon nimega marsruutideks"],
        ];

        foreach ($posts as $postData) {
            $post = $admin->posts()->create([
                ...$postData,
                'published' => true,
            ]);
            $post->comments()->create([
                'author_name'  => 'Jaan Tamm',
                'author_email' => 'jaan@example.com',
                'body'         => 'Väga kasulik postitus, aitäh!',
                'approved'     => true,
            ]);
        }

        // Demo markerid
        $markers = [
            ['name' => 'Tallinna Vanalinn', 'latitude' => 59.4372, 'longitude' => 24.7453, 'description' => 'UNESCO maailmapärandi nimistusse kuuluv vanalinn'],
            ['name' => 'Tartu Ülikool',     'latitude' => 58.3806, 'longitude' => 26.7225, 'description' => 'Eesti vanim ülikool, asutatud 1632'],
            ['name' => 'Pärnu Rand',         'latitude' => 58.3760, 'longitude' => 24.5034, 'description' => 'Eesti suvepeаlinna kuulus liivarand'],
            ['name' => 'Saaremaa Kuressaare Loss', 'latitude' => 58.2524, 'longitude' => 22.4892, 'description' => 'Keskaegne piiskopilinnus'],
        ];

        foreach ($markers as $m) {
            Marker::create([...$m, 'added' => now()->subDays(rand(1, 30))]);
        }

        // Demo filmid
        $films = [
            ['title' => 'Inception', 'director' => 'Christopher Nolan', 'year' => 2010, 'genre' => 'Sci-Fi', 'rating' => 8.8, 'description' => 'Unenägude sees unenäod. Dom Cobb on varas, kes oskab siseneda inimeste alateadvusse.', 'image' => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?w=400'],
            ['title' => 'The Dark Knight', 'director' => 'Christopher Nolan', 'year' => 2008, 'genre' => 'Action', 'rating' => 9.0, 'description' => 'Batman seisab silmitsi Jokeriga, kaosele pühendunud anarhisti ja kurjategijaga.', 'image' => 'https://images.unsplash.com/photo-1635805737707-575885ab0820?w=400'],
            ['title' => 'Schindler\'s List', 'director' => 'Steven Spielberg', 'year' => 1993, 'genre' => 'Draama', 'rating' => 9.0, 'description' => 'Tõsielust inspireeritud lugu saksa ärimehest, kes päästis üle tuhande Poola juudi.', 'image' => 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=400'],
            ['title' => 'Interstellar', 'director' => 'Christopher Nolan', 'year' => 2014, 'genre' => 'Sci-Fi', 'rating' => 8.6, 'description' => 'Astronaudid reisivad läbi mustaaugu otsides uut kodu inimkonnale.', 'image' => 'https://images.unsplash.com/photo-1446941611757-91d2c3bd3d45?w=400'],
            ['title' => 'The Godfather', 'director' => 'Francis Ford Coppola', 'year' => 1972, 'genre' => 'Draama', 'rating' => 9.2, 'description' => 'Vägivaldne maffia-dünastia saga Corleone perekonnast.', 'image' => 'https://images.unsplash.com/photo-1594909122845-11baa439b7bf?w=400'],
            ['title' => 'Pulp Fiction', 'director' => 'Quentin Tarantino', 'year' => 1994, 'genre' => 'Põnevik', 'rating' => 8.9, 'description' => 'Omavahel põimuvad lood kurjategijate maailmast Los Angeleses.', 'image' => 'https://images.unsplash.com/photo-1509347528160-9a9e33742cdb?w=400'],
        ];

        foreach ($films as $f) {
            Film::create([...$f, 'user_id' => $admin->id]);
        }
    }
}
