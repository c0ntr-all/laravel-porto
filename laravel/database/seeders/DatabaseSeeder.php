<?php

namespace Database\Seeders;

use App\Containers\LifelogSection\Post\Data\Seeders\LifelogPostSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LifelogPostSeeder::class,
        ]);
    }
}
