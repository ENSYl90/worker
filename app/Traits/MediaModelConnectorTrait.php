<?php

namespace App\Traits;

use Exception;

trait MediaModelConnectorTrait
{
    private function resolveDemoMediaPath(string $directory, string $name, string $suffix = ''): ?string
    {
        $baseName = strtolower(str_replace(' ', '', $name)) . $suffix . ".png";
        $photo = public_path($directory . '/' . $baseName);

        if (file_exists($photo)) {
            return $photo;
        }

        $normalizedTarget = preg_replace('/[^a-z0-9]/', '', strtolower($name . $suffix));
        $files = glob(public_path($directory . '/*.png')) ?: [];

        foreach ($files as $file) {
            $normalizedFile = preg_replace('/[^a-z0-9]/', '', strtolower(pathinfo($file, PATHINFO_FILENAME)));
            if ($normalizedFile === $normalizedTarget) {
                return $file;
            }
        }

        return null;
    }

    public function setVendorImages($vendor)
    {
        try {
            $photo = $this->resolveDemoMediaPath('images/vendors', $vendor->name);
            if ($photo) {
                $vendor->clearMediaCollection("logo");
                $vendor->addMedia($photo)
                    ->preservingOriginal()
                    ->toMediaCollection("logo");
            }
        } catch (Exception $ex) {
            logger("error syncing vendor logo", [$ex]);
        }

        //
        try {
            $photo = $this->resolveDemoMediaPath('images/vendors', $vendor->name, '_feature_image');
            if ($photo) {
                $vendor->clearMediaCollection("feature_image");
                $vendor->addMedia($photo)
                    ->preservingOriginal()
                    ->toMediaCollection("feature_image");
            }
        } catch (Exception $ex) {
            logger("error syncing vendor feature_image", [$ex]);
        }

        return $vendor;
    }

    public function setProductImages($product)
    {
        try {
            $photo = $this->resolveDemoMediaPath('images/products', $product->name);
            if ($photo) {
                $product->clearMediaCollection();
                $product->addMedia($photo)
                    ->preservingOriginal()
                    ->toMediaCollection();
            }
        } catch (Exception $ex) {
            logger("error syncing product image", [$ex]);
        }
        return $product;
    }
}
