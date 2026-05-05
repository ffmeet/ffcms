<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SiteIconManager
{
    private const ICON_SPECS = [
        'favicon_path' => 32,
        'apple_touch_icon_path' => 180,
    ];

    public function regenerate(SiteSetting $settings): void
    {
        $sourcePath = $settings->frontend_logo_path ?: $settings->admin_logo_path;

        if (! filled($sourcePath)) {
            $this->cleanup($settings);
            $settings->forceFill([
                'favicon_path' => null,
                'apple_touch_icon_path' => null,
            ])->saveQuietly();

            return;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($sourcePath)) {
            throw new RuntimeException('站点 Logo 文件不存在，无法生成站点图标。');
        }

        $source = $this->createImageResource((string) $disk->path($sourcePath));
        $directory = 'branding/favicon';
        $basename = 'site-icon';
        $paths = [];

        foreach (self::ICON_SPECS as $column => $size) {
            $targetPath = sprintf('%s/%s-%d.png', $directory, $basename, $size);
            $image = $this->resizeContainSquare($source, $size);

            ob_start();
            imagepng($image, null, 8);
            $binary = (string) ob_get_clean();
            imagedestroy($image);

            $disk->put($targetPath, $binary);
            $paths[$column] = $targetPath;
        }

        imagedestroy($source);

        $settings->forceFill($paths)->saveQuietly();
    }

    public function cleanup(SiteSetting $settings): void
    {
        $disk = Storage::disk('public');

        collect([
            $settings->favicon_path,
            $settings->apple_touch_icon_path,
        ])->filter()->each(fn (string $path) => $disk->delete($path));
    }

    private function createImageResource(string $path)
    {
        $contents = @file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('站点 Logo 读取失败。');
        }

        $image = @imagecreatefromstring($contents);

        if (! $image) {
            throw new RuntimeException('站点 Logo 格式暂不支持。');
        }

        return $image;
    }

    private function resizeContainSquare($source, int $size)
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $target = imagecreatetruecolor($size, $size);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transparent);

        $scale = min($size / max(1, $sourceWidth), $size / max(1, $sourceHeight));
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $dstX = (int) floor(($size - $targetWidth) / 2);
        $dstY = (int) floor(($size - $targetHeight) / 2);

        imagecopyresampled(
            $target,
            $source,
            $dstX,
            $dstY,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        return $target;
    }
}
