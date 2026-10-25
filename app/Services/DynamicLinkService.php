<?php

namespace App\Services;

use Illuminate\Support\Facades\URL;

class DynamicLinkService
{
    public static function generateProductLink($productId)
    {
        return URL::route('dynamic.product', $productId);
    }

    public static function generateServiceLink($serviceId)
    {
        return URL::route('dynamic.service', $serviceId);
    }

    public static function generateVendorLink($vendorId)
    {
        return URL::route('dynamic.vendor', $vendorId);
    }

    public static function generateLink($type, $id)
    {
        switch ($type) {
            case 'product':
                return self::generateProductLink($id);
            case 'service':
                return self::generateServiceLink($id);
            case 'vendor':
                return self::generateVendorLink($id);
            default:
                throw new \InvalidArgumentException("Invalid type: {$type}");
        }
    }

    public static function generateDeepLink($type, $id)
    {
        $scheme = env('dynamic_link.scheme', 'glover');
        return "{$scheme}://{$type}/{$id}";
    }

    public static function generateLinkWithMetadata($type, $id, $title = null, $description = null, $imageUrl = null)
    {
        $shareableLink = self::generateLink($type, $id);
        $deepLink = self::generateDeepLink($type, $id);

        return [
            'type' => $type,
            'id' => $id,
            'title' => $title ?: ucfirst($type),
            'description' => $description,
            'image_url' => $imageUrl,
            'shareable_link' => $shareableLink,
            'deep_link' => $deepLink,
            'created_at' => now()->toISOString()
        ];
    }

    public static function generateBulkLinks(array $items)
    {
        $results = [];

        foreach ($items as $item) {
            if (!isset($item['type'], $item['id'])) {
                $results[] = [
                    'success' => false,
                    'error' => 'Missing type or id',
                    'item' => $item
                ];
                continue;
            }

            try {
                $link = self::generateLinkWithMetadata(
                    $item['type'],
                    $item['id'],
                    $item['title'] ?? null,
                    $item['description'] ?? null,
                    $item['image_url'] ?? null
                );

                $results[] = array_merge(['success' => true], $link);
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'type' => $item['type'],
                    'id' => $item['id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}