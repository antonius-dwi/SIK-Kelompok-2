{{--
    Sumber PDF server-side (DomPDF). Hanya berisi lembar SIK — tanpa sidebar, navbar,
    tombol aplikasi, atau markup modal. Gaya diambil dari public/css/sik-pdf.css agar
    hasil DomPDF identik dengan pratinjau cetak.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle }}</title>
    <style>{!! $sheetCss !!}</style>
</head>
<body>
    @include('partials.permit-sheet', [
        'application' => $application,
        'signer' => $signer,
        'tembusan' => $tembusan,
        'logoSrc' => $logoSrc,
    ])
</body>
</html>
