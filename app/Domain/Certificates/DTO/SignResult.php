<?php

namespace App\Domain\Certificates\DTO;

class SignResult
{
    public function __construct(
        public string $documentHashHex,
        public string $signatureBase64,
        public string $publicToken,
        public bool $tsaEnabled,
        public ?string $tsaNonce,
        public ?string $tsaSignatureBase64,
        public ?\DateTimeInterface $tsaAt
    ) {}
}