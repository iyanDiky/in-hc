<template>
  <div>
    <!-- Breadcrumb Header -->
    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
      <div class="card-body px-4 py-3">
        <div class="row align-items-center">
          <div class="col-9">
            <h4 class="fw-semibold mb-8">Surat Masuk</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="#">Persuratan</a></li>
                <li class="breadcrumb-item" aria-current="page">Surat Masuk</li>
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
                placeholder="Cari perihal, nomor, pengirim..." 
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
            <i class="ti ti-plus fs-4"></i> Tambah Surat Masuk
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
              <label class="form-label fs-3 fw-semibold mb-1">Status Disposisi</label>
              <select class="form-select" v-model="filter.statusDisposisi">
                <option value="">Semua Status</option>
                <option value="sudah">Sudah Disposisi</option>
                <option value="belum">Belum Disposisi</option>
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
          <table id="suratMasukTable" class="table align-middle text-nowrap mb-0 w-100">
            <thead>
              <tr>
                <th scope="col" style="min-width: 300px;">Perihal & Nomor Surat</th>
                <th scope="col">Tanggal Surat</th>
                <th scope="col">Status Disposisi</th>
                <th scope="col">Pengirim</th>
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
            <h5 class="modal-title">{{ isEdit ? 'Edit Surat Masuk' : 'Tambah Surat Masuk' }}</h5>
            <button type="button" class="btn-close" @click="closeModal()"></button>
          </div>
          <div class="modal-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
                <input type="date" class="form-control" v-model="form.tanggal_surat">
                </div>
                <div class="col-md-6 mb-3">
                <label class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="form.nomor_surat" placeholder="Masukkan nomor surat">
                </div>
                <div class="col-md-6 mb-3">
                <label class="form-label">Pengirim <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="form.pengirim" placeholder="Asal/Pengirim surat">
                </div>
                <div class="col-md-6 mb-3">
                <label class="form-label">Tujuan <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="form.tujuan" placeholder="Tujuan surat">
                </div>
                <div class="col-md-12 mb-3">
                <label class="form-label">Perihal <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="form.perihal" placeholder="Perihal / Judul surat">
                </div>
                <div class="col-md-12 mb-3">
                <label class="form-label">Evidence (Opsional) <small v-if="isEdit && form.evidence" class="text-info">- Berkas tersimpan. Unggah untuk mengganti.</small></label>
                <input type="file" class="form-control" @change="handleFileUpload" accept=".pdf,image/png,image/jpeg,image/jpg">
                <div v-if="isEdit && form.evidence" class="mt-1">
                    <a :href="getFileUrl(form.evidence)" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Berkas Saat Ini</a>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api, { getStorageUrl } from '../utils/api'

const router = useRouter()

const showModal = ref(false)
const isEdit = ref(false)
const showFilter = ref(false)
const searchQuery = ref('')

const filter = ref({
  startDate: '',
  endDate: '',
  statusDisposisi: ''
})

const isFilterActive = computed(() => {
  return !!(filter.value.startDate || filter.value.endDate || filter.value.statusDisposisi)
})

const form = ref({ 
  id: '', 
  tanggal_surat: '', 
  nomor_surat: '', 
  pengirim: '', 
  tujuan: '', 
  perihal: '', 
  evidence: '', 
  catatan: '' 
})

const getFileUrl = (path) => {
  return getStorageUrl(path)
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

const evidenceFile = ref(null)

const handleFileUpload = (event) => {
  evidenceFile.value = event.target.files[0]
}

let dataTableInstance = null
let searchTimeout = null

const toggleFilter = () => {
  showFilter.value = !showFilter.value
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
  filter.value.statusDisposisi = ''
  if (dataTableInstance) {
    dataTableInstance.ajax.reload()
  }
}

const initDataTable = () => {
  if (dataTableInstance) {
    dataTableInstance.destroy()
  }
  
  // Tunggu sampai jQuery dan DataTables ter-load
  if (!window.$ || !window.$.fn.dataTable) {
    setTimeout(initDataTable, 100)
    return
  }

  dataTableInstance = window.$('#suratMasukTable').DataTable({
    serverSide: true,
    processing: true,
    order: [],
    dom: 'rt<"d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 border-top"lip>',
    ajax: async function (data, callback, settings) {
      try {
        const payload = {
          ...data,
          start_date: filter.value.startDate || null,
          end_date: filter.value.endDate || null,
          status_disposisi: filter.value.statusDisposisi || null
        }
        const response = await api.post('/surat-masuk/datatables', payload)
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
    columns: [
      { 
        data: 'perihal',
        render: function(data, type, row) {
          const safePerihal = escapeHtml(row.perihal || '-')
          const safeNomor = escapeHtml(row.nomor_surat || '-')
          return `
            <div class="d-flex align-items-center">
              <div class="rounded-2 p-2 bg-light-primary text-primary me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                <i class="ti ti-file-text fs-6"></i>
              </div>
              <div>
                <h6 class="fw-semibold mb-0 fs-4 text-truncate" style="max-width: 340px;" title="${safePerihal}">${safePerihal}</h6>
                <p class="mb-0 text-muted fs-3">${safeNomor}</p>
              </div>
            </div>
          `;
        }
      },
      { 
        data: 'tanggal_surat',
        render: function(data) {
          return `<p class="mb-0 fw-normal">${formatDate(data)}</p>`;
        }
      },
      {
        data: 'disposisi_exists',
        orderable: false,
        render: function(data) {
          if (data) {
            return `
              <div class="d-flex align-items-center">
                <span class="bg-success p-1 rounded-circle d-inline-block me-2" style="width: 8px; height: 8px;"></span>
                <p class="mb-0 text-success fw-medium fs-3">Sudah Disposisi</p>
              </div>
            `;
          } else {
            return `
              <div class="d-flex align-items-center">
                <span class="bg-warning p-1 rounded-circle d-inline-block me-2" style="width: 8px; height: 8px;"></span>
                <p class="mb-0 text-warning fw-medium fs-3">Belum Disposisi</p>
              </div>
            `;
          }
        }
      },
      { 
        data: 'pengirim',
        render: function(data) {
          return `<h6 class="mb-0 fs-4 fw-medium">${escapeHtml(data || '-')}</h6>`;
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
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger btn-delete" data-id="${row.id}" href="javascript:void(0)">
                    <i class="ti ti-trash fs-4"></i> Hapus
                  </a>
                </li>
              </ul>
            </div>
          `;
        }
      }
    ],
    order: [[1, "desc"]],
    language: {
      search: "Cari:",
      lengthMenu: "Tampilkan _MENU_ data",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
      infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
      infoFiltered: "(disaring dari _MAX_ total data)",
      paginate: {
        first: "Awal",
        last: "Akhir",
        next: "Selanjutnya",
        previous: "Sebelumnya"
      }
    },
    drawCallback: function() {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return window.bootstrap.Tooltip.getInstance(tooltipTriggerEl) || new window.bootstrap.Tooltip(tooltipTriggerEl)
      })
    }
  })

  // Bind events for dynamically created buttons inside DataTables
  window.$('#suratMasukTable tbody').off('click', '.btn-detail')
  window.$('#suratMasukTable tbody').on('click', '.btn-detail', function(e) {
    e.preventDefault()
    const data = dataTableInstance.row(window.$(this).closest('tr')).data()
    if (data) openDetail(data)
  })

  window.$('#suratMasukTable tbody').off('click', '.btn-edit')
  window.$('#suratMasukTable tbody').on('click', '.btn-edit', function(e) {
    e.preventDefault()
    const data = dataTableInstance.row(window.$(this).closest('tr')).data()
    if (data) openModal(data)
  })

  window.$('#suratMasukTable tbody').off('click', '.btn-delete')
  window.$('#suratMasukTable tbody').on('click', '.btn-delete', function(e) {
    e.preventDefault()
    const data = dataTableInstance.row(window.$(this).closest('tr')).data()
    if (data) deleteItem(data.id)
  })
}

const fetchItems = async () => {
  if (!dataTableInstance) {
    initDataTable()
  } else {
    dataTableInstance.ajax.reload(null, false)
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(date)
}

const openModal = (item = null) => {
  document.querySelectorAll('.tooltip').forEach(el => el.remove())
  if (item) {
    isEdit.value = true
    form.value = { ...item }
  } else {
    isEdit.value = false
    form.value = { 
        id: '', 
        tanggal_surat: '', 
        nomor_surat: '', 
        pengirim: '', 
        tujuan: '', 
        perihal: '', 
        evidence: '', 
        catatan: '' 
    }
  }
  evidenceFile.value = null
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const openDetail = (item) => {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    const tooltip = window.bootstrap.Tooltip.getInstance(el)
    if (tooltip) tooltip.hide()
  })
  document.querySelectorAll('.tooltip').forEach(el => el.remove())
  router.push(`/surat-masuk/${item.id}/detail`)
}

const saveData = async () => {
  try {
    const formData = new FormData()
    for (const key in form.value) {
      if (form.value[key] !== null && form.value[key] !== undefined) {
        formData.append(key, form.value[key])
      }
    }
    if (evidenceFile.value) {
      formData.append('evidence', evidenceFile.value)
    }

    if (isEdit.value) {
      await api.post('/surat-masuk/update', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      await api.post('/surat-masuk/create', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    }
    closeModal()
    fetchItems()
    window.Swal.fire('Berhasil!', 'Data berhasil disimpan.', 'success')
  } catch (error) {
    window.Swal.fire('Gagal!', error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data', 'error')
  }
}

const deleteItem = (id) => {
  document.querySelectorAll('.tooltip').forEach(el => el.remove())
  window.Swal.fire({
    title: 'Apakah Anda yakin?',
    text: "Data yang dihapus tidak dapat dikembalikan!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then(async (result) => {
    if (result.isConfirmed || result.value) {
      try {
        await api.post('/surat-masuk/delete', { id })
        fetchItems()
        window.Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success')
      } catch (error) {
        window.Swal.fire('Gagal!', error.response?.data?.message || 'Terjadi kesalahan saat menghapus data', 'error')
      }
    }
  })
}

onMounted(() => {
  fetchItems()
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
