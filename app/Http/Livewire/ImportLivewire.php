<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessDataImportJob;
use App\Models\ImportExportJob;

class ImportLivewire extends BaseLivewireComponent
{


    public $dataType;
    public $dataTypeName;
    public $photo;

    public function render()
    {
        return view('livewire.imports', [
            'importJobs' => $this->recentImportJobs(),
        ]);
    }


    public function showImportDialog($dataType, $dataTypeName)
    {

        $this->dataType = $dataType;
        $this->dataTypeName = $dataTypeName;
        $this->showCreateModal();
    }

    public function processImport()
    {
        //
        $this->validate(
            [
                "photo" => "required"
            ],
            [
                "photo.required" => __("Please select data file to import")
            ]
        );


        $uploadedFile = $this->photo->store('imports/excel');

        try {

            $this->isDemo();

            $job = ImportExportJob::create([
                'user_id' => auth()->id(),
                'type' => ImportExportJob::TYPE_IMPORT,
                'data_type' => $this->dataType,
                'data_type_name' => $this->dataTypeName,
                'status' => ImportExportJob::STATUS_PENDING,
                'progress' => 0,
                'source_path' => $uploadedFile,
                'message' => __('Waiting for queue worker'),
            ]);

            ProcessDataImportJob::dispatch($job->id);

            $this->showSuccessAlert(__("Import queued successfully. You can track the progress below."));

            $this->reset();
            $this->showCreate = false;
        } catch (\Exception $error) {
            if (!empty($uploadedFile)) {
                \Storage::delete($uploadedFile);
            }

            logger("error", [$error]);
            $this->showErrorAlert($error->getMessage() ?? __("Data import failed!"));
        }
    }

    public function recentImportJobs()
    {
        return ImportExportJob::where('type', ImportExportJob::TYPE_IMPORT)
            ->latest()
            ->limit(10)
            ->get();
    }
}
