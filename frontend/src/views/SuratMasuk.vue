<template>
  <div>
    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
      <div class="card-body px-4 py-3">
        <div class="row align-items-center">
          <div class="col-9">
            <h4 class="fw-semibold mb-8">Surat Masuk</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="text-muted" href="#">Persuratan</a></li>
                <li class="breadcrumb-item" aria-current="page">Surat Masuk</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="card w-100 position-relative overflow-hidden">
      <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-semibold mb-0 lh-sm">Data Surat Masuk</h5>
        <button class="btn btn-primary" @click="openModal()">Tambah Data</button>
      </div>
      <div class="card-body p-4">
        <div class="table-responsive rounded-2 mb-4">
          <table class="table border text-nowrap customize-table mb-0 align-middle">
            <thead class="text-dark fs-4">
              <tr>
                <th><h6 class="fs-4 fw-semibold mb-0">Tgl Surat</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Nomor Surat</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Pengirim</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Perihal</h6></th>
                <th><h6 class="fs-4 fw-semibold mb-0">Aksi</h6></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items.data" :key="item.id">
                <td>{{ item.tanggal_surat }}</td>
                <td>{{ item.nomor_surat }}</td>
                <td>{{ item.pengirim }}</td>
                <td>
                  <p class="mb-0 text-truncate" style="max-width: 200px;" :title="item.perihal">
                    {{ item.perihal }}
                  </p>
                </td>
                <td>
                  <button class="btn btn-sm btn-secondary me-2" @click="openDetail(item)" data-bs-toggle="tooltip" data-bs-placement="top" title="Detail"><i class="ti ti-eye fs-5"></i></button>
                  <button class="btn btn-sm btn-info me-2" @click="openModal(item)" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="ti ti-pencil fs-5"></i></button>
                  <button class="btn btn-sm btn-danger" @click="deleteItem(item.id)" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus"><i class="ti ti-trash fs-5"></i></button>
                </td>
              </tr>
              <tr v-if="!items.data || items.data.length === 0">
                <td colspan="6" class="text-center text-muted py-4">Tidak ada data</td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Simple Pagination (if needed later) -->
        <div class="d-flex justify-content-between align-items-center" v-if="items.total > 0">
            <small class="text-muted">Menampilkan {{ items.from }} - {{ items.to }} dari {{ items.total }} data</small>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
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

    <!-- Modal Detail -->
    <div v-if="showDetailModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detail Surat Masuk</h5>
            <button type="button" class="btn-close" @click="closeDetail()"></button>
          </div>
          <div class="modal-body">
            <table class="table table-bordered mb-4">
              <tbody>
                <tr><th style="width: 30%">Tanggal Surat</th><td>{{ detailItem.tanggal_surat }}</td></tr>
                <tr><th>Nomor Surat</th><td>{{ detailItem.nomor_surat }}</td></tr>
                <tr><th>Pengirim</th><td>{{ detailItem.pengirim }}</td></tr>
                <tr><th>Tujuan</th><td>{{ detailItem.tujuan }}</td></tr>
                <tr><th>Perihal</th><td>{{ detailItem.perihal }}</td></tr>
                <tr><th>Catatan</th><td>{{ detailItem.catatan || '-' }}</td></tr>
              </tbody>
            </table>
            <h6>Preview Evidence</h6>
            <div v-if="detailItem.evidence" class="border rounded p-2 text-center" style="background: #f8f9fa;">
                <object :data="getFileUrl(detailItem.evidence)" type="application/pdf" width="100%" height="400px" v-if="detailItem.evidence.endsWith('.pdf')">
                    <p>Browser Anda tidak mendukung preview PDF. <a :href="getFileUrl(detailItem.evidence)" target="_blank">Download di sini</a>.</p>
                </object>
                <img v-else :src="getFileUrl(detailItem.evidence)" alt="Evidence Preview" class="img-fluid" style="max-height: 400px;">
            </div>
            <div v-else class="alert alert-secondary text-center">
                Tidak ada file evidence yang diunggah.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" @click="closeDetail()">Tutup</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import api from '../utils/api'


const items = ref({ data: [], total: 0, from: 0, to: 0 })
const showModal = ref(false)
const showDetailModal = ref(false)
const isEdit = ref(false)
const detailItem = ref({})
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
  return `http://localhost:8000/storage/${path}`
}

const evidenceFile = ref(null)

const handleFileUpload = (event) => {
  evidenceFile.value = event.target.files[0]
}

const fetchItems = async () => {
  try {
    const res = await api.post('/surat-masuk/list', { limit: 15 })
    items.value = res.data
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

const openModal = (item = null) => {
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
  detailItem.value = { ...item }
  showDetailModal.value = true
}

const closeDetail = () => {
  showDetailModal.value = false
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
