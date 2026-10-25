<?php

namespace App\Jobs;

use App\Exports\CategoriesExport;
use App\Exports\EarningsExport;
use App\Exports\MenuExport;
use App\Exports\PayoutsExport;
use App\Exports\ProductsExport;
use App\Exports\ServicesExport;
use App\Exports\SubCategoriesExport;
use App\Exports\VendorsExport;
use App\Models\ImportExportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessDataExportJob implements ShouldQueue
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
                'message' => __('Export started'),
            ]);

            $fileName = Str::slug($record->file_name ?: $record->data_type_name ?: 'export');
            $filePath = 'exports/' . $fileName . '-' . $record->id . '-' . now()->format('YmdHis') . '.xlsx';

            $record->update([
                'progress' => 50,
                'message' => __('Generating export file'),
            ]);

            Excel::store($this->exporterFor($record->data_type), $filePath, 'public');

            $record->update([
                'status' => ImportExportJob::STATUS_COMPLETED,
                'progress' => 100,
                'result_path' => $filePath,
                'message' => __('Data exported successfully!'),
                'finished_at' => now(),
            ]);
        } catch (Throwable $error) {
            $record->update([
                'status' => ImportExportJob::STATUS_FAILED,
                'progress' => 100,
                'message' => $error->getMessage() ?: __('Data export failed!'),
                'finished_at' => now(),
            ]);

            throw $error;
        }
    }

    protected function exporterFor($dataType)
    {
        switch ((int) $dataType) {
            case 1:
                return new CategoriesExport;
            case 2:
                return new SubCategoriesExport;
            case 3:
                return new VendorsExport;
            case 4:
                return new MenuExport;
            case 5:
                return new ProductsExport;
            case 6:
                return new ServicesExport;
            case 7:
                return new EarningsExport;
            case 8:
                return new PayoutsExport;
            default:
                throw new \InvalidArgumentException(__('Unsupported export type'));
        }
    }
}
