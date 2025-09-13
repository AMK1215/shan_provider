<?php

namespace Database\Seeders;

use App\Models\ContactType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agents = User::pluck('id')->toArray();
        $contactTypes = ContactType::pluck('id', 'name')->toArray();

        $sampleContacts = [
            ['name' => 'John FB', 'value' => 'john.fb', 'type' => 'Facebook'],
            ['name' => 'Anna Email', 'value' => 'anna@example.com', 'type' => 'Email'],
            ['name' => 'Tom IG', 'value' => '@tominsta', 'type' => 'Instagram'],
            ['name' => 'Lisa Line', 'value' => 'lisa_line', 'type' => 'Line'],
            ['name' => 'Mike Phone', 'value' => '+123456789', 'type' => 'Phone'],
            ['name' => 'Sara Viber', 'value' => '+987654321', 'type' => 'Viber'],
            ['name' => 'Leo Telegram', 'value' => '@leobot', 'type' => 'Telegram'],
            ['name' => 'Eva WhatsApp', 'value' => '+1122334455', 'type' => 'WhatsApp'],
        ];

        foreach ($sampleContacts as $contact) {
            DB::table('contacts')->insert([
                'agent_id' => $agents[array_rand($agents)],
                'name' => $contact['name'],
                'value' => $contact['value'],
                'type_id' => $contactTypes[$contact['type']] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
