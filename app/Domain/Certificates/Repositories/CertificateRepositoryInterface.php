<?php

namespace App\Domain\Certificates\Repositories;

use App\Models\Certificate;

interface CertificateRepositoryInterface
{
    public function findOrFail(string $id): Certificate;
    public function save(Certificate $certificate): Certificate;
}