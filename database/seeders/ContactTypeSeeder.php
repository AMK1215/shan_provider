<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Facebook', 'image' => 'facebook.png'],
            ['name' => 'Email', 'image' => 'email.png'],
            ['name' => 'Instagram', 'image' => 'instagram.png'],
            ['name' => 'Line', 'image' => 'line.png'],
            ['name' => 'Phone', 'image' => 'phone.png'],
            ['name' => 'Viber', 'image' => 'viber.png'],
            ['name' => 'Telegram', 'image' => 'telegram.png'],
            ['name' => 'WhatsApp', 'image' => 'whatsapp.png'],

        ];
        foreach ($types as $type) {
            \App\Models\ContactType::create($type);
        }
    }
}
