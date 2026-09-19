<?php declare(strict_types=1);

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Migrations\Support\IdentitySchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $userFk = [['columns' => ['user_id'], 'on' => 'users']];
        $galleryMediaFks = [
            ['columns' => ['user_id'], 'on' => 'users'],
            ['columns' => ['album_id'], 'on' => 'gallery_albums'],
        ];
        $cascadeUserFk = [['columns' => ['user_id'], 'on' => 'users', 'onDelete' => 'cascade']];

        $imageMap = IdentitySchema::convertUuidPrimaryKey('gallery_images', $galleryMediaFks);
        IdentitySchema::convertSavedFromId('gallery_images', $imageMap);
        $this->remapMediaMorphs($imageMap, [
            ContainerAliasEnum::GALLERY_IMAGE->value,
            'images',
        ]);

        $videoMap = IdentitySchema::convertUuidPrimaryKey('gallery_videos', $galleryMediaFks);
        IdentitySchema::convertSavedFromId('gallery_videos', $videoMap);
        $this->remapMediaMorphs($videoMap, [
            ContainerAliasEnum::GALLERY_VIDEO->value,
            'videos',
        ]);

        $documentMap = IdentitySchema::convertUuidPrimaryKey('app_documents', $userFk);
        $this->remapMediaMorphs($documentMap, [
            ContainerAliasEnum::APP_DOCUMENT->value,
        ]);

        IdentitySchema::changeColumnToUnsignedBigInteger('attachments', 'attachable_id');
        IdentitySchema::changeColumnToUnsignedBigInteger('attachments', 'fileable_id');
        IdentitySchema::convertUuidPrimaryKey('attachments', $userFk);

        IdentitySchema::changeColumnToUnsignedBigInteger('custom_fields', 'fieldable_id');
        IdentitySchema::convertUuidPrimaryKey('custom_fields', $userFk);

        IdentitySchema::convertUuidPrimaryKey('user_notifications', $cascadeUserFk, false);

        IdentitySchema::changeColumnToUnsignedBigInteger('comments', 'commentable_id');

        foreach ([
            'gallery_albums',
            'lifelog_posts',
            'lifelog_presets',
            'tm_tasks',
            'tm_task_lists',
            'tm_task_templates',
            'gallery_images',
            'gallery_videos',
            'app_documents',
            'attachments',
            'custom_fields',
        ] as $table) {
            IdentitySchema::addUuidV7Column($table);
        }
    }

    public function down(): void
    {
        // Irreversible identity conversion.
    }

    /**
     * @param array<string, int> $mapping
     * @param list<string> $types
     */
    private function remapMediaMorphs(array $mapping, array $types): void
    {
        IdentitySchema::remapMorphIds('attachments', 'fileable_type', 'fileable_id', $types, $mapping);
        IdentitySchema::remapMorphIds('comments', 'commentable_type', 'commentable_id', $types, $mapping);
        IdentitySchema::remapMorphIds('activity_system_logs', 'main_type', 'main_id', $types, $mapping);
        IdentitySchema::remapMorphIds('activity_system_logs', 'related_type', 'related_id', $types, $mapping);
        IdentitySchema::remapMorphIds('activity_use_case_logs', 'loggable_type', 'loggable_id', $types, $mapping);
    }
};
