# Planning: Penyesuaian Halaman Surat Masuk Berdasarkan Template `eco-product-list`

Dokumen ini berisi rancangan dan panduan teknis implementasi untuk memperbarui tampilan halaman **Surat Masuk** pada aplikasi **In-HC** dengan mengadopsi layout dan gaya visual dari template:
`template_frontend/package/html/main/eco-product-list.html`.

---

## 1. Ringkasan Kebutuhan & Perubahan

1. **Adopsi Tampilan Baris Tabel (`eco-product-list.html`)**:
   - Mengubah layout baris tabel Surat Masuk dari tabel standar menjadi desain modern ala *Product List*.
   - **Perihal & Nomor Surat**: Menggantikan kolom *Products*. Menampilkan ikon dokumen di kiri, judul **Perihal** (teks tebal/fs-4), dan **Nomor Surat** di bawahnya (teks sekunder/fs-3).
   - **Tanggal Surat**: Menggantikan kolom *Date*. Menampilkan tanggal surat yang diformat rapi (`DD MMMM YYYY`).
   - **Status Disposisi**: Menggantikan kolom *Status*. Menggunakan indikator dot / badge status (*Sudah Disposisi* warna hijau, *Belum Disposisi* warna kuning/oranye).
   - **Pengirim**: Menggantikan kolom *Price*. Menampilkan identitas pengirim surat.
   - **Aksi**: Menggantikan kolom *Actions*. Menggunakan tombol dropdown / action menu dengan ikon `ti ti-dots-vertical` atau tombol aksi praktis (Detail, Edit, Hapus).

2. **Fitur Filter Lanjutan**:
   - **Rentang Tanggal (`Date Range`)**: Filter data berdasarkan `tanggal_surat` (Tanggal Awal & Tanggal Akhir).
   - **Status Disposisi**: Filter data berdasarkan status (*Semua*, *Sudah Disposisi*, *Belum Disposisi*).
   - **Pencarian Cepat**: Input search real-time terintegrasi di atas tabel.

3. **Integrasi Server-Side (Backend)**:
   - Seluruh data tabel, pagination, pencarian, dan filter dieksekusi secara server-side melalui endpoint `POST /api/surat-masuk/datatables`.

---

## 2. Struktur Desain UI (Berdasarkan `eco-product-list.html`)

### 2.1 Header & Filter Bar
```html
<div class="card">
  <div class="card-body p-4">
    <!-- Header: Search, Filter Toggle, dan Tambah Data -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="position-relative">
          <input type="text" class="form-control py-2 ps-5" placeholder="Cari surat..." v-model="searchQuery" @input="onSearchInput" />
          <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
        </div>
        <button class="btn btn-outline-secondary d-flex align-items-center gap-1" @click="toggleFilter">
          <i class="ti ti-filter"></i> Filter
        </button>
      </div>
      <button class="btn btn-primary d-flex align-items-center gap-1" @click="openModal()">
        <i class="ti ti-plus"></i> Tambah Surat Masuk
      </button>
    </div>

    <!-- Collapsible Filter Panel -->
    <div v-if="showFilter" class="card bg-light border-0 mb-4 p-3 rounded-3">
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fs-3 fw-semibold">Tanggal Awal</label>
          <input type="date" class="form-control" v-model="filter.startDate" />
        </div>
        <div class="col-md-3">
          <label class="form-label fs-3 fw-semibold">Tanggal Akhir</label>
          <input type="date" class="form-control" v-model="filter.endDate" />
        </div>
        <div class="col-md-3">
          <label class="form-label fs-3 fw-semibold">Status Disposisi</label>
          <select class="form-select" v-model="filter.statusDisposisi">
            <option value="">Semua Status</option>
            <option value="sudah">Sudah Disposisi</option>
            <option value="belum">Belum Disposisi</option>
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-primary w-100" @click="applyFilter">Terapkan</button>
          <button class="btn btn-outline-dark w-100" @click="resetFilter">Reset</button>
        </div>
      </div>
    </div>

    <!-- Tabel Surat Masuk -->
    <div class="table-responsive border rounded">
      <table id="suratMasukTable" class="table align-middle text-nowrap mb-0 w-100">
        <thead>
          <tr>
            <th scope="col">Perihal & Nomor Surat</th>
            <th scope="col">Tanggal Surat</th>
            <th scope="col">Status Disposisi</th>
            <th scope="col">Pengirim</th>
            <th scope="col" class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <!-- DataTables Server-Side Rendering -->
        </tbody>
      </table>
    </div>
  </div>
</div>
```

---

## 3. Spesifikasi Kolom DataTables

| No | Kolom | Properti Data | Render Output (Template Style) |
|---|---|---|---|
| 1 | **Perihal & Nomor Surat** | `perihal`, `nomor_surat` | Icon dokumen dalam box rounded + Judul Perihal (`fw-semibold fs-4`) + Nomor Surat (`text-muted fs-3`) di baris kedua. |
| 2 | **Tanggal Surat** | `tanggal_surat` | Format lokal Indonesia (contoh: `12 Januari 2026`). |
| 3 | **Status Disposisi** | `disposisi_exists` | Dot status warna + Teks: <br>• **Sudah Disposisi**: `<span class="bg-success p-1 rounded-circle d-inline-block me-2"></span> Sudah Disposisi`<br>• **Belum Disposisi**: `<span class="bg-warning p-1 rounded-circle d-inline-block me-2"></span> Belum Disposisi` |
| 4 | **Pengirim** | `pengirim` | `<h6 class="mb-0 fs-4 fw-medium text-dark">${pengirim}</h6>` |
| 5 | **Aksi** | `id` | Dropdown action button (`ti ti-dots-vertical`) atau group tombol aksi (Detail, Edit, Hapus). |

---

## 4. Penyesuaian Backend (Laravel API)

### File: `backend/app/Http/Controllers/SuratMasukController.php`

Update method `datatables(Request $request)` untuk mendukung parameter filter tanggal dan status disposisi:

```php
public function datatables(Request $request)
{
    $query = SuratMasuk::query()->withExists('disposisi');

    // 1. Filter Rentang Tanggal Surat
    if ($request->filled('start_date')) {
        $query->whereDate('tanggal_surat', '>=', $request->input('start_date'));
    }
    if ($request->filled('end_date')) {
        $query->whereDate('tanggal_surat', '<=', $request->input('end_date'));
    }

    // 2. Filter Status Disposisi
    if ($request->filled('status_disposisi')) {
        $status = $request->input('status_disposisi');
        if ($status === 'sudah') {
            $query->has('disposisi');
        } elseif ($status === 'belum') {
            $query->doesntHave('disposisi');
        }
    }

    // Count total records
    $recordsTotal = SuratMasuk::count();
    
    // 3. Global Search
    $searchValue = $request->input('search.value');
    if (!empty($searchValue)) {
        $search = strtolower($searchValue);
        $query->where(function($q) use ($search) {
            $q->whereRaw('LOWER(nomor_surat) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(perihal) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(pengirim) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(tujuan) LIKE ?', ["%{$search}%"]);
        });
    }

    $recordsFiltered = $query->count();

    // 4. Sorting & Pagination
    $order = $request->input('order');
    $columns = $request->input('columns');
    if (!empty($order) && !empty($columns)) {
        foreach ($order as $o) {
            $columnIndex = $o['column'];
            $dir = $o['dir'];
            $columnName = $columns[$columnIndex]['data'] ?? null;
            if ($columnName && $columnName !== 'null') {
                $query->orderBy($columnName, $dir);
            }
        }
    } else {
        $query->orderBy('tanggal_surat', 'desc');
    }

    $start = $request->input('start', 0);
    $length = $request->input('length', 10);
    if ($length > 0) {
        $query->offset($start)->limit($length);
    }

    $data = $query->get();

    return response()->json([
        'draw' => intval($request->input('draw', 1)),
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data
    ]);
}
```

---

## 5. Penyesuaian Frontend (Vue 3)

### File: `frontend/src/views/SuratMasuk.vue`

1. **State Reactive untuk Filter**:
   ```javascript
   const showFilter = ref(false)
   const filter = ref({
     startDate: '',
     endDate: '',
     statusDisposisi: ''
   })
   ```

2. **Payload AJAX DataTables**:
   Kirim parameter filter ke backend saat DataTables memanggil API:
   ```javascript
   ajax: async function (data, callback, settings) {
     const payload = {
       ...data,
       start_date: filter.value.startDate,
       end_date: filter.value.endDate,
       status_disposisi: filter.value.statusDisposisi
     }
     const response = await api.post('/surat-masuk/datatables', payload)
     callback({
       draw: response.data.draw,
       recordsTotal: response.data.recordsTotal,
       recordsFiltered: response.data.recordsFiltered,
       data: response.data.data
     })
   }
   ```

3. **Render Kolom Bergaya `eco-product-list`**:
   - Kolom 1 (*Perihal & Nomor Surat*):
     ```javascript
     {
       data: 'perihal',
       render: function(data, type, row) {
         return `
           <div class="d-flex align-items-center">
             <div class="rounded-2 p-2 bg-light-primary text-primary me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
               <i class="ti ti-file-text fs-6"></i>
             </div>
             <div>
               <h6 class="fw-semibold mb-0 fs-4 text-truncate" style="max-width: 320px;" title="${row.perihal || ''}">${row.perihal || '-'}</h6>
               <p class="mb-0 text-muted fs-3">${row.nomor_surat || '-'}</p>
             </div>
           </div>
         `;
       }
     }
     ```
   - Kolom 2 (*Tanggal Surat*):
     ```javascript
     {
       data: 'tanggal_surat',
       render: function(data) {
         return `<p class="mb-0 text-dark fw-normal">${formatDate(data)}</p>`;
       }
     }
     ```
   - Kolom 3 (*Status Disposisi*):
     ```javascript
     {
       data: 'disposisi_exists',
       orderable: false,
       render: function(data) {
         if (data) {
           return `
             <div class="d-flex align-items-center">
               <span class="bg-success p-1 rounded-circle me-2" style="width: 8px; height: 8px;"></span>
               <span class="text-success fw-medium">Sudah Disposisi</span>
             </div>
           `;
         }
         return `
           <div class="d-flex align-items-center">
             <span class="bg-warning p-1 rounded-circle me-2" style="width: 8px; height: 8px;"></span>
             <span class="text-warning fw-medium">Belum Disposisi</span>
           </div>
         `;
       }
     }
     ```
   - Kolom 4 (*Pengirim*):
     ```javascript
     {
       data: 'pengirim',
       render: function(data) {
         return `<h6 class="mb-0 fs-4 text-dark fw-medium">${data || '-'}</h6>`;
       }
     }
     ```
   - Kolom 5 (*Aksi*):
     ```javascript
     {
       data: null,
       orderable: false,
       className: 'text-end',
       render: function(data, type, row) {
         return `
           <div class="dropdown dropstart">
             <a href="javascript:void(0)" class="text-muted fs-6" data-bs-toggle="dropdown" aria-expanded="false">
               <i class="ti ti-dots-vertical"></i>
             </a>
             <ul class="dropdown-menu">
               <li>
                 <a class="dropdown-item d-flex align-items-center gap-2 btn-detail" data-id="${row.id}" href="javascript:void(0)">
                   <i class="ti ti-eye fs-4 text-primary"></i> Detail Surat
                 </a>
               </li>
               <li>
                 <a class="dropdown-item d-flex align-items-center gap-2 btn-edit" data-id="${row.id}" href="javascript:void(0)">
                   <i class="ti ti-pencil fs-4 text-info"></i> Edit Surat
                 </a>
               </li>
               <li><hr class="dropdown-divider"></li>
               <li>
                 <a class="dropdown-item d-flex align-items-center gap-2 text-danger btn-delete" data-id="${row.id}" href="javascript:void(0)">
                   <i class="ti ti-trash fs-4"></i> Hapus
                 </a>
               </li>
             </ul>
           </div>
         `;
       }
     }
     ```

---

## 6. Rencana Kerja (Checklist Implementasi)

- [ ] **Backend**:
  - [ ] Tambahkan parameter filter `start_date`, `end_date`, dan `status_disposisi` pada method `datatables` di `SuratMasukController.php`.
  - [ ] Pastikan query `withExists('disposisi')`, `has('disposisi')`, dan `doesntHave('disposisi')` berjalan optimal dengan database PostgreSQL.
  - [ ] Uji respon JSON API datatables dengan berbagai kombinasi filter.

- [ ] **Frontend**:
  - [ ] Perbarui template HTML pada `SuratMasuk.vue` untuk memuat Filter Bar (Rentang Tanggal & Status Disposisi) dan tombol Search.
  - [ ] Sesuaikan definisi kolom DataTables (`columns`) agar menampilkan Perihal + Nomor Surat, Tanggal Surat, Status Disposisi (dot indicator), Pengirim, dan Dropdown Aksi.
  - [ ] Hubungkan form filter dengan fungsi reload DataTable (`dataTableInstance.ajax.reload()`).
  - [ ] Implementasikan event listener untuk tombol aksi (*Detail*, *Edit*, *Hapus*) dari dropdown menu.
  - [ ] Perbaiki styling CSS agar sesuai dengan class template Modernize (`table align-middle text-nowrap`, rounded container, dsb).

- [ ] **Testing & Validasi**:
  - [ ] Uji filter tanggal surat (hanya tanggal tertentu, rentang tanggal).
  - [ ] Uji filter status disposisi (*Sudah Disposisi* vs *Belum Disposisi*).
  - [ ] Uji kombinasi search text dan filter.
  - [ ] Pastikan navigasi ke halaman detail (`/surat-masuk/:id/detail`), modal edit, dan hapus data tetap berjalan normal.
