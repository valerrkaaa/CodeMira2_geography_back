<?php

namespace App\Services\Files;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function buildPath($prefix, $userId, $fileId, $extension){
        return $prefix . '/' . $userId . '/' . $fileId . '.' . $extension;
    }
    public function getFile($path){
        return Storage::get($path);
    }

    public function saveFile($path, $content){
        return Storage::put($path, $content);
    }
}