<?php

namespace App\Domain\Certificates\Repositories;

use App\Domain\Certificates\Models\Certificate;

class EloquentCertificateRepository implements CertificateRepositoryInterface
{
    public function findOrFail(string $id): Certificate
    {
        return Certificate::query()->findOrFail($id);
    }

    public function save(Certificate $certificate): Certificate
    {
        $certificate->save();
        return $certificate->refresh();
    }
}