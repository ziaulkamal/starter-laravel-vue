# Starter Laravel + Vue + Inertia

Starter kit full-stack berbasis **Laravel 13**, **Vue 3**, dan **Inertia.js** dengan tema admin Bootstrap 5 (Modernize). Dirancang sebagai fondasi yang scalable untuk aplikasi web modern dengan dukungan TypeScript bertahap.

---

## Tech Stack

### Backend

| Library | Versi | Fungsi |
|---|---|---|
| PHP | `^8.3` | Runtime bahasa server |
| Laravel | `^13.8` | Framework PHP — routing, ORM, middleware, auth |
| laravel/tinker | `^3.0` | REPL interaktif untuk Artisan |
| Inertia.js (server) | via Composer | Jembatan Laravel ↔ Vue, menggantikan kebutuhan REST API terpisah |

### Frontend

| Library | Versi | Fungsi |
|---|---|---|
| Vue | `^3.5` | Framework UI reaktif — Composition API |
| @inertiajs/vue3 | `^2.0` | Inertia adapter untuk Vue 3 — navigasi SPA tanpa REST API |
| Bootstrap | `^5.3.8` | CSS framework utama — grid, komponen, utility class |
| @popperjs/core | `^2.11.8` | Peer dependency Bootstrap — positioning dropdown & tooltip |
| SimpleBar | `^6.3.3` | Custom scrollbar untuk sidebar |

### Build & Tooling

| Library | Versi | Fungsi |
|---|---|---|
| Vite | `^8.0.0` | Build tool dan dev server dengan HMR |
| laravel-vite-plugin | `^3.1` | Integrasi Vite ↔ Laravel (hot reload, asset manifest) |
| @vitejs/plugin-vue | `^6.0` | Kompilasi Vue SFC di Vite |
| Sass | `^1.99.0` | Preprocessor SCSS |
| TypeScript | `^6.0.3` | Tipe statis — konfigurasi gradual migration dari JS |
| vue-tsc | `^3.2.9` | Type checker khusus Vue SFC (`npm run type-check`) |
| @types/node | `^25.7.0` | Tipe Node.js untuk tooling dan `vite.config.js` |

### Asset & Icon

| Asset | Keterangan |
|---|---|
| Tabler Icons | Font icon berbasis SVG — dipanggil via class `ti ti-*` |
| Modernize Theme | Tema Bootstrap 5 admin — SCSS-nya diintegrasikan ke `resources/scss/` |

---

## Persyaratan

- PHP `>= 8.3` dengan ekstensi: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`
- Composer `>= 2.x`
- Node.js `>= 20.x`
- npm `>= 10.x`

---

## Instalasi

```bash
# 1. Clone repository
git clone <repo-url>
cd starter-laravel-vue

# 2. Install dependency PHP
composer install

# 3. Buat file environment & generate key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env, lalu jalankan migrasi
php artisan migrate

# 5. Install dependency Node
npm install

# 6. Build assets
npm run build
```

---

## Perintah

### Frontend (npm)

```bash
npm run dev          # Dev server Vite dengan hot module replacement
npm run build        # Build production ke public/build/
npm run type-check   # Cek tipe TypeScript via vue-tsc (tanpa emit file)
```

### Backend (Artisan)

```bash
php artisan serve    # Jalankan dev server Laravel
php artisan migrate  # Jalankan migrasi database
php artisan tinker   # REPL interaktif
```

### Shortcut (Composer)

```bash
# Jalankan semua sekaligus: Laravel server + queue + log + Vite dev server
composer dev
```

---

## Struktur Direktori

```
resources/
├── fonts/
│   └── tabler-icons/              # Font icon Tabler (woff2, ttf, svg)
├── images/                        # Semua aset gambar (diimport via alias @images)
│   ├── logos/                     # Logo dark & light (SVG)
│   ├── profile/                   # Foto profil contoh
│   ├── products/                  # Gambar produk contoh
│   └── svgs/                      # Icon SVG tambahan
├── js/
│   ├── app.ts                     # Entry point — inisialisasi Inertia + Vue
│   ├── env.d.ts                   # Deklarasi tipe global (Vue shim, vite/client)
│   ├── Composables/               # Logic reusable tanpa template
│   │   ├── useSidebar.ts          # Toggle sidebar & resize handler
│   │   └── useTheme.ts            # Dark/light mode toggle
│   ├── Components/
│   │   ├── Header/
│   │   │   ├── AppHeader.vue          # Orchestrator header
│   │   │   ├── HeaderNotifications.vue
│   │   │   ├── HeaderSearch.vue
│   │   │   ├── HeaderThemeToggle.vue
│   │   │   └── HeaderUserMenu.vue
│   │   └── Sidebar/
│   │       ├── AppSidebar.vue         # Orchestrator sidebar
│   │       ├── SidebarLogo.vue
│   │       ├── SidebarNav.vue
│   │       └── SidebarProfile.vue
│   ├── Layouts/
│   │   └── AppLayout.vue          # Layout utama — sidebar + header + slot konten
│   └── Pages/                     # Satu file per halaman (Inertia page component)
│       └── Home.vue
└── scss/
    ├── app.scss                   # Entry SCSS — urutan import sangat penting
    ├── variables/                 # Variabel tema (warna, spacing, breakpoint)
    ├── layouts/                   # Gaya sidebar, header, dan struktur halaman
    ├── components/                # Gaya tombol, card, dropdown
    ├── utilities/                 # Override Bootstrap, icon size, background
    └── vendors/                   # Gaya library pihak ketiga (SimpleBar)
```

---

## Arsitektur Frontend

### Alur Rendering

```
Laravel Route
  → Inertia::render('NamaHalaman')
    → app.ts (resolvePageComponent)
      → AppLayout.vue (layout wrapper)
        → Pages/NamaHalaman.vue (konten halaman)
```

### Pola Komponen

- **`Layouts/`** — menerima `<slot>` konten halaman, menyediakan wrapper sidebar + header
- **`Components/Header/`** dan **`Components/Sidebar/`** — komponen presentasional, props-driven, tidak ada side effect
- **`Composables/`** — logic murni (tidak ada template), bisa dipakai ulang di komponen mana pun

### Menambah Halaman Baru

```bash
# 1. Buat file di resources/js/Pages/
# Contoh: resources/js/Pages/Settings.vue

# 2. Tambah route di routes/web.php
Route::get('/settings', fn() => Inertia::render('Settings'));
```

### Menambah Menu Sidebar

Gunakan `slot name="sidebar-menu"` dari dalam halaman:

```vue
<AppLayout>
    <template #sidebar-menu>
        <li class="sidebar-item" :class="{ active: $page.url === '/settings' }">
            <Link class="sidebar-link" href="/settings">
                <span><i class="ti ti-settings"></i></span>
                <span class="hide-menu">Settings</span>
            </Link>
        </li>
    </template>

    <!-- Konten halaman -->
    <div class="row">...</div>
</AppLayout>
```

---

## Arsitektur SCSS

`resources/scss/app.scss` adalah satu-satunya entry point. **Urutan import kritis** — variabel kustom harus masuk sebelum Bootstrap agar override bekerja:

```
1. Variabel kustom tema    → override $primary, $gray-*, breakpoint, dll. sebelum Bootstrap
2. Bootstrap               → dikompilasi menggunakan variabel yang sudah di-override
3. Layouts                 → sidebar, header (mengacu pada variabel Bootstrap)
4. Components & Utilities  → layer styling di atas Bootstrap
5. Vendors                 → SimpleBar dan library lain
```

### Path Alias

| Alias | Target | Contoh |
|---|---|---|
| `@` | `resources/js/` | `import AppLayout from '@/Layouts/AppLayout.vue'` |
| `@images` | `resources/images/` | `import logo from '@images/logos/dark-logo.svg'` |

---

## TypeScript

Setup ini menggunakan **migrasi bertahap** — file `.vue` lama tetap berjalan, file baru ditulis dalam TypeScript.

### Konfigurasi tsconfig.json

| Setting | Nilai | Keterangan |
|---|---|---|
| `strict` | `true` | File `.ts` baru di-check secara ketat |
| `allowJs` | `true` | File `.js` dan `.vue` lama tetap valid |
| `checkJs` | `false` | File `.js` tidak dipaksa bertipe, migrasi opsional |
| `moduleResolution` | `bundler` | Sesuai Vite — tidak butuh ekstensi eksplisit saat import |
| `noEmit` | `true` | Vite yang handle output; `tsc` hanya untuk type check |

### Urutan Migrasi yang Disarankan

1. **`Composables/`** — murni logika, tidak ada template, paling mudah dideklarasikan tipenya
2. **`Components/Header/` dan `Components/Sidebar/`** — komponen kecil, props-nya terbatas
3. **`Layouts/AppLayout.vue`** — setelah pola tipe composable sudah terbentuk
4. **`Pages/`** — terakhir, tipe biasanya bergantung pada shared types dari composables

### Contoh Composable Bertipe

```typescript
// resources/js/Composables/useSidebar.ts
import { onMounted, onBeforeUnmount } from 'vue'

const XL_BREAKPOINT = 1300

export function useSidebar() {
    function isMobile(): boolean {
        return window.innerWidth < XL_BREAKPOINT
    }

    function toggleSidebar(): void {
        if (isMobile()) {
            document.getElementById('main-wrapper')?.classList.toggle('show-sidebar')
        } else {
            const current = document.body.getAttribute('data-sidebartype')
            document.body.setAttribute('data-sidebartype', current === 'full' ? 'mini-sidebar' : 'full')
        }
    }

    // ...
    return { toggleSidebar, closeMobileSidebar }
}
```

---

## Catatan Penting

### Bootstrap 5 + Sass Deprecation Warning

Bootstrap 5 menggunakan sintaks Sass lama (`@import`, `red()`, `if()`, dll.) yang deprecated di Dart Sass 2.x. Warning ini **sudah di-silence** via konfigurasi `vite.config.js`:

```js
css: {
    preprocessorOptions: {
        scss: {
            quietDeps: true,  // silence semua warning dari node_modules
            silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
        },
    },
},
```

Ini adalah pendekatan resmi yang direkomendasikan tim Bootstrap. Akan diperbaiki secara permanen saat Bootstrap 6 rilis dengan dukungan penuh `@use`/`@forward`.

### Dark Mode

Dark mode dikendalikan via atribut HTML di `<html>`:

```js
document.documentElement.setAttribute('data-bs-theme', 'dark')  // aktifkan dark mode
document.documentElement.setAttribute('data-bs-theme', 'light') // kembali light mode
```

Logo switching (dark ↔ light) ditangani murni oleh CSS tanpa state Vue:

```scss
.dark-logo  { display: inline-block; }
.light-logo { display: none; }

[data-bs-theme="dark"] {
    .dark-logo  { display: none; }
    .light-logo { display: inline-block; }
}
```

### Responsive Sidebar

| Kondisi | Mekanisme | Class/Atribut |
|---|---|---|
| Desktop `>= 1300px` | Collapse/expand sidebar | `data-sidebartype="full"` atau `"mini-sidebar"` pada `<body>` |
| Mobile `< 1300px` | Show/hide sidebar sebagai overlay | `.show-sidebar` pada `#main-wrapper` |

Breakpoint 1300px sesuai dengan `xl` di `$grid-breakpoints` tema Modernize.
