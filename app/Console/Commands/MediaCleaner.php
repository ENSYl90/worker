<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\File;

class MediaCleaner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:media';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean broken media paths';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Scanning media models...");
        $totalMediaset = Media::count();
        $deletedMedia = 0;

        $this->output->progressStart($totalMediaset);
        Media::chunk(500, function ($mediaSet) use (&$deletedMedia) {
            foreach ($mediaSet as $media) {
                $path = storage_path("app/public/{$media->id}/{$media->file_name}");
                if (!file_exists($path)) {
                    try {
                        // verify with spatie's built-in getPath just to be certain
                        $spatiePath = $media->getPath();
                        if (!file_exists($spatiePath)) {
                            $media->delete();
                            $deletedMedia++;
                        }
                    } catch (\Exception $e) {
                        $media->delete();
                        $deletedMedia++;
                    }
                }
                $this->output->progressAdvance();
            }
        });
        $this->output->progressFinish();

        $this->info("Total media records checked: {$totalMediaset}");
        $this->info("Deleted broken media records: {$deletedMedia}");
        $this->newLine();

        $this->info("Scanning unused media directories...");
        $folderPath = storage_path("app/public");
        $folders = File::directories($folderPath);
        $deletedFolders = 0;

        $this->output->progressStart(count($folders));
        foreach ($folders as $folder) {
            $folderName = basename($folder);
            if (is_numeric($folderName)) {
                $mediaExists = Media::where('id', $folderName)->exists();
                if (!$mediaExists) {
                    File::deleteDirectory($folder);
                    $deletedFolders++;
                }
            }
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        $this->info("Total directories checked: " . count($folders));
        $this->info("Deleted unused directories: {$deletedFolders}");
        $this->newLine();

        $this->info("Media cleanup completed successfully!");

        return 0;
    }
}
