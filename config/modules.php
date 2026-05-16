<?php

/*
|--------------------------------------------------------------------------
| Module Permission Registry
|--------------------------------------------------------------------------
| Daftarkan setiap modul beserta aksi yang tersedia.
| Seeder akan membaca file ini dan membuat permission otomatis.
|
| Format:
|   'nama-modul' => ['aksi1', 'aksi2', ...]
|
| Permission yang dihasilkan: {modul}.{aksi}
| Contoh: 'users' => ['view','create'] → users.view, users.create
|
*/

return [
    'users'    => ['view', 'create', 'edit', 'delete'],
    'settings' => ['menu', 'profile-menu'],

    // ── MTQ ──────────────────────────────────────────────────────────
    'peserta'   => ['view', 'create', 'edit', 'delete', 'verify'],
    'sesi'      => ['view', 'create', 'edit', 'delete', 'publish', 'close'],
    'nilai'     => ['view', 'input', 'unlock'],
    'laporan'   => ['view', 'export'],
    'sanggahan' => ['view', 'create', 'respond'],
    'audit-log' => ['view', 'export'],
    'dashboard' => ['view'],
    'berita'    => ['view', 'create', 'edit', 'delete'],
    'galeri'    => ['view', 'create', 'edit', 'delete'],
];
