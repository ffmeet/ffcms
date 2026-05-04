<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;

class UploadDiagnostics
{
    public static function log(string $event, array $context = [], string $level = 'info'): void
    {
        Log::channel('upload')->log($level, $event, $context);
    }

    public static function baseContext(?Request $request = null): array
    {
        return [
            'request_id' => $request?->headers->get('X-Request-Id'),
            'route' => $request?->route()?->getName(),
            'path' => $request?->path(),
            'method' => $request?->method(),
            'user_id' => $request?->user()?->id,
            'ip' => $request?->ip(),
            'content_length' => $request?->server('CONTENT_LENGTH'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_tmp_dir' => ini_get('upload_tmp_dir'),
            'sys_temp_dir' => ini_get('sys_temp_dir'),
        ];
    }

    public static function uploadedFileContext(?UploadedFile $file, array $extra = []): array
    {
        return array_merge([
            'original_name' => $file?->getClientOriginalName(),
            'mime_type' => $file?->getClientMimeType(),
            'size' => $file?->getSize(),
            'tmp_path' => $file?->getRealPath(),
            'error' => $file?->getError(),
        ], $extra);
    }

    public static function throwableContext(Throwable $throwable, array $extra = []): array
    {
        return array_merge([
            'exception' => get_class($throwable),
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
        ], $extra);
    }
}
