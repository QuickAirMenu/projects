<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;

/**
 * HasAttachments — provides a generic storeAttachment() helper.
 */
trait HasAttachments
{
    /**
     * Store an uploaded file under a namespaced folder and return path info.
     */
    public function storeAttachment(UploadedFile $file, string $folder = 'attachments'): array
    {
        $path = $file->store("{$folder}/{$this->id}", 'public');

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    /**
     * Return a human-readable file size string.
     */
    public static function humanFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
