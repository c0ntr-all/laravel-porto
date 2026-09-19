<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // commentable_id stays unsignedBigInteger: media and other parents use bigint primary keys.
    }

    public function down(): void
    {
        //
    }
};
