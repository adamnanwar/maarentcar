<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait DeletesPublicStorageFile
{
    /**
     * Delete a previously uploaded file on the "public" disk given the full
     * URL stored in the database (as produced by Storage::disk('public')->url()).
     */
    private function deletePublicFile(?string $url): void
    {
        if (! $url) {
            return;
        }

        $path = Str::after($url, '/storage/');

        if ($path !== '' && $path !== $url) {
            Storage::disk('public')->delete($path);
        }
    }
}
