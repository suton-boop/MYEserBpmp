<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Domain\Certificates\Repositories\CertificateRepositoryInterface;
use App\Domain\Certificates\Repositories\EloquentCertificateRepository;

use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CertificateRepositoryInterface::class,
            EloquentCertificateRepository::class
        );
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}