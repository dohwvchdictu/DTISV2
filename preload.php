<?php

// Preload script for OPcache optimization in production
// This file preloads commonly used Laravel classes

if (php_sapi_name() === 'cli') {
    return;
}

opcache_compile_file(__DIR__ . '/vendor/autoload.php');

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';

// Preload core Laravel classes
$preloadClasses = [
    // Core Laravel
    Illuminate\Foundation\Application::class,
    Illuminate\Http\Request::class,
    Illuminate\Http\Response::class,
    Illuminate\Routing\Router::class,
    Illuminate\View\View::class,
    Illuminate\Database\Eloquent\Model::class,
    Illuminate\Support\Collection::class,
    Illuminate\Support\Facades\Facade::class,
    
    // Common facades
    Illuminate\Support\Facades\Route::class,
    Illuminate\Support\Facades\View::class,
    Illuminate\Support\Facades\DB::class,
    Illuminate\Support\Facades\Cache::class,
    Illuminate\Support\Facades\Session::class,
    
    // Livewire
    Livewire\Component::class,
    Livewire\Livewire::class,
    
    // Application models
    App\Models\User::class,
    App\Models\Document::class,
    App\Models\Category::class,
    App\Models\Bundle::class,
    App\Models\Action::class,
    App\Models\Log::class,
    App\Models\CitizenCharter::class,
];

foreach ($preloadClasses as $class) {
    if (class_exists($class)) {
        opcache_compile_file((new ReflectionClass($class))->getFileName());
    }
}

// Preload all Livewire components
$livewireComponents = glob(__DIR__ . '/app/Livewire/**/*.php');
foreach ($livewireComponents as $component) {
    opcache_compile_file($component);
}

// Preload middleware
$middlewareFiles = glob(__DIR__ . '/app/Http/Middleware/*.php');
foreach ($middlewareFiles as $middleware) {
    opcache_compile_file($middleware);
}

// Preload service providers
$providers = glob(__DIR__ . '/app/Providers/*.php');
foreach ($providers as $provider) {
    opcache_compile_file($provider);
}