<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="ltr"
      data-bs-theme="light"
      data-color-theme="Blue_Theme"
      data-layout="vertical">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title inertia>{{ config('app.name') }}</title>
        <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
        <link rel="icon" type="image/png" href="/favicon.png" />

        {{-- Google Fonts: preconnect agar browser buka koneksi lebih awal,
             &display=swap mencegah invisible text selama font belum termuat --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap"
              media="print" onload="this.media='all'">
        <noscript>
            <link rel="stylesheet"
                  href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap">
        </noscript>

        @vite('resources/js/app.ts')
        @inertiaHead
    </head>
    <body data-sidebartype="full">
        @inertia
    </body>
</html>
