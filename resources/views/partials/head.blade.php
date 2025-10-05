<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="{{ asset('assets/images/favicon.png') }}" sizes="any">
{{-- <link rel="icon" href="{{ assets('assets/images/favicon.png') }}" type="image/svg+xml"> --}}
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<link rel="stylesheet" href="{{ asset('assets/css/all.css') }}">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
