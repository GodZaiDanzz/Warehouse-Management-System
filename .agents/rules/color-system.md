# Rules Sistem Palet Warna Maksimal 3 Warna
**Proyek:** Warehouse Management System (WMS) - PT Mudalaya Energy Indonesia

Sistem desain website ini mengadopsi prinsip harmoni warna **60 - 30 - 10** dengan batasan ketat **maksimal 3 rumpun warna utama** untuk menjaga konsistensi, profesionalisme, dan identitas perusahaan EPC & Energi.

---

## 1. Aturan Tiga Warna Utama (The 3-Color Hierarchy)

### **Warna 1: Netral / Grayscale (60% - Fondasi Struktur & Konten)**
Digunakan untuk latar belakang halaman, kartu, modal, batas garis (border), dan hierarki tipografi.
- **Light Mode:**
  - Background Utama: `#F9FAFB` (Gray-50)
  - Card & Surface: `#FFFFFF` (White)
  - Border & Garis: `#E4E7EC` (Gray-200) / `#D0D5DD` (Gray-300)
  - Teks Utama: `#101828` (Gray-900)
  - Teks Sekunder/Muted: `#667085` (Gray-500)
- **Dark Mode:**
  - Background Utama: `#0C111D` (Gray-950)
  - Card & Surface: `#1D2939` (Gray-800) / `#101828` (Gray-900)
  - Border & Garis: `#344054` (Gray-700)
  - Teks: `#F9FAFB` (White/90)

---

### **Warna 2: Corporate Forest Green (`#275538`) (30% - Identitas Merek & Aksi Utama)**
Warna resmi identitas PT Mudalaya Energy Indonesia. Menjadi warna primer yang dominan pada komponen operasional.
- **Penerapan Wajib:**
  1. Header Tabel (`table thead`, `.theme-table-header`)
  2. Tombol Utama (Tombol "Tambah", "Simpan", "Submit")
  3. Indikator Sidebar Aktif (`menu-item-active`, ikon aktif)
  4. Brand Icon & Logo Container
  5. Primary State (Checkbox checked, Radio active, Fokus border)
- **Toleransi Shade:** Hanya diperbolehkan variasi kecerahan/opasitas dari `#275538` (contoh: `#1c3e29` saat hover/active, atau opasitas rendah `rgba(39, 85, 56, 0.1)` untuk latar badge aktif).

---

### **Warna 3: Energy Amber / Gold (`#F59E0B` / `#D97706`) (10% - Aksen & Sorotan)**
Warna aksen kontras yang melambangkan sektor energi dan memberikan titik fokus mata (focal point).
- **Penerapan:**
  1. Banner Khusus / Pengumuman Penting (misal: Banner Laporan Consumable)
  2. Badge Khusus ("New", "Pro", "Pending")
  3. Tombol Aksi Sekunder / Filter / Export (jika ingin dibedakan dari tombol primer)
  4. Highlight grafik atau metrik yang memerlukan perhatian pengguna

---

## 2. Aturan Khusus untuk Indikator Status & Chart

1. **Status Fungsional (Success / Warning / Critical):**
   - Dilarang membuat blok card berwarna warni (misal: merah menyala, ungu, pink, toska secara bersamaan).
   - Gunakan indikator mikro: Badge dengan latar transparan lembut atau teks berwarna pada angka tanpa mengubah dominasi halaman.
   - Status Kritis (Stok Habis): Gunakan Red Soft (`#DC2626`) **hanya** pada teks status/angka atau dot kecil.

2. **ApexCharts & Visualisasi Data:**
   - Dilarang menggunakan palet pelangi (> 5 warna berbeda).
   - Gunakan skema monokromatik Forest Green dengan variasi intensitas (shade `#275538`, `#3d7a53`, `#62a67a`), dipadukan dengan Aksen Amber (`#F59E0B`) dan Netral Gray (`#98A2B3`).

3. **Konsistensi Tombol & Form:**
   - Semua tombol aksi (Tambah, Simpan, Update) harus merujuk pada variabel CSS `--theme-btn-add-bg` dan `--theme-btn-save-bg` yang sinkron dengan `--theme-table-header-bg` (`#275538`).
   - Hilangkan penggunaan kelas warna liar seperti `bg-brand-600` (biru template), `bg-indigo-600`, atau `bg-purple-600`.
