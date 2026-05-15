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

    // Contoh modul baru — uncomment jika modul sudah dibuat:
    // 'employees' => ['view', 'create', 'edit', 'delete'],
    // 'reports'   => ['view', 'export'],
    // 'products'  => ['view', 'create', 'edit', 'delete'],
];
