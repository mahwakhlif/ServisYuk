# -*- coding: utf-8 -*-
"""
Script Generator Dokumen Laporan Lengkap Proyek Mandiri RPL - ServisYuk
Mata Kuliah: Rekayasa Perangkat Lunak (TIKB2503021)
STMIK IKMI CIREBON
"""

import os
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import qn, nsdecls

def create_report():
    doc = Document()

    # 1. Page Setup (A4, Standard 1 inch margins)
    for section in doc.sections:
        section.page_width = Inches(8.27)
        section.page_height = Inches(11.69)
        section.top_margin = Inches(1.0)
        section.bottom_margin = Inches(1.0)
        section.left_margin = Inches(1.0)
        section.right_margin = Inches(1.0)

    # Palette Colors
    PRIMARY_COLOR = RGBColor(30, 58, 138)    # Deep Navy / #1E3A8A
    SECONDARY_COLOR = RGBColor(37, 99, 235)  # Royal Blue / #2563EB
    DARK_TEXT = RGBColor(31, 41, 55)        # Charcoal / #1F2937
    MUTED_TEXT = RGBColor(107, 114, 128)    # Slate / #6B7280
    BG_LIGHT = 'F3F4F6'
    BG_PRIMARY = '1E3A8A'
    BG_SUCCESS = 'ECFDF5'
    BORDER_COLOR = 'CBD5E1'

    # Helper Styles
    def style_run(run, font_name='Arial', size_pt=11, bold=False, italic=False, color=DARK_TEXT):
        run.font.name = font_name
        run.font.size = Pt(size_pt)
        run.bold = bold
        run.italic = italic
        run.font.color.rgb = color

    def add_para(text='', align=WD_ALIGN_PARAGRAPH.LEFT, space_before=0, space_after=6, line_spacing=1.15):
        p = doc.add_paragraph()
        p.alignment = align
        p.paragraph_format.space_before = Pt(space_before)
        p.paragraph_format.space_after = Pt(space_after)
        p.paragraph_format.line_spacing = line_spacing
        if text:
            r = p.add_run(text)
            style_run(r)
        return p

    def add_heading_1(text):
        p = add_para(space_before=16, space_after=8)
        r = p.add_run(text)
        style_run(r, font_name='Arial', size_pt=16, bold=True, color=PRIMARY_COLOR)
        return p

    def add_heading_2(text):
        p = add_para(space_before=12, space_after=6)
        r = p.add_run(text)
        style_run(r, font_name='Arial', size_pt=13, bold=True, color=SECONDARY_COLOR)
        return p

    def add_heading_3(text):
        p = add_para(space_before=8, space_after=4)
        r = p.add_run(text)
        style_run(r, font_name='Arial', size_pt=11.5, bold=True, color=PRIMARY_COLOR)
        return p

    def set_cell_background(cell, hex_color):
        tcPr = cell._tc.get_or_add_tcPr()
        shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>')
        tcPr.append(shd)

    def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
        tcPr = cell._tc.get_or_add_tcPr()
        tcMar = parse_xml(
            f'<w:tcMar {nsdecls("w")}>'
            f'<w:top w:w="{top}" w:type="dxa"/>'
            f'<w:bottom w:w="{bottom}" w:type="dxa"/>'
            f'<w:left w:w="{left}" w:type="dxa"/>'
            f'<w:right w:w="{right}" w:type="dxa"/>'
            f'</w:tcMar>'
        )
        tcPr.append(tcMar)

    def set_table_borders(table, color="CCCCCC", sz="4", val="single"):
        tblPr = table._tbl.tblPr
        borders = parse_xml(
            f'<w:tblBorders {nsdecls("w")}>'
            f'<w:top w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'<w:bottom w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'<w:left w:val="none"/>'
            f'<w:right w:val="none"/>'
            f'<w:insideH w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'<w:insideV w:val="none"/>'
            f'</w:tblBorders>'
        )
        tblPr.append(borders)

    def add_callout(text, title="CATATAN PENTING"):
        tbl = doc.add_table(rows=1, cols=1)
        tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
        tbl.autofit = False
        cell = tbl.cell(0, 0)
        cell.width = Inches(6.27)
        set_cell_background(cell, BG_LIGHT)
        set_cell_margins(cell, top=140, bottom=140, left=200, right=200)

        # left border thick
        tcPr = cell._tc.get_or_add_tcPr()
        borders = parse_xml(
            f'<w:tcBorders {nsdecls("w")}>'
            f'<w:left w:val="single" w:sz="24" w:space="0" w:color="1E3A8A"/>'
            f'<w:top w:val="none"/>'
            f'<w:right w:val="none"/>'
            f'<w:bottom w:val="none"/>'
            f'</w:tcBorders>'
        )
        tcPr.append(borders)

        p = cell.paragraphs[0]
        p.paragraph_format.space_before = Pt(2)
        p.paragraph_format.space_after = Pt(4)
        r_title = p.add_run(f"📌 {title}: ")
        style_run(r_title, size_pt=10.5, bold=True, color=PRIMARY_COLOR)
        r_text = p.add_run(text)
        style_run(r_text, size_pt=10, italic=True, color=DARK_TEXT)
        doc.add_paragraph().paragraph_format.space_after = Pt(4)

    def add_image_box(image_path, caption, width_in=6.0):
        if os.path.exists(image_path):
            p_img = doc.add_paragraph()
            p_img.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p_img.paragraph_format.space_before = Pt(8)
            p_img.paragraph_format.space_after = Pt(4)
            p_img.add_run().add_picture(image_path, width=Inches(width_in))

            p_cap = doc.add_paragraph()
            p_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p_cap.paragraph_format.space_before = Pt(2)
            p_cap.paragraph_format.space_after = Pt(12)
            r = p_cap.add_run(caption)
            style_run(r, size_pt=9.5, italic=True, color=MUTED_TEXT)
        else:
            p_err = doc.add_paragraph()
            p_err.alignment = WD_ALIGN_PARAGRAPH.CENTER
            r = p_err.add_run(f"[Gambar tidak ditemukan pada: {image_path}]")
            style_run(r, size_pt=10, italic=True, color=RGBColor(220, 38, 38))

    # ==========================================
    # HALAMAN SAMPUL / COVER
    # ==========================================
    p_inst = add_para(align=WD_ALIGN_PARAGRAPH.CENTER, space_before=10, space_after=2)
    r = p_inst.add_run("PROGRAM STUDI S1 TEKNIK INFORMATIKA\nSTMIK IKMI CIREBON")
    style_run(r, font_name='Arial', size_pt=12, bold=True, color=PRIMARY_COLOR)

    p_line = add_para(align=WD_ALIGN_PARAGRAPH.CENTER, space_before=4, space_after=24)
    r = p_line.add_run("―" * 38)
    style_run(r, size_pt=11, bold=True, color=SECONDARY_COLOR)

    p_title = add_para(align=WD_ALIGN_PARAGRAPH.CENTER, space_before=10, space_after=12)
    r = p_title.add_run("LAPORAN TUGAS PROYEK AKHIR MANDIRI\nREKAYASA PERANGKAT LUNAK (TIKB2503021)")
    style_run(r, font_name='Arial', size_pt=16, bold=True, color=PRIMARY_COLOR)

    p_subtitle = add_para(align=WD_ALIGN_PARAGRAPH.CENTER, space_before=8, space_after=24)
    r = p_subtitle.add_run(
        "Rancang Bangun Sistem Informasi Layanan Jasa Perbaikan Peralatan Rumah Tangga "
        "(Home Appliance Repair) Terintegrasi Multi-Provider Artificial Intelligence "
        "dan Gateway WhatsApp Real-Time\n\n\"ServisYuk\""
    )
    style_run(r, font_name='Arial', size_pt=13, bold=True, color=SECONDARY_COLOR)

    add_callout(
        "Laporan ini disusun secara utuh dan mandiri untuk memenuhi persyaratan Ujian Akhir Semester (UAS) "
        "Mata Kuliah Rekayasa Perangkat Lunak berbasis kurikulum Outcome-Based Education (OBE).",
        "PERNYATAAN KEASLIAN PROYEK"
    )

    p_meta = add_para(align=WD_ALIGN_PARAGRAPH.CENTER, space_before=30, space_after=6)
    r = p_meta.add_run("Disusun Oleh:\n")
    style_run(r, size_pt=11, bold=True, color=DARK_TEXT)

    # Tabel Identitas Penyusun
    tbl_id = doc.add_table(rows=5, cols=2)
    tbl_id.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_id, color="E2E8F0")

    id_data = [
        ("Nama Mahasiswa / Author", ": [NAMA LENGKAP MAHASISWA]"),
        ("Nomor Induk Mahasiswa (NIM)", ": [NOMOR INDUK MAHASISWA / NIM]"),
        ("Kelompok / Tema", ": KELOMPOK HAMBALANG (Jasa Perbaikan Rumah)"),
        ("Program Studi / Kelas", ": S1 TEKNIK INFORMATIKA / 1C-TI-25"),
        ("Dosen Pengampu", ": YUDHISTIRA ARIE WIJAYA, M.Kom., CDS.")
    ]
    for row_idx, (k, v) in enumerate(id_data):
        row = tbl_id.rows[row_idx]
        cell_k, cell_v = row.cells[0], row.cells[1]
        cell_k.width, cell_v.width = Inches(2.5), Inches(3.7)
        set_cell_margins(cell_k, top=60, bottom=60, left=100, right=100)
        set_cell_margins(cell_v, top=60, bottom=60, left=100, right=100)

        pk = cell_k.paragraphs[0]
        pk.paragraph_format.space_before, pk.paragraph_format.space_after = Pt(2), Pt(2)
        rk = pk.add_run(k)
        style_run(rk, size_pt=10, bold=True, color=PRIMARY_COLOR)

        pv = cell_v.paragraphs[0]
        pv.paragraph_format.space_before, pv.paragraph_format.space_after = Pt(2), Pt(2)
        rv = pv.add_run(v)
        style_run(rv, size_pt=10, bold=False, color=DARK_TEXT)

    p_foot = add_para(align=WD_ALIGN_PARAGRAPH.CENTER, space_before=40, space_after=0)
    r = p_foot.add_run("CIREBON\nTAHUN AKADEMIK 2026/2027")
    style_run(r, font_name='Arial', size_pt=11, bold=True, color=DARK_TEXT)

    doc.add_page_break()

    # ==========================================
    # LEMBAR TAUTAN DELIVERABLES & PENGESAHAN
    # ==========================================
    add_heading_1("LEMBAR METADATA & TAUTAN LUARAN PROYEK (DELIVERABLES)")

    p_deliv_desc = add_para(
        "Berdasarkan Buku Panduan Proyek Akhir Mandiri Rekayasa Perangkat Lunak TIKB2503021 BAB II & BAB V, "
        "berikut adalah seluruh bukti luaran digital yang telah dipublikasikan dan dapat diakses:"
    )

    tbl_links = doc.add_table(rows=5, cols=3)
    tbl_links.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_links, color="CBD5E1")

    headers_link = ["Item Luaran", "Tautan Resmi (URL)", "Keterangan Akses"]
    hdr_row = tbl_links.rows[0]
    for c_idx, h_text in enumerate(headers_link):
        cell = hdr_row.cells[c_idx]
        set_cell_background(cell, BG_PRIMARY)
        set_cell_margins(cell, top=100, bottom=100, left=100, right=100)
        p = cell.paragraphs[0]
        p.paragraph_format.space_before, p.paragraph_format.space_after = Pt(2), Pt(2)
        r = p.add_run(h_text)
        style_run(r, size_pt=10, bold=True, color=RGBColor(255, 255, 255))

    links_rows_data = [
        (
            "1. Repositori GitHub Publik",
            "https://github.com/mahwakhlif/ServisYuk",
            "Kode sumber web MVC, microservice chatbot, skrip unit test, dan CI/CD pipeline (Branch: main)."
        ),
        (
            "2. Video Progres 1 (PRD & Konsep)",
            "https://youtu.be/[LINK_VIDEO_PROGRES_1]",
            "Durasi 3–5 Menit: Latar belakang masalah, nilai guna, kebutuhan fungsional & peran strategis AI."
        ),
        (
            "3. Video Progres 2 (UML & UI/UX)",
            "https://youtu.be/[LINK_VIDEO_PROGRES_2]",
            "Durasi 5–7 Menit: Bedah alur logika 4 Diagram UML (Use Case, Activity, Class, Sequence) & walkthrough Figma."
        ),
        (
            "4. Video Final Demo & CI/CD",
            "https://youtu.be/[LINK_VIDEO_FINAL_DEMO]",
            "Durasi 8–12 Menit: Live demo sistem utuh, respon AI real-time, eksekusi test lokal & verifikasi hijau GitHub Actions."
        )
    ]

    for r_idx, (it, url, ket) in enumerate(links_rows_data):
        row = tbl_links.rows[r_idx + 1]
        widths = [Inches(1.8), Inches(2.4), Inches(2.07)]
        for c_idx, text_val in enumerate([it, url, ket]):
            cell = row.cells[c_idx]
            cell.width = widths[c_idx]
            set_cell_margins(cell, top=80, bottom=80, left=100, right=100)
            if r_idx % 2 == 1:
                set_cell_background(cell, BG_LIGHT)
            p = cell.paragraphs[0]
            p.paragraph_format.space_before, p.paragraph_format.space_after = Pt(2), Pt(2)
            r = p.add_run(text_val)
            is_bold = (c_idx == 0)
            is_link = (c_idx == 1 and "http" in text_val)
            col_rgb = SECONDARY_COLOR if is_link else DARK_TEXT
            style_run(r, size_pt=9.5, bold=is_bold, color=col_rgb)

    p_space = add_para(space_before=16, space_after=8)

    # Identitas Anggota Kelompok Hambalang
    add_heading_2("Daftar Anggota Kelompok Hambalang (Topik: Jasa Perbaikan Rumah)")
    tbl_group = doc.add_table(rows=5, cols=4)
    tbl_group.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_group, color="CBD5E1")

    hdr_group = tbl_group.rows[0]
    for c_idx, h_text in enumerate(["No.", "Nama Lengkap Mahasiswa", "NIM", "Peran / Tanggung Jawab Mandiri"]):
        cell = hdr_group.cells[c_idx]
        set_cell_background(cell, BG_PRIMARY)
        set_cell_margins(cell, top=100, bottom=100, left=100, right=100)
        p = cell.paragraphs[0]
        r = p.add_run(h_text)
        style_run(r, size_pt=10, bold=True, color=RGBColor(255, 255, 255))

    group_data = [
        ("1", "[NAMA ANGGOTA 1 / AUTHOR]", "[NIM ANGGOTA 1]", "Full-Stack Software Engineer (End-to-End Build ServisYuk)"),
        ("2", "[NAMA ANGGOTA 2]", "[NIM ANGGOTA 2]", "Anggota Kelompok Hambalang (Aplikasi Mandiri Masing-Masing)"),
        ("3", "[NAMA ANGGOTA 3]", "[NIM ANGGOTA 3]", "Anggota Kelompok Hambalang (Aplikasi Mandiri Masing-Masing)"),
        ("4", "[NAMA ANGGOTA 4]", "[NIM ANGGOTA 4]", "Anggota Kelompok Hambalang (Aplikasi Mandiri Masing-Masing)")
    ]
    for r_idx, g_vals in enumerate(group_data):
        row = tbl_group.rows[r_idx + 1]
        widths = [Inches(0.6), Inches(2.2), Inches(1.4), Inches(2.07)]
        for c_idx, val in enumerate(g_vals):
            cell = row.cells[c_idx]
            cell.width = widths[c_idx]
            set_cell_margins(cell, top=80, bottom=80, left=100, right=100)
            if r_idx % 2 == 1:
                set_cell_background(cell, BG_LIGHT)
            p = cell.paragraphs[0]
            r = p.add_run(val)
            style_run(r, size_pt=9.5, color=DARK_TEXT)

    doc.add_page_break()

    # ==========================================
    # KATA PENGANTAR & DAFTAR ISI RINGKAS
    # ==========================================
    add_heading_1("KATA PENGANTAR")
    add_para(
        "Puji dan syukur penulis panjatkan ke hadirat Tuhan Yang Maha Esa atas limpahan rahmat, taufik, "
        "dan hidayah-Nya, sehingga Laporan Tugas Proyek Akhir Mandiri mata kuliah Rekayasa Perangkat Lunak "
        "(TIKB2503021) berjudul \"Rancang Bangun Sistem Informasi Layanan Jasa Perbaikan Peralatan Rumah Tangga "
        "Berbasis Multi-Provider Artificial Intelligence dan Gateway WhatsApp Real-Time (ServisYuk)\" "
        "dapat diselesaikan dengan baik dan tepat waktu."
    )
    add_para(
        "Laporan ini disusun sebagai salah satu syarat kelulusan dan pemenuhan luaran Ujian Akhir Semester (UAS) "
        "Semester 3 Program Studi S1 Teknik Informatika STMIK IKMI Cirebon. Pendekatan yang diterapkan dalam "
        "proyek ini mengacu pada standar Outcome-Based Education (OBE), di mana setiap mahasiswa dituntut memiliki "
        "penguasaan penuh secara mandiri (Full End-to-End Build) mulai dari inisiasi PRD/SRS, pemodelan arsitektur UML, "
        "perancangan UI/UX Figma, konstruksi kode bersih berbasis MVC, integrasi kecerdasan buatan, hingga "
        "penerapan Continuous Integration & Continuous Deployment (CI/CD) pada GitHub Actions."
    )
    add_para(
        "Penulis mengucapkan terima kasih yang sebesar-besarnya kepada Bapak Yudhistira Arie Wijaya, M.Kom., CDS. "
        "selaku Dosen Pengampu mata kuliah Rekayasa Perangkat Lunak atas segala bimbingan, arahan teknis, dan inspirasi "
        "yang telah diberikan sepanjang proses perkuliahan. Penulis menyadari bahwa laporan ini masih memiliki ruang "
        "untuk penyempurnaan. Oleh karena itu, kritik dan saran yang membangun sangat diharapkan demi pengembangan "
        "sistem di masa yang akan datang."
    )

    p_sig = add_para(align=WD_ALIGN_PARAGRAPH.RIGHT, space_before=20, space_after=2)
    r = p_sig.add_run("Cirebon, November 2026\n\n\nPenulis / Mahasiswa")
    style_run(r, size_pt=10.5, italic=True)

    add_heading_2("Daftar Struktur Dokumen Terpadu")
    toc_items = [
        ("BAB I : PRODUCT REQUIREMENTS DOCUMENT (PRD) / SRS MANDIRI", "Halaman 4"),
        ("BAB II: PERANCANGAN ARSITEKTUR & PEMODELAN UML (SDD MANDIRI)", "Halaman 9"),
        ("BAB III: PERANCANGAN ANTARMUKA PENGGUNA (UI/UX DESIGN FIGMA)", "Halaman 18"),
        ("BAB IV: IMPLEMENTASI KODE, INTEGRASI AI & AUTOMATED TESTING", "Halaman 21"),
        ("BAB V : PENERAPAN CI/CD GITHUB ACTIONS & BUKTI HASIL PENGUJIAN", "Halaman 26"),
        ("BAB VI: KESIMPULAN, REFLEKSI PEMBELAJARAN & SARAN", "Halaman 30"),
        ("LAMPIRAN: PANDUAN INSTALASI & KREDENSIAL PENGUJIAN SISTEM", "Halaman 32")
    ]
    for title, pg in toc_items:
        p = add_para(space_before=3, space_after=3)
        r1 = p.add_run(title)
        style_run(r1, size_pt=10.5, bold=True, color=PRIMARY_COLOR)
        r2 = p.add_run(f" ......... {pg}")
        style_run(r2, size_pt=10, italic=True, color=MUTED_TEXT)

    doc.add_page_break()

    # ==========================================
    # BAB I: PRODUCT REQUIREMENTS DOCUMENT (PRD)
    # ==========================================
    add_heading_1("BAB I: PRODUCT REQUIREMENTS DOCUMENT (PRD) / SRS")

    add_heading_2("1.1 Ringkasan Produk (Executive Summary)")
    add_para(
        "ServisYuk merupakan platform berbasis web modern yang dirancang khusus untuk mendigitalkan dan "
        "mengotomatisasi ekosistem pelayanan jasa perbaikan peralatan rumah tangga (Home Appliance Repair). "
        "Platform ini menghubungkan tiga entitas utama—Customer (pelanggan), Teknisi (mitra lapangan), dan "
        "Administrator (pengelola operasional)—ke dalam satu siklus kerja yang transparan, terukur, dan terpadu."
    )
    add_para(
        "Keunggulan utama ServisYuk terletak pada integrasi Asisten Kecerdasan Artifisial (AI Multi-Provider) "
        "yang siaga 24 jam sehari. AI ini bertindak sebagai konsultan triase awal yang membedah gejala kerusakan "
        "alat secara interaktif, mengestimasi rentang biaya perbaikan yang wajar, serta memberikan panduan keselamatan "
        "sebelum teknisi profesional tiba di alamat pelanggan."
    )

    add_heading_2("1.2 Latar Belakang Masalah & Urgensi Solusi")
    add_para(
        "Dalam kehidupan sehari-hari, kerusakan peralatan esensial seperti AC, lemari es, mesin cuci, dispenser, "
        "atau kompor gas menimbulkan disrupsi signifikan bagi kenyamanan rumah tangga. Sayangnya, proses pemesanan "
        "jasa perbaikan konvensional selama ini memiliki berbagai kelemahan mendasar:"
    )
    problems = [
        ("Ketidakpastian Biaya & Asimetri Informasi", "Pelanggan sering kali merasa was-was terhadap manipulasi tarif servis (mark-up harga sparepart) atau diagnosa yang sengaja dilebih-lebihkan oleh teknisi nakal."),
        ("Pengelolaan Pesanan yang Berserakan", "Pemesanan melalui telepon atau WhatsApp personal rentan tercecer, jadwal teknisi tumpang tindih, dan riwayat pekerjaan sulit diakses kembali."),
        ("Ketiadaan Informasi Kerusakan Awal", "Teknisi kerap datang ke lokasi tanpa mengetahui spesifikasi merek, tipe, dan gejala kerusakan unit secara rinci, sehingga tidak membawa suku cadang pengganti yang tepat."),
        ("Pelacakan Status yang Tidak Transparan", "Pelanggan tidak memiliki visibilitas terhadap status perbaikan unit mereka—apakah teknisi sedang dalam perjalanan, sedang pemeriksaan, atau menunggu sparepart.")
    ]
    for prob_title, prob_desc in problems:
        p = add_para(space_before=2, space_after=4)
        r_b = p.add_run(f"• {prob_title}: ")
        style_run(r_b, bold=True, color=PRIMARY_COLOR)
        r_d = p.add_run(prob_desc)
        style_run(r_d, color=DARK_TEXT)

    add_heading_2("1.3 Tujuan & Nilai Guna Produk (Value Proposition)")
    add_para("Pengembangan sistem ServisYuk bertujuan untuk:")
    goals = [
        "Menyediakan portal terintegrasi bagi pelanggan untuk memesan jasa perbaikan kapan saja (24/7) secara mandiri.",
        "Memberikan diagnosis awal berbasis AI guna memprediksi komponen rusak dan estimasi biaya secara objektif.",
        "Mempermudah Admin dalam mengalokasikan dan memonitor pekerjaan teknisi berdasarkan keahlian dan ketersediaan waktu.",
        "Menyediakan portal kerja bagi teknisi untuk mencatat hasil inspeksi, tindakan perbaikan, dan suku cadang yang diganti.",
        "Mewujudkan sistem notifikasi otomatis melalui gateway WhatsApp dan Telegram secara instan."
    ]
    for g in goals:
        p = add_para(space_before=2, space_after=3)
        r = p.add_run(f"1. {g}" if g == goals[0] else f"{goals.index(g)+1}. {g}")
        style_run(r, color=DARK_TEXT)

    add_heading_2("1.4 Profil & Karakteristik Target Pengguna")
    tbl_users = doc.add_table(rows=4, cols=3)
    tbl_users.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_users, color="CBD5E1")

    hdr_u = tbl_users.rows[0]
    for c_idx, h_text in enumerate(["Aktor / Role", "Karakteristik & Deskripsi", "Tanggung Jawab Utama"]):
        cell = hdr_u.cells[c_idx]
        set_cell_background(cell, BG_PRIMARY)
        set_cell_margins(cell, top=100, bottom=100, left=100, right=100)
        p = cell.paragraphs[0]
        r = p.add_run(h_text)
        style_run(r, size_pt=10, bold=True, color=RGBColor(255, 255, 255))

    users_data = [
        (
            "Customer (Pelanggan)",
            "Masyarakat umum pemilik peralatan elektronik rumah tangga yang memerlukan perbaikan cepat, transparan, dan terpercaya.",
            "Registrasi akun, konsultasi via AI Chatbot, memilih jenis layanan, mengajukan booking servis, dan memantau timeline pengerjaan."
        ),
        (
            "Teknisi (Mitra Lapangan)",
            "Tenaga ahli perbaikan elektronik tersertifikasi yang bertugas memeriksa dan mereparasi peralatan di lokasi pelanggan.",
            "Menerima tugas, melihat riwayat gejala unit dari AI, memperbarui status kerja secara live, dan mengisi laporan tindakan/sparepart."
        ),
        (
            "Administrator",
            "Pengelola operasional sistem ServisYuk yang memegang kendali penuh terhadap tata kelola master data dan transaksi.",
            "Memverifikasi pesanan servis baru, menugaskan teknisi yang kompeten, mengelola tarif jasa, memantau metrik performa, dan mengatur konfigurasi AI."
        )
    ]
    for r_idx, (act, char, resp) in enumerate(users_data):
        row = tbl_users.rows[r_idx + 1]
        widths = [Inches(1.5), Inches(2.2), Inches(2.57)]
        for c_idx, text_val in enumerate([act, char, resp]):
            cell = row.cells[c_idx]
            cell.width = widths[c_idx]
            set_cell_margins(cell, top=80, bottom=80, left=100, right=100)
            if r_idx % 2 == 1:
                set_cell_background(cell, BG_LIGHT)
            p = cell.paragraphs[0]
            r = p.add_run(text_val)
            style_run(r, size_pt=9.5, bold=(c_idx == 0), color=DARK_TEXT)

    add_heading_2("1.5 Spesifikasi Kebutuhan Fungsional (Functional Requirements)")
    add_para(
        "Kebutuhan fungsional sistem didefinisikan secara eksplisit menggunakan format User Stories "
        "dan Acceptance Criteria sebagai acuan pengujian sistem:"
    )

    tbl_fr = doc.add_table(rows=16, cols=4)
    tbl_fr.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_fr, color="CBD5E1")

    hdr_fr = tbl_fr.rows[0]
    for c_idx, h_text in enumerate(["Kode FR", "Fitur Fungsional", "Aktor Terkait", "Acceptance Criteria (Kriteria Keberterimaan)"]):
        cell = hdr_fr.cells[c_idx]
        set_cell_background(cell, BG_PRIMARY)
        set_cell_margins(cell, top=100, bottom=100, left=100, right=100)
        p = cell.paragraphs[0]
        r = p.add_run(h_text)
        style_run(r, size_pt=9.5, bold=True, color=RGBColor(255, 255, 255))

    fr_data = [
        ("FR-01", "Autentikasi Multi-Role", "Semua", "Pengguna dapat login dan logout dengan keamanan hash Bcrypt dan pemisahan session role."),
        ("FR-02", "Registrasi Customer & OTP", "Customer", "Pendaftaran akun baru diverifikasi menggunakan nomor telepon dan kode OTP WhatsApp 6-digit."),
        ("FR-03", "Manajemen Profil Akun", "Semua", "Pengguna dapat memperbarui nama, nomor telepon, alamat domisili, dan kata sandi akun."),
        ("FR-04", "Pengajuan Permintaan Servis", "Customer", "Customer dapat memilih kategori alat, mengunggah foto kerusakan, dan memilih jadwal kunjungan."),
        ("FR-05", "Konsultasi & Diagnosis AI", "Customer", "Customer dapat menanyakan gejala kerusakan ke AI dan menerima analisis penyebab dan estimasi biaya."),
        ("FR-06", "Manajemen Data Customer", "Admin", "Admin dapat melihat riwayat transaksi customer, status keaktifan, dan memblokir akun jika diperlukan."),
        ("FR-07", "Manajemen Mitra Teknisi", "Admin", "Admin dapat mendaftarkan teknisi baru, mengatur spesialisasi keahlian, dan mengubah status ketersediaan."),
        ("FR-08", "Manajemen Katalog Jasa", "Admin", "Admin dapat menambah atau memperbarui katalog jenis servis, estimasi harga dasar, dan deskripsi pengerjaan."),
        ("FR-09", "Penugasan Teknisi (Dispatch)", "Admin", "Admin dapat menugaskan teknisi yang kompeten untuk tiket servis baru yang berstatus Menunggu."),
        ("FR-10", "Portal Tugas Teknisi", "Teknisi", "Teknisi dapat melihat daftar pekerjaan aktif, alamat pelanggan, dan riwayat diagnosis awal AI."),
        ("FR-11", "Pembaruan Status Servis", "Teknisi", "Teknisi dapat memperbarui status (Dalam Perjalanan, Pemeriksaan, Perbaikan, Selesai, Batal)."),
        ("FR-12", "Input Laporan Pekerjaan", "Teknisi", "Teknisi mencatat hasil inspeksi, tindakan reparasi, komponen yang diganti, serta rincian biaya aktual."),
        ("FR-13", "Pelacakan Timeline Servis", "Customer", "Customer dapat memantau progres pengerjaan servis secara visual melalui timeline interaktif 5 tahap."),
        ("FR-14", "Riwayat & Invoice Digital", "Customer", "Customer dapat melihat arsip pesanan selesai dan mengunduh bukti invoice perbaikan berformat resmi."),
        ("FR-15", "Dashboard Statistik & Laporan", "Admin", "Admin dapat melihat ringkasan metrik total servis, pendapatan bulanan, dan kinerja kepuasan teknisi.")
    ]

    for r_idx, (code, feat, act, ac) in enumerate(fr_data):
        row = tbl_fr.rows[r_idx + 1]
        widths = [Inches(0.8), Inches(1.8), Inches(1.1), Inches(2.57)]
        for c_idx, text_val in enumerate([code, feat, act, ac]):
            cell = row.cells[c_idx]
            cell.width = widths[c_idx]
            set_cell_margins(cell, top=60, bottom=60, left=80, right=80)
            if r_idx % 2 == 1:
                set_cell_background(cell, BG_LIGHT)
            p = cell.paragraphs[0]
            r = p.add_run(text_val)
            style_run(r, size_pt=9, bold=(c_idx == 0), color=DARK_TEXT)

    add_heading_2("1.6 Kebutuhan Non-Fungsional (Non-Functional Requirements)")
    nfr_items = [
        ("NFR-01: Keamanan (Security)", "Kredensial pengguna dilindungi menggunakan algoritma hashing standar industri (Bcrypt). Seluruh API Key penyedia AI disimpan dalam database dengan enkripsi simetris dua arah AES-256-CBC dan Initialization Vector (IV) acak. Proteksi CSRF Token diterapkan pada setiap formulir POST."),
        ("NFR-02: Kinerja & Latensi (Performance)", "Waktu respon inferensi chatbot AI ditargetkan di bawah 3 detik melalui pemanfaatan Groq LPU Hardware Acceleration dan arsitektur microservice asynchronous."),
        ("NFR-03: Ketersediaan (High Availability)", "Sistem dilengkapi fitur AI Fallback multi-tier: jika layanan Groq mengalami gangguan kuota (HTTP 429), permintaan secara otomatis dialihkan ke Google Gemini atau OpenAI tanpa interupsi bagi pengguna."),
        ("NFR-04: Responsivitas Antarmuka (Responsiveness)", "Tata letak web responsif dibangun menggunakan framework Bootstrap 5.3 dan custom CSS, mendukung tampilan optimal pada layar smartphone, tablet, maupun komputer desktop.")
    ]
    for nfr_code, nfr_desc in nfr_items:
        p = add_para(space_before=3, space_after=4)
        r_title = p.add_run(f"• {nfr_code}: ")
        style_run(r_title, bold=True, color=PRIMARY_COLOR)
        r_desc = p.add_run(nfr_desc)
        style_run(r_desc, color=DARK_TEXT)

    add_heading_2("1.7 Spesifikasi Fitur Kecerdasan Artifisial (AI Multi-Provider)")
    add_para(
        "Fitur AI dalam ServisYuk bukan sekadar chatbot biasa, melainkan mesin diagnosis kerusakan peralatan "
        "rumah tangga cerdas yang dirancang dengan arsitektur microservice Node.js mandiri (port 5005). "
        "Sistem mendukung switching penyedia AI secara dinamis melalui Console Pengaturan Admin:"
    )
    ai_providers = [
        ("Groq Cloud (Primary Engine)", "Menggunakan model Qwen 3.8 27B / GPT-OSS 20B berkecepatan tinggi (hingga 500 token/detik) dengan latensi ultra-rendah."),
        ("Google Gemini AI Studio (Secondary Engine)", "Menggunakan model resmi gemini-3.8-flash melalui OpenAI-compatible endpoint untuk penalaran komprehensif."),
        ("NVIDIA NIM & OpenAI (Alternative Fallback)", "Mendukung integrasi Llama 3.1 Nemotron 70B dan GPT-4o Mini sebagai penyedia cadangan jika terjadi kegagalan jaringan.")
    ]
    for ai_name, ai_detail in ai_providers:
        p = add_para(space_before=2, space_after=3)
        r1 = p.add_run(f"― {ai_name}: ")
        style_run(r1, bold=True, color=SECONDARY_COLOR)
        r2 = p.add_run(ai_detail)
        style_run(r2, color=DARK_TEXT)

    doc.add_page_break()

    # ==========================================
    # BAB II: PERANCANGAN SISTEM & PEMODELAN UML
    # ==========================================
    add_heading_1("BAB II: PERANCANGAN SISTEM & PEMODELAN UML (SDD)")

    add_heading_2("2.1 Arsitektur Sistem Berorientasi Objek & Microservice")
    add_para(
        "ServisYuk mengadopsi pola arsitektur Model-View-Controller (MVC) terstruktur pada lapisan aplikasi web "
        "utama (PHP 8.2), yang dipadukan secara harmonis dengan Microservice AI (Node.js Express). "
        "Desain ini memisahkan tanggung jawab bisnis, representasi data, dan inferensi AI berat ke dalam lingkungan yang terisolasi."
    )

    add_heading_2("2.2 Use Case Diagram & Skenario Interaksi Sistem")
    add_para(
        "Use Case Diagram ServisYuk memetakan seluruh kapabilitas sistem yang dapat dieksekusi oleh 4 aktor: "
        "Customer, Teknisi, dan Admin diposisikan di sisi kiri sebagai aktor manusia, sedangkan Asisten AI 24 Jam "
        "berada di sisi kanan sebagai aktor sistem otonom yang melayani diagnosis interaktif secara berkelanjutan."
    )

    # Insert Use Case Image
    add_image_box(
        r'c:\xampp\htdocs\jasa\use_case_servisyuk.png',
        "Gambar 2.1: Use Case Diagram ServisYuk (4 Aktor: Customer, Teknisi, Admin, & Asisten AI 24 Jam)",
        width_in=6.1
    )

    add_para(
        "Tabel berikut mendeskripsikan secara rinci skenario operasional use case utama sistem:"
    )

    tbl_uc = doc.add_table(rows=7, cols=4)
    tbl_uc.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_uc, color="CBD5E1")

    hdr_uc = tbl_uc.rows[0]
    for c_idx, h_text in enumerate(["Kode UC", "Nama Use Case", "Aktor Terlibat", "Deskripsi Alur Operasional"]):
        cell = hdr_uc.cells[c_idx]
        set_cell_background(cell, BG_PRIMARY)
        set_cell_margins(cell, top=100, bottom=100, left=100, right=100)
        p = cell.paragraphs[0]
        r = p.add_run(h_text)
        style_run(r, size_pt=9.5, bold=True, color=RGBColor(255, 255, 255))

    uc_data = [
        ("UC-01", "Registrasi & Verifikasi OTP", "Customer", "Customer mendaftar, sistem membangkitkan OTP acak 6-digit dan mengirimkannya via WhatsApp Fonnte untuk validasi instan."),
        ("UC-02", "Konsultasi Kerusakan AI", "Customer, Asisten AI", "Customer berdialog dengan AI mengenai gejala alat; AI menganalisis basis pengetahuan lokal dan API LLM lalu merangkum potensi kerusakan."),
        ("UC-03", "Pengajuan Tiket Servis", "Customer", "Customer mengisi formulir detail unit, melampirkan foto masalah, menentukan tanggal servis, dan sistem membuat kode booking acak unik."),
        ("UC-04", "Verifikasi & Penugasan", "Admin, Teknisi", "Admin meninjau antrean permohonan servis baru, memilih teknisi yang memiliki spesialisasi relevan, dan memicu notifikasi penugasan."),
        ("UC-05", "Pengerjaan & Update Status", "Teknisi", "Teknisi memperbarui tahapan kerja (Menuju Lokasi, Inspeksi, Pengerjaan, Selesai) yang langsung tercermin pada live timeline customer."),
        ("UC-06", "Penerbitan Laporan & Invoice", "Teknisi, Customer", "Teknisi mencatat komponen yang diganti dan total ongkos; sistem otomatis mengunci status 'Selesai' serta menerbitkan invoice digital.")
    ]
    for r_idx, (code, name, act, desc) in enumerate(uc_data):
        row = tbl_uc.rows[r_idx + 1]
        widths = [Inches(0.8), Inches(1.8), Inches(1.3), Inches(2.37)]
        for c_idx, text_val in enumerate([code, name, act, desc]):
            cell = row.cells[c_idx]
            cell.width = widths[c_idx]
            set_cell_margins(cell, top=60, bottom=60, left=80, right=80)
            if r_idx % 2 == 1:
                set_cell_background(cell, BG_LIGHT)
            p = cell.paragraphs[0]
            r = p.add_run(text_val)
            style_run(r, size_pt=9, bold=(c_idx == 0), color=DARK_TEXT)

    add_heading_2("2.3 Activity Diagram Alur Bisnis & AI")
    add_para(
        "Activity Diagram memvisualisasikan alur dinamis pertukaran aktivitas antar entitas. "
        "Aktivitas dimulai dari customer yang memasukkan keluhan kerusakan, diproses oleh AI Assistant, "
        "diverifikasi oleh Admin, dikerjakan oleh Teknisi di lapangan, hingga terbitnya laporan servis selesai:"
    )

    # Insert Activity Diagram Image
    add_image_box(
        r'c:\xampp\htdocs\jasa\activity_diagram_servisyuk.png',
        "Gambar 2.2: Activity Diagram Alur Bisnis End-to-End & Integrasi Fitur AI",
        width_in=6.1
    )

    add_heading_2("2.4 Class Diagram Berorientasi Objek (Prinsip SOLID)")
    add_para(
        "Class Diagram ServisYuk dibangun dengan mengedepankan prinsip rekayasa perangkat lunak modern "
        "(Single Responsibility, Open/Closed, Dependency Inversion). Kelas-kelas dibagi secara tegas ke dalam "
        "tiga lapisan: Controller (pengendali alur), Model (representasi entitas basis data), dan Core Services "
        "(layanan utilitas, keamanan, dan integrasi API pihak ketiga):"
    )

    # Insert Class Diagram Image
    add_image_box(
        r'c:\xampp\htdocs\jasa\class_diagram_servisyuk.png',
        "Gambar 2.3: Class Diagram Berorientasi Objek Aplikasi ServisYuk (Penerapan Prinsip SOLID)",
        width_in=6.1
    )

    add_para(
        "Komponen Inti pada Class Diagram mencakup:\n"
        "1. Core Database (Singleton): Menjamin hanya ada satu koneksi aktif PDO MySQL di seluruh siklus hidup aplikasi.\n"
        "2. AiConfigService: Mengelola enkripsi token API dua arah AES-256-CBC dan menyediakan katalog model multi-provider.\n"
        "3. FonnteService & OtpService: Menangani pemformatan nomor telepon internasional (+62) dan siklus kedaluwarsa kode OTP.\n"
        "4. Domain Models (ServiceRequest, ServiceReport, Diagnosis, User, Technician): Menangani aturan integritas data bisnis."
    )

    add_heading_2("2.5 Sequence Diagram Alur Pertukaran Pesan")
    add_para(
        "Sequence Diagram memperlihatkan interaksi kronologis pesan antar-komponen saat terjadi proses konsultasi AI "
        "dan pengajuan servis perbaikan:"
    )

    # Insert Sequence Diagram Image
    add_image_box(
        r'c:\xampp\htdocs\jasa\sequence_diagram_servisyuk.png',
        "Gambar 2.4: Sequence Diagram Interaksi Komponen (Frontend, Controller, Database, Microservice Node.js, AI API)",
        width_in=6.1
    )

    add_heading_2("2.6 Entity Relationship Diagram (ERD) Basis Data")
    add_para(
        "Skema basis data relasional ServisYuk dirancang untuk menjamin integritas data (ACID compliance) "
        "dengan relasi kunci asing (Foreign Keys) yang ketat antar-tabel:"
    )

    # Insert ERD Image
    add_image_box(
        r'c:\xampp\htdocs\jasa\erd_servisyuk.png',
        "Gambar 2.5: Entity Relationship Diagram (ERD) Basis Data Relasional ServisYuk",
        width_in=6.1
    )

    tbl_erd_desc = doc.add_table(rows=8, cols=3)
    tbl_erd_desc.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_erd_desc, color="CBD5E1")

    hdr_e = tbl_erd_desc.rows[0]
    for c_idx, h_text in enumerate(["Nama Tabel", "Kunci Utama & Relasi", "Deskripsi Fungsi Data"]):
        cell = hdr_e.cells[c_idx]
        set_cell_background(cell, BG_PRIMARY)
        set_cell_margins(cell, top=100, bottom=100, left=100, right=100)
        p = cell.paragraphs[0]
        r = p.add_run(h_text)
        style_run(r, size_pt=9.5, bold=True, color=RGBColor(255, 255, 255))

    erd_data = [
        ("users", "id (PK)", "Menyimpan data identitas pengguna, hashed password, peran (admin/customer/technician), dan status akun."),
        ("technicians", "id (PK), user_id (FK -> users.id)", "Menyimpan atribut keahlian khusus teknisi, sertifikasi, tahun pengalaman, dan availability."),
        ("services", "id (PK)", "Menyimpan katalog jenis peralatan elektronik rumah tangga, biaya dasar, dan ikon kategori."),
        ("service_requests", "id (PK), customer_id (FK), service_id (FK)", "Menyimpan data pesanan servis, kode booking unik, keluhan, alamat, foto, dan status pengerjaan."),
        ("technician_assignments", "id (PK), request_id (FK), technician_id (FK)", "Menghubungkan tiket servis dengan teknisi yang ditugaskan beserta timestamp penugasan."),
        ("diagnoses", "id (PK), request_id (FK)", "Menyimpan rekaman analisis gejala dari AI dan catatan pembanding dari teknisi lapangan."),
        ("service_reports", "id (PK), request_id (FK), technician_id (FK)", "Menyimpan laporan akhir pengerjaan, suku cadang yang diganti, catatan kendala, dan tanggal selesai.")
    ]
    for r_idx, (t_name, t_rel, t_desc) in enumerate(erd_data):
        row = tbl_erd_desc.rows[r_idx + 1]
        widths = [Inches(1.5), Inches(2.2), Inches(2.57)]
        for c_idx, text_val in enumerate([t_name, t_rel, t_desc]):
            cell = row.cells[c_idx]
            cell.width = widths[c_idx]
            set_cell_margins(cell, top=60, bottom=60, left=80, right=80)
            if r_idx % 2 == 1:
                set_cell_background(cell, BG_LIGHT)
            p = cell.paragraphs[0]
            r = p.add_run(text_val)
            style_run(r, size_pt=9, bold=(c_idx == 0), color=DARK_TEXT)

    doc.add_page_break()

    # ==========================================
    # BAB III: PERANCANGAN ANTARMUKA PENGGUNA (UI/UX FIGMA)
    # ==========================================
    add_heading_1("BAB III: PERANCANGAN ANTARMUKA PENGGUNA (UI/UX)")

    add_heading_2("3.1 Konsep Desain & Visual Design System")
    add_para(
        "Antarmuka ServisYuk dirancang dengan mengedepankan prinsip Human-Computer Interaction (HCI) yang intuitif, "
        "bersih, dan memancarkan rasa aman bagi pengguna yang sedang mengalami kepanikan akibat kerusakan alat rumah tangga:"
    )
    ui_rules = [
        ("Palet Warna Profesional", "Warna dominan menggunakan Royal Blue (#2563EB) dan Deep Navy (#0F172A) untuk mencerminkan keandalan dan profesionalisme teknologi; aksen Emerald Green (#10B981) digunakan untuk status sukses/selesai, serta Amber Orange (#F59E0B) untuk peringatan status pending."),
        ("Tipografi Terbaca Tinggi", "Menggunakan font keluarga sans-serif modern (Plus Jakarta Sans dan Inter) dengan hierarki visual kontras yang tegas antara judul, label, dan konten."),
        ("Micro-Interactions & Visual Feedback", "Setiap tindakan kritis (seperti submit form permohonan, penugasan teknisi, dan interaksi chat AI) dilengkapi loading spinner dan feedback toast notifikasi yang jelas.")
    ]
    for u_title, u_desc in ui_rules:
        p = add_para(space_before=2, space_after=3)
        r1 = p.add_run(f"• {u_title}: ")
        style_run(r1, bold=True, color=PRIMARY_COLOR)
        r2 = p.add_run(u_desc)
        style_run(r2, color=DARK_TEXT)

    add_heading_2("3.2 Prototipe Antarmuka Figma Komprehensif")
    add_para(
        "Seluruh artboard prototipe antarmuka sistem telah dimodelkan ke dalam vektor SVG berkualitas tinggi "
        "yang siap diimpor langsung ke canvas kerja Figma (berkas servisyuk_figma_all_in_one.svg):"
    )

    # Insert UI UX Figma Preview Image
    add_image_box(
        r'c:\xampp\htdocs\jasa\ui_ux_servisyuk_figma_preview.png',
        "Gambar 3.1: Prototipe Desain UI/UX Lengkap ServisYuk (Landing Page, AI Chatbot, Portal Pelanggan, Teknisi & Admin)",
        width_in=6.1
    )

    add_para(
        "Rincian Artboard Prototipe Antarmuka yang Dikembangkan:\n"
        "1. Landing Page Publik: Menampilkan hero section interaktif, keunggulan perbaikan berbasis AI, katalog kategori peralatan rumah tangga, dan ulasan kepuasan pelanggan.\n"
        "2. AI Diagnosis Wizard & Live Chatbot: Antarmuka percakapan real-time yang memandu pengguna mengidentifikasi kerusakan alat dengan opsi quick suggestions.\n"
        "3. Customer Portal & Service Timeline: Halaman pelacakan status pesanan perbaikan dengan visual progress tracker 5 tahapan pengerjaan teknisi.\n"
        "4. Admin Operations Dashboard: Panel kontrol operasional untuk memverifikasi pesanan servis baru, memilih dan menugaskan teknisi, serta monitoring metrik pendapatan.\n"
        "5. AI Provider Configuration Console: Panel kontrol admin untuk mengelola API Key penyedia AI, memilih model aktif, dan melakukan test konektivitas.\n"
        "6. Technician Field Portal: Antarmuka berbasis ponsel pintar bagi teknisi untuk melihat lokasi pelanggan, navigasi GPS, dan pengisian formulir laporan pekerjaan."
    )

    doc.add_page_break()

    # ==========================================
    # BAB IV: IMPLEMENTASI KODE & UNIT TESTING
    # ==========================================
    add_heading_1("BAB IV: IMPLEMENTASI KODE & AUTOMATED UNIT TESTING")

    add_heading_2("4.1 Struktur Direktori & Clean Code")
    add_para(
        "Kode sumber project ServisYuk disusun dengan struktur direktori yang rapi sesuai standar industri "
        "perangkat lunak. Setiap berkas memiliki tanggung jawab terisolasi (Separation of Concerns):"
    )

    p_code = add_para(
        "jasa/\n"
        "├── .github/workflows/ci.yml   # Konfigurasi Continuous Integration (GitHub Actions)\n"
        "├── assets/                    # Lembar gaya CSS, script JavaScript, dan image statis\n"
        "├── chatbot/                   # Microservice AI Chatbot Node.js Express (port 5005)\n"
        "│   ├── server.js              # Proxy inferensi LLM multi-provider & penanganan fallback\n"
        "│   ├── ai_config.json         # Konfigurasi aktif model dan provider tersinkronisasi\n"
        "│   └── database_knowledge.json# Basis pengetahuan lokal panduan perbaikan cepat\n"
        "├── config/                    # Pengaturan database MySQL, mailer, dan environment\n"
        "├── controllers/               # Controller MVC (Admin, Auth, Customer, Teknisi, Api)\n"
        "├── core/                      # Utilitas inti (Database PDO, Helper, Fonnte, AiConfig, Otp)\n"
        "├── database/                  # Skrip DDL schema.sql dan DML seeders.sql\n"
        "├── models/                    # Model entitas bisnis (User, ServiceRequest, Report, dll.)\n"
        "├── tests/                     # Automated Test Suite (Unit Testing & Test Runner)\n"
        "│   ├── bootstrap.php          # Inisialisasi lingkungan pengujian terisolasi\n"
        "│   ├── run_tests.php          # Test Runner mandiri tanpa dependensi luar\n"
        "│   ├── HelperTest.php         # Pengujian fungsi utilitas dan helper\n"
        "│   ├── FonnteServiceTest.php  # Pengujian normalisasi nomor WhatsApp\n"
        "│   └── AiConfigServiceTest.php# Pengujian enkripsi AES-256 dan preset provider AI\n"
        "├── views/                     # Template antarmuka HTML/PHP modular per role\n"
        "├── composer.json              # Definisi dependensi dan autoloading PHPUnit\n"
        "├── phpunit.xml                # Konfigurasi resmi runner PHPUnit\n"
        "└── README.md                  # Dokumentasi resmi dan petunjuk instalasi sistem",
        line_spacing=1.05
    )
    p_code.paragraph_format.left_indent = Inches(0.2)
    for run in p_code.runs:
        style_run(run, font_name='Consolas', size_pt=8.5, color=DARK_TEXT)

    add_heading_2("4.2 Rangkaian Pengujian Unit Otomatis (Unit Testing Suite)")
    add_para(
        "Untuk memastikan keandalan fungsi-fungsi inti, dikembangkan 22 skenario pengujian unit terotomatisasi "
        "yang dapat dieksekusi baik menggunakan runner mandiri maupun melalui PHPUnit resmi:"
    )

    tbl_tests = doc.add_table(rows=4, cols=3)
    tbl_tests.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_tests, color="CBD5E1")

    hdr_t = tbl_tests.rows[0]
    for c_idx, h_text in enumerate(["Berkas Pengujian", "Fungsi yang Diuji", "Skenario Kasus Uji & Verifikasi"]):
        cell = hdr_t.cells[c_idx]
        set_cell_background(cell, BG_PRIMARY)
        set_cell_margins(cell, top=100, bottom=100, left=100, right=100)
        p = cell.paragraphs[0]
        r = p.add_run(h_text)
        style_run(r, size_pt=9.5, bold=True, color=RGBColor(255, 255, 255))

    tests_info = [
        (
            "HelperTest.php\n(11 Skenario)",
            "Helper::formatRupiah\nHelper::formatDateIndo\nHelper::getStatusInfo\nHelper::generateBookingCode\nHelper::sanitize",
            "1. Format mata uang angka 0, ribuan, dan string numeric.\n2. Konversi tanggal Indo dengan/tanpa penanda waktu WIB.\n3. Pemetaan status alur kerja (pending, inprogress, completed).\n4. Format kode unik booking (SRV-YYYYMMDD-XXXX) dan invoice.\n5. Filtrasi karakter berbahaya pencegah serangan XSS."
        ),
        (
            "FonnteServiceTest.php\n(6 Skenario)",
            "FonnteService::formatPhoneNumber\nFonnteService::maskPhoneNumber",
            "1. Konversi format nomor lokal 08... ke standar 628...\n2. Penanganan awalan +62 dan nomor tanpa angka nol depan (8...).\n3. Pembersihan spasi dan tanda hubung pemisah nomor.\n4. Penolakan nomor palsu (terlalu pendek atau nomor rumah).\n5. Masking digit nomor telepon untuk log audit yang aman (62812*****890)."
        ),
        (
            "AiConfigServiceTest.php\n(5 Skenario)",
            "AiConfigService::encryptSecret\nAiConfigService::decryptSecret\nAiConfigService::PROVIDER_PRESETS",
            "1. Validasi keberadaan katalog provider resmi (Groq, Gemini, NVIDIA, OpenAI).\n2. Enkripsi dan dekripsi bolak-balik (round-trip) token API menggunakan AES-256-CBC.\n3. Uji keacakan IV (dua kali enkripsi string sama menghasilkan ciphertext berbeda).\n4. Penanganan dekripsi terhadap token kosong atau ciphertext rusak."
        )
    ]
    for r_idx, (fn, fu, sc) in enumerate(tests_info):
        row = tbl_tests.rows[r_idx + 1]
        widths = [Inches(1.8), Inches(2.1), Inches(2.37)]
        for c_idx, text_val in enumerate([fn, fu, sc]):
            cell = row.cells[c_idx]
            cell.width = widths[c_idx]
            set_cell_margins(cell, top=80, bottom=80, left=80, right=80)
            if r_idx % 2 == 1:
                set_cell_background(cell, BG_LIGHT)
            p = cell.paragraphs[0]
            r = p.add_run(text_val)
            style_run(r, size_pt=9, bold=(c_idx == 0), color=DARK_TEXT)

    add_heading_2("4.3 Hasil Eksekusi Unit Test Lokal (100% Lolos Uji)")
    add_para(
        "Pengujian unit dieksekusi secara lokal pada terminal pengembangan menggunakan skrip runner mandiri "
        "tests/run_tests.php dengan PHP 8.2 bawaan XAMPP. Seluruh 22 skenario pengujian berhasil dilalui dengan sempurna:"
    )

    p_log = add_para(
        "=========================================================\n"
        "   ServisYuk Automated Unit Test Suite\n"
        "   PHP Version: 8.2.12 | Execution Date: 2026-11-18\n"
        "=========================================================\n\n"
        "Running Suite: AiConfigServiceTest\n"
        "  [PASS] testProviderPresetsContainEssentialProviders\n"
        "  [PASS] testDefaultModelsListIsNotEmpty\n"
        "  [PASS] testEncryptAndDecryptSecretRoundTrip\n"
        "  [PASS] testEncryptionRandomIvProducesDifferentOutputs\n"
        "  [PASS] testDecryptEmptyOrInvalidSecret\n\n"
        "Running Suite: FonnteServiceTest\n"
        "  [PASS] testFormatPhoneNumberStandard08\n"
        "  [PASS] testFormatPhoneNumberInternationalPlus62\n"
        "  [PASS] testFormatPhoneNumberLeading8\n"
        "  [PASS] testFormatPhoneNumberWithDashesAndSpaces\n"
        "  [PASS] testFormatPhoneNumberInvalidInputs\n"
        "  [PASS] testMaskPhoneNumber\n\n"
        "Running Suite: HelperTest\n"
        "  [PASS] testFormatRupiahWithStandardAmount\n"
        "  [PASS] testFormatRupiahWithZeroAndNull\n"
        "  [PASS] testFormatRupiahWithStringNumeric\n"
        "  [PASS] testFormatDateIndoWithValidDate\n"
        "  [PASS] testFormatDateIndoWithTime\n"
        "  [PASS] testFormatDateIndoWithEmptyOrNull\n"
        "  [PASS] testGetStatusInfoReturnsExpectedArrayStructure\n"
        "  [PASS] testGetStatusInfoDifferentStatuses\n"
        "  [PASS] testGenerateBookingCodeFormat\n"
        "  [PASS] testGenerateInvoiceNumberFormat\n"
        "  [PASS] testSanitizeInput\n\n"
        "=========================================================\n"
        "Ringkasan Hasil Pengujian (Test Summary):\n"
        "  Total Pengujian : 22\n"
        "  Berhasil (PASS) : 22\n"
        "  Gagal (FAIL)    : 0\n"
        "  Durasi Eksekusi : 5.67 ms\n"
        "=========================================================\n"
        "HASIL: SUKSES (Semua unit test berhasil 100%)",
        line_spacing=1.05
    )
    p_log.paragraph_format.left_indent = Inches(0.2)
    for run in p_log.runs:
        style_run(run, font_name='Consolas', size_pt=8.5, color=RGBColor(5, 150, 105) if "SUKSES" in run.text else DARK_TEXT)

    doc.add_page_break()

    # ==========================================
    # BAB V: PENERAPAN CI/CD GITHUB ACTIONS & BUKTI PENGUJIAN
    # ==========================================
    add_heading_1("BAB V: PENERAPAN CI/CD GITHUB ACTIONS & BUKTI PENGUJIAN")

    add_heading_2("5.1 Arsitektur Pipeline CI/CD (.github/workflows/ci.yml)")
    add_para(
        "Sebagai implementasi praktik rekayasa perangkat lunak modern (DevOps), ServisYuk dilengkapi dengan "
        "Continuous Integration Pipeline menggunakan GitHub Actions. Pipeline ini secara otomatis dipicu "
        "setiap kali ada instruksi push kode baru atau pengajuan Pull Request ke branch utama (main):"
    )

    add_para(
        "Tahapan Eksekusi Pipeline Terdiri dari 3 Pekerjaan (Jobs) Utama:\n"
        "1. Job 1: PHP Syntax Check (Lint) — Memindai seluruh berkas kode PHP dalam folder app, core, config, dan tests menggunakan perintah 'php -l' untuk memastikan tidak ada kesalahan kompilasi sintaks.\n"
        "2. Job 2: PHPUnit Unit Testing — Mempersiapkan runtime PHP 8.2 terisolasi pada server Ubuntu GitHub, mengaktifkan ekstensi yang diperlukan (pdo, mbstring, curl, openssl), dan mengeksekusi ke-22 skenario pengujian unit secara otomatis.\n"
        "3. Job 3: Chatbot Microservice Validation — Memvalidasi integritas kode server Node.js (node --check chatbot/server.js) dan memastikan file basis pengetahuan AI (JSON) bebas dari kesalahan parsing."
    )

    add_heading_2("5.2 Bukti Repositori GitHub & Halaman Dokumentasi")
    add_para(
        "Seluruh kode sumber, dokumentasi PRD, diagram arsitektur, dan berkas CI/CD telah diunggah secara publik "
        "pada repositori GitHub resmi: https://github.com/mahwakhlif/ServisYuk."
    )

    # Insert Image 1: GitHub Repo & README
    add_image_box(
        r'c:\xampp\htdocs\jasa\docs_assets\github_repo_readme.png',
        "Gambar 5.1: Tampilan Repositori GitHub Resmi ServisYuk beserta Badge Status CI/CD Aktif",
        width_in=6.1
    )

    add_heading_2("5.3 Bukti Hasil Eksekusi Workflow Runs (Status Hijau)")
    add_para(
        "Gambar berikut memperlihatkan riwayat eksekusi workflow pipeline pada tab Actions repositori GitHub. "
        "Seluruh komit pemicu berhasil menyelesaikan pengujian dengan status hijau (Success) tanpa ada kegagalan:"
    )

    # Insert Image 2: GitHub Actions Runs List
    add_image_box(
        r'c:\xampp\htdocs\jasa\docs_assets\github_actions_runs.png',
        "Gambar 5.2: Riwayat Workflow Runs GitHub Actions ServisYuk Berstatus Hijau (Success)",
        width_in=6.1
    )

    add_heading_2("5.4 Bukti Rincian Pekerjaan Pipeline (Zero Failure / 100% Success)")
    add_para(
        "Gambar 5.3 memperlihatkan detail eksekusi live dari ServisYuk CI Pipeline (Run #44136942303). "
        "Ketiga pekerjaan (PHP Syntax Linting, Chatbot Microservice Validation, dan PHPUnit Unit Testing) "
        "selesai dalam total durasi kilat hanya 24 detik:"
    )

    # Insert Image 3: Detailed CI Success Run
    add_image_box(
        r'c:\xampp\htdocs\jasa\docs_assets\github_actions_ci_success.png',
        "Gambar 5.3: Rincian Eksekusi Sukses Job 1, 2, dan 3 pada ServisYuk CI Pipeline (Durasi: 24 Detik)",
        width_in=6.1
    )

    tbl_ci_summary = doc.add_table(rows=4, cols=4)
    tbl_ci_summary.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_ci_summary, color="CBD5E1")

    hdr_c = tbl_ci_summary.rows[0]
    for c_idx, h_text in enumerate(["Tahap / Job CI", "Waktu Eksekusi", "Status", "Hasil Evaluasi Kualitas"]):
        cell = hdr_c.cells[c_idx]
        set_cell_background(cell, BG_PRIMARY)
        set_cell_margins(cell, top=100, bottom=100, left=100, right=100)
        p = cell.paragraphs[0]
        r = p.add_run(h_text)
        style_run(r, size_pt=9.5, bold=True, color=RGBColor(255, 255, 255))

    ci_job_data = [
        ("1. PHP Syntax Check (Lint)", "8 Detik", "🟢 SUCCESS", "18 berkas PHP dipindai; 0 syntax error detected."),
        ("3. Chatbot Microservice Validation", "11 Detik", "🟢 SUCCESS", "Sintaks server.js lolos verifikasi Node.js; 2 file JSON valid."),
        ("2. PHPUnit Unit Testing", "11 Detik", "🟢 SUCCESS", "Ke-22 pengujian unit berhasil lolos 100% pada lingkungan PHP 8.2.")
    ]
    for r_idx, (j_name, j_dur, j_stat, j_res) in enumerate(ci_job_data):
        row = tbl_ci_summary.rows[r_idx + 1]
        widths = [Inches(2.2), Inches(1.1), Inches(1.2), Inches(1.77)]
        for c_idx, text_val in enumerate([j_name, j_dur, j_stat, j_res]):
            cell = row.cells[c_idx]
            cell.width = widths[c_idx]
            set_cell_margins(cell, top=80, bottom=80, left=80, right=80)
            if r_idx % 2 == 1:
                set_cell_background(cell, BG_LIGHT)
            p = cell.paragraphs[0]
            r = p.add_run(text_val)
            style_run(r, size_pt=9, bold=(c_idx in [0, 2]), color=RGBColor(5, 150, 105) if c_idx == 2 else DARK_TEXT)

    doc.add_page_break()

    # ==========================================
    # BAB VI: KESIMPULAN, REFLEKSI & SARAN
    # ==========================================
    add_heading_1("BAB VI: KESIMPULAN & REFLEKSI PEMBELAJARAN")

    add_heading_2("6.1 Kesimpulan Capaian Proyek Mandiri")
    add_para(
        "Berdasarkan seluruh tahapan perancangan, pengembangan, pengujian, dan otomatisasi yang telah dilakukan, "
        "dapat disimpulkan bahwa proyek mandiri ServisYuk telah berhasil diselesaikan secara utuh (100% Full End-to-End Build) "
        "sesuai dengan seluruh ketentuan Rubrik Penilaian OBE mata kuliah Rekayasa Perangkat Lunak:"
    )
    conclusions = [
        "Penyusunan PRD/SRS Mandiri telah memetakan kebutuhan nyata masyarakat terhadap transparansi jasa perbaikan elektronik, mendefinisikan 15 kebutuhan fungsional (FR) dan spesifikasi arsitektur AI Multi-Provider.",
        "Pemodelan Arsitektur UML (Use Case, Activity, Class, Sequence) dan ERD basis data telah dibangun dengan presisi, konsisten antar-diagram, serta mematuhi kaidah rekayasa perangkat lunak berorientasi objek dan prinsip SOLID.",
        "Rancangan Antarmuka UI/UX Figma telah menghasilkan kit desain interaktif yang manusiawi, memudahkan pengguna dalam berkonsultasi dengan AI maupun melacak pengerjaan servis.",
        "Integrasi Fitur AI real-time beroperasi stabil dengan latensi ultra-cepat melalui pemanfaatan Groq LPU, didukung sistem fallback Google Gemini serta enkripsi token keamanan AES-256-CBC.",
        "Otomatisasi Pengujian & DevOps berhasil diwujudkan melalui 22 skenario Automated Unit Testing dan CI/CD Pipeline GitHub Actions yang berjalan secara otomatis dengan status hijau (Success) dalam waktu 24 detik."
    ]
    for c in conclusions:
        p = add_para(space_before=2, space_after=3)
        r = p.add_run(f"1. {c}" if c == conclusions[0] else f"{conclusions.index(c)+1}. {c}")
        style_run(r, color=DARK_TEXT)

    add_heading_2("6.2 Refleksi Pembelajaran Rekayasa Perangkat Lunak (OBE)")
    add_para(
        "Pengerjaan proyek mandiri ini memberikan pengalaman praktis yang sangat berharga dalam memahami siklus "
        "hidup pengembangan perangkat lunak (SDLC) seutuhnya. Penulis tidak hanya dituntut untuk menulis baris kode, "
        "tetapi juga bertindak sebagai System Analyst dalam merumuskan kebutuhan, Software Architect dalam memodelkan UML, "
        "UI/UX Designer dalam merancang pengalaman pengguna, hingga DevOps Engineer dalam mengonfigurasi otomatisasi CI/CD. "
        "Kemampuan menyelesaikan tantangan teknis—seperti penanganan failover model AI dan isolasi unit testing—"
        "menjadi bukti kematangan kompetensi rekayasa perangkat lunak yang selaras dengan profil lulusan Teknik Informatika."
    )

    add_heading_2("6.3 Saran Pengembangan Lanjutan")
    add_para(
        "Untuk pengembangan di masa depan, sistem ServisYuk dapat ditingkatkan dengan beberapa inisiatif berikut:\n"
        "1. Integrasi Multimodal Vision AI: Memungkinkan pelanggan mengunggah foto komponen yang terbakar atau mengeluarkan asap untuk langsung dianalisis oleh model Computer Vision.\n"
        "2. Geolocation Tracking Real-Time: Pelacakan posisi koordinat teknisi secara live di peta digital saat berstatus 'Dalam Perjalanan'.\n"
        "3. Payment Gateway Otomatis: Integrasi dengan gerbang pembayaran digital (Midtrans / Xendit) untuk pembayaran non-tunai yang aman melalui escrow account."
    )

    doc.add_page_break()

    # ==========================================
    # LAMPIRAN: PANDUAN SETUP & KREDENSIAL
    # ==========================================
    add_heading_1("LAMPIRAN: PANDUAN INSTALASI & KREDENSIAL")

    add_heading_2("A. Petunjuk Instalasi Aplikasi (Setup Instructions)")
    add_para(
        "1. Kloning Repositori GitHub:\n"
        "   git clone https://github.com/mahwakhlif/ServisYuk.git\n"
        "   (Letakkan di direktori c:\\xampp\\htdocs\\jasa)\n\n"
        "2. Konfigurasi Variabel Lingkungan (.env):\n"
        "   Salin file .env.example menjadi .env, sesuaikan pengaturan kredensial database lokal:\n"
        "   DB_HOST=127.0.0.1, DB_PORT=3306, DB_DATABASE=db_jasa_perbaikan, DB_USERNAME=root, DB_PASSWORD=\n\n"
        "3. Impor Struktur & Data Basis Data:\n"
        "   Buka phpMyAdmin (http://localhost/phpmyadmin), buat database baru bernama 'db_jasa_perbaikan'.\n"
        "   Impor berkas database/schema.sql dan database/seeders.sql secara berurutan.\n\n"
        "4. Menjalankan Server Aplikasi:\n"
        "   - Pastikan modul Apache dan MySQL pada XAMPP Control Panel aktif (Running).\n"
        "   - Buka terminal pada folder chatbot, lalu jalankan: 'node server.js' (atau klik ganda 'run_chatbot.bat').\n\n"
        "5. Menjalankan Automated Unit Testing Mandiri:\n"
        "   Buka terminal pada root project, jalankan: 'C:\\xampp\\php\\php.exe tests/run_tests.php'\n\n"
        "6. Akses Antarmuka Web di Browser:\n"
        "   Buka tautan: http://localhost/jasa"
    )

    add_heading_2("B. Kredensial Akun Pengujian Bawaan (Default Seeded Accounts)")
    tbl_seed = doc.add_table(rows=4, cols=4)
    tbl_seed.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_seed, color="CBD5E1")

    hdr_s = tbl_seed.rows[0]
    for c_idx, h_text in enumerate(["Peran Pengguna (Role)", "Alamat Email", "Kata Sandi", "Hak Akses & Area Dashboard"]):
        cell = hdr_s.cells[c_idx]
        set_cell_background(cell, BG_PRIMARY)
        set_cell_margins(cell, top=100, bottom=100, left=100, right=100)
        p = cell.paragraphs[0]
        r = p.add_run(h_text)
        style_run(r, size_pt=9.5, bold=True, color=RGBColor(255, 255, 255))

    seed_data = [
        ("Administrator", "admin@servisyuk.com", "admin123", "Dashboard Admin, Kelola Teknisi/Jasa, Tiket Servis & Konfigurasi AI"),
        ("Teknisi Lapangan", "budi.teknisi@servisyuk.com", "teknisi123", "Portal Teknisi, Terima Penugasan, Update Status & Input Laporan"),
        ("Pelanggan (Customer)", "pelanggan@gmail.com", "customer123", "Portal Pelanggan, AI Diagnosis, Ajukan Booking & Tracking Status")
    ]
    for r_idx, (role, em, pw, hak) in enumerate(seed_data):
        row = tbl_seed.rows[r_idx + 1]
        widths = [Inches(1.5), Inches(1.8), Inches(1.1), Inches(2.07)]
        for c_idx, text_val in enumerate([role, em, pw, hak]):
            cell = row.cells[c_idx]
            cell.width = widths[c_idx]
            set_cell_margins(cell, top=80, bottom=80, left=80, right=80)
            if r_idx % 2 == 1:
                set_cell_background(cell, BG_LIGHT)
            p = cell.paragraphs[0]
            r = p.add_run(text_val)
            style_run(r, size_pt=9, bold=(c_idx == 0), color=DARK_TEXT)

    # Simpan Berkas Laporan Word
    output_filename = r'c:\xampp\htdocs\jasa\LAPORAN_PROYEK_AKHIR_MANDIRI_RPL_SERVISYUK.docx'
    doc.save(output_filename)
    print(f"Laporan berhasil dibuat dan disimpan di: {output_filename}")

if __name__ == '__main__':
    create_report()
