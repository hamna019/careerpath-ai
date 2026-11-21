<?php

namespace Database\Seeders;

use App\Models\AdminId;
use Illuminate\Database\Seeder;

class AdminIdSeeder extends Seeder
{
    public function run(): void
    {
        $ids = [
            'P-798401',
            'P-542989',
            'P-621946',
            'P-107928',
            'P-331953',
        ];

        foreach ($ids as $id) {
            AdminId::firstOrCreate(['admin_id' => $id]);
        }
    }
}
