<template>
  <div>
    <!-- Breadcrumb Header -->
    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
      <div class="card-body px-4 py-3">
        <div class="row align-items-center">
          <div class="col-9">
            <h4 class="fw-semibold mb-8">Data Pelamar</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="#">Rekrutmen</a></li>
                <li class="breadcrumb-item" aria-current="page">Data Pelamar</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Card Container -->
    <div class="card w-100 position-relative overflow-hidden">
      <div class="card-body p-4">
        <!-- Top Toolbar: Search, Filter Toggle, and Add Button -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
          <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="position-relative" style="min-width: 260px;">
              <input 
                type="text" 
                class="form-control py-2 ps-5" 
                placeholder="Cari pelamar, nomor, institusi, jurusan..." 
                v-model="searchQuery"
                @input="onSearchInput"
              />
              <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-muted ms-3"></i>
            </div>
            <button 
              class="btn d-flex align-items-center gap-2"
              :class="showFilter || isFilterActive ? 'btn-primary' : 'btn-outline-secondary'"
              @click="toggleFilter"
            >
              <i class="ti ti-filter"></i>
              <span>Filter</span>
              <span v-if="isFilterActive" class="badge bg-white text-primary rounded-pill ms-1 px-2 py-1 fs-1">Aktif</span>
            </button>
          </div>
          <button class="btn btn-primary d-flex align-items-center gap-2" @click="openModal()">
            <i class="ti ti-plus fs-4"></i> Tambah Pelamar
          </button>
        </div>

        <!-- Collapsible Filter Panel -->
        <div v-show="showFilter" class="card bg-light border-0 mb-4 p-3 rounded-3 shadow-none">
          <div class="row g-3 align-items-end">
            <div class="col-md-3 col-sm-6">
              <label class="form-label fs-3 fw-semibold mb-1">Tanggal Diterima (Awal)</label>
              <input type="date" class="form-control" v-model="filter.startDate" />
            </div>
            <div class="col-md-3 col-sm-6">
              <label class="form-label fs-3 fw-semibold mb-1">Tanggal Diterima (Akhir)</label>
              <input type="date" class="form-control" v-model="filter.endDate" />
            </div>
            <div class="col-md-3 col-sm-6">
              <label class="form-label fs-3 fw-semibold mb-1">Jenjang Pendidikan</label>
              <select class="form-select" v-model="filter.pendidikan">
                <option value="">Semua Pendidikan</option>
                <option value="SMA">SMA / SMK / Sederajat</option>
                <option value="D3">Diploma 3 (D3)</option>
                <option value="D4">Diploma 4 (D4)</option>
                <option value="S1">Sarjana (S1)</option>
                <option value="S2">Magister (S2)</option>
                <option value="S3">Doktor (S3)</option>
                <option value="LAINNYA">Lainnya</option>
              </select>
            </div>
            <div class="col-md-3 col-sm-6 d-flex gap-2">
              <button class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-1" @click="applyFilter">
                <i class="ti ti-check"></i> Terapkan
              </button>
              <button class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-1" @click="resetFilter">
                <i class="ti ti-rotate-clockwise"></i> Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Table Container (DataTables) -->
        <div class="table-responsive border rounded-3">
          <table id="pelamarTable" class="table align-middle text-nowrap mb-0 w-100">
            <thead>
              <tr>
                <th scope="col" style="min-width: 220px;">Pelamar & No. Lamaran</th>
                <th scope="col">Tanggal Diterima</th>
                <th scope="col">Pendidikan & Institusi</th>
                <th scope="col">Tempat, Tgl Lahir</th>
                <th scope="col">Catatan / Posisi</th>
                <th scope="col" class="text-end pe-4">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <!-- DataTables will populate this automatically -->
            </tbody>
          </table>
        </div>

      </div>
    </div>

    <!-- Modal Form Tambah / Edit -->
    <div v-if="showModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEdit ? 'Edit Data Pelamar' : 'Tambah Data Pelamar' }}</h5>
            <button type="button" class="btn-close" @click="closeModal()"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div :class="isEdit ? 'col-md-6 mb-3' : 'col-md-12 mb-3'">
                <label class="form-label">Tanggal Berkas Diterima <span class="text-danger">*</span></label>
                <input type="date" class="form-control" v-model="form.tanggal_diterima" required>
              </div>
              <div v-if="isEdit" class="col-md-6 mb-3">
                <label class="form-label">Nomor Lamaran (Angka) <span class="text-danger">*</span></label>
                <input 
                  type="number" 
                  min="1" 
                  class="form-control" 
                  v-model.number="form.nomor_lamaran" 
                  placeholder="Nomor urut lamaran" 
                  required
                >
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label">Nama Lengkap Pelamar <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="form.nama" placeholder="Masukkan nama lengkap pelamar" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Tempat Lahir</label>
                <input type="text" class="form-control" v-model="form.tempat_lahir" placeholder="Kota / tempat lahir">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control" v-model="form.tanggal_lahir">
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Jenjang Pendidikan <span class="text-danger">*</span></label>
                <select class="form-select" v-model="form.pendidikan" required>
                  <option value="SMA">SMA / SMK / Sederajat</option>
                  <option value="D3">Diploma 3 (D3)</option>
                  <option value="D4">Diploma 4 (D4)</option>
                  <option value="S1">Sarjana (S1)</option>
                  <option value="S2">Magister (S2)</option>
                  <option value="S3">Doktor (S3)</option>
                  <option value="LAINNYA">Lainnya</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Nama Institusi / Universitas</label>
                <input type="text" class="form-control" v-model="form.institusi" placeholder="Contoh: Universitas Indonesia">
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Program Studi / Jurusan</label>
                <input type="text" class="form-control" v-model="form.jurusan" placeholder="Contoh: Teknik Informatika">
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label">Lampiran Berkas / CV / Evidence <small v-if="isEdit && form.evidence" class="text-info">- Berkas tersimpan. Unggah untuk mengganti.</small></label>
                <input type="file" class="form-control" @change="handleFileUpload" accept=".pdf,image/png,image/jpeg,image/jpg">
                <div v-if="isEdit && form.evidence" class="mt-2">
                  <a :href="getFileUrl(form.evidence)" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                    <i class="ti ti-file"></i> Lihat Berkas Saat Ini
                  </a>
                </div>
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label">Catatan / Posisi yang Dilamar</label>
                <textarea class="form-control" v-model="form.catatan" rows="3" placeholder="Tambahkan catatan kualifikasi atau posisi yang diminati"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" @click="closeModal()">Batal</button>
            <button type="button" class="btn btn-primary" @click="saveData()">Simpan</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Detail / Pratinjau Pelamar -->
    <div v-if="showDetailModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-light">
            <div>
              <h5 class="modal-title fw-semibold text-dark mb-0">Detail Data Pelamar</h5>
              <span class="badge bg-primary text-white fs-2 mt-1">No. Lamaran: #{{ detailItem?.nomor_lamaran || '-' }}</span>
            </div>
            <button type="button" class="btn-close" @click="showDetailModal = false"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-4">
              <div class="d-flex align-items-center gap-3 mb-2">
                <div class="p-3 rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center">
                  <i class="ti ti-user-check fs-7"></i>
                </div>
                <div>
                  <h4 class="fw-bold text-dark mb-1">{{ detailItem?.nama || '-' }}</h4>
                  <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge" :class="getPendidikanBadgeClass(detailItem?.pendidikan)">
                      {{ detailItem?.pendidikan || 'S1' }}
                    </span>
                    <span class="text-muted fs-3" v-if="detailItem?.institusi">
                      <i class="ti ti-school me-1"></i> {{ detailItem?.institusi }} {{ detailItem?.jurusan ? `- ${detailItem?.jurusan}` : '' }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="d-flex flex-wrap align-items-center gap-3 text-muted fs-3 mt-3">
                <span><i class="ti ti-calendar me-1"></i> Tanggal Diterima: <strong class="text-dark">{{ formatDate(detailItem?.tanggal_diterima) }}</strong></span>
                <span><i class="ti ti-clock me-1"></i> Input: <strong class="text-dark">{{ formatDateTime(detailItem?.created_at) }}</strong></span>
              </div>
            </div>

            <div class="row g-3 p-3 bg-light rounded-3 mb-4">
              <div class="col-md-6">
                <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Tempat, Tanggal Lahir</label>
                <p class="mb-0 fw-semibold text-dark fs-3">
                  {{ formatBirth(detailItem?.tempat_lahir, detailItem?.tanggal_lahir) }}
                </p>
              </div>
              <div class="col-md-6">
                <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Petugas Penginput</label>
                <p class="mb-0 fw-medium text-dark">
                  {{ detailItem?.user_input?.nama || '-' }} 
                  <small class="text-muted" v-if="detailItem?.user_input?.npp">(NPP: {{ detailItem?.user_input?.npp }})</small>
                </p>
              </div>
              <div class="col-md-6">
                <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Institusi Pendidikan</label>
                <p class="mb-0 fw-semibold text-dark">{{ detailItem?.institusi || '-' }}</p>
              </div>
              <div class="col-md-6">
                <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Jurusan / Program Studi</label>
                <p class="mb-0 fw-semibold text-dark">{{ detailItem?.jurusan || '-' }}</p>
              </div>
              <div class="col-12" v-if="detailItem?.catatan">
                <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Catatan / Posisi yang Dilamar</label>
                <p class="mb-0 text-dark bg-white p-2 rounded border">{{ detailItem?.catatan }}</p>
              </div>
            </div>

            <!-- Evidence Section -->
            <div>
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-semibold text-dark mb-0">Lampiran Berkas / CV Pelamar</h6>
                <a 
                  v-if="detailItem?.evidence" 
                  :href="getFileUrl(detailItem?.evidence)" 
                  target="_blank" 
                  download
                  class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                >
                  <i class="ti ti-download"></i> Unduh Berkas
                </a>
              </div>

              <div v-if="detailItem?.evidence" class="border rounded-3 overflow-hidden bg-light text-center p-2">
                <template v-if="isPdf(detailItem?.evidence)">
                  <embed 
                    :src="getFileUrl(detailItem?.evidence)" 
                    type="application/pdf" 
                    width="100%" 
                    height="450px" 
                    class="rounded"
                  />
                </template>
                <template v-else>
                  <img 
                    :src="getFileUrl(detailItem?.evidence)" 
                    alt="Evidence Preview" 
                    class="img-fluid rounded shadow-sm" 
                    style="max-height: 450px; object-fit: contain;" 
                  />
                </template>
              </div>
              <div v-else class="alert alert-light text-center py-4 mb-0 text-muted border">
                <i class="ti ti-file-off fs-7 d-block mb-1"></i>
                Tidak ada berkas lampiran CV/dokumen untuk pelamar ini.
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showDetailModal = false">Tutup</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../utils/api'

const showModal = ref(false)
const showDetailModal = ref(false)
const isEdit = ref(false)
const showFilter = ref(false)
const searchQuery = ref('')

const filter = ref({
  startDate: '',
  endDate: '',
  pendidikan: ''
})

const isFilterActive = computed(() => {
  return !!(filter.value.startDate || filter.value.endDate || filter.value.pendidikan)
})

const form = ref({ 
  id: '', 
  tanggal_diterima: '', 
  nomor_lamaran: '', 
  nama: '', 
  tempat_lahir: '', 
  tanggal_lahir: '', 
  pendidikan: 'S1', 
  institusi: '', 
  jurusan: '', 
  evidence: '', 
  catatan: '' 
})

const detailItem = ref(null)
const evidenceFile = ref(null)

const getFileUrl = (path) => {
  if (!path) return ''
  return `http://localhost:8000/storage/${path}`
}

const isPdf = (path) => {
  if (!path) return false
  return path.toLowerCase().endsWith('.pdf')
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return dateStr
  return d.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return dateStr
  return d.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatBirth = (place, date) => {
  const formattedDate = date ? formatDate(date) : ''
  if (!place && !formattedDate) return '-'
  if (place && !formattedDate) return place
  if (!place && formattedDate) return formattedDate
  return `${place}, ${formattedDate}`
}

const getPendidikanBadgeClass = (pendidikan) => {
  switch (pendidikan) {
    case 'S1':
      return 'bg-light-primary text-primary fw-semibold'
    case 'S2':
    case 'S3':
      return 'bg-light-success text-success fw-semibold'
    case 'D3':
    case 'D4':
      return 'bg-light-info text-info fw-semibold'
    case 'SMA':
      return 'bg-light-warning text-warning fw-semibold'
    default:
      return 'bg-light-secondary text-secondary fw-semibold'
  }
}

const escapeHtml = (unsafe) => {
  if (unsafe === null || unsafe === undefined) return ''
  return String(unsafe)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;")
}

const toggleFilter = () => {
  showFilter.value = !showFilter.value
}

const applyFilter = () => {
  if (dataTableInstance) {
    dataTableInstance.ajax.reload()
  }
}

const resetFilter = () => {
  filter.value = {
    startDate: '',
    endDate: '',
    pendidikan: ''
  }
  if (dataTableInstance) {
    dataTableInstance.ajax.reload()
  }
}

let searchTimeout = null
const onSearchInput = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    if (dataTableInstance) {
      dataTableInstance.search(searchQuery.value).draw()
    }
  }, 350)
}

const handleFileUpload = (e) => {
  const files = e.target.files
  if (files && files.length > 0) {
    evidenceFile.value = files[0]
  } else {
    evidenceFile.value = null
  }
}

const openModal = () => {
  isEdit.value = false
  const today = new Date().toISOString().split('T')[0]
  form.value = {
    id: '',
    tanggal_diterima: today,
    nomor_lamaran: '',
    nama: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    pendidikan: 'S1',
    institusi: '',
    jurusan: '',
    evidence: '',
    catatan: ''
  }
  evidenceFile.value = null
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const editItem = async (id) => {
  try {
    const res = await api.post('/lamaran/detail', { id })
    const item = res.data
    isEdit.value = true
    form.value = {
      id: item.id,
      tanggal_diterima: item.tanggal_diterima,
      nomor_lamaran: item.nomor_lamaran,
      nama: item.nama,
      tempat_lahir: item.tempat_lahir || '',
      tanggal_lahir: item.tanggal_lahir || '',
      pendidikan: item.pendidikan || 'S1',
      institusi: item.institusi || '',
      jurusan: item.jurusan || '',
      evidence: item.evidence || '',
      catatan: item.catatan || ''
    }
    evidenceFile.value = null
    showModal.value = true
  } catch (error) {
    console.error('Error fetching detail', error)
    window.Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: 'Gagal memuat data pelamar untuk diedit'
    })
  }
}

const viewDetail = async (id) => {
  try {
    const res = await api.post('/lamaran/detail', { id })
    detailItem.value = res.data
    showDetailModal.value = true
  } catch (error) {
    console.error('Error fetching detail', error)
    window.Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: 'Gagal memuat rincian data pelamar'
    })
  }
}

const saveData = async () => {
  if (!form.value.tanggal_diterima || !form.value.nama || !form.value.pendidikan) {
    window.Swal.fire({
      icon: 'warning',
      title: 'Validasi',
      text: 'Mohon isi Tanggal Diterima, Nama Pelamar, dan Pendidikan'
    })
    return
  }

  if (isEdit.value && !form.value.nomor_lamaran) {
    window.Swal.fire({
      icon: 'warning',
      title: 'Validasi',
      text: 'Nomor lamaran wajib diisi saat edit data'
    })
    return
  }

  const formData = new FormData()
  if (isEdit.value) {
    formData.append('id', form.value.id)
    formData.append('nomor_lamaran', form.value.nomor_lamaran)
  }
  formData.append('tanggal_diterima', form.value.tanggal_diterima)
  formData.append('nama', form.value.nama)
  if (form.value.tempat_lahir) formData.append('tempat_lahir', form.value.tempat_lahir)
  if (form.value.tanggal_lahir) formData.append('tanggal_lahir', form.value.tanggal_lahir)
  formData.append('pendidikan', form.value.pendidikan)
  if (form.value.institusi) formData.append('institusi', form.value.institusi)
  if (form.value.jurusan) formData.append('jurusan', form.value.jurusan)
  if (form.value.catatan) formData.append('catatan', form.value.catatan)
  if (evidenceFile.value) {
    formData.append('evidence', evidenceFile.value)
  }

  try {
    if (isEdit.value) {
      await api.post('/lamaran/update', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      window.Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Data pelamar berhasil diperbarui!',
        timer: 1500,
        showConfirmButton: false
      })
    } else {
      const res = await api.post('/lamaran/create', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      const savedNumber = res.data?.data?.nomor_lamaran
      window.Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: `Data pelamar berhasil disimpan dengan Nomor Lamaran: #${savedNumber || '1'}`,
        timer: 2000,
        showConfirmButton: false
      })
    }
    closeModal()
    if (dataTableInstance) {
      dataTableInstance.ajax.reload()
    }
  } catch (error) {
    console.error('Error saving data', error)
    window.Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data.'
    })
  }
}

// DataTables Initialization
let dataTableInstance = null

const initDataTable = () => {
  if (dataTableInstance) {
    dataTableInstance.destroy()
  }
  
  if (!window.$ || !window.$.fn.dataTable) {
    setTimeout(initDataTable, 100)
    return
  }

  dataTableInstance = window.$('#pelamarTable').DataTable({
    serverSide: true,
    processing: true,
    dom: 'rt<"d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 border-top"lip>',
    ajax: async function (data, callback, settings) {
      try {
        const payload = {
          ...data,
          start_date: filter.value.startDate || null,
          end_date: filter.value.endDate || null,
          pendidikan: filter.value.pendidikan || null
        }
        const response = await api.post('/lamaran/datatables', payload)
        callback({
          draw: response.data.draw,
          recordsTotal: response.data.recordsTotal,
          recordsFiltered: response.data.recordsFiltered,
          data: response.data.data
        })
      } catch (error) {
        console.error('Error fetching datatables data', error)
        callback({
          draw: data.draw,
          recordsTotal: 0,
          recordsFiltered: 0,
          data: []
        })
      }
    },
    order: [[1, 'desc'], [0, 'desc']],
    columns: [
      { 
        data: 'nama',
        render: function(data, type, row) {
          const safeNama = escapeHtml(row.nama || '-')
          const safeNomor = escapeHtml(row.nomor_lamaran !== null && row.nomor_lamaran !== undefined ? String(row.nomor_lamaran) : '-')
          return `
            <div class="d-flex align-items-center">
              <div class="p-2 rounded-2 bg-light-primary text-primary me-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="ti ti-user-check fs-6"></i>
              </div>
              <div>
                <h6 class="fw-semibold mb-0 fs-4 text-dark">${safeNama}</h6>
                <span class="badge bg-light-secondary text-secondary fs-2 py-0 px-2 mt-1">
                  <i class="ti ti-hash me-1"></i>No. Lamaran: #${safeNomor}
                </span>
              </div>
            </div>
          `;
        }
      },
      { 
        data: 'tanggal_diterima',
        render: function(data) {
          return `<p class="mb-0 text-dark fw-normal">${formatDate(data)}</p>`;
        }
      },
      { 
        data: 'pendidikan',
        render: function(data, type, row) {
          const safePendidikan = escapeHtml(row.pendidikan || 'S1')
          const safeInstitusi = escapeHtml(row.institusi || '')
          const safeJurusan = escapeHtml(row.jurusan || '')
          const badgeClass = getPendidikanBadgeClass(row.pendidikan)
          
          let subtitle = ''
          if (safeInstitusi && safeJurusan) {
            subtitle = `${safeInstitusi} - ${safeJurusan}`
          } else if (safeInstitusi) {
            subtitle = safeInstitusi
          } else if (safeJurusan) {
            subtitle = safeJurusan
          }

          return `
            <div>
              <span class="badge ${badgeClass} fs-2 py-1 px-2 mb-1">${safePendidikan}</span>
              ${subtitle ? `<p class="mb-0 text-muted fs-2 text-truncate" style="max-width: 250px;" title="${subtitle}">${subtitle}</p>` : ''}
            </div>
          `;
        }
      },
      { 
        data: 'tempat_lahir',
        render: function(data, type, row) {
          const birth = formatBirth(row.tempat_lahir, row.tanggal_lahir)
          return `<p class="mb-0 text-dark fs-3">${escapeHtml(birth)}</p>`;
        }
      },
      {
        data: 'catatan',
        render: function(data) {
          const safeCatatan = escapeHtml(data || '-')
          return `<p class="mb-0 text-dark fs-3 text-truncate" style="max-width: 200px;" title="${safeCatatan}">${safeCatatan}</p>`;
        }
      },
      { 
        data: null, 
        orderable: false,
        className: 'text-end pe-3',
        render: function(data, type, row) {
          return `
            <div class="dropdown dropstart d-inline-block">
              <a href="javascript:void(0)" class="text-muted fs-6 p-2 rounded-circle hover-bg d-inline-flex align-items-center justify-content-center" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-dots-vertical"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2 py-2 btn-detail" data-id="${row.id}" href="javascript:void(0)">
                    <i class="ti ti-eye fs-4 text-primary"></i> Detail Pelamar
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2 py-2 btn-edit" data-id="${row.id}" href="javascript:void(0)">
                    <i class="ti ti-pencil fs-4 text-info"></i> Edit Data
                  </a>
                </li>
                ${row.evidence ? `
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="${getFileUrl(row.evidence)}" target="_blank" download>
                    <i class="ti ti-download fs-4 text-success"></i> Unduh Berkas
                  </a>
                </li>
                ` : ''}
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger btn-delete" data-id="${row.id}" href="javascript:void(0)">
                    <i class="ti ti-trash fs-4"></i> Hapus Data
                  </a>
                </li>
              </ul>
            </div>
          `;
        }
      }
    ],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari pelamar...",
      lengthMenu: "Tampilkan _MENU_ data",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pelamar",
      infoEmpty: "Menampilkan 0 data",
      infoFiltered: "(disaring dari _MAX_ total data)",
      zeroRecords: "Tidak ada data pelamar yang cocok",
      emptyTable: "Belum ada data pelamar",
      paginate: {
        first: "Awal",
        last: "Akhir",
        next: "Selanjutnya",
        previous: "Sebelumnya"
      }
    }
  })

  // Event Delegations
  window.$('#pelamarTable').off('click', '.btn-detail').on('click', '.btn-detail', function() {
    const id = window.$(this).data('id')
    viewDetail(id)
  })

  window.$('#pelamarTable').off('click', '.btn-edit').on('click', '.btn-edit', function() {
    const id = window.$(this).data('id')
    editItem(id)
  })

  window.$('#pelamarTable').off('click', '.btn-delete').on('click', '.btn-delete', function() {
    const id = window.$(this).data('id')
    deleteItem(id)
  })
}

const deleteItem = (id) => {
  window.Swal.fire({
    title: 'Konfirmasi Hapus',
    text: 'Apakah Anda yakin ingin menghapus data pelamar ini?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then(async (result) => {
    if (result.isConfirmed || result.value) {
      try {
        await api.post('/lamaran/delete', { id })
        window.Swal.fire({
          icon: 'success',
          title: 'Terhapus!',
          text: 'Data pelamar berhasil dihapus.',
          timer: 1500,
          showConfirmButton: false
        })
        if (dataTableInstance) {
          dataTableInstance.ajax.reload()
        }
      } catch (error) {
        console.error('Error deleting data pelamar', error)
        window.Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: error.response?.data?.message || 'Terjadi kesalahan saat menghapus data.'
        })
      }
    }
  })
}

onMounted(() => {
  initDataTable()
})
</script>

<style scoped>
/* Hilangkan icon panah yang nabrak angka di select length */
:deep(.dataTables_length .form-select) {
  background-image: none !important;
  padding-right: 0.75rem !important;
  padding-left: 0.75rem !important;
}
/* Matikan border & padding ganda dari class bawaan template pada elemen <li> */
:deep(.table-responsive .dataTables_wrapper .dataTables_paginate .paginate_button) {
  padding: 0 !important;
  border: none !important;
}
:deep(.hover-bg:hover) {
  background-color: rgba(0, 0, 0, 0.05);
}
/* Pastikan dropdown menu tidak terpotong oleh overflow-x */
.table-responsive {
  min-height: 220px;
}

:deep(.table th) {
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--bs-heading-color, var(--bs-body-color, #2a3547));
  padding: 14px 16px;
  background-color: var(--bs-body-bg, #f8fafc);
  border-bottom: 1px solid var(--bs-border-color, #ebf1f6);
}

:deep(.table td) {
  padding: 14px 16px;
  border-bottom: 1px solid var(--bs-border-color, #ebf1f6);
}

:deep([data-bs-theme="dark"]) .table th,
:global([data-bs-theme="dark"]) .table th,
:global(#main-wrapper[data-bs-theme="dark"]) .table th {
  background-color: #202936 !important;
  color: #fff !important;
  border-bottom-color: #333F55 !important;
}

:deep([data-bs-theme="dark"]) .table td,
:global([data-bs-theme="dark"]) .table td,
:global(#main-wrapper[data-bs-theme="dark"]) .table td {
  border-bottom-color: #333F55 !important;
}
</style>
