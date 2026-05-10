{{--
    MATCHGO - Landing Page
    Platform matchmaking futsal — temukan lawan bermain, tentukan lapangan, dan bagi biaya secara transparan.

    Usage:
    This is the main landing page view. Include it in your routes:
        Route::get('/', function () { return view('welcome'); });

    Components used:
        <x-button />              → resources/views/components/button.blade.php
        <x-card />                → resources/views/components/card.blade.php
        <x-doodle-underline />    → resources/views/components/doodle-underline.blade.php
        <x-doodle-underline-wide /> → resources/views/components/doodle-underline-wide.blade.php
--}}

@extends('layouts.app')
@section('hide-layout-nav', true)

@section('content')

    {{-- Navbar --}}
    @include('sections.navbar')

    {{-- Hero Section --}}
    @include('sections.hero')

    {{-- Features Section --}}
    @include('sections.features')

    {{-- How It Works Section --}}
    @include('sections.how-it-works')

    {{-- Content Section (Two-Column) --}}
    @include('sections.content')

    {{-- Call-to-Action Section --}}
    @include('sections.cta')

    {{-- Footer --}}
    @include('sections.footer')

@endsection