<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocsController extends Controller
{
    public function index()
    {
        //fail in production
        if (inProduction()) {
            abort(404);
        }


        $docs = $this->docs();

        if ($docs->isEmpty()) {
            return view('docs.index', [
                'docs' => $docs,
                'currentDoc' => null,
                'content' => '',
            ]);
        }

        return redirect()->route('docs.show', $docs->first()['slug']);
    }

    public function show(string $slug)
    {
        //fail in production
        if (inProduction()) {
            abort(404);
        }
        $docs = $this->docs();
        $currentDoc = $docs->firstWhere('slug', $slug);

        abort_if(empty($currentDoc), 404);

        $markdown = File::get($currentDoc['path']);
        [, $markdown] = $this->frontMatter($markdown);
        $content = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return view('docs.index', [
            'docs' => $docs,
            'currentDoc' => $currentDoc,
            'content' => $this->addHeadingAnchors($content),
        ]);
    }

    private function docs()
    {
        $docsPath = base_path('docs');

        if (!File::isDirectory($docsPath)) {
            return collect();
        }

        return collect(File::files($docsPath))
            ->filter(fn($file) => strtolower($file->getExtension()) === 'md')
            ->map(function ($file) {
                $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                [$meta] = $this->frontMatter(File::get($file->getPathname()));
                $group = $meta['group'] ?? 'General';

                return [
                    'slug' => $name,
                    'title' => $meta['title'] ?? Str::headline($name),
                    'group' => $group,
                    'group_slug' => Str::slug($group),
                    'order' => (int) ($meta['order'] ?? 999),
                    'path' => $file->getPathname(),
                ];
            })
            ->sortBy([
                ['group', 'asc'],
                ['order', 'asc'],
                ['title', 'asc'],
            ])
            ->values();
    }

    private function frontMatter(string $markdown): array
    {
        if (!str_starts_with($markdown, "---\n")) {
            return [[], $markdown];
        }

        $endPosition = strpos($markdown, "\n---\n", 4);

        if ($endPosition === false) {
            return [[], $markdown];
        }

        $frontMatter = substr($markdown, 4, $endPosition - 4);
        $content = ltrim(substr($markdown, $endPosition + 5));
        $meta = [];

        foreach (preg_split('/\r\n|\r|\n/', $frontMatter) as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($key !== '') {
                $meta[$key] = trim($value, "\"'");
            }
        }

        return [$meta, $content];
    }

    private function addHeadingAnchors(string $content): string
    {
        libxml_use_internal_errors(true);

        $document = new \DOMDocument();
        $document->loadHTML(
            '<!doctype html><html><body>' . mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8') . '</body></html>'
        );

        $usedIds = [];

        foreach (range(1, 6) as $level) {
            foreach ($document->getElementsByTagName('h' . $level) as $heading) {
                $baseId = Str::slug($heading->textContent);
                $baseId = $baseId === '' ? 'section' : $baseId;
                $id = $baseId;
                $counter = 2;

                while (in_array($id, $usedIds, true)) {
                    $id = $baseId . '-' . $counter;
                    $counter++;
                }

                $usedIds[] = $id;
                $heading->setAttribute('id', $id);

                $anchor = $document->createElement('a', '#');
                $anchor->setAttribute('href', '#' . $id);
                $anchor->setAttribute('class', 'docs-heading-anchor');
                $anchor->setAttribute('aria-label', 'Link to this section');
                $heading->appendChild($document->createTextNode(' '));
                $heading->appendChild($anchor);
            }
        }

        $body = $document->getElementsByTagName('body')->item(0);
        $html = '';

        foreach ($body->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        libxml_clear_errors();

        return $html;
    }
}
