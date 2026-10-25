<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
/**
 * Download an icon from Hugeicons CDN
 *
 * @param string $iconName The name of the icon (e.g., 'hotel-01', 'crown', 'beach')
 * @param string $format The format of the icon (svg or png). Default: 'svg'
 * @param string $style The style of the icon (stroke-rounded, duotone, solid, etc.). Default: 'stroke-rounded'
 * @return string|null Returns the temporary file path on success, null on failure
 *
 * @example
 * $tempPath = downloadHugeicon('hotel-01');
 * if ($tempPath) {
 *     $model->addMedia($tempPath)->toMediaCollection();
 *     cleanupTempIcon($tempPath);
 * }
 */
function downloadHugeicon(string $iconName, string $format = 'svg', string $style = 'stroke-rounded'): ?string
{
    if (!App::isProduction()) {
        return null;
    }

    try {
        // Build the Hugeicons CDN URL
        $iconUrl = "https://cdn.hugeicons.com/icons/{$iconName}-{$style}.{$format}";

        // Download the icon with timeout
        $response = Http::timeout(10)->get($iconUrl);

        if (!$response->successful()) {
            Log::warning("Failed to download icon from Hugeicons", [
                'icon' => $iconName,
                'url' => $iconUrl,
                'status' => $response->status()
            ]);
            return null;
        }

        // Create a unique temporary file path
        $uniqueId = uniqid();
        $tempDir = storage_path('app/temp/icons');

        // Ensure temp directory exists
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = "{$tempDir}/{$iconName}-{$uniqueId}.{$format}";

        // Save the icon to temporary file
        file_put_contents($tempPath, $response->body());

        return $tempPath;

    } catch (\Exception $e) {
        Log::error("Error downloading icon from Hugeicons", [
            'icon' => $iconName,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return null;
    }
}

/**
 * Download and attach a Hugeicon to a model
 *
 * @param mixed $model The model that uses Spatie Media Library
 * @param string $iconName The name of the icon
 * @param string $collectionName The media collection name. Default: 'default'
 * @param string $format The format of the icon (svg or png). Default: 'svg'
 * @param string $style The style of the icon. Default: 'stroke-rounded'
 * @return bool Returns true on success, false on failure
 *
 * @example
 * attachHugeiconToModel($category, 'hotel-01');
 * attachHugeiconToModel($subcategory, 'crown', 'icons', 'png');
 */
function attachHugeiconToModel(
    $model,
    string $iconName,
    string $collectionName = 'default',
    string $format = 'svg',
    string $style = 'stroke-rounded'
): bool {

    if (!App::isProduction()) {
        return true;
    }

    try {
        // Download the icon
        $tempPath = downloadHugeicon($iconName, $format, $style);

        if (!$tempPath) {
            return false;
        }

        // Clear existing media in the collection
        $model->clearMediaCollection($collectionName);

        // Attach the icon to the model
        $model->addMedia($tempPath)
            ->preservingOriginal()
            ->toMediaCollection($collectionName);

        // Clean up the temporary file
        cleanupTempIcon($tempPath);

        return true;

    } catch (\Exception $e) {
        Log::error("Error attaching icon to model", [
            'icon' => $iconName,
            'model' => get_class($model),
            'model_id' => $model->id ?? null,
            'error' => $e->getMessage()
        ]);

        // Clean up temp file if it exists
        if (isset($tempPath) && $tempPath) {
            cleanupTempIcon($tempPath);
        }

        return false;
    }
}

/**
 * Clean up a temporary icon file
 *
 * @param string $tempPath The path to the temporary file
 * @return bool Returns true if file was deleted or doesn't exist, false on error
 */
function cleanupTempIcon(string $tempPath): bool
{
    try {
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
        return true;
    } catch (\Exception $e) {
        Log::warning("Failed to cleanup temporary icon file", [
            'path' => $tempPath,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Batch download multiple icons from Hugeicons
 *
 * @param array $iconNames Array of icon names to download
 * @param string $format The format of the icons. Default: 'svg'
 * @param string $style The style of the icons. Default: 'stroke-rounded'
 * @return array Returns associative array [iconName => tempPath or null]
 *
 * @example
 * $icons = batchDownloadHugeicons(['hotel-01', 'crown', 'beach']);
 * foreach ($icons as $name => $path) {
 *     if ($path) {
 *         // Use the icon
 *         cleanupTempIcon($path);
 *     }
 * }
 */
function batchDownloadHugeicons(array $iconNames, string $format = 'svg', string $style = 'stroke-rounded'): array
{
    $results = [];

    foreach ($iconNames as $iconName) {
        $results[$iconName] = downloadHugeicon($iconName, $format, $style);
    }

    return $results;
}

/**
 * Clean up all temporary icon files in the temp directory
 *
 * @param int $olderThanMinutes Only delete files older than this many minutes. Default: 60
 * @return int Returns the number of files deleted
 */
function cleanupAllTempIcons(int $olderThanMinutes = 60): int
{
    $tempDir = storage_path('app/temp/icons');

    if (!file_exists($tempDir)) {
        return 0;
    }

    $count = 0;
    $cutoffTime = time() - ($olderThanMinutes * 60);

    try {
        $files = glob($tempDir . '/*');

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }
    } catch (\Exception $e) {
        Log::error("Error cleaning up temp icon files", [
            'error' => $e->getMessage()
        ]);
    }

    return $count;
}
