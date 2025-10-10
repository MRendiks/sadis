<?php

namespace App\Support;

use App\Models\Folder;
use Illuminate\Support\Str;

class FolderPathResolver
{
    /**
     * Nama direktori untuk divisi (gunakan code/slug jika ada, fallback ke slug(name))
     */
    public static function divisionDir(Folder $folder): string
    {
        $division = $folder->division; // pastikan eager load di pemanggil
        if (!$division) return 'unknown-division';

        $base = $division->code ?? ($division->slug ?? null);
        if (!$base) $base = Str::slug($division->name ?? ('division-'.$division->id));

        return $base;
    }

    /**
     * Build path nested: {division}/{root}/{child}/{current}
     * Contoh: APD/SUB/SUB-1
     */
    public static function buildPath(Folder $folder, bool $includeCurrent = true): string
    {
        $segments = [];
        $cur = $folder;

        // kumpulkan slug dari root → current
        while ($cur) {
            array_unshift($segments, $cur->slug);
            $cur = $cur->parent;
        }

        if (!$includeCurrent) {
            array_pop($segments);
        }

        $divisionDir = self::divisionDir($folder);

        // pakai forward slash agar portable di Storage
        return trim(implode('/', array_filter([$divisionDir, ...$segments])), '/');
    }
}
