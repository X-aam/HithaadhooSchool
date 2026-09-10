<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'light') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "light" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/images/logo.png" type="image/png">
        <link rel="apple-touch-icon" href="/images/logo.png">

        {{-- Thaana (Dhivehi) webfont — Noto Sans Thaana is the only Thaana
             family reliably hosted on Google Fonts. Classic local fonts
             (Faruma / MV Elaaf) are preferred first via CSS when installed. --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thaana:wght@400..800&display=swap" rel="stylesheet">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        @if (config('app.debug'))
            <div
                id="testing-banner"
                role="alert"
                style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; position: relative; z-index: 9999; padding: 0.5rem 2.5rem 0.5rem 1rem; background-color: #f59e0b; color: #1f2937; font-size: 0.875rem; font-weight: 600; text-align: center; line-height: 1.25rem;"
            >
                <span>⚠️ This site is for testing only.</span>
                <button
                    type="button"
                    aria-label="Dismiss banner"
                    onclick="(function(){var b=document.getElementById('testing-banner');if(b){b.remove();try{sessionStorage.setItem('testing-banner-dismissed','1');}catch(e){}}})()"
                    style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: transparent; border: 0; cursor: pointer; font-size: 1.25rem; line-height: 1; color: inherit; padding: 0 0.25rem;"
                >&times;</button>
            </div>
            <script>
                (function () {
                    try {
                        if (sessionStorage.getItem('testing-banner-dismissed') === '1') {
                            var b = document.getElementById('testing-banner');
                            if (b) b.remove();
                        }
                    } catch (e) {}
                })();
            </script>
        @endif

        <x-inertia::app />
    </body>
</html>
