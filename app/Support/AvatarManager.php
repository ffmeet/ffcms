<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AvatarManager
{
    private const SIZES = [
        'large' => 256,
        'medium' => 120,
        'small' => 48,
    ];

    public function storeForUser(User $user, UploadedFile $file): array
    {
        $this->cleanup($user);

        $disk = Storage::disk('public');
        $directory = 'avatars/'.$user->id;
        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? $extension : 'png';
        $basename = 'avatar-'.Str::random(20);

        $originalPath = $file->storeAs($directory, $basename.'-original.'.$extension, 'public');
        $source = $this->createImageResource($file->getRealPath());

        $paths = [
            'avatar_original_path' => $originalPath,
        ];

        foreach (self::SIZES as $key => $size) {
            $targetPath = $directory.'/'.$basename.'-'.$key.'.png';
            $image = $this->resizeSquare($source, $size);

            ob_start();
            imagepng($image, null, 8);
            $binary = (string) ob_get_clean();
            imagedestroy($image);

            $disk->put($targetPath, $binary);
            $paths['avatar_'.$key.'_path'] = $targetPath;
        }

        imagedestroy($source);

        return $paths;
    }

    public function cleanup(User $user): void
    {
        $disk = Storage::disk('public');

        collect([
            $user->avatar_original_path,
            $user->avatar_large_path,
            $user->avatar_medium_path,
            $user->avatar_small_path,
        ])->filter()->each(fn (string $path) => $disk->delete($path));
    }

    private function createImageResource(string $path)
    {
        $contents = @file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('头像文件读取失败。');
        }

        $image = @imagecreatefromstring($contents);

        if (! $image) {
            throw new RuntimeException('头像文件格式暂不支持。');
        }

        return $image;
    }

    private function resizeSquare($source, int $size)
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $cropSize = min($sourceWidth, $sourceHeight);
        $srcX = (int) floor(($sourceWidth - $cropSize) / 2);
        $srcY = (int) floor(($sourceHeight - $cropSize) / 2);

        $target = imagecreatetruecolor($size, $size);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transparent);

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $srcX,
            $srcY,
            $size,
            $size,
            $cropSize,
            $cropSize,
        );

        return $target;
    }
}
