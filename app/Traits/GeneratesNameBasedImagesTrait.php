<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GeneratesNameBasedImagesTrait
{
    public function generateNameBasedImages(string $name, string $directory = 'seeder-media/generated', array $options = []): array
    {
        $directory = $this->nameImageDirectory($directory);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $slug = $options['slug'] ?? Str::slug($name);
        $logoPath = "{$directory}/{$slug}-logo.png";
        $featureImagePath = "{$directory}/{$slug}-feature.png";

        if (extension_loaded('imagick') && class_exists(\Imagick::class)) {
            $this->generateImagickNameLogo($name, $logoPath, $options);
            $this->generateImagickNameFeatureImage($name, $featureImagePath, $options);

            return [
                'logo' => $logoPath,
                'feature_image' => $featureImagePath,
            ];
        }

        if (extension_loaded('gd')) {
            $this->generateGdNameLogo($name, $logoPath, $options);
            $this->generateGdNameFeatureImage($name, $featureImagePath, $options);

            return [
                'logo' => $logoPath,
                'feature_image' => $featureImagePath,
            ];
        }

        throw new \RuntimeException('Unable to generate name based images because neither Imagick nor GD is installed.');
    }

    protected function nameImageDirectory(string $directory): string
    {
        if (str_starts_with($directory, DIRECTORY_SEPARATOR)) {
            return rtrim($directory, DIRECTORY_SEPARATOR);
        }

        return storage_path('app/' . trim($directory, '/'));
    }

    protected function generateImagickNameLogo(string $name, string $path, array $options = []): void
    {
        [$primary, $secondary] = $this->nameImageColors($name, $options);
        $initials = $this->nameImageInitials($name);
        $width = $options['logo_width'] ?? 400;
        $height = $options['logo_height'] ?? 400;

        $image = new \Imagick();
        $image->newImage($width, $height, new \ImagickPixel($primary));
        $image->setImageFormat('png');

        $overlay = new \Imagick();
        $overlay->newPseudoImage($width, $height, "radial-gradient:{$secondary}-{$primary}");
        $image->compositeImage($overlay, \Imagick::COMPOSITE_OVER, 0, 0);

        $draw = new \ImagickDraw();
        $draw->setFillColor(new \ImagickPixel($options['text_color'] ?? 'white'));
        $this->setNameImageImagickFont($draw);
        $draw->setFontWeight(700);
        $draw->setFontSize($options['logo_font_size'] ?? (strlen($initials) > 2 ? 132 : 150));
        $draw->setGravity(\Imagick::GRAVITY_CENTER);
        $image->annotateImage($draw, 0, 0, 0, $initials);

        $image->writeImage($path);
        $overlay->clear();
        $image->clear();
    }

    protected function generateImagickNameFeatureImage(string $name, string $path, array $options = []): void
    {
        [$primary, $secondary] = $this->nameImageColors($name, $options);
        $width = $options['feature_width'] ?? 1280;
        $height = $options['feature_height'] ?? 720;

        $image = new \Imagick();
        $image->newPseudoImage($width, $height, "gradient:{$primary}-{$secondary}");
        $image->setImageFormat('png');

        $accent = new \ImagickDraw();
        $accent->setFillColor(new \ImagickPixel('rgba(255,255,255,0.14)'));
        $accent->circle((int) ($width * 0.84), (int) ($height * 0.21), (int) ($width * 0.97), (int) ($height * 0.44));
        $accent->circle((int) ($width * 0.12), (int) ($height * 0.90), (int) ($width * 0.29), (int) ($height * 0.60));
        $image->drawImage($accent);

        $draw = new \ImagickDraw();
        $draw->setFillColor(new \ImagickPixel($options['text_color'] ?? 'white'));
        $this->setNameImageImagickFont($draw);
        $draw->setFontWeight(700);
        $draw->setFontSize($options['feature_font_size'] ?? 86);
        $draw->setGravity(\Imagick::GRAVITY_CENTER);
        $image->annotateImage($draw, 0, -25, 0, $this->nameImageWrapText($name, $options['wrap_length'] ?? 18));

        $image->writeImage($path);
        $image->clear();
    }

    protected function generateGdNameLogo(string $name, string $path, array $options = []): void
    {
        [$primary, $secondary] = $this->nameImageColors($name, $options);
        $width = $options['logo_width'] ?? 400;
        $height = $options['logo_height'] ?? 400;
        $image = imagecreatetruecolor($width, $height);
        $this->fillNameImageGdGradient($image, $width, $height, $primary, $secondary);
        $this->drawCenteredNameImageGdText($image, $this->nameImageInitials($name), 5, (int) ($width / 2), (int) (($height / 2) - 14));
        imagepng($image, $path);
        imagedestroy($image);
    }

    protected function generateGdNameFeatureImage(string $name, string $path, array $options = []): void
    {
        [$primary, $secondary] = $this->nameImageColors($name, $options);
        $width = $options['feature_width'] ?? 1280;
        $height = $options['feature_height'] ?? 720;
        $image = imagecreatetruecolor($width, $height);
        $this->fillNameImageGdGradient($image, $width, $height, $primary, $secondary);
        $this->drawCenteredNameImageGdText($image, $this->nameImageWrapText($name, $options['wrap_length'] ?? 18), 5, (int) ($width / 2), (int) (($height / 2) - 20));
        imagepng($image, $path);
        imagedestroy($image);
    }

    protected function setNameImageImagickFont(\ImagickDraw $draw): void
    {
        $fontPath = $this->resolveNameImageFontPath();

        if ($fontPath) {
            $draw->setFont($fontPath);
        }
    }

    protected function resolveNameImageFontPath(): ?string
    {
        $fontPaths = [
            '/System/Library/Fonts/Supplemental/Arial.ttf',
            '/System/Library/Fonts/Supplemental/Helvetica.ttf',
            '/Library/Fonts/Arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];

        foreach ($fontPaths as $fontPath) {
            if (file_exists($fontPath)) {
                return $fontPath;
            }
        }

        return null;
    }

    protected function fillNameImageGdGradient($image, int $width, int $height, string $start, string $end): void
    {
        $start = $this->nameImageHexToRgb($start);
        $end = $this->nameImageHexToRgb($end);

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $red = (int) ($start[0] + (($end[0] - $start[0]) * $ratio));
            $green = (int) ($start[1] + (($end[1] - $start[1]) * $ratio));
            $blue = (int) ($start[2] + (($end[2] - $start[2]) * $ratio));
            imageline($image, 0, $y, $width, $y, imagecolorallocate($image, $red, $green, $blue));
        }
    }

    protected function drawCenteredNameImageGdText($image, string $text, int $font, int $centerX, int $centerY): void
    {
        $lines = explode("\n", $text);
        $lineHeight = imagefontheight($font) + 8;
        $white = imagecolorallocate($image, 255, 255, 255);
        $startY = $centerY - ((count($lines) * $lineHeight) / 2);

        foreach ($lines as $index => $line) {
            $textWidth = imagefontwidth($font) * strlen($line);
            imagestring($image, $font, (int) ($centerX - ($textWidth / 2)), (int) ($startY + ($index * $lineHeight)), $line, $white);
        }
    }

    protected function nameImageColors(string $name, array $options = []): array
    {
        if (!empty($options['colors']) && count($options['colors']) >= 2) {
            return array_values($options['colors']);
        }

        $palettes = [
            ['#2563eb', '#14b8a6'],
            ['#db2777', '#f97316'],
            ['#059669', '#84cc16'],
            ['#7c3aed', '#06b6d4'],
            ['#dc2626', '#f59e0b'],
            ['#0f766e', '#3b82f6'],
            ['#9333ea', '#ec4899'],
            ['#0891b2', '#22c55e'],
        ];

        return $palettes[abs(crc32($name)) % count($palettes)];
    }

    protected function nameImageInitials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name));
        $initials = '';

        foreach ($words as $word) {
            if ($word !== '') {
                $initials .= Str::upper(Str::substr($word, 0, 1));
            }
        }

        return Str::substr($initials, 0, 3) ?: 'V';
    }

    protected function nameImageWrapText(string $text, int $length): string
    {
        return wordwrap($text, $length, "\n", false);
    }

    protected function nameImageHexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
