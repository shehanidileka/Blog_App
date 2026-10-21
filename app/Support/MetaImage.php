<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MetaImage
{
    private const WIDTH = 1200;
    private const HEIGHT = 630;
    private const MAX_BYTES = 280 * 1024;

    /**
     * Makes a 1200x630 JPEG (under ~280KB) from a cover image on the "public" disk.
     * Returns the new path (e.g. posts/meta/abc.jpg) or null if it fails.
     */
    public static function fromCover(?string $coverPath): ?string
    {
        if (! $coverPath || ! extension_loaded('gd')) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($coverPath)) {
            return null;
        }

        try {
            $src = @imagecreatefromstring($disk->get($coverPath));

            if (! $src) {
                return null;
            }

            $sw = imagesx($src);
            $sh = imagesy($src);

            // Crop the centre of the image to a 1200:630 ratio
            $scale = max(self::WIDTH / $sw, self::HEIGHT / $sh);
            $cw = (int) round(self::WIDTH / $scale);
            $ch = (int) round(self::HEIGHT / $scale);
            $sx = (int) max(0, ($sw - $cw) / 2);
            $sy = (int) max(0, ($sh - $ch) / 2);

            $dst = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
            imagecopyresampled($dst, $src, 0, 0, $sx, $sy, self::WIDTH, self::HEIGHT, $cw, $ch);

            // Lower the quality step by step until the file is small enough
            $data = '';
            foreach ([82, 72, 62, 52, 42] as $quality) {
                ob_start();
                imagejpeg($dst, null, $quality);
                $data = ob_get_clean();

                if (strlen($data) <= self::MAX_BYTES) {
                    break;
                }
            }

            imagedestroy($src);
            imagedestroy($dst);

            $path = 'posts/meta/' . pathinfo($coverPath, PATHINFO_FILENAME) . '.jpg';
            $disk->put($path, $data);

            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }
}