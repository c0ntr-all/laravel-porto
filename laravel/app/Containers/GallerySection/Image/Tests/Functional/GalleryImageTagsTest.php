<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tests\Functional;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GalleryImageTagsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Image $image;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $album = Album::create([
            'user_id' => $this->user->id,
            'name' => 'Photos',
        ]);
        $this->image = Image::create([
            'user_id' => $this->user->id,
            'album_id' => $album->id,
            'source' => FileSourceEnum::WEB->value,
            'extension' => 'png',
            'external_url' => 'https://cdn.example.com/a.png',
            'width' => 10,
            'height' => 10,
        ]);
    }

    public function test_user_can_assign_existing_tags_to_image(): void
    {
        $tags = Tag::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/v1/gallery/images/{$this->image->id}/tags", [
                'tags' => $tags->pluck('id')->all(),
            ]);

        $response->assertOk()
            ->assertJsonPath('data.type', ContainerAliasEnum::GALLERY_IMAGE->value)
            ->assertJsonPath('included.0.type', 'tags');

        $tags->each(function (Tag $tag): void {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $this->image->id,
                'taggable_type' => ContainerAliasEnum::GALLERY_IMAGE->value,
                'user_id' => $this->user->id,
            ]);
        });
    }

    public function test_user_can_assign_new_tags_to_image_and_include_them(): void
    {
        $newTagNames = ['Sunset', 'Canon'];

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/v1/gallery/images/{$this->image->id}/tags", [
                'new_tags' => $newTagNames,
            ]);

        $response->assertOk()
            ->assertJsonPath('included.0.type', 'tags');

        collect($newTagNames)->each(function (string $name): void {
            $this->assertDatabaseHas('tags', [
                'user_id' => $this->user->id,
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
            $this->assertDatabaseHas('taggables', [
                'tag_id' => Tag::where('name', $name)->where('user_id', $this->user->id)->value('id'),
                'taggable_id' => $this->image->id,
                'taggable_type' => ContainerAliasEnum::GALLERY_IMAGE->value,
            ]);
        });

        $included = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/gallery/images/{$this->image->id}?include=tags");

        $included->assertOk()
            ->assertJsonPath('included.0.type', 'tags');
        $this->assertCount(2, $included->json('included'));
    }

    public function test_assigning_tags_replaces_previous_image_tags(): void
    {
        $keep = Tag::factory()->create(['user_id' => $this->user->id]);
        $drop = Tag::factory()->create(['user_id' => $this->user->id]);
        $this->image->attachTag($drop, $this->user->id);

        $this->actingAs($this->user, 'api')
            ->putJson("/api/v1/gallery/images/{$this->image->id}/tags", [
                'tags' => [$keep->id],
            ])
            ->assertOk();

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $keep->id,
            'taggable_id' => $this->image->id,
            'taggable_type' => ContainerAliasEnum::GALLERY_IMAGE->value,
        ]);
        $this->assertDatabaseMissing('taggables', [
            'tag_id' => $drop->id,
            'taggable_id' => $this->image->id,
            'taggable_type' => ContainerAliasEnum::GALLERY_IMAGE->value,
        ]);
    }

    public function test_user_cannot_assign_another_users_tag_to_image(): void
    {
        $foreignTag = Tag::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $this->actingAs($this->user, 'api')
            ->putJson("/api/v1/gallery/images/{$this->image->id}/tags", [
                'tags' => [$foreignTag->id],
            ])
            ->assertUnprocessable();
    }
}
