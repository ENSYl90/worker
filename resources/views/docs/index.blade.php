@extends('layouts.guest')

@section('title', $currentDoc['title'] ?? 'Documentation')

@section('styles')
    <style>
        .docs-shell {
            min-height: 100vh;
            background: #f8fafc;
            color: #0f172a;
        }

        .docs-layout {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            min-height: 100vh;
        }

        .docs-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 24px;
        }

        .docs-brand {
            margin-bottom: 24px;
        }

        .docs-brand a {
            color: #0f172a;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
        }

        .docs-nav {
            display: grid;
            gap: 18px;
        }

        .docs-nav-group {
            display: grid;
            gap: 6px;
        }

        .docs-nav-title {
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            padding: 0 12px;
            text-transform: uppercase;
        }

        .docs-nav a {
            border-radius: 8px;
            color: #475569;
            display: block;
            font-size: 14px;
            line-height: 1.35;
            padding: 10px 12px;
            text-decoration: none;
        }

        .docs-nav a:hover,
        .docs-nav a.active {
            background: #e0f2fe;
            color: #075985;
        }

        .docs-main {
            min-width: 0;
            padding: 40px 24px;
        }

        .docs-content {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin: 0 auto;
            max-width: 980px;
            padding: 40px;
        }

        .docs-content h1,
        .docs-content h2,
        .docs-content h3,
        .docs-content h4 {
            color: #0f172a;
            font-weight: 700;
            line-height: 1.2;
            margin: 28px 0 14px;
            scroll-margin-top: 24px;
        }

        .docs-content h1 {
            font-size: 34px;
            margin-top: 0;
        }

        .docs-content h2 {
            border-top: 1px solid #e2e8f0;
            font-size: 24px;
            padding-top: 28px;
        }

        .docs-content h3 {
            font-size: 19px;
        }

        .docs-content p,
        .docs-content li {
            color: #334155;
            font-size: 15px;
            line-height: 1.75;
        }

        .docs-content ul,
        .docs-content ol {
            margin: 12px 0 18px 24px;
        }

        .docs-content a {
            color: #0369a1;
            text-decoration: underline;
        }

        .docs-heading-anchor {
            color: #94a3b8;
            font-size: 0.75em;
            opacity: 0;
            text-decoration: none;
        }

        .docs-content h1:hover .docs-heading-anchor,
        .docs-content h2:hover .docs-heading-anchor,
        .docs-content h3:hover .docs-heading-anchor,
        .docs-content h4:hover .docs-heading-anchor,
        .docs-content h5:hover .docs-heading-anchor,
        .docs-content h6:hover .docs-heading-anchor,
        .docs-heading-anchor:focus {
            opacity: 1;
        }

        .docs-content code {
            background: #f1f5f9;
            border-radius: 5px;
            color: #be123c;
            font-size: 13px;
            padding: 2px 5px;
        }

        .docs-content pre {
            background: #0f172a;
            border-radius: 8px;
            color: #e2e8f0;
            margin: 16px 0 24px;
            overflow-x: auto;
            padding: 18px;
        }

        .docs-code-block {
            position: relative;
        }

        .docs-code-block pre {
            padding-right: 86px;
        }

        .docs-copy-button {
            background: #1e293b;
            border: 1px solid #475569;
            border-radius: 6px;
            color: #e2e8f0;
            cursor: pointer;
            font-size: 12px;
            line-height: 1;
            padding: 8px 10px;
            position: absolute;
            right: 10px;
            top: 10px;
        }

        .docs-copy-button:hover,
        .docs-copy-button:focus {
            background: #334155;
            outline: none;
        }

        .docs-content pre code {
            background: transparent;
            color: inherit;
            padding: 0;
        }

        .docs-content table {
            border-collapse: collapse;
            display: block;
            margin: 16px 0 24px;
            overflow-x: auto;
            width: 100%;
        }

        .docs-content th,
        .docs-content td {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            text-align: left;
        }

        .docs-content th {
            background: #f1f5f9;
        }

        .docs-empty {
            color: #475569;
            text-align: center;
        }

        @media (max-width: 820px) {
            .docs-layout {
                grid-template-columns: 1fr;
            }

            .docs-sidebar {
                height: auto;
                position: static;
            }

            .docs-content {
                padding: 24px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="docs-shell">
        <div class="docs-layout">
            <aside class="docs-sidebar">
                <div class="docs-brand">
                    <a href="{{ route('docs.index') }}">API Documentation</a>
                </div>

                <nav class="docs-nav" aria-label="Documentation files">
                    @forelse ($docs->groupBy('group') as $group => $groupDocs)
                        <div class="docs-nav-group">
                            <div class="docs-nav-title">{{ $group }}</div>
                            @foreach ($groupDocs as $doc)
                                <a
                                    href="{{ route('docs.show', $doc['slug']) }}"
                                    class="{{ ($currentDoc['slug'] ?? '') === $doc['slug'] ? 'active' : '' }}"
                                >
                                    {{ $doc['title'] }}
                                </a>
                            @endforeach
                        </div>
                    @empty
                        <span>No markdown files found.</span>
                    @endforelse
                </nav>
            </aside>

            <main class="docs-main">
                <article class="docs-content">
                    @if ($currentDoc)
                        {!! $content !!}
                    @else
                        <p class="docs-empty">Add Markdown files to the docs folder to view them here.</p>
                    @endif
                </article>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.docs-content pre').forEach((pre) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'docs-code-block';
                pre.parentNode.insertBefore(wrapper, pre);
                wrapper.appendChild(pre);

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'docs-copy-button';
                button.textContent = 'Copy';
                button.setAttribute('aria-label', 'Copy code');
                wrapper.appendChild(button);

                button.addEventListener('click', async () => {
                    const code = pre.innerText;

                    try {
                        if (
                            typeof navigator !== 'undefined'
                            && navigator.clipboard
                            && typeof navigator.clipboard.writeText === 'function'
                        ) {
                            await navigator.clipboard.writeText(code);
                        } else {
                            const textarea = document.createElement('textarea');
                            textarea.value = code;
                            textarea.setAttribute('readonly', '');
                            textarea.style.position = 'fixed';
                            textarea.style.top = '-1000px';
                            document.body.appendChild(textarea);
                            textarea.focus();
                            textarea.select();
                            textarea.setSelectionRange(0, textarea.value.length);
                            if (!document.execCommand('copy')) {
                                throw new Error('Copy command failed');
                            }
                            textarea.remove();
                        }

                        button.textContent = 'Copied';
                        window.setTimeout(() => {
                            button.textContent = 'Copy';
                        }, 1600);
                    } catch (error) {
                        button.textContent = 'Failed';
                        window.setTimeout(() => {
                            button.textContent = 'Copy';
                        }, 1600);
                    }
                });
            });
        });
    </script>
@endpush
