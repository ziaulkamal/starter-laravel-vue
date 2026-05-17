<?php

/*
|--------------------------------------------------------------------------
| Module Permission Registry — Sistem Baitul Mal Aceh Barat Daya
|--------------------------------------------------------------------------
| Format: 'nama-modul' => ['aksi1', 'aksi2', ...]
| Hasil: {modul}.{aksi} → contoh: users.view, mustahik.create
*/

return [
    // ── Administrasi Sistem (Super Admin) ────────────────────────────
    'users'       => ['view', 'create', 'edit', 'delete', 'toggle-active'],
    'gampongs'    => ['view', 'create', 'edit', 'delete'],
    'roles'       => ['view', 'edit'],
    'konfigurasi' => ['view', 'edit'],
    'audit-log'   => ['view'],
    'settings'    => ['menu', 'profile-menu'],

    // ── Mustahik (Admin Kabupaten + Admin Gampong) ───────────────────
    'mustahik'    => ['view', 'create', 'edit', 'delete', 'nonaktifkan'],
    'berkas'      => ['upload', 'download', 'delete'],

    // ── Pengajuan & Verifikasi ───────────────────────────────────────
    'pengajuan'   => ['view', 'create'],
    'verifikasi'  => ['view', 'approve', 'tolak', 'bulk-approve'],

    // ── Zakat ────────────────────────────────────────────────────────
    'zakat-fitrah' => ['view', 'create', 'delete', 'kunci'],
    'zakat-mal'    => ['view', 'create', 'delete'],
    'zakat-monitor'=> ['view', 'unlock'],

    // ── Muzakki ─────────────────────────────────────────────────────
    'muzakki'     => ['view', 'create', 'edit'],

    // ── Program & Penyaluran ─────────────────────────────────────────
    'program'     => ['view', 'create', 'edit', 'delete'],
    'penyaluran'  => ['view', 'create', 'bulk'],

    // ── Laporan ──────────────────────────────────────────────────────
    'laporan'     => ['view', 'export'],
];
