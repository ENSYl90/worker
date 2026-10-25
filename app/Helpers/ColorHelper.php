<?php

use Illuminate\Support\Facades\Cache;
use OzdemirBurak\Iris\Color\Hex;
use OzdemirBurak\Iris\Color\Hsla;

if (!function_exists('getAppColorShades')) {
    function getAppColorShades(): array
    {
        $savedColor = setting('websiteColor', '#246fd0');
        $cacheKey = 'app_color_styles_' . md5($savedColor);

        return Cache::rememberForever($cacheKey, function () use ($savedColor) {
            $appColor = new Hex($savedColor);

            $hsla = new Hsla(
                sprintf('%s,40%%,75%%,0.45', $appColor->toHsla()->hue())
            );

            $colorShades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];
            $percentages = [95, 90, 75, 50, 25, 0, 5, 15, 25, 35];
            $shades = [];

            foreach ($colorShades as $index => $shade) {
                $shades[$shade] = $index < 5
                    ? (string) $appColor->brighten($percentages[$index])
                    : (string) $appColor->darken($percentages[$index]);
            }

            // Normalize short HEX
            $hex = strlen(trim($savedColor)) == 4
                ? '#' . str_repeat($savedColor[1], 2) . str_repeat($savedColor[2], 2) . str_repeat($savedColor[3], 2)
                : $savedColor;

            $r = hexdec(substr($hex, 1, 2));
            $g = hexdec(substr($hex, 3, 2));
            $b = hexdec(substr($hex, 5, 2));
            $isDark = ($r + $g + $b) < 381;

            return [
                'hsla' => (string) $hsla,
                'shades' => $shades,
                'isDark' => $isDark,
            ];
        });
    }
}
