<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    public function upload(UploadedFile $file , string $folder = "uploads" , string $disk = "public")
    {
        return $file->store($folder , $disk);
    }

    public function delete(string $path , string $disk = "public")
    {
         if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
         }
    }

    public function replace(UploadedFile $newFile , string $oldPath , string $folder = "uploads" , string $disk = "public")
    {
        $this->delete($oldPath , $disk);
        return $this->upload($newFile , $folder , $disk);
    }
}