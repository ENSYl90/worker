<?php

namespace App\Imports;

use App\Models\Vendor;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class VendorsImport implements OnEachRow, WithHeadingRow
{

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();
        if (!empty($row["name"])) {
            $vendor = Vendor::updateOrCreate(
                ['id' => $row["id"]],
                $row
            );

            //
            try {
                $categoriesIds = explode(",", $row["categories_id"]);
                if (!empty($categoriesIds)) {
                    $vendor->categories()->sync($categoriesIds);
                }
            } catch (\Exception $e) {
                // Handle the exception, e.g., log it or ignore it
                logger()->warning("Error syncing categories for vendor ID {$vendor->id}: ", ['error' => $e->getMessage()]);
            }

            if ($row["logo"] != null && !empty($row["logo"])) {

                $vendor->clearMediaCollection("logo");
                $vendor->addMediaFromUrl($row["logo"])->toMediaCollection('logo');
            }
            if ($row["feature_image"] != null && !empty($row["feature_image"])) {

                $vendor->clearMediaCollection("feature_image");
                $vendor->addMediaFromUrl($row["feature_image"])->toMediaCollection('feature_image');
            }
        }
    }
}
