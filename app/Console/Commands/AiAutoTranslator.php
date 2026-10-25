<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAutoTranslator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:translate {folder?} {languageCode?} {--provider=openai : The AI provider to use (openai, gemini, claude)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto translate new lang strings using AI (OpenAI, Gemini, Claude)';

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
        try {
            $provider = $this->option('provider');
            $folder = $this->argument('folder');

            if (!in_array($provider, ['openai', 'gemini', 'claude'])) {
                $this->error("Invalid provider. Supported providers: openai, gemini, claude");
                return 1;
            }

            if (!empty($folder)) {
                $path = base_path() . "/resources/lang/$folder";
                if (!File::exists($path)) {
                    File::makeDirectory($path);
                }

                $fullLangFilePath = base_path() . "/website-lang-full.txt";
                $newLangFilePath = base_path() . "/website-lang.txt";
            } else {
                $path = base_path() . "/resources/lang";
                $fullLangFilePath = base_path() . "/lang-full.txt";
                $newLangFilePath = base_path() . "/lang.txt";
            }

            // Load new lang strings
            if (!File::exists($newLangFilePath)) {
                $this->info("No new language file found.");
                return 0;
            }

            $newLang = File::get($newLangFilePath);
            if (empty(trim($newLang))) {
                $this->info("No new strings to translate.");
                return 0;
            }

            $newLangArray = array_filter(explode("\n", $newLang), fn($value) => !empty(trim($value)));
            $newLangArray = array_values($newLangArray); // Reindex

            // Load language codes
            $codes = config('backend.languageCodes', []);
            $languageCode = $this->argument('languageCode');
            if (!empty($languageCode)) {
                $codes = [$languageCode];
            }

            if (empty($codes)) {
                $this->warn("No language codes found in config('backend.languageCodes')");
                // Fallback or exit? usage of config implies it exists.
                // If specific code provided, we use that.
            }

            foreach ($codes as $code) {
                if ($code == 'en') {
                    continue;
                }

                $this->info("Translating to [$code] using $provider...");

                // Chunk the array to avoid token limits - Gemini/OpenAI can handle much larger batches easily
                $chunks = array_chunk($newLangArray, 100); // Increased from 20 to 100 to reduce total requests
                $newTranslatedData = [];

                $bar = $this->output->createProgressBar(count($chunks));
                $bar->start();

                foreach ($chunks as $chunk) {
                    $translations = $this->translateBatch($chunk, $code, $provider);

                    if ($translations) {
                        foreach ($translations as $original => $translated) {
                            $newTranslatedData[$original] = $translated;
                        }
                    } else {
                        // Fallback: if batch fails, maybe log it or keep original?
                        // For now we just keep original to avoid crashing app
                        foreach ($chunk as $item) {
                            $newTranslatedData[$item] = $item;
                        }
                        $this->error("\nFailed to translate chunk for $code");
                    }
                    $bar->advance();
                    // Sleep to avoid rate limits - especially for free tiers (15 RPM limit)
                    sleep(10); 
                }
                $bar->finish();
                $this->newLine();

                // Merge and Save
                $langFilePath = $path . "/" . $code . ".json";
                if (!File::exists($langFilePath)) {
                    File::put($langFilePath, "{}");
                }

                $languageFile = File::get($langFilePath);
                $langJson = json_decode($languageFile, true) ?? [];

                // Merge: new translations overwrite old ones if collision, but here usually new strings are distinct
                $newFullLangJson = array_merge($newTranslatedData, $langJson);

                // Sort by key for cleaner file
                ksort($newFullLangJson);

                File::put($langFilePath, json_encode($newFullLangJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                $this->info("Done: $code");
                $this->info("----------------------");
            }

            // Append new strings to full lang file
            File::append($fullLangFilePath, "\n" . implode("\n", $newLangArray));

            // Clear the new lang file
            File::put($newLangFilePath, "");

            $this->info("All translations completed successfully.");

        } catch (\Exception $error) {
            $this->newLine();
            $this->error($error->getMessage());
            Log::error("AiAutoTranslator Error", ['exception' => $error]);
            return 1;
        }
        return 0;
    }

    protected function translateBatch($strings, $targetLangCode, $provider)
    {
        $prompt = "You are a professional translator and developer. Translate the following list of unique strings into the language with code '{$targetLangCode}'. 
        
        CRITICAL RULES:
        1. Return ONLY a valid JSON object where keys are the original English and values are the translation.
        2. Preserve all Laravel variables starting with a colon (e.g., :name, :count, :deleted_drivers). Do not translate them.
        3. Maintain the same tone and context (Super App, Logistics, Food Delivery).
        4. If a string is already in the target language or a proper noun, keep it as is.
        5. DO NOT include code blocks or explanations. Just raw JSON.

        Strings to translate:
        " . json_encode($strings, JSON_UNESCAPED_UNICODE);

        logger()->info("Translating batch with provider: $provider", ['prompt' => $prompt]);
        switch ($provider) {
            case 'openai':
                return $this->callOpenAI($prompt);
            case 'gemini':
                return $this->callGemini($prompt);
            case 'claude':
                return $this->callClaude($prompt);
            default:
                throw new \Exception("Unknown provider: $provider");
        }
    }

    protected function callOpenAI($prompt)
    {
        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            throw new \Exception("OPENAI_API_KEY is missing in .env");
        }

        $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4-turbo-preview', // Or gpt-3.5-turbo if cheaper
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant that outputs JSON.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3,
        ]);

        if ($response->failed()) {
            Log::error("OpenAI Error", $response->json());
            return null;
        }

        $content = $response->json('choices.0.message.content');
        return json_decode($content, true);
    }

    protected function callGemini($prompt, $retryCount = 0)
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            throw new \Exception("GEMINI_API_KEY is missing in .env");
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json'
            ]
        ]);

        if ($response->failed()) {
            $error = $response->json();
            $errorCode = $error['error']['code'] ?? null;

            // Handle Quota/Rate Limit with exponential backoff
            if ($errorCode == 429 && $retryCount < 5) {
                $waitTime = pow(2, $retryCount + 1) * 10; // 20s, 40s, 80s, 160s, 320s
                $this->warn("\nRate limit hit (429). Retrying ($retryCount/5) in {$waitTime}s...");
                sleep($waitTime);
                return $this->callGemini($prompt, $retryCount + 1);
            }

            Log::error("Gemini Error", $error);
            return null;
        }

        $content = $response->json('candidates.0.content.parts.0.text');
        $content = $this->cleanJsonString($content);

        return json_decode($content, true);
    }

    protected function callClaude($prompt)
    {
        $apiKey = env('ANTHROPIC_API_KEY');
        if (!$apiKey) {
            throw new \Exception("ANTHROPIC_API_KEY is missing in .env");
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => 4096,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ]);

        if ($response->failed()) {
            Log::error("Claude Error", $response->json());
            return null;
        }

        $content = $response->json('content.0.text');
        $content = $this->cleanJsonString($content);

        return json_decode($content, true);
    }

    protected function cleanJsonString($string)
    {
        // Remove markdown code blocks if present
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $string, $matches)) {
            return $matches[1];
        }
        if (preg_match('/```\s*([\s\S]*?)\s*```/', $string, $matches)) {
            return $matches[1];
        }
        return $string;
    }
}
