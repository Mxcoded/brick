<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Reduce room_types.image_url from a browser URL to a path on the public disk.
 *
 * Only rows whose value is one of our own disk URLs are rewritten. Rows holding a
 * genuinely remote photograph - the Unsplash URLs in RoomTypeSeeder - are left
 * exactly as they are: there is no local copy to point at, and RoomType::imageUrl()
 * passes them through untouched.
 *
 * The path arithmetic is inlined rather than calling Website\Support\MediaPath,
 * because a migration has to keep working after that class is refactored or moved.
 * The two must stay in step; RoomTypeMediaContractTest asserts they agree.
 *
 * Rows are walked in chunks with raw queries so a row that cannot be parsed leaves
 * its column untouched instead of aborting the migration and rolling back every row
 * processed so far.
 */
return new class extends Migration
{
    /**
     * The marker Laravel's public disk puts in front of every file URL. Matching on
     * it rather than on APP_URL means a row uploaded under a different host still
     * normalises correctly.
     */
    private const STORAGE_MARKER = '/storage/';

    public function up(): void
    {
        $this->rewriteToDiskPaths();
    }

    public function down(): void
    {
        $disk = Storage::disk('public');

        $this->eachRewritableRow(function (string $path) use ($disk): ?string {
            return $disk->url($path);
        });
    }

    private function rewriteToDiskPaths(): void
    {
        $this->eachRewritableRow(function (string $path): ?string {
            return $path;
        });
    }

    /**
     * @param  callable(string): ?string  $replacement  Receives the disk path and
     *                                                  returns the new column value.
     */
    private function eachRewritableRow(callable $replacement): void
    {
        DB::table('room_types')
            ->whereNotNull('image_url')
            ->where('image_url', 'like', '%'.self::STORAGE_MARKER.'%')
            ->chunkById(200, function ($roomTypes) use ($replacement): void {
                foreach ($roomTypes as $roomType) {
                    $markerPosition = strpos($roomType->image_url, self::STORAGE_MARKER);

                    if ($markerPosition === false) {
                        continue;
                    }

                    $path = ltrim(substr($roomType->image_url, $markerPosition + strlen(self::STORAGE_MARKER)), '/');
                    $value = $replacement($path);

                    if ($value === null || $value === $roomType->image_url) {
                        continue;
                    }

                    DB::table('room_types')
                        ->where('id', $roomType->id)
                        ->update(['image_url' => $value]);
                }
            });
    }
};
