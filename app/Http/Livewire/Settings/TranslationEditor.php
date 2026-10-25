<?php

namespace App\Http\Livewire\Settings;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class TranslationEditor extends BaseSettingsComponent
{
    public $languages = [];
    public $selectedLanguage = 'en';
    public $translations = [];
    public $filteredTranslations = [];
    public $searchTerm = '';
    public $editingKey = null;
    public $editingValue = '';
    public $newKey = '';
    public $newValue = '';
    public $showAddForm = false;

    // New language creation
    public $showCreateLanguageForm = false;
    public $newLanguageName = '';
    public $newLanguageCode = '';
    public $sourceLanguage = 'en';

    protected $listeners = ['refreshTranslations' => '$refresh'];

    public function mount()
    {
        // Check if user has permission
        if (!canI('edit-system-translations')) {
            abort(403, __('You do not have permission to edit system translations.'));
        }

        // Check if in production environment
        if (!app()->environment('production')) {
            // Will show warning in view, but allow access for testing
        }

        $this->loadAvailableLanguages();
        $this->loadTranslations();
    }

    public function getCanEditProperty()
    {
        return canI('edit-system-translations') && app()->environment('production');
    }

    public function getIsProductionProperty()
    {
        return app()->environment('production');
    }

    public function loadAvailableLanguages()
    {
        $langPath = resource_path('lang');
        $files = File::files($langPath);

        // Get language names and codes from backend config
        $languageNames = config('backend.languages', []);
        $languageCodes = config('backend.languageCodes', []);

        // Create a mapping of code to name
        $languageMap = [];
        foreach ($languageCodes as $index => $code) {
            $languageMap[$code] = $languageNames[$index] ?? strtoupper($code);
        }

        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $locale = $file->getBasename('.json');

                // Only add if the language exists in backend config
                if (isset($languageMap[$locale])) {
                    $this->languages[] = [
                        'code' => $locale,
                        'name' => $this->getLanguageName($locale, $languageMap)
                    ];
                }
            }
        }

        // Sort by code
        usort($this->languages, function($a, $b) {
            return strcmp($a['code'], $b['code']);
        });
    }

    public function getLanguageName($code, $languageMap = null)
    {
        // Use provided map or load from config
        if ($languageMap === null) {
            $languageNames = config('backend.languages', []);
            $languageCodes = config('backend.languageCodes', []);

            $languageMap = [];
            foreach ($languageCodes as $index => $langCode) {
                $languageMap[$langCode] = $languageNames[$index] ?? strtoupper($langCode);
            }
        }

        // Native language names for better display
        $nativeNames = [
            'en' => 'English',
            'ar' => 'العربية',
            'de' => 'Deutsch',
            'es' => 'Español',
            'fr' => 'Français',
            'hi' => 'हिन्दी',
            'ja' => '日本語',
            'ko' => '한국어',
            'my' => 'မြန်မာ',
            'pt' => 'Português',
            'ru' => 'Русский',
            'tr' => 'Türkçe',
            'zh' => '中文',
        ];

        $englishName = $languageMap[$code] ?? strtoupper($code);
        $nativeName = $nativeNames[$code] ?? '';

        // Return format: "English (English)" or "Arabic (العربية)"
        if ($nativeName && $nativeName !== $englishName) {
            return "$englishName ($nativeName)";
        }

        return $englishName;
    }

    public function loadTranslations()
    {
        $filePath = resource_path("lang/{$this->selectedLanguage}.json");

        if (File::exists($filePath)) {
            $content = File::get($filePath);
            $this->translations = json_decode($content, true) ?? [];
        } else {
            $this->translations = [];
        }

        $this->filterTranslations();
    }

    public function updatedSelectedLanguage()
    {
        $this->loadTranslations();
        $this->searchTerm = '';
        $this->editingKey = null;
    }

    public function updatedSearchTerm()
    {
        $this->filterTranslations();
    }

    public function filterTranslations()
    {
        if (empty($this->searchTerm)) {
            $this->filteredTranslations = $this->translations;
        } else {
            $search = strtolower($this->searchTerm);
            $this->filteredTranslations = array_filter($this->translations, function($value, $key) use ($search) {
                return stripos($key, $search) !== false || stripos($value, $search) !== false;
            }, ARRAY_FILTER_USE_BOTH);
        }
    }

    public function startEdit($key)
    {
        $this->editingKey = $key;
        $this->editingValue = $this->translations[$key] ?? '';
    }

    public function cancelEdit()
    {
        $this->editingKey = null;
        $this->editingValue = '';
    }

    public function saveEdit()
    {
        if ($this->editingKey === null) {
            return;
        }

        $this->translations[$this->editingKey] = $this->editingValue;
        $this->saveTranslationsToFile();

        $this->editingKey = null;
        $this->editingValue = '';
        $this->filterTranslations();

        $this->showSuccessAlert(__('Translation updated successfully!'));
    }

    public function toggleAddForm()
    {
        $this->showAddForm = !$this->showAddForm;
        $this->newKey = '';
        $this->newValue = '';
    }

    public function addNewTranslation()
    {
        $this->validate([
            'newKey' => 'required|string',
            'newValue' => 'required|string',
        ]);

        if (isset($this->translations[$this->newKey])) {
            $this->showErrorAlert('Translation key already exists!');
            return;
        }

        $this->translations[$this->newKey] = $this->newValue;
        $this->saveTranslationsToFile();

        $this->newKey = '';
        $this->newValue = '';
        $this->showAddForm = false;
        $this->filterTranslations();

        $this->showSuccessAlert(__('Translation added successfully!'));
    }

    public function deleteTranslation($key)
    {
        if (isset($this->translations[$key])) {
            unset($this->translations[$key]);
            $this->saveTranslationsToFile();
            $this->filterTranslations();

            $this->showSuccessAlert(__('Translation deleted successfully!'));
        }
    }

    public function saveTranslationsToFile()
    {
        try {
            $filePath = resource_path("lang/{$this->selectedLanguage}.json");

            // Create backup before modifying
            $backupPath = resource_path("lang/backups");
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            $backupFile = $backupPath . "/{$this->selectedLanguage}_" . date('Y-m-d_H-i-s') . '.json';
            if (File::exists($filePath)) {
                File::copy($filePath, $backupFile);
            }

            // Sort translations alphabetically by key
            ksort($this->translations);

            // Save with pretty print
            $json = json_encode($this->translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            File::put($filePath, $json);

            // Clear translation cache
            Cache::forget('translations');
            \Artisan::call('cache:clear');

            // Clean old backups (keep only last 10)
            $this->cleanOldBackups($backupPath, $this->selectedLanguage);

            return true;
        } catch (\Exception $e) {
            $this->showErrorAlert('Error saving translations: ' . $e->getMessage());
            return false;
        }
    }

    private function cleanOldBackups($backupPath, $language)
    {
        $backups = File::glob($backupPath . "/{$language}_*.json");

        if (count($backups) > 10) {
            // Sort by modification time
            usort($backups, function($a, $b) {
                return File::lastModified($a) - File::lastModified($b);
            });

            // Delete oldest backups
            $toDelete = array_slice($backups, 0, count($backups) - 10);
            foreach ($toDelete as $file) {
                File::delete($file);
            }
        }
    }

    public function exportTranslations()
    {
        $fileName = "{$this->selectedLanguage}_translations_" . date('Y-m-d_H-i-s') . '.json';
        $json = json_encode($this->translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response()->streamDownload(function() use ($json) {
            echo $json;
        }, $fileName);
    }

    public function importTranslations($fileContent)
    {
        try {
            $imported = json_decode($fileContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->showErrorAlert('Invalid JSON file!');
                return;
            }

            $this->translations = array_merge($this->translations, $imported);
            $this->saveTranslationsToFile();
            $this->filterTranslations();

            $this->showSuccessAlert(__('Translations imported successfully!'));
        } catch (\Exception $e) {
            $this->showErrorAlert('Error importing translations: ' . $e->getMessage());
        }
    }

    public function getMissingTranslationsCount()
    {
        $enFilePath = resource_path('lang/en.json');
        if (!File::exists($enFilePath)) {
            return 0;
        }

        $enTranslations = json_decode(File::get($enFilePath), true) ?? [];
        $missing = array_diff_key($enTranslations, $this->translations);

        return count($missing);
    }

    public function copyFromEnglish()
    {
        $enFilePath = resource_path('lang/en.json');
        if (!File::exists($enFilePath)) {
            $this->showErrorAlert('English translation file not found!');
            return;
        }

        $enTranslations = json_decode(File::get($enFilePath), true) ?? [];
        $missing = array_diff_key($enTranslations, $this->translations);

        if (empty($missing)) {
            $this->showInfoAlert('No missing translations found!');
            return;
        }

        $this->translations = array_merge($this->translations, $missing);
        $this->saveTranslationsToFile();
        $this->filterTranslations();

        $this->showSuccessAlert(count($missing) . ' translations copied from English!');
    }

    public function toggleCreateLanguageForm()
    {
        $this->showCreateLanguageForm = !$this->showCreateLanguageForm;
        $this->newLanguageName = '';
        $this->newLanguageCode = '';
        $this->sourceLanguage = 'en';
    }

    public function createNewLanguage()
    {
        // Validate inputs
        $this->validate([
            'newLanguageName' => 'required|string|min:2',
            'newLanguageCode' => 'required|string|min:2|max:5|alpha',
            'sourceLanguage' => 'required|string',
        ]);

        // Convert language code to lowercase
        $this->newLanguageCode = strtolower($this->newLanguageCode);

        // Check if language already exists
        $newFilePath = resource_path("lang/{$this->newLanguageCode}.json");
        if (File::exists($newFilePath)) {
            $this->showErrorAlert("Language with code '{$this->newLanguageCode}' already exists!");
            return;
        }

        // Check if source language exists
        $sourceFilePath = resource_path("lang/{$this->sourceLanguage}.json");
        if (!File::exists($sourceFilePath)) {
            $this->showErrorAlert("Source language file not found!");
            return;
        }

        try {
            // Copy source language file to new language file
            $sourceContent = File::get($sourceFilePath);
            File::put($newFilePath, $sourceContent);

            // Update backend config
            $this->updateBackendConfig();

            // Reload languages
            $this->loadAvailableLanguages();

            // Switch to new language
            $this->selectedLanguage = $this->newLanguageCode;
            $this->loadTranslations();

            // Close form and reset
            $this->showCreateLanguageForm = false;
            $this->newLanguageName = '';
            $this->newLanguageCode = '';
            $this->sourceLanguage = 'en';

            $this->showSuccessAlert("New language '{$this->newLanguageName}' created successfully!");

        } catch (\Exception $e) {
            $this->showErrorAlert('Error creating new language: ' . $e->getMessage());
        }
    }

    private function updateBackendConfig()
    {
        $configPath = config_path('backend.php');

        if (!File::exists($configPath)) {
            throw new \Exception('Backend config file not found!');
        }

        // Read current config
        $configContent = File::get($configPath);

        // Parse the languages array
        preg_match('/"languages"\s*=>\s*\[(.*?)\]/s', $configContent, $languagesMatch);
        preg_match('/"languageCodes"\s*=>\s*\[(.*?)\]/s', $configContent, $codesMatch);

        if (empty($languagesMatch) || empty($codesMatch)) {
            throw new \Exception('Could not parse backend config file!');
        }

        // Add new language to languages array
        $languagesArray = $languagesMatch[1];
        $newLanguagesArray = rtrim($languagesArray, ",\n\r\t ");
        $newLanguagesArray .= ",\n        \"{$this->newLanguageName}\"";

        // Add new language code to languageCodes array
        $codesArray = $codesMatch[1];
        $newCodesArray = rtrim($codesArray, ",\n\r\t ");
        $newCodesArray .= ",\n        \"{$this->newLanguageCode}\"";

        // Replace in config content
        $newConfigContent = preg_replace(
            '/"languages"\s*=>\s*\[(.*?)\]/s',
            '"languages" => [' . $newLanguagesArray . "\n    ]",
            $configContent,
            1
        );

        $newConfigContent = preg_replace(
            '/"languageCodes"\s*=>\s*\[(.*?)\]/s',
            '"languageCodes" => [' . $newCodesArray . "\n    ]",
            $newConfigContent,
            1
        );

        // Backup current config
        $backupPath = config_path('backend_backup_' . date('Y-m-d_H-i-s') . '.php');
        File::copy($configPath, $backupPath);

        // Write new config
        File::put($configPath, $newConfigContent);

        // Clear config cache
        \Artisan::call('config:clear');
    }

    public function render()
    {
        return view('livewire.settings.translation-editor')
            ->layout('layouts.app', ['title' => __('Translation Editor')]);
    }
}
