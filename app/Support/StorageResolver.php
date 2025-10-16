<?php // app/Support/StorageResolver.php
namespace App\Support;

class StorageResolver
{
    public static function disk(): string
    {
       return env('FILESYSTEM_DISK_FINAL', env('FILES_DEFAULT_DISK', 'synology_sftp'));
    }
}
