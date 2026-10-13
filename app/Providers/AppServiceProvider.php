<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

require_once app_path('helpers.php');

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $isLocalhost = in_array(request()->getHost(), ['127.0.0.1', 'localhost', '::1']);

        if (!$isLocalhost && (config('app.env') === 'production' || str_starts_with(config('app.url'), 'https://') || request()->header('x-forwarded-proto') === 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Perangkat eksternal (HP via tunnel Pinggy/LAN) tidak dapat mengakses dev server localhost:5173 di laptop.
        // Gunakan aset hasil compile (public/build/manifest.json) untuk request non-localhost.
        if (!$isLocalhost) {
            \Illuminate\Support\Facades\Vite::useHotFile(storage_path('vite.hot.disabled'));
        }

        // Jika menggunakan shared hosting yang membutuhkan prefix /public (hanya jika diaktifkan di .env)
        if (env('APP_SHARED_HOSTING', false)) {
            if (!str_contains(config('app.asset_url', ''), 'public')) {
                \Illuminate\Support\Facades\Vite::createAssetPathsUsing(function ($path) {
                    return asset('public/' . ltrim($path, '/'));
                });
            }
        }

        Blade::directive('formatDate', function ($expression) {
            return "<?php echo \\App\Models\CompanyProfile::formatDate($expression); ?>";
        });

        Blade::directive('formatDateTime', function ($expression) {
            return "<?php echo \\App\Models\CompanyProfile::formatDateTime($expression); ?>";
        });
    }
}
