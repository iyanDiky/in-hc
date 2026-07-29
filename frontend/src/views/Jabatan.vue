<template>
  <div>
    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
      <div class="card-body px-4 py-3">
        <div class="row align-items-center">
          <div class="col-9">
            <h4 class="fw-semibold mb-8">Jabatan</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="text-muted" href="#">Master Data</a></li>
                <li class="breadcrumb-item" aria-current="page">Jabatan</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="card w-100 position-relative overflow-hidden">
      <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-semibold mb-0 lh-sm">Data Jabatan</h5>
        <button class="btn btn-primary" @click="openModal()">Tambah Data</button>
      </div>
      <div class="card-body p-4">
        <div class="table-responsive rounded-2 mb-4">
          <table id="jabatanTable" class="table border text-nowrap customize-table mb-0 align-middle">
            <thead class="text-dark fs-4">
              <tr>
                <th><h6 class="fs-4 fw-semibold mb-0">Kode</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Jabatan</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Aksi</h6></th>
              </tr>
            </thead>
            <tbody>
              <!-- DataTables will populate this automatically -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Form (Simplified) -->
    <div v-if="showModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEdit ? 'Edit Jabatan' : 'Tambah Jabatan' }}</h5>
            <button type="button" class="btn-close" @click="closeModal()"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Kode</label>
              <input type="text" class="form-control" v-model="form.kode">
            </div>
            <div class="mb-3">
              <label class="form-label">Jabatan</label>
              <input type="text" class="form-control" v-model="form.jabatan">
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
import { ref, onMounted, nextTick } from 'vue'
import api from '../utils/api'


const items = ref([])
const showModal = ref(false)
const isEdit = ref(false)
const form = ref({ id: '', kode: '', jabatan: '' })

let dataTableInstance = null

const initDataTable = () => {
  if (dataTableInstance) {
    dataTableInstance.destroy()
  }

  if (!window.$ || !window.$.fn.dataTable) {
    setTimeout(initDataTable, 100)
    return
  }

  dataTableInstance = window.$('#jabatanTable').DataTable({
    serverSide: true,
    processing: true,
    ajax: async function (data, callback, settings) {
      try {
        const response = await api.post('/jabatan/datatables', data)
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
        data: 'kode',
        render: function(data) {
          return `<p class="mb-0 fw-normal">${data || '-'}</p>`;
        }
      },
      { 
        data: 'jabatan',
        render: function(data) {
          return `<p class="mb-0 fw-normal">${data || '-'}</p>`;
        }
      },
      { 
        data: null, 
        orderable: false,
        render: function(data, type, row) {
          return `
            <button class="btn btn-sm btn-info me-2 btn-edit" data-id="${row.id}" data-bs-toggle="tooltip" title="Edit">
              <i class="ti ti-pencil fs-5"></i>
            </button>
            <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" data-bs-toggle="tooltip" title="Hapus">
              <i class="ti ti-trash fs-5"></i>
            </button>
          `;
        }
      }
    ],
    order: [[1, "asc"]],
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

  window.$('#jabatanTable tbody').off('click', '.btn-edit')
  window.$('#jabatanTable tbody').on('click', '.btn-edit', function() {
    const data = dataTableInstance.row(window.$(this).parents('tr')).data()
    openModal(data)
  })

  window.$('#jabatanTable tbody').off('click', '.btn-delete')
  window.$('#jabatanTable tbody').on('click', '.btn-delete', function() {
    const data = dataTableInstance.row(window.$(this).parents('tr')).data()
    deleteItem(data.id)
  })
}

const fetchItems = async () => {
  if (!dataTableInstance) {
    initDataTable()
  } else {
    dataTableInstance.ajax.reload(null, false)
  }
}

const openModal = (item = null) => {
  if (item) {
    isEdit.value = true
    form.value = { ...item }
  } else {
    isEdit.value = false
    form.value = { id: '', kode: '', jabatan: '' }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const saveData = async () => {
  try {
    if (isEdit.value) {
      await api.post('/jabatan/update', form.value)
    } else {
      await api.post('/jabatan/create', form.value)
    }
    closeModal()
    fetchItems()
    window.Swal.fire('Berhasil!', 'Data berhasil disimpan.', 'success')
  } catch (error) {
    window.Swal.fire('Gagal!', error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data', 'error')
  }
}

const deleteItem = (id) => {
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
        await api.post('/jabatan/delete', { id })
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
