<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Judul dipakai browser sebagai saran nama berkas saat "Unduh PDF" (Save as PDF). --}}
    <title>{{ str_replace('/', '-', (string) ($application->permit_number ?? $application->number)) }}</title>
    <link href="{{ asset('css/sik.css') }}?v={{ @filemtime(public_path('css/sik.css')) ?: '1' }}" rel="stylesheet">
    <style>
        /* Pratinjau = lembar A4 apa adanya (210mm, padding 14/16mm) supaya identik dengan PDF. */
        html, body { margin: 0; padding: 0; background: #fff; }
        body { display: flex; justify-content: center; }
        /* max-width dilepas supaya lembar tetap A4 (210mm) dan diskalakan utuh,
           bukan menyempit & membungkus ulang teks di layar sempit. */
        /* flex: 0 0 auto -> lembar tidak menyusut mengikuti lebar iframe (dulu bisa
           mengecil & membungkus ulang teks); penyusutan ditangani skrip skala di bawah. */
        .sik-sheet { margin: 0; border: 0; border-radius: 0; box-shadow: none; max-width: none; flex: 0 0 auto; }
        /* Lembar 210mm diskalakan agar utuh di modal sempit (mobile), tanpa mengubah
           tata letaknya sendiri. */
        body.sik-embed-scaled { display: block; }
        body.sik-embed-scaled .sik-sheet { transform-origin: top left; }
    </style>
    <script>
        /* Skala dihitung setelah stylesheet & font selesai dimuat, kalau tidak lebar
           lembar masih salah (logo/teks bisa tampak membesar). Dihitung ulang saat resize. */
        (function () {
            function sesuaikan() {
                var sheet = document.querySelector('.sik-sheet');
                if (!sheet) { return; }

                sheet.style.transform = 'none';
                document.body.classList.remove('sik-embed-scaled');
                document.body.style.height = '';

                var natural = Math.round(sheet.getBoundingClientRect().width);
                var avail = document.documentElement.clientWidth;
                if (natural > avail) {
                    var ratio = avail / natural;
                    sheet.style.transform = 'scale(' + ratio + ')';
                    document.body.classList.add('sik-embed-scaled');
                    document.body.style.height = Math.ceil(sheet.getBoundingClientRect().height * ratio) + 'px';
                }
            }

            window.addEventListener('load', sesuaikan);
            window.addEventListener('resize', sesuaikan);
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(sesuaikan).catch(function () {});
            }
        })();
    </script>
</head>
<body>
    @include('partials.permit-sheet', ['application' => $application, 'signer' => $signer, 'tembusan' => $tembusan])
</body>
</html>
