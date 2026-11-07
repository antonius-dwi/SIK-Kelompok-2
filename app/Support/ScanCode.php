<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * QR code untuk dokumen SIK (SVG, tanpa ekstensi GD). SVG dipakai karena DomPDF di hosting hanya butuh rasterizer internal dan PNG berbasis GD tidak selalu tersedia; QR ini untuk identifikasi/verifikasi dokumen, bukan tanda tangan digital tersertifikasi.
 */
class ScanCode
{
    /** Ukuran matriks QR (px) sebelum diskalakan oleh CSS/mm di dokumen. */
    private const SIZE = 480;

    /** Quiet zone 4 modul: syarat agar QR tetap terbaca saat dicetak. */
    private const MARGIN = 4;

    public static function svg(string $payload): string
    {
        $renderer = new ImageRenderer(new RendererStyle(self::SIZE, self::MARGIN), new SvgImageBackEnd());

        return (new Writer($renderer))->writeString($payload);
    }

    /** Data URI agar dokumen (pratinjau & PDF) tidak bergantung pada request tambahan. */
    public static function dataUri(string $payload): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(self::svg($payload));
    }
}
