<?php

namespace Modules\Website\Support;

use Illuminate\Support\Facades\Storage;

/**
 * The contract for the media columns on Website catalogue tables.
 *
 * `image_url` is named for what it has always been called, but the stored value is
 * now a path on the `public` disk - `room-types/deluxe-suite_20261004_a1b2c3.jpg` -
 * rather than a browser URL. Storing the path is what makes the column portable:
 * an absolute URL bakes in whatever APP_URL happened to be on the day of upload,
 * so the same row renders differently on staging than in production, and the
 * front desk module had no way to know which host to expect.
 *
 * The reader tolerates absolute URLs so three things keep working without a data
 * migration: rows uploaded before this contract existed, the stock photographs in
 * the seeders, and any legitimately remote image. Only new writes are paths.
 */
final class MediaPath
{
    /**
     * The marker Laravel's public disk puts in front of every file URL. Matching on
     * it rather than on APP_URL means a row uploaded under a different host still
     * normalises correctly.
     */
    private const STORAGE_MARKER = '/storage/';

    /**
     * Schemes that identify a value as already being a browser URL.
     */
    private const REMOTE_PREFIXES = ['http://', 'https://', '//', 'data:'];

    /**
     * Does this value already point somewhere a browser can fetch it directly?
     */
    public static function isRemote(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        foreach (self::REMOTE_PREFIXES as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reduce a stored value to the path it represents on the given disk.
     *
     * Returns null when the value is blank or is a remote URL we have no local
     * copy of, which is the caller's signal to leave the column alone.
     */
    public static function toStoragePath(?string $value, string $disk = 'public'): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (self::isRemote($value)) {
            $markerPosition = strpos($value, self::STORAGE_MARKER);

            // Our own disk served over HTTP: strip the origin and the marker so the
            // row becomes host-independent.
            return $markerPosition === false
                ? null
                : ltrim(substr($value, $markerPosition + strlen(self::STORAGE_MARKER)), '/');
        }

        // A bare path, possibly with a leading slash from a hand-edited row.
        return ltrim($value, '/');
    }

    /**
     * Resolve a stored value to something an <img src> can load.
     *
     * This is the only place in the codebase that should turn a media column into a
     * URL. Blade templates and API payloads call this rather than echoing
     * `image_url`, which is what allowed the front desk to render
     * `storage/https://…` for a room type that had an image.
     */
    public static function toUrl(?string $value, string $disk = 'public'): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (self::isRemote($value)) {
            return $value;
        }

        return Storage::disk($disk)->url(ltrim($value, '/'));
    }
}
