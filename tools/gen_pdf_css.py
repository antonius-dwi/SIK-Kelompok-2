"""Hasilkan public/css/sik-pdf.css dari aturan .sik-* pada public/css/sik.css.

Satu sumber layout: angka spasi/ukuran/font diambil apa adanya dari sik.css,
hanya bagian yang tidak didukung DomPDF yang diganti (flexbox -> table-cell,
width:100% -> mm eksplisit, border-radius/box-shadow dibuang).
Jalankan ulang skrip ini setiap kali aturan .sik-* di sik.css diubah.
"""

import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parent.parent
CSS = ROOT / "public/css/sik.css"
PDF = ROOT / "public/css/sik-pdf.css"

OVERRIDES = {
    ".sik-sheet": (
        "width: 178mm; margin: 0; padding: 0; color: #1d2939; background: #fff; "
        'font-family: "times", "Times New Roman", serif; font-size: 9.8pt; line-height: 1.34;'
    ),
    ".sik-kop": "display: table; width: 178mm; border-collapse: collapse;",
    ".sik-kop-logo": (
        "display: table-cell; width: 46mm; padding: 0; text-align: left; vertical-align: middle;"
    ),
    ".sik-kop-logo img": "width: 46mm; height: auto;",
    ".sik-kop-text strong": (
        'display: block; margin-bottom: .8mm; font-family: "helvetica", Arial, sans-serif; '
        "font-size: 11.5pt; font-weight: bold;"
    ),
    # leading kop dirapatkan: DomPDF menghitung tinggi baris lebih besar sehingga
    # blok kop jadi lebih tinggi daripada pratinjau (target tinggi kop 15,3mm).
    ".sik-kop-text strong, .sik-kop-text span, .sik-kop-text small": "line-height: 1.15;",
    # DomPDF tidak mendukung grid: baris label/isi memakai float + margin,
    # geometri sama dengan grid 33mm + gap 2.5mm pada pratinjau.
    # DomPDF tidak mendukung table-layout: fixed; dengan layout otomatis, lebar
    # th 33mm + text-align:left menghasilkan label di 16mm dan isi di 51,5mm
    # persis seperti pratinjau.
    ".sik-rows": "width: 178mm; border-collapse: collapse;",
    ".sik-rows th": (
        "width: 33mm; padding: .6mm 2.5mm .6mm 0; text-align: left; vertical-align: top; "
        "font-size: 9.8pt; font-weight: bold; white-space: nowrap;"
    ),
    ".sik-rows td": "padding: .6mm 0; vertical-align: top; text-align: justify;",
    # 126,5mm + padding/border 5 kolom (~16mm) = 142,5mm = lebar kolom isi pratinjau.
    ".sik-table": (
        "width: 126.5mm; margin: 1.2mm 0 .4mm; border-collapse: collapse; "
        "font-size: 8.8pt; line-height: 1.3;"
    ),
}

# Aturan tambahan khusus PDF (tidak ada padanannya di sik.css).
EXTRA = [
    # DomPDF menghitung tinggi baris lebih besar, sehingga blok kop jadi lebih
    # tinggi daripada pratinjau (pratinjau: kop 15,3mm). Leading dirapatkan
    # sampai tinggi kop sama, tanpa mengubah ukuran huruf.
    # Kompensasi sisa selisih tinggi blok kop (~2,5mm) supaya seluruh isi turun
    # di posisi yang sama dengan pratinjau.
    # DomPDF membungkus satu baris lebih banyak pada daftar Dasar/Ketentuan;
    # jarak blok penutup dirapatkan 1mm agar bagian bawah tetap sejajar.
    (".sik-closing", "width: 100%; margin-top: 4mm; border-collapse: collapse; page-break-inside: avoid;"),
    (".sik-tembusan", "margin-top: 4mm; font-size: 9pt; page-break-inside: avoid;"),
]

HEADER = """/* ============================================================================
   Gaya PDF (DomPDF) — DICERMATI dari aturan .sik-* pada public/css/sik.css.
   Dihasilkan oleh tools/gen_pdf_css.py; jangan disunting manual tanpa
   menyamakan angkanya dengan sik.css.

   Perbedaan yang memang perlu karena keterbatasan DomPDF:
   - .sik-kop memakai table-cell (DomPDF tidak mendukung flexbox/grid);
   - lebar eksplisit dalam mm menggantikan width: 100% (padding tidak
     mengurangi lebar isi pada DomPDF);
   - font memakai family inti PDF "times"/"helvetica" yang metriknya sama
     dengan Times New Roman/Arial pada pratinjau;
   - border-radius & box-shadow lembar dibuang (tidak terlihat saat dicetak).
   ============================================================================ */
@page {
    size: A4;
    margin: 0;
}

/* Margin halaman dipasang pada body: DomPDF mengabaikan margin @page tetapi
   mengulang margin body di setiap halaman, sehingga halaman lanjutan ikut bermargin. */
html {
    margin: 0;
    padding: 0;
}

body {
    margin: 14mm 16mm;
    padding: 0;
    color: #1d2939;
    background: #fff;
}

"""


def main() -> None:
    css = CSS.read_text(encoding="utf-8")
    rules = re.findall(r"(?m)^(\.[^{}\n]*sik[^{}\n]*)\{([^}]*)\}", css)
    lines = []
    for selector, body in rules:
        selector = selector.strip()
        # DomPDF tidak mendukung CSS variables: ganti token dengan nilai hex final.
        token = {
            "var(--sik-primary)": "#0E7C85",
            "var(--sik-primary-dark)": "#0A616A",
            "var(--sik-primary-soft)": "#E4F4F5",
            "var(--sik-cyan)": "#18B0B8",
            "var(--sik-navy)": "#0A2A43",
            "var(--sik-ink)": "#12293D",
            "var(--sik-muted)": "#5C7186",
            "var(--sik-border)": "#DCE7EF",
            "var(--sik-success)": "#16805B",
            "var(--sik-danger)": "#C64C3C",
        }
        body = " ".join(body.split())
        for nama, nilai in token.items():
            body = body.replace(nama, nilai)
        body = OVERRIDES.get(selector, body)
        lines.append(f"{selector} {{ {body} }}")
    lines.append("")
    lines.append("/* Penyesuaian halus agar metrik DomPDF mendekati pratinjau. */")
    for selector, body in EXTRA:
        lines.append(f"{selector} {{ {body} }}")
    PDF.write_text(HEADER + "\n".join(lines) + "\n", encoding="utf-8", newline="\n")
    print(f"sik-pdf.css ditulis: {len(lines)} aturan dari sik.css")


if __name__ == "__main__":
    main()
