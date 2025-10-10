<?php // app/Support/StorageResolver.php
namespace App\Support;

class StorageResolver
{
    public static function disk(): string
    {
        return config('files.default_disk', env('FILES_DEFAULT_DISK','local_files'));
    }
}
