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
          <table class="table border text-nowrap customize-table mb-0 align-middle">
            <thead class="text-dark fs-4">
              <tr>
                <th><h6 class="fs-4 fw-semibold mb-0">Kode</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Jabatan</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Aksi</h6></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>{{ item.kode }}</td>
                <td>{{ item.jabatan }}</td>
                <td>
                  <button class="btn btn-sm btn-info me-2" @click="openModal(item)">Edit</button>
                  <button class="btn btn-sm btn-danger" @click="deleteItem(item.id)">Hapus</button>
                </td>
              </tr>
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
import { ref, onMounted } from 'vue'
import axios from 'axios'

const api = axios.create({ baseURL: 'http://localhost:8000/api' })
const items = ref([])
const showModal = ref(false)
const isEdit = ref(false)
const form = ref({ id: '', kode: '', jabatan: '' })

const fetchItems = async () => {
  try {
    const res = await api.post('/jabatan/list')
    items.value = res.data.data
  } catch (error) {
    console.error(error)
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
  } catch (error) {
    alert(error.response?.data?.message || 'Error saving data')
  }
}

const deleteItem = async (id) => {
  if (confirm('Hapus data ini?')) {
    try {
      await api.post('/jabatan/delete', { id })
      fetchItems()
    } catch (error) {
      console.error(error)
    }
  }
}

onMounted(() => {
  fetchItems()
})
</script>
