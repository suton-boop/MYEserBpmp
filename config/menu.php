<?php

return [
    [
        'header' => 'E-Sertifikat',
    ],

    [
        'title' => 'Dashboard',
        'icon' => 'fa-solid fa-gauge',
        'route' => 'admin.dashboard',
        'active' => 'admin.dashboard',
        'permission' => 'dashboard-read',
    ],

    [
        'title' => 'Event',
        'icon' => 'fa-solid fa-calendar-days',
        'route' => 'admin.system.events.index',
        'active' => 'admin.system.events.*',
        'permission' => 'event-manage',
    ],

    [
        'title' => 'Peserta',
        'icon' => 'fa-solid fa-users',
        'route' => 'admin.participants.index',
        'active' => 'admin.participants.*',
        'permission' => 'participant-manage',
    ],

    [
        'title' => 'Template Sertifikat',
        'icon' => 'fa-solid fa-certificate',
        'route' => 'admin.system.templates.index',
        'active' => 'admin.system.templates.*',
        'permission' => 'template-manage',
    ],

    [
        'title' => 'Generate Sertifikat',
        'icon' => 'fa-solid fa-wand-magic-sparkles',
        'route' => 'admin.certificates.index',
        'active' => 'admin.certificates.*',
        'permission' => 'certificate-generate',
    ],

    [
        'title' => 'Sertifikat Terbit',
        'icon' => 'fa-solid fa-file-contract',
        'route' => 'admin.certificates.published',
        'active' => 'admin.certificates.published',
        'permission' => 'dashboard-read',
    ],

    [
        'title' => 'Distribusi Email',
        'icon' => 'fa-solid fa-envelope',
        'route' => 'admin.emails.index',
        'active' => 'admin.emails.*',
        'permission' => 'certificate-send',
    ],

    [
        'title' => 'Laporan',
        'icon' => 'fa-solid fa-chart-line',
        'route' => 'admin.reports',
        'active' => 'admin.reports',
        'permission' => 'report-read',
    ],

    [
        'title' => 'Statistik Pengunjung',
        'icon' => 'fa-solid fa-users-viewfinder',
        'route' => 'admin.visitors.index',
        'active' => 'admin.visitors.index',
        'permission' => 'report-read',
    ],

    [
        'title' => 'Profil Pengguna',
        'icon' => 'fa-solid fa-user-pen',
        'route' => 'admin.profile.edit',
        'active' => 'admin.profile.*',
    ],

    [
        'header' => 'Manajemen Sistem',
        'permissionAny' => [
            'certificate-approve',
            'tte-manage',
            'monitoring-read',
            'audit-read',
            'user-manage',
            'role-manage',
            'permission-manage',
        ],
    ],

    [
        'title' => 'Persetujuan Sertifikat',
        'icon' => 'fa-solid fa-circle-check',
        'route' => 'admin.system.approvals.index',
        'active' => 'admin.system.approvals.*',
        'permission' => 'certificate-approve',
    ],

    [
        'title' => 'TTE (Dashboard)',
        'icon' => 'fa-solid fa-pen-nib',
        'route' => 'admin.tte.index',
        'active' => 'admin.tte.index',
        'permission' => 'tte-manage',
    ],

    [
        'title' => 'TTE - Signing Queue',
        'icon' => 'fa-solid fa-signature',
        'route' => 'admin.tte.signing.index',
        'active' => 'admin.tte.signing.*',
        'permission' => 'tte-manage',
    ],

    [
        'title' => 'TTE - Signer Certificates',
        'icon' => 'fa-solid fa-key',
        'route' => 'admin.tte.signers.index',
        'active' => 'admin.tte.signers.*',
        'permission' => 'tte-manage',
    ],

    [
        'title' => 'Monitoring',
        'icon' => 'fa-solid fa-chart-column',
        'route' => 'admin.monitoring.index',
        'active' => 'admin.monitoring.*',
        'permission' => 'monitoring-read',
    ],

    [
        'title' => 'Audit Trail',
        'icon' => 'fa-solid fa-clipboard-list',
        'route' => 'admin.audit.index',
        'active' => 'admin.audit.*',
        'permission' => 'audit-read',
    ],

    [
        'title' => 'Users',
        'icon' => 'fa-solid fa-user-gear',
        'route' => 'admin.system.users.index',
        'active' => 'admin.system.users.*',
        'permission' => 'user-manage',
    ],

    [
        'title' => 'Roles',
        'icon' => 'fa-solid fa-shield-halved',
        'route' => 'admin.system.roles.index',
        'active' => 'admin.system.roles.*',
        'permission' => 'role-manage',
    ],

    [
        'title' => 'Pengaturan Umum',
        'icon' => 'fa-solid fa-cogs',
        'route' => 'admin.system.settings.index',
        'active' => 'admin.system.settings.*',
        'permission' => 'role-manage', // we restrict strictly via controller anyway, but use role-manage as proxy
    ],

    [
        'title' => 'Permissions',
        'icon' => 'fa-solid fa-lock',
        'route' => 'admin.system.permissions.index',
        'active' => 'admin.system.permissions.*',
        'permission' => 'permission-manage',
    ],
];