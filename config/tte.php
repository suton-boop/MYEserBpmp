<?php

return [
    'qr' => [
        'issuer' => env('TTE_QR_ISSUER', 'TTE-INTERNAL'),
        'jwt_secret' => env('TTE_QR_JWT_SECRET', env('APP_KEY')),
        'jwt_ttl_seconds' => (int) env('TTE_QR_JWT_TTL', 86400 * 365), // 1 tahun
        'verify_route_name' => 'public.verify.show',
    ],

    'security' => [
        'hash_algo' => 'sha256',
        'signature_algo' => OPENSSL_ALGO_SHA256,
        'signed_url_ttl_minutes' => (int) env('TTE_SIGNED_URL_TTL', 60 * 24), // 24 jam
        'verify_rate_limit' => env('TTE_VERIFY_RATE', '30,1'), // 30 req / menit
        'replay_nonce_ttl_seconds' => (int) env('TTE_REPLAY_NONCE_TTL', 300),
    ],

    // Internal TSA (Timestamp Signing Authority)
    'tsa' => [
        'enabled' => (bool) env('TTE_TSA_ENABLED', true),
        'signer_certificate_code' => env('TTE_TSA_CERT_CODE', 'TSA-001'),
    ],

    // PDF stamping (visible + metadata)
    'pdf' => [
        'storage_disk' => env('TTE_PDF_DISK', 'local'),
        'pdf_root' => env('TTE_PDF_ROOT', 'tte/pdf'),
        'stamp' => [
            'enabled' => (bool) env('TTE_PDF_STAMP_ENABLED', true),
            'signature_image_path' => env('TTE_SIGNATURE_IMAGE_PATH', storage_path('app/tte/signature/sign.png')),
        ],
    ],
];