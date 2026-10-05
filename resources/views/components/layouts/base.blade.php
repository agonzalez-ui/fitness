@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=gu-fitcr-1" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}?v=gu-fitcr-1" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=gu-fitcr-1">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body {{ $attributes->merge(['class' => 'h-full font-sans text-slate-900 antialiased']) }}>
    {{ $slot }}
    @livewireScripts
</body>
</html>
