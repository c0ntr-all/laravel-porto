<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Seeders;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Models\Post;
use Illuminate\Database\Seeder;

class LifelogPostSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first() ?? User::factory()->create();

        $tags = Tag::factory()
            ->count(15)
            ->create(['user_id' => $user->id]);

        Post::factory()
            ->count(100)
            ->withTags($tags)
            ->create(['user_id' => $user->id]);
    }
}
