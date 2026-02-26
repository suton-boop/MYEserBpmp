<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Domain\Certificates\Repositories\CertificateRepositoryInterface;
use App\Domain\Certificates\Repositories\EloquentCertificateRepository;

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
    }
}