<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessDataExportJob;
use App\Models\ImportExportJob;

class ExportLivewire extends BaseLivewireComponent
{


    public $dataType;
    public $dataTypeName;
    public $photo;

    public function render()
    {
        return view('livewire.exports', [
            'exportJobs' => $this->recentExportJobs(),
        ]);
    }


    public function exportData($dataType, $fileName)
    {


        try {

            $this->isDemo();

            $job = ImportExportJob::create([
                'user_id' => auth()->id(),
                'type' => ImportExportJob::TYPE_EXPORT,
                'data_type' => $dataType,
                'data_type_name' => $fileName,
                'file_name' => $fileName,
                'status' => ImportExportJob::STATUS_PENDING,
                'progress' => 0,
                'message' => __('Waiting for queue worker'),
            ]);

            ProcessDataExportJob::dispatch($job->id);

            $this->showSuccessAlert(__("Export queued successfully. The download link will appear below when it is ready."));
        } catch (\Exception $error) {
            logger("error", [$error]);
            $this->showErrorAlert($error->getMessage() ?? __("Data export failed!"));
        }
    }

    public function recentExportJobs()
    {
        return ImportExportJob::where('type', ImportExportJob::TYPE_EXPORT)
            ->latest()
            ->limit(10)
            ->get();
    }
}
