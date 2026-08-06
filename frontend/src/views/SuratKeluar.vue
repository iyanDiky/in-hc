<template>
  <div>
    <!-- Breadcrumb Header -->
    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
      <div class="card-body px-4 py-3">
        <div class="row align-items-center">
          <div class="col-9">
            <h4 class="fw-semibold mb-8">Surat Keluar</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="#">Persuratan</a></li>
                <li class="breadcrumb-item" aria-current="page">Surat Keluar</li>
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
                placeholder="Cari perihal, nomor, tujuan, pemohon..." 
                v-model="searchQuery"
                @input="onSearchInput"
              />
              <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-muted ms-3"></i>
            </div>
            <!-- Pilihan Tahun Filter -->
            <div style="min-width: 140px;">
              <select class="form-select py-2 fw-semibold text-primary" v-model="selectedYear" @change="onYearChange">
                <option value="">Semua Tahun</option>
                <option v-for="yr in availableYears" :key="yr" :value="String(yr)">
                  Tahun {{ yr }}
                </option>
              </select>
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
            <i class="ti ti-plus fs-4"></i> Tambah Surat Keluar
          </button>
        </div>

        <!-- Collapsible Filter Panel -->
        <div v-show="showFilter" class="card bg-light border-0 mb-4 p-3 rounded-3 shadow-none">
          <div class="row g-3 align-items-end">
            <div class="col-md-3 col-sm-6">
              <label class="form-label fs-3 fw-semibold mb-1">Tanggal Awal</label>
              <input type="date" class="form-control" v-model="filter.startDate" />
            </div>
            <div class="col-md-3 col-sm-6">
              <label class="form-label fs-3 fw-semibold mb-1">Tanggal Akhir</label>
              <input type="date" class="form-control" v-model="filter.endDate" />
            </div>
            <div class="col-md-3 col-sm-6">
              <label class="form-label fs-3 fw-semibold mb-1">Bagian / Seksi Pemohon</label>
              <select class="form-select" v-model="filter.bagianSeksiRequest">
                <option value="">Semua Bagian / Seksi</option>
                <option v-for="bs in bagianSeksiList" :key="bs.id" :value="bs.id">
                  {{ bs.kode ? `[${bs.kode}] ` : '' }}{{ bs.bagian_seksi }}
                </option>
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

        <!-- Table Container (Product List Style) -->
        <div class="table-responsive border rounded-3">
          <table id="suratKeluarTable" class="table align-middle text-nowrap mb-0 w-100">
            <thead>
              <tr>
                <th scope="col" style="width: 90px;">No. Surat</th>
                <th scope="col" style="min-width: 280px;">Perihal</th>
                <th scope="col">Tanggal Surat</th>
                <th scope="col">Tujuan</th>
                <th scope="col">Pemohon (Unit/Bagian)</th>
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
    <div v-if="showModal" id="formModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEdit ? 'Edit Surat Keluar' : 'Tambah Surat Keluar' }}</h5>
            <button type="button" class="btn-close" @click="closeModal()"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div :class="isEdit ? 'col-md-6 mb-3' : 'col-md-12 mb-3'">
                <label class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
                <input type="date" class="form-control" v-model="form.tanggal_surat" required>
              </div>
              <div v-if="isEdit" class="col-md-6 mb-3">
                <label class="form-label">Nomor Surat (Angka) <span class="text-danger">*</span></label>
                <input 
                  type="number" 
                  min="1" 
                  class="form-control" 
                  v-model.number="form.nomor_surat" 
                  placeholder="Nomor urut surat (angka)" 
                  required
                >
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label">Tujuan Surat <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="form.tujuan" placeholder="Instansi / Pihak tujuan surat" required>
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label">Perihal <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="form.perihal" placeholder="Perihal / Judul surat keluar" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Pegawai Pemohon (User Request)</label>
                <select class="select2-user form-control" v-model="form.user_request" style="width: 100%; height: 36px">
                  <option value="">Pilih Pegawai Pemohon...</option>
                  <option v-for="u in usersList" :key="u.id" :value="u.id">
                    {{ u.nama }} (NPP: {{ u.npp || '-' }})
                  </option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Bagian / Seksi Pemohon</label>
                <select class="select2-seksi form-control" v-model="form.bagian_seksi_request" style="width: 100%; height: 36px">
                  <option value="">Pilih Bagian Seksi...</option>
                  <option v-for="bs in bagianSeksiList" :key="bs.id" :value="bs.id">
                    {{ bs.kode ? `[${bs.kode}] ` : '' }}{{ bs.bagian_seksi }}
                  </option>
                </select>
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label">Evidence / Lampiran (Opsional) <small v-if="isEdit && form.evidence" class="text-info">- Berkas tersimpan. Unggah untuk mengganti.</small></label>
                <input type="file" class="form-control" @change="handleFileUpload" accept=".pdf,image/png,image/jpeg,image/jpg">
                <div v-if="isEdit && form.evidence" class="mt-2">
                  <a :href="getFileUrl(form.evidence)" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                    <i class="ti ti-file"></i> Lihat Berkas Saat Ini
                  </a>
                </div>
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label">Catatan (Opsional)</label>
                <textarea class="form-control" v-model="form.catatan" rows="3" placeholder="Tambahkan catatan jika ada"></textarea>
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

    <!-- Modal Detail / Pratinjau Surat Keluar -->
    <div v-if="showDetailModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-light">
            <div>
              <h5 class="modal-title fw-semibold text-dark mb-0">Detail Surat Keluar</h5>
              <span class="badge bg-primary text-white fs-2 mt-1">Nomor Surat: {{ detailItem?.nomor_surat ? `No. ${detailItem.nomor_surat}` : '-' }}</span>
            </div>
            <button type="button" class="btn-close" @click="showDetailModal = false"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-4">
              <h4 class="fw-bold text-dark mb-2">{{ detailItem?.perihal || '-' }}</h4>
              <div class="d-flex flex-wrap align-items-center gap-3 text-muted fs-3">
                <span><i class="ti ti-calendar me-1"></i> Tanggal: <strong class="text-dark">{{ formatDate(detailItem?.tanggal_surat) }}</strong></span>
                <span><i class="ti ti-clock me-1"></i> Input: <strong class="text-dark">{{ formatDateTime(detailItem?.created_at) }}</strong></span>
              </div>
            </div>

            <div class="row g-3 p-3 bg-light rounded-3 mb-4">
              <div class="col-md-6">
                <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Tujuan Surat</label>
                <p class="mb-0 fw-semibold text-dark fs-4">{{ detailItem?.tujuan || '-' }}</p>
              </div>
              <div class="col-md-6">
                <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Petugas Penginput</label>
                <p class="mb-0 fw-medium text-dark">{{ detailItem?.user_input?.nama || '-' }} <small class="text-muted" v-if="detailItem?.user_input?.npp">(NPP: {{ detailItem?.user_input?.npp }})</small></p>
              </div>
              <div class="col-md-6">
                <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Pegawai Pemohon (Request)</label>
                <p class="mb-0 fw-semibold text-dark">{{ detailItem?.user_request?.nama || '-' }} <small class="text-muted" v-if="detailItem?.user_request?.npp">({{ detailItem?.user_request?.npp }})</small></p>
              </div>
              <div class="col-md-6">
                <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Bagian / Seksi Pemohon</label>
                <p class="mb-0 fw-semibold text-dark">
                  <span v-if="detailItem?.bagian_seksi_request" class="badge bg-light-primary text-primary fw-semibold">
                    {{ detailItem?.bagian_seksi_request?.kode ? `[${detailItem?.bagian_seksi_request?.kode}] ` : '' }}{{ detailItem?.bagian_seksi_request?.bagian_seksi }}
                  </span>
                  <span v-else class="text-muted">-</span>
                </p>
              </div>
              <div class="col-12" v-if="detailItem?.catatan">
                <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Catatan</label>
                <p class="mb-0 text-dark bg-white p-2 rounded border">{{ detailItem?.catatan }}</p>
              </div>
            </div>

            <!-- Evidence Section -->
            <div>
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-semibold text-dark mb-0">Lampiran / Evidence Surat</h6>
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
                Tidak ada lampiran evidence untuk surat keluar ini.
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
import { ref, computed, onMounted, nextTick } from 'vue'
import api, { getStorageUrl } from '../utils/api'

const showModal = ref(false)
const showDetailModal = ref(false)
const isEdit = ref(false)
const showFilter = ref(false)
const searchQuery = ref('')

const currentYear = new Date().getFullYear().toString()
const selectedYear = ref(currentYear)
const availableYears = ref([new Date().getFullYear()])

const usersList = ref([])
const bagianSeksiList = ref([])

const filter = ref({
  startDate: '',
  endDate: '',
  bagianSeksiRequest: ''
})

const isFilterActive = computed(() => {
  return !!(filter.value.startDate || filter.value.endDate || filter.value.bagianSeksiRequest)
})

const form = ref({ 
  id: '', 
  tanggal_surat: '', 
  nomor_surat: '', 
  tujuan: '', 
  perihal: '', 
  user_request: '', 
  bagian_seksi_request: '', 
  evidence: '', 
  catatan: '' 
})

const detailItem = ref(null)
const evidenceFile = ref(null)

const getFileUrl = (path) => {
  if (!path) return ''
  return getStorageUrl(path)
}

const isPdf = (path) => {
  if (!path) return false
  return path.toLowerCase().endsWith('.pdf')
}

const escapeHtml = (unsafe) => {
  if (!unsafe) return ''
  return String(unsafe)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return dateStr
  return d.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
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

const handleFileUpload = (event) => {
  evidenceFile.value = event.target.files[0]
}

const onUserRequestChange = () => {
  if (form.value.user_request) {
    const selectedUser = usersList.value.find(u => u.id === form.value.user_request)
    if (selectedUser && selectedUser.bagian_seksi_id) {
      form.value.bagian_seksi_request = selectedUser.bagian_seksi_id
    }
  }
}

let dataTableInstance = null
let searchTimeout = null

const toggleFilter = () => {
  showFilter.value = !showFilter.value
}

const onYearChange = () => {
  if (dataTableInstance) {
    dataTableInstance.ajax.reload()
  }
}

const onSearchInput = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    if (dataTableInstance) {
      dataTableInstance.search(searchQuery.value).draw()
    }
  }, 350)
}

const applyFilter = () => {
  if (dataTableInstance) {
    dataTableInstance.ajax.reload()
  }
}

const resetFilter = () => {
  filter.value.startDate = ''
  filter.value.endDate = ''
  filter.value.bagianSeksiRequest = ''
  if (dataTableInstance) {
    dataTableInstance.ajax.reload()
  }
}

const fetchOptions = async () => {
  try {
    const [usersRes, bsRes, yearsRes] = await Promise.all([
      api.post('/users/list', { limit: 1000 }),
      api.post('/bagian-seksi/list', { limit: 1000 }),
      api.post('/surat-keluar/years')
    ])
    usersList.value = usersRes.data.data || usersRes.data || []
    bagianSeksiList.value = bsRes.data.data || bsRes.data || []
    if (Array.isArray(yearsRes.data) && yearsRes.data.length > 0) {
      availableYears.value = yearsRes.data
    }
  } catch (error) {
    console.error('Error fetching options for select', error)
  }
}

const initDataTable = () => {
  if (dataTableInstance) {
    dataTableInstance.destroy()
  }
  
  if (!window.$ || !window.$.fn.dataTable) {
    setTimeout(initDataTable, 100)
    return
  }

  dataTableInstance = window.$('#suratKeluarTable').DataTable({
    serverSide: true,
    processing: true,
    order: [],
    dom: 'rt<"d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 border-top"lip>',
    ajax: async function (data, callback, settings) {
      try {
        const payload = {
          ...data,
          tahun: selectedYear.value || null,
          start_date: filter.value.startDate || null,
          end_date: filter.value.endDate || null,
          bagian_seksi_request: filter.value.bagianSeksiRequest || null
        }
        const response = await api.post('/surat-keluar/datatables', payload)
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
    order: [[0, 'desc']],
    columns: [
      { 
        data: 'nomor_surat',
        width: '90px',
        render: function(data, type, row) {
          const safeNomor = escapeHtml(row.nomor_surat !== null && row.nomor_surat !== undefined ? String(row.nomor_surat) : '-')
          return `
            <div class="d-inline-flex align-items-center">
              <span class="badge bg-light-primary text-primary fw-bold fs-3 px-2 py-1 rounded-2">
                <i class="ti ti-hash me-1"></i>${safeNomor}
              </span>
            </div>
          `;
        }
      },
      { 
        data: 'perihal',
        render: function(data, type, row) {
          const safePerihal = escapeHtml(row.perihal || '-')
          return `
            <div>
              <h6 class="fw-semibold mb-0 fs-4 text-dark text-truncate" style="max-width: 320px;" title="${safePerihal}">${safePerihal}</h6>
            </div>
          `;
        }
      },
      { 
        data: 'tanggal_surat',
        render: function(data) {
          return `<p class="mb-0 text-dark fw-normal">${formatDate(data)}</p>`;
        }
      },
      { 
        data: 'tujuan',
        render: function(data) {
          return `<h6 class="mb-0 fs-4 text-dark fw-medium">${escapeHtml(data || '-')}</h6>`;
        }
      },
      {
        data: null,
        orderable: false,
        render: function(data, type, row) {
          const userName = escapeHtml(row.user_request?.nama || '-')
          const bsName = escapeHtml(row.bagian_seksi_request?.bagian_seksi || '-')
          const bsCode = escapeHtml(row.bagian_seksi_request?.kode || '')
          return `
            <div>
              <p class="mb-0 text-dark fw-semibold fs-3">${userName}</p>
              <span class="badge bg-light-primary text-primary fs-2 py-1 px-2 mt-1">
                ${bsCode ? `[${bsCode}] ` : ''}${bsName}
              </span>
            </div>
          `;
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
                    <i class="ti ti-eye fs-4 text-primary"></i> Detail Surat
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2 py-2 btn-edit" data-id="${row.id}" href="javascript:void(0)">
                    <i class="ti ti-pencil fs-4 text-info"></i> Edit Surat
                  </a>
                </li>
                ${row.evidence ? `
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="${getFileUrl(row.evidence)}" target="_blank" download>
                    <i class="ti ti-download fs-4 text-success"></i> Unduh Lampiran
                  </a>
                </li>
                ` : ''}
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger btn-delete" data-id="${row.id}" href="javascript:void(0)">
                    <i class="ti ti-trash fs-4"></i> Hapus Surat
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
      searchPlaceholder: "Cari surat keluar...",
      lengthMenu: "Tampilkan _MENU_ data",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ surat keluar",
      infoEmpty: "Menampilkan 0 data",
      infoFiltered: "(disaring dari _MAX_ total data)",
      zeroRecords: "Tidak ada data surat keluar yang cocok",
      emptyTable: "Belum ada data surat keluar",
      paginate: {
        first: "Awal",
        last: "Akhir",
        next: "Selanjutnya",
        previous: "Sebelumnya"
      }
    }
  })

  // Event Delegations
  window.$('#suratKeluarTable').off('click', '.btn-detail').on('click', '.btn-detail', function() {
    const id = window.$(this).data('id')
    viewDetail(id)
  })

  window.$('#suratKeluarTable').off('click', '.btn-edit').on('click', '.btn-edit', function() {
    const id = window.$(this).data('id')
    editItem(id)
  })

  window.$('#suratKeluarTable').off('click', '.btn-delete').on('click', '.btn-delete', function() {
    const id = window.$(this).data('id')
    deleteItem(id)
  })
}

const initModalSelect2 = () => {
  const $ = window.$
  if (!$ || !$.fn.select2) return

  // Inisialisasi Select2 Pegawai Pemohon
  $('.select2-user').select2({
    dropdownParent: $('#formModal')
  })
  $('.select2-user').val(form.value.user_request).trigger('change.select2')
  $('.select2-user').on('change', function() {
    const val = $(this).val()
    form.value.user_request = val
    if (val) {
      const selectedUser = usersList.value.find(u => u.id === val)
      if (selectedUser && selectedUser.bagian_seksi_id) {
        form.value.bagian_seksi_request = selectedUser.bagian_seksi_id
        $('.select2-seksi').val(selectedUser.bagian_seksi_id).trigger('change.select2')
      }
    }
  })

  // Inisialisasi Select2 Bagian Seksi Pemohon
  $('.select2-seksi').select2({
    dropdownParent: $('#formModal')
  })
  $('.select2-seksi').val(form.value.bagian_seksi_request).trigger('change.select2')
  $('.select2-seksi').on('change', function() {
    form.value.bagian_seksi_request = $(this).val()
  })
}

const openModal = async () => {
  isEdit.value = false
  if (usersList.value.length === 0 || bagianSeksiList.value.length === 0) {
    await fetchOptions()
  }
  const today = new Date().toISOString().split('T')[0]
  form.value = { 
    id: '', 
    tanggal_surat: today, 
    nomor_surat: '', 
    tujuan: '', 
    perihal: '', 
    user_request: '', 
    bagian_seksi_request: '', 
    evidence: '', 
    catatan: '' 
  }
  evidenceFile.value = null
  showModal.value = true

  await nextTick()
  setTimeout(() => {
    initModalSelect2()
  }, 50)
}

const closeModal = () => {
  showModal.value = false
  evidenceFile.value = null
}

const editItem = async (id) => {
  try {
    if (usersList.value.length === 0 || bagianSeksiList.value.length === 0) {
      await fetchOptions()
    }
    const res = await api.post('/surat-keluar/detail', { id })
    const data = res.data
    form.value = {
      id: data.id,
      tanggal_surat: data.tanggal_surat ? data.tanggal_surat.split('T')[0] : '',
      nomor_surat: data.nomor_surat !== null && data.nomor_surat !== undefined ? data.nomor_surat : '',
      tujuan: data.tujuan || '',
      perihal: data.perihal || '',
      user_request: data.user_request || '',
      bagian_seksi_request: data.bagian_seksi_request || '',
      evidence: data.evidence || '',
      catatan: data.catatan || ''
    }
    isEdit.value = true
    evidenceFile.value = null
    showModal.value = true

    await nextTick()
    setTimeout(() => {
      initModalSelect2()
    }, 50)
  } catch (error) {
    console.error('Error fetching detail for edit', error)
    window.Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: 'Gagal mengambil data surat keluar'
    })
  }
}

const viewDetail = async (id) => {
  try {
    const res = await api.post('/surat-keluar/detail', { id })
    detailItem.value = res.data
    showDetailModal.value = true
  } catch (error) {
    console.error('Error fetching detail', error)
    window.Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: 'Gagal mengambil detail surat keluar'
    })
  }
}

const saveData = async () => {
  if (!form.value.tanggal_surat || !form.value.tujuan || !form.value.perihal) {
    window.Swal.fire({
      icon: 'warning',
      title: 'Validasi',
      text: 'Tanggal Surat, Tujuan, dan Perihal wajib diisi!'
    })
    return
  }

  if (isEdit.value && (!form.value.nomor_surat || form.value.nomor_surat < 1)) {
    window.Swal.fire({
      icon: 'warning',
      title: 'Validasi',
      text: 'Nomor Surat (Angka) wajib diisi!'
    })
    return
  }

  try {
    const formData = new FormData()
    formData.append('tanggal_surat', form.value.tanggal_surat)
    if (isEdit.value && form.value.nomor_surat) {
      formData.append('nomor_surat', form.value.nomor_surat)
    }
    formData.append('tujuan', form.value.tujuan)
    formData.append('perihal', form.value.perihal)
    
    if (form.value.user_request) {
      formData.append('user_request', form.value.user_request)
    }
    if (form.value.bagian_seksi_request) {
      formData.append('bagian_seksi_request', form.value.bagian_seksi_request)
    }
    if (form.value.catatan) {
      formData.append('catatan', form.value.catatan)
    }
    if (evidenceFile.value) {
      formData.append('evidence', evidenceFile.value)
    }

    if (isEdit.value) {
      formData.append('id', form.value.id)
      await api.post('/surat-keluar/update', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      window.Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Data surat keluar berhasil diperbarui!',
        timer: 1500,
        showConfirmButton: false
      })
    } else {
      const res = await api.post('/surat-keluar/create', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      const createdNomor = res.data?.data?.nomor_surat
      window.Swal.fire({
        icon: 'success',
        title: 'Surat Keluar Berhasil Ditambahkan!',
        html: `Nomor Surat Keluar yang diterbitkan:<br><span class="badge bg-primary fs-5 mt-2 px-3 py-2">No. ${createdNomor}</span>`,
        confirmButtonText: 'Selesai',
        confirmButtonColor: '#5d87ff'
      })
    }

    closeModal()
    if (dataTableInstance) {
      dataTableInstance.ajax.reload()
    }
  } catch (error) {
    console.error('Error saving surat keluar', error)
    window.Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data.'
    })
  }
}

const deleteItem = (id) => {
  window.Swal.fire({
    title: 'Hapus Surat Keluar?',
    text: 'Data yang dihapus tidak dapat dipulihkan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then(async (result) => {
    if (result.isConfirmed || result.value) {
      try {
        await api.post('/surat-keluar/delete', { id })
        window.Swal.fire({
          icon: 'success',
          title: 'Terhapus!',
          text: 'Data surat keluar berhasil dihapus.',
          timer: 1500,
          showConfirmButton: false
        })
        if (dataTableInstance) {
          dataTableInstance.ajax.reload()
        }
      } catch (error) {
        console.error('Error deleting surat keluar', error)
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
  fetchOptions()
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
