<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tests\Functional;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GalleryVideoTagsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_assign_existing_and_new_tags_to_video(): void
    {
        $user = User::factory()->create();
        $album = Album::create([
            'user_id' => $user->id,
            'name' => 'Clips',
        ]);
        $video = Video::create([
            'user_id' => $user->id,
            'album_id' => $album->id,
            'source' => FileSourceEnum::WEB->value,
            'extension' => 'mp4',
            'external_url' => 'https://cdn.example.com/a.mp4',
            'width' => 10,
            'height' => 10,
        ]);
        $existing = Tag::factory()->create(['user_id' => $user->id, 'name' => 'Travel']);

        $response = $this->actingAs($user, 'api')
            ->putJson("/api/v1/gallery/videos/{$video->id}/tags", [
                'tags' => [$existing->id],
                'new_tags' => ['Holiday'],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.type', ContainerAliasEnum::GALLERY_VIDEO->value)
            ->assertJsonPath('included.0.type', 'tags');

        $this->assertDatabaseHas('tags', [
            'user_id' => $user->id,
            'name' => 'Holiday',
            'slug' => Str::slug('Holiday'),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $existing->id,
            'taggable_id' => $video->id,
            'taggable_type' => ContainerAliasEnum::GALLERY_VIDEO->value,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => Tag::where('name', 'Holiday')->where('user_id', $user->id)->value('id'),
            'taggable_id' => $video->id,
            'taggable_type' => ContainerAliasEnum::GALLERY_VIDEO->value,
        ]);

        $included = $this->actingAs($user, 'api')
            ->getJson("/api/v1/gallery/videos/{$video->id}?include=tags");

        $included->assertOk()
            ->assertJsonPath('included.0.type', 'tags');
        $this->assertCount(2, $included->json('included'));
    }
}
