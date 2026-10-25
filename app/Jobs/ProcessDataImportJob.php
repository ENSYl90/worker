<?php

namespace App\Jobs;

use App\Imports\CategoriesImport;
use App\Imports\MenusImport;
use App\Imports\ProductsImport;
use App\Imports\ServicesImport;
use App\Imports\SubcategoriesImport;
use App\Imports\VendorsImport;
use App\Models\ImportExportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessDataImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900;
    public $tries = 1;

    public $importExportJobId;

    public function __construct($importExportJobId)
    {
        $this->importExportJobId = $importExportJobId;
    }

    public function handle()
    {
        $record = ImportExportJob::findOrFail($this->importExportJobId);

        try {
            $record->update([
                'status' => ImportExportJob::STATUS_PROCESSING,
                'progress' => 10,
                'started_at' => now(),
                'message' => __('Import started'),
            ]);

            Excel::import($this->importerFor($record->data_type), $record->source_path);

            if (!empty($record->source_path)) {
                Storage::delete($record->source_path);
            }

            $record->update([
                'status' => ImportExportJob::STATUS_COMPLETED,
                'progress' => 100,
                'message' => __('Data imported successfully!'),
                'finished_at' => now(),
            ]);
        } catch (Throwable $error) {
            $record->update([
                'status' => ImportExportJob::STATUS_FAILED,
                'progress' => 100,
                'message' => $error->getMessage() ?: __('Data import failed!'),
                'finished_at' => now(),
            ]);

            throw $error;
        }
    }

    protected function importerFor($dataType)
    {
        switch ((int) $dataType) {
            case 1:
                return new CategoriesImport;
            case 2:
                return new VendorsImport;
            case 3:
                return new MenusImport;
            case 4:
                return new ProductsImport;
            case 5:
                return new SubcategoriesImport;
            case 6:
                return new ServicesImport;
            default:
                throw new \InvalidArgumentException(__('Unsupported import type'));
        }
    }
}
