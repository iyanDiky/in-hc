<template>
  <div>
    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
      <div class="card-body px-4 py-3">
        <div class="row align-items-center">
          <div class="col-9">
            <h4 class="fw-semibold mb-8">Bagian Seksi</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="text-muted" href="#">Master Data</a></li>
                <li class="breadcrumb-item" aria-current="page">Bagian Seksi</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="card w-100 position-relative overflow-hidden">
      <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-semibold mb-0 lh-sm">Data Bagian Seksi</h5>
        <button class="btn btn-primary" @click="openModal()">Tambah Data</button>
      </div>
      <div class="card-body p-4">
        <div class="table-responsive rounded-2 mb-4">
          <table class="table border text-nowrap customize-table mb-0 align-middle">
            <thead class="text-dark fs-4">
              <tr>
                <th><h6 class="fs-4 fw-semibold mb-0">Kode</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Bagian Seksi</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Unit Kerja</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Aksi</h6></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>{{ item.kode }}</td>
                <td>{{ item.bagian_seksi }}</td>
                <td>{{ item.unit_kerja?.unit_kerja }}</td>
                <td>
                  <button class="btn btn-sm btn-info me-2" @click="openModal(item)" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="ti ti-pencil fs-5"></i></button>
                  <button class="btn btn-sm btn-danger" @click="deleteItem(item.id)" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus"><i class="ti ti-trash fs-5"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" id="formModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEdit ? 'Edit Bagian Seksi' : 'Tambah Bagian Seksi' }}</h5>
            <button type="button" class="btn-close" @click="closeModal()"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Kode</label>
              <input type="text" class="form-control" v-model="form.kode">
            </div>
            <div class="mb-3">
              <label class="form-label">Bagian Seksi</label>
              <input type="text" class="form-control" v-model="form.bagian_seksi">
            </div>
            <div class="mb-3">
              <label class="form-label">Unit Kerja</label>
              <select class="select2 form-control" v-model="form.unit_kerja_id" style="width: 100%; height: 36px">
                <option value="">Pilih Unit Kerja...</option>
                <option v-for="uk in unitKerjaList" :key="uk.id" :value="uk.id">
                  {{ uk.unit_kerja }}
                </option>
              </select>
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
import axios from 'axios'

const api = axios.create({ baseURL: 'http://localhost:8000/api' })
const items = ref([])
const unitKerjaList = ref([])
const showModal = ref(false)
const isEdit = ref(false)
const form = ref({ id: '', kode: '', bagian_seksi: '', unit_kerja_id: '' })

const fetchItems = async () => {
  try {
    const res = await api.post('/bagian-seksi/list')
    items.value = res.data.data
    nextTick(() => {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return window.bootstrap.Tooltip.getInstance(tooltipTriggerEl) || new window.bootstrap.Tooltip(tooltipTriggerEl)
      })
    })
  } catch (error) {
    console.error(error)
  }
}

const fetchUnitKerja = async () => {
  try {
    // Get unit kerja for dropdown (set limit to 100 for now to get all)
    const res = await api.post('/unit-kerja/list', { limit: 100 })
    unitKerjaList.value = res.data.data
  } catch (error) {
    console.error(error)
  }
}

const openModal = async (item = null) => {
  if (item) {
    isEdit.value = true
    form.value = { ...item }
  } else {
    isEdit.value = false
    form.value = { id: '', kode: '', bagian_seksi: '', unit_kerja_id: '' }
  }
  showModal.value = true

  await nextTick()
  // Small delay to ensure modal is fully rendered before select2 binds to it
  setTimeout(() => {
    $('.select2').select2({
      dropdownParent: $('#formModal')
    })
    
    // Set initial value in select2
    $('.select2').val(form.value.unit_kerja_id).trigger('change.select2')

    // Update vue state when select2 changes
    $('.select2').on('change', function() {
      form.value.unit_kerja_id = $(this).val()
    })
  }, 50)
}

const closeModal = () => {
  showModal.value = false
}

const saveData = async () => {
  try {
    if (isEdit.value) {
      await api.post('/bagian-seksi/update', form.value)
    } else {
      await api.post('/bagian-seksi/create', form.value)
    }
    showModal.value = false
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
        await api.post('/bagian-seksi/delete', { id })
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
  fetchUnitKerja()
})
</script>
