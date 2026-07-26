<template>
  <div>
    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
      <div class="card-body px-4 py-3">
        <div class="row align-items-center">
          <div class="col-9">
            <h4 class="fw-semibold mb-8">Data User</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="text-muted" href="#">Pengaturan</a></li>
                <li class="breadcrumb-item" aria-current="page">Users</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="card w-100 position-relative overflow-hidden">
      <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-semibold mb-0 lh-sm">Daftar User</h5>
        <button class="btn btn-primary" @click="openModal()">Tambah Data</button>
      </div>
      <div class="card-body p-4">
        <div class="table-responsive rounded-2 mb-4">
          <table class="table border text-nowrap customize-table mb-0 align-middle">
            <thead class="text-dark fs-4">
              <tr>
                <th><h6 class="fs-4 fw-semibold mb-0">NPP</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Nama</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Username</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Tempat/Tgl Lahir</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Jabatan</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Bagian Seksi</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Level</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Aksi</h6></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>{{ item.npp }}</td>
                <td>{{ item.nama }}</td>
                <td>{{ item.username }}</td>
                <td>{{ item.tempat_lahir }}, {{ item.tanggal_lahir }}</td>
                <td>{{ item.jabatan?.jabatan }}</td>
                <td>{{ item.bagian_seksi?.bagian_seksi }}</td>
                <td><span class="badge bg-primary rounded-3 fw-semibold">{{ item.level }}</span></td>
                <td>
                  <button class="btn btn-sm btn-info me-2" @click="openModal(item)" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="ti ti-pencil fs-5"></i></button>
                  <button class="btn btn-sm btn-warning me-2" @click="resetPasswordItem(item.id)" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset Password"><i class="ti ti-key fs-5"></i></button>
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
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEdit ? 'Edit User' : 'Tambah User' }}</h5>
            <button type="button" class="btn-close" @click="closeModal()"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">NPP</label>
                <input type="text" class="form-control" v-model="form.npp">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" v-model="form.nama">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" v-model="form.username">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Level</label>
                <select class="form-select" v-model="form.level">
                  <option value="">Pilih Level...</option>
                  <option value="admin">Admin</option>
                  <option value="user">User</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Tempat Lahir</label>
                <input type="text" class="form-control" v-model="form.tempat_lahir">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control" v-model="form.tanggal_lahir">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Jabatan</label>
                <select class="select2-jabatan form-control" v-model="form.jabatan_id" style="width: 100%; height: 36px">
                  <option value="">Pilih Jabatan...</option>
                  <option v-for="jb in jabatanList" :key="jb.id" :value="jb.id">
                    {{ jb.jabatan }}
                  </option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Bagian Seksi</label>
                <select class="select2-seksi form-control" v-model="form.bagian_seksi_id" style="width: 100%; height: 36px">
                  <option value="">Pilih Bagian Seksi...</option>
                  <option v-for="bs in bagianSeksiList" :key="bs.id" :value="bs.id">
                    {{ bs.bagian_seksi }}
                  </option>
                </select>
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
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'

const api = axios.create({ baseURL: 'http://localhost:8000/api' })
const items = ref([])
const jabatanList = ref([])
const bagianSeksiList = ref([])
const showModal = ref(false)
const isEdit = ref(false)
const form = ref({ 
  id: '', 
  npp: '', 
  nama: '',
  username: '', 
  password: '', 
  level: '', 
  tempat_lahir: '', 
  tanggal_lahir: '', 
  jabatan_id: '', 
  bagian_seksi_id: '' 
})

const fetchItems = async () => {
  try {
    const res = await api.post('/users/list')
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

const fetchJabatan = async () => {
  try {
    const res = await api.post('/jabatan/list', { limit: 100 })
    jabatanList.value = res.data.data
  } catch (error) {
    console.error(error)
  }
}

const fetchBagianSeksi = async () => {
  try {
    const res = await api.post('/bagian-seksi/list', { limit: 100 })
    bagianSeksiList.value = res.data.data
  } catch (error) {
    console.error(error)
  }
}

const openModal = async (item = null) => {
  if (item) {
    isEdit.value = true
    form.value = { ...item, password: '' }
  } else {
    isEdit.value = false
    form.value = { 
      id: '', npp: '', nama: '', username: '', password: '', level: '', 
      tempat_lahir: '', tanggal_lahir: '', jabatan_id: '', bagian_seksi_id: '' 
    }
  }
  showModal.value = true

  await nextTick()
  setTimeout(() => {
    // Initialize Jabatan Select2
    $('.select2-jabatan').select2({
      dropdownParent: $('#formModal')
    })
    $('.select2-jabatan').val(form.value.jabatan_id).trigger('change.select2')
    $('.select2-jabatan').on('change', function() {
      form.value.jabatan_id = $(this).val()
    })

    // Initialize Bagian Seksi Select2
    $('.select2-seksi').select2({
      dropdownParent: $('#formModal')
    })
    $('.select2-seksi').val(form.value.bagian_seksi_id).trigger('change.select2')
    $('.select2-seksi').on('change', function() {
      form.value.bagian_seksi_id = $(this).val()
    })
  }, 50)
}

const closeModal = () => {
  showModal.value = false
}

const saveData = async () => {
  try {
    if (isEdit.value) {
      await api.post('/users/update', form.value)
    } else {
      await api.post('/users/create', form.value)
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
        await api.post('/users/delete', { id })
        fetchItems()
        window.Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success')
      } catch (error) {
        window.Swal.fire('Gagal!', error.response?.data?.message || 'Terjadi kesalahan saat menghapus data', 'error')
      }
    }
  })
}

const resetPasswordItem = (id) => {
  window.Swal.fire({
    title: 'Reset Password?',
    text: "Password akan dikembalikan ke default (Bankkalsel1*)",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ffae1f',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, reset!',
    cancelButtonText: 'Batal'
  }).then(async (result) => {
    if (result.isConfirmed || result.value) {
      try {
        await api.post('/users/reset-password', { id })
        window.Swal.fire('Berhasil!', 'Password berhasil direset ke default.', 'success')
      } catch (error) {
        window.Swal.fire('Gagal!', error.response?.data?.message || 'Terjadi kesalahan saat mereset password', 'error')
      }
    }
  })
}

onMounted(() => {
  fetchItems()
  fetchJabatan()
  fetchBagianSeksi()
})
</script>
