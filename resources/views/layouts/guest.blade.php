<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Izin Kerja Sisi Udara')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/sik.css') }}?v={{ @filemtime(public_path('css/sik.css')) ?: '1' }}" rel="stylesheet">
</head>
<body class="login-page">
    @yield('content')
</body>
</html>
