<template>
  <div>
    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
      <div class="card-body px-4 py-3">
        <div class="row align-items-center">
          <div class="col-9">
            <h4 class="fw-semibold mb-8">Detail Surat Masuk</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><router-link to="/surat-masuk" class="text-muted">Surat Masuk</router-link></li>
                <li class="breadcrumb-item" aria-current="page">Detail</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Surat Masuk Card -->
    <div class="card w-100 position-relative overflow-hidden mb-4">
      <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center bg-light">
        <h5 class="card-title fw-semibold mb-0 lh-sm">Informasi Surat</h5>
      </div>
      <div class="card-body p-4">
        <div class="row">
          <div class="col-md-6 mb-3">
            <h6 class="fw-semibold">Tanggal Surat</h6>
            <p class="mb-0 text-muted">{{ formatDate(detailItem?.tanggal_surat) }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <h6 class="fw-semibold">Nomor Surat</h6>
            <p class="mb-0 text-muted">{{ detailItem?.nomor_surat }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <h6 class="fw-semibold">Pengirim</h6>
            <p class="mb-0 text-muted">{{ detailItem?.pengirim }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <h6 class="fw-semibold">Tujuan</h6>
            <p class="mb-0 text-muted">{{ detailItem?.tujuan }}</p>
          </div>
          <div class="col-md-12 mb-3">
            <h6 class="fw-semibold">Perihal</h6>
            <p class="mb-0 text-muted">{{ detailItem?.perihal }}</p>
          </div>
          <div class="col-md-12 mb-3" v-if="detailItem?.catatan">
            <h6 class="fw-semibold">Catatan</h6>
            <p class="mb-0 text-muted">{{ detailItem?.catatan }}</p>
          </div>
          <div class="col-md-12">
            <h6 class="fw-semibold">Evidence</h6>
            <div v-if="detailItem?.evidence">
              <a :href="getFileUrl(detailItem.evidence)" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Berkas Evidence</a>
            </div>
            <div v-else>
              <span class="text-muted">-</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Disposisi History & Input -->
    <div class="card w-100 position-relative overflow-hidden">
      <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center bg-light">
        <h5 class="card-title fw-semibold mb-0 lh-sm">Disposisi Surat</h5>
      </div>
      <div class="card-body p-4">
        
        <!-- Timeline Disposisi -->
        <div class="mb-5" v-if="disposisiList.length > 0">
          <h6 class="fw-semibold mb-4">Riwayat Disposisi</h6>
          <div class="d-flex flex-column gap-4">
            <div v-for="disp in disposisiList" :key="disp.id" class="d-flex align-items-start">
              <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; min-width: 40px;">
                <i class="ti ti-user fs-5"></i>
              </div>
              <div class="ms-3 w-100 bg-light p-3 rounded">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <h6 class="fw-semibold mb-0">
                    {{ disp.disposisi_oleh?.nama || 'Unknown' }}
                    <small v-if="disp.jabatan || disp.bagian_seksi" class="text-muted d-block fw-normal" style="font-size: 0.85em;">
                      {{ disp.jabatan?.jabatan || '' }} {{ disp.jabatan && disp.bagian_seksi ? '-' : '' }} {{ disp.bagian_seksi?.bagian_seksi || '' }}
                    </small>
                  </h6>
                  <div class="d-flex align-items-center">
                    <small class="text-muted me-2">{{ formatDateTime(disp.disposisi_waktu) }}</small>
                    <button class="btn btn-sm btn-light-danger text-danger p-1 rounded d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;" @click="deleteDisposisi(disp.id)" title="Hapus Disposisi">
                      <i class="ti ti-trash"></i>
                    </button>
                  </div>
                </div>
                <div class="mb-2">
                  <span v-for="tujuan in disp.tujuans" :key="tujuan.id" class="badge bg-secondary me-1">
                    @{{ tujuan.tujuan?.bagian_seksi }}
                  </span>
                </div>
                <p class="mb-0 text-dark">{{ disp.catatan }}</p>
                
                <div v-if="disp.evidence" class="mt-2">
                  <a :href="getFileUrl(disp.evidence)" target="_blank" class="btn btn-sm btn-outline-info"><i class="ti ti-paperclip"></i> Lampiran</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="alert alert-secondary mb-5">
          Belum ada disposisi untuk surat ini.
        </div>

        <!-- Form Input Disposisi -->
        <h6 class="fw-semibold mb-3">Tambah Disposisi Baru</h6>
        
        <div class="row mb-3">
          <div class="col-md-4 mb-2 mb-md-0">
            <label class="form-label fw-semibold">Disposisi Atas Nama</label>
            <select class="form-select" v-model="disposisiOlehId">
              <option value="">Pilih User</option>
              <option v-for="user in userList" :key="user.id" :value="user.id">
                {{ user.nama }}
              </option>
            </select>
          </div>
          <div class="col-md-4 mb-2 mb-md-0">
            <label class="form-label fw-semibold">Jabatan</label>
            <select class="form-select" v-model="disposisiOlehJabatanId">
              <option value="">Pilih Jabatan</option>
              <option v-for="jab in jabatanList" :key="jab.id" :value="jab.id">
                {{ jab.jabatan }}
              </option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Bagian Seksi</label>
            <select class="form-select" v-model="disposisiOlehBagianSeksiId">
              <option value="">Pilih Bagian Seksi</option>
              <option v-for="bag in bagianSeksiList" :key="bag.id" :value="bag.id">
                {{ bag.bagian_seksi }}
              </option>
            </select>
          </div>
        </div>
        <div class="border rounded p-3 position-relative">
          <div class="mb-2 position-relative">
            <textarea 
              class="form-control border-0 shadow-none" 
              rows="3" 
              placeholder="Ketik @ untuk memilih tujuan bagian seksi, lalu tulis keterangan disposisi..."
              v-model="catatan"
              @input="handleInput"
              ref="textareaRef"
              style="resize: none;"
            ></textarea>
            
            <!-- Mention Dropdown Menu -->
            <div v-if="showMentionMenu" class="dropdown-menu show shadow" style="position: absolute; bottom: 100%; left: 0; z-index: 1000; max-height: 200px; overflow-y: auto; margin-bottom: 5px;">
              <a 
                v-for="(seksi, index) in filteredBagianSeksi" 
                :key="seksi.id" 
                class="dropdown-item" 
                :class="{'active': index === mentionSelectedIndex}"
                href="javascript:void(0)"
                @click="selectMention(seksi)"
              >
                {{ seksi.bagian_seksi }}
              </a>
              <div v-if="filteredBagianSeksi.length === 0" class="dropdown-item text-muted">Tidak ditemukan</div>
            </div>
          </div>
          
          <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <div class="d-flex align-items-center">
              <label class="btn btn-sm btn-light mb-0 me-2 cursor-pointer">
                <i class="ti ti-paperclip"></i> Lampiran
                <input type="file" class="d-none" @change="handleFileUpload">
              </label>
              <span v-if="evidenceFile" class="text-primary small text-truncate" style="max-width: 200px;">{{ evidenceFile.name }}</span>
            </div>
            <button class="btn btn-primary" @click="submitDisposisi" :disabled="isSubmitting || !catatan.trim()">
              <i class="ti ti-send me-1"></i> Kirim Disposisi
            </button>
          </div>
        </div>
        
        <!-- Peringatan tag tujuan kosong -->
        <small v-if="taggedSeksi.length === 0 && catatan.trim()" class="text-warning d-block mt-2">
          <i class="ti ti-alert-circle"></i> Anda belum men-tag (@) satupun Bagian Seksi sebagai tujuan.
        </small>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../utils/api'

const route = useRoute()
const suratMasukId = route.params.id

const detailItem = ref(null)
const disposisiList = ref([])
const bagianSeksiList = ref([])
const jabatanList = ref([])
const userList = ref([])
const disposisiOlehId = ref('')
const disposisiOlehJabatanId = ref('')
const disposisiOlehBagianSeksiId = ref('')

// Form state
const catatan = ref('')
const evidenceFile = ref(null)
const isSubmitting = ref(false)
const textareaRef = ref(null)

// Mention system state
const showMentionMenu = ref(false)
const mentionKeyword = ref('')
const mentionSelectedIndex = ref(0)
const mentionStartIndex = ref(-1)
const taggedSeksi = ref([]) // array of objects { id, name }

const filteredBagianSeksi = computed(() => {
  if (!mentionKeyword.value) return bagianSeksiList.value
  const kw = mentionKeyword.value.toLowerCase()
  return bagianSeksiList.value.filter(bs => bs.bagian_seksi.toLowerCase().includes(kw))
})

const getFileUrl = (path) => {
  return `http://localhost:8000/storage/${path}`
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric'
  }).format(date)
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  }).format(date)
}

const handleFileUpload = (event) => {
  evidenceFile.value = event.target.files[0]
}

const fetchData = async () => {
  try {
    const res = await api.post('/surat-masuk/detail', { id: suratMasukId })
    detailItem.value = res.data
  } catch (error) {
    window.Swal.fire('Gagal!', 'Gagal memuat detail surat masuk', 'error')
  }
}

const fetchDisposisi = async () => {
  try {
    const res = await api.get(`/surat-masuk/${suratMasukId}/disposisi`)
    disposisiList.value = res.data.data
  } catch (error) {
    console.error('Failed to load disposisi', error)
  }
}

const fetchBagianSeksi = async () => {
  try {
    const res = await api.post('/bagian-seksi/list', { limit: 1000 })
    bagianSeksiList.value = res.data.data
  } catch (error) {
    console.error('Failed to load bagian seksi', error)
  }
}

const fetchJabatan = async () => {
  try {
    const res = await api.post('/jabatan/list', { limit: 1000 })
    jabatanList.value = res.data.data
  } catch (error) {
    console.error('Failed to load jabatan', error)
  }
}

const fetchUsers = async () => {
  try {
    const res = await api.post('/users/list', { limit: 1000 })
    userList.value = res.data.data
    
    // Set default value to logged in user
    const userData = localStorage.getItem('user_data')
    if (userData) {
      const parsed = JSON.parse(userData)
      disposisiOlehId.value = parsed.id
      disposisiOlehJabatanId.value = parsed.jabatan_id || ''
      disposisiOlehBagianSeksiId.value = parsed.bagian_seksi_id || ''
    }
  } catch (error) {
    console.error('Failed to load users', error)
  }
}

watch(disposisiOlehId, (newId) => {
  if (newId && userList.value.length > 0) {
    const user = userList.value.find(u => u.id === newId)
    if (user) {
      disposisiOlehJabatanId.value = user.jabatan_id || ''
      disposisiOlehBagianSeksiId.value = user.bagian_seksi_id || ''
    }
  }
})

// Mention Logic
const handleInput = (e) => {
  const cursorPosition = textareaRef.value.selectionStart
  const textBeforeCursor = catatan.value.substring(0, cursorPosition)
  
  // Find the last @ word
  const match = textBeforeCursor.match(/@(\w*)$/)
  
  if (match) {
    showMentionMenu.value = true
    mentionKeyword.value = match[1]
    mentionStartIndex.value = match.index
    mentionSelectedIndex.value = 0
  } else {
    showMentionMenu.value = false
  }
}

const selectMention = (seksi) => {
  // Replace the @keyword with @SeksiName
  const textBeforeCursor = catatan.value.substring(0, mentionStartIndex.value)
  const cursorPosition = textareaRef.value.selectionStart
  const textAfterCursor = catatan.value.substring(cursorPosition)
  
  const mentionText = `@${seksi.bagian_seksi.replace(/\s+/g, '_')} `
  
  catatan.value = textBeforeCursor + mentionText + textAfterCursor
  
  // Track this tagged seksi
  if (!taggedSeksi.value.find(t => t.id === seksi.id)) {
    taggedSeksi.value.push({ id: seksi.id, name: mentionText.trim() })
  }
  
  showMentionMenu.value = false
  
  // Reset focus
  setTimeout(() => {
    textareaRef.value.focus()
  }, 10)
}

const submitDisposisi = async () => {
  if (!catatan.value.trim()) return
  
  // Extract all valid tagged seksi from the actual text to ensure they weren't deleted
  const finalTags = []
  let cleanCatatan = catatan.value

  taggedSeksi.value.forEach(tag => {
    if (catatan.value.includes(tag.name)) {
      finalTags.push(tag.id)
      // Remove mention from catatan
      cleanCatatan = cleanCatatan.split(tag.name).join('')
    }
  })
  
  // Clean up any extra spaces
  cleanCatatan = cleanCatatan.replace(/\s+/g, ' ').trim()
  
  isSubmitting.value = true
  try {
    const formData = new FormData()
    formData.append('catatan', cleanCatatan)
    if (disposisiOlehId.value) {
      formData.append('disposisi_oleh', disposisiOlehId.value)
    }
    if (disposisiOlehJabatanId.value) {
      formData.append('disposisi_oleh_jabatan', disposisiOlehJabatanId.value)
    }
    if (disposisiOlehBagianSeksiId.value) {
      formData.append('disposisi_oleh_bagian_seksi', disposisiOlehBagianSeksiId.value)
    }
    
    // Add multiple arrays to FormData
    if (finalTags.length > 0) {
      finalTags.forEach(id => {
        formData.append('tujuan_bagian_seksi_ids[]', id)
      })
    } else {
      // API requires at least 1 tujuan
      window.Swal.fire('Peringatan', 'Anda harus men-tag (@) minimal 1 Bagian Seksi', 'warning')
      isSubmitting.value = false
      return
    }
    
    if (evidenceFile.value) {
      formData.append('evidence', evidenceFile.value)
    }
    
    await api.post(`/surat-masuk/${suratMasukId}/disposisi`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    // Reset form
    catatan.value = ''
    evidenceFile.value = null
    taggedSeksi.value = []
    
    // Refresh list
    fetchDisposisi()
    
    window.Swal.fire('Berhasil!', 'Disposisi terkirim.', 'success')
  } catch (error) {
    window.Swal.fire('Gagal!', error.response?.data?.message || 'Gagal mengirim disposisi', 'error')
  } finally {
    isSubmitting.value = false
  }
}

const deleteDisposisi = async (id) => {
  window.Swal.fire({
    title: 'Apakah Anda yakin?',
    text: "Disposisi yang dihapus tidak dapat dikembalikan!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then(async (result) => {
    if (result.isConfirmed || result.value) {
      try {
        await api.delete(`/surat-masuk/${suratMasukId}/disposisi/${id}`)
        fetchDisposisi()
        window.Swal.fire('Terhapus!', 'Disposisi berhasil dihapus.', 'success')
      } catch (error) {
        window.Swal.fire('Gagal!', error.response?.data?.message || 'Gagal menghapus disposisi', 'error')
      }
    }
  })
}

onMounted(() => {
  fetchData()
  fetchDisposisi()
  fetchBagianSeksi()
  fetchJabatan()
  fetchUsers()
})
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>
