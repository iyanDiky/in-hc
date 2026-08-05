<template>
  <div class="container-fluid">
    <!-- User Greeting Banner -->
    <div class="row">
      <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
          <div class="d-flex align-items-center gap-3">
            <div class="position-relative">
              <div class="border border-2 border-primary rounded-circle p-1">
                <img 
                  :src="userProfileImg" 
                  class="rounded-circle" 
                  alt="user profile" 
                  width="56" 
                  height="56"
                  @error="onImageError"
                />
              </div>
              <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle">
                <span class="visually-hidden">Online</span>
              </span>
            </div>
            <div>
              <h3 class="fw-semibold mb-1">
                {{ greetingText }}, <span class="text-primary">{{ currentUser?.nama || 'Pengguna' }}</span> 👋
              </h3>
              <span class="text-muted fs-3">Selamat datang di Sistem Informasi IN-HC — {{ currentDateFormatted }}</span>
            </div>
          </div>
          <div class="d-flex align-items-center flex-wrap gap-2">
            <router-link to="/surat-masuk" class="btn btn-light-primary text-primary fw-semibold d-flex align-items-center gap-1 shadow-none">
              <i class="ti ti-mail-plus fs-5"></i>
              <span>Surat Masuk</span>
            </router-link>
            <router-link to="/surat-keluar" class="btn btn-primary fw-semibold d-flex align-items-center gap-1 shadow-none">
              <i class="ti ti-file-export fs-5"></i>
              <span>Surat Keluar</span>
            </router-link>
            <router-link to="/pelamar" class="btn btn-light-success text-success fw-semibold d-flex align-items-center gap-1 shadow-none">
              <i class="ti ti-user-check fs-5"></i>
              <span>Data Pelamar</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Section: Big Analytics Chart Card -->
    <div class="row">
      <div class="col-12">
        <div class="card overflow-hidden">
          <div class="card-body pb-0">
            <div class="row pb-4 align-items-stretch">
              <!-- Left: Financial/Statistical Highlights -->
              <div class="col-lg-4 d-flex flex-column justify-content-between mb-4 mb-lg-0">
                <div>
                  <h5 class="card-title fw-semibold mb-1">Statistik & Tren Layanan</h5>
                  <span class="text-muted fs-3">Akumulasi data tahun {{ selectedYear }}</span>
                </div>

                <div class="my-4">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fs-3 text-muted">Total Volume Surat</span>
                    <span 
                      v-if="summary.growth_percentage >= 0" 
                      class="badge bg-light-success text-success fw-semibold fs-2 px-2 py-1"
                    >
                      <i class="ti ti-arrow-up-right me-1"></i>+{{ summary.growth_percentage }}%
                    </span>
                    <span 
                      v-else 
                      class="badge bg-light-danger text-danger fw-semibold fs-2 px-2 py-1"
                    >
                      <i class="ti ti-arrow-down-right me-1"></i>{{ summary.growth_percentage }}%
                    </span>
                  </div>
                  <h2 class="fw-bold mb-2">{{ summary.total_surat.toLocaleString('id-ID') }} <span class="fs-4 text-muted fw-normal">surat</span></h2>
                  <span class="text-muted fs-2">
                    <i class="ti ti-info-circle me-1"></i>{{ summary.total_bulan_ini }} surat & {{ summary.pelamar_bulan_ini || 0 }} berkas pelamar bulan ini
                  </span>
                </div>

                <div>
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fs-3 fw-semibold">Tingkat Disposisi Surat Masuk</span>
                    <span class="fs-3 fw-bold text-primary">{{ summary.persentase_disposisi }}%</span>
                  </div>
                  <div class="progress" style="height: 8px;">
                    <div 
                      class="progress-bar bg-primary" 
                      role="progressbar" 
                      :style="{ width: summary.persentase_disposisi + '%' }" 
                      aria-valuemin="0" 
                      aria-valuemax="100"
                    ></div>
                  </div>
                  <span class="fs-2 text-muted mt-1 d-block">{{ summary.surat_masuk_terdisposisi }} dari {{ summary.total_surat_masuk }} surat masuk telah didisposisi</span>
                </div>
              </div>

              <!-- Right: Multi-Series Line Chart -->
              <div class="col-lg-8">
                <div class="d-md-flex align-items-center justify-content-between mb-3">
                  <div>
                    <h6 class="fw-semibold mb-0">Tren Volume Bulanan</h6>
                    <span class="fs-2 text-muted">Perbandingan Surat Masuk, Surat Keluar, Disposisi, & Pelamar</span>
                  </div>
                  <div class="mt-2 mt-md-0" style="min-width: 140px;">
                    <select class="form-select form-select-sm" v-model="selectedYear" @change="fetchMonthlyChart">
                      <option :value="currentYear">{{ currentYear }}</option>
                      <option :value="currentYear - 1">{{ currentYear - 1 }}</option>
                      <option :value="currentYear - 2">{{ currentYear - 2 }}</option>
                    </select>
                  </div>
                </div>
                <div class="position-relative" style="min-height: 240px;">
                  <div v-if="loadingChart" class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center bg-body bg-opacity-50 z-1">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                  </div>
                  <div id="financial-monthly-chart" ref="monthlyChartEl"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom 4 Columns Bordered Info -->
          <div class="border-top">
            <div class="row gx-0">
              <div class="col-md-3 col-sm-6 border-end">
                <div class="p-4 py-3">
                  <p class="fs-3 fw-semibold text-danger mb-1 d-flex align-items-center">
                    <span class="round-8 bg-danger rounded-circle d-inline-block me-2"></span>
                    Surat Masuk
                  </p>
                  <h3 class="fw-bold mb-0">{{ summary.total_surat_masuk.toLocaleString('id-ID') }}</h3>
                  <span class="fs-2 text-muted">{{ summary.surat_masuk_bulan_ini }} surat masuk bulan ini</span>
                </div>
              </div>
              <div class="col-md-3 col-sm-6 border-end">
                <div class="p-4 py-3">
                  <p class="fs-3 fw-semibold text-primary mb-1 d-flex align-items-center">
                    <span class="round-8 bg-primary rounded-circle d-inline-block me-2"></span>
                    Surat Keluar
                  </p>
                  <h3 class="fw-bold mb-0">{{ summary.total_surat_keluar.toLocaleString('id-ID') }}</h3>
                  <span class="fs-2 text-muted">{{ summary.surat_keluar_bulan_ini }} nomor surat keluar dibuat</span>
                </div>
              </div>
              <div class="col-md-3 col-sm-6 border-end">
                <div class="p-4 py-3">
                  <p class="fs-3 fw-semibold text-info mb-1 d-flex align-items-center">
                    <span class="round-8 bg-info rounded-circle d-inline-block me-2"></span>
                    Disposisi Selesai
                  </p>
                  <h3 class="fw-bold mb-0">{{ summary.total_disposisi.toLocaleString('id-ID') }}</h3>
                  <span class="fs-2 text-muted">{{ summary.disposisi_bulan_ini }} instruksi disposisi tercatat</span>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="p-4 py-3">
                  <p class="fs-3 fw-semibold text-success mb-1 d-flex align-items-center">
                    <span class="round-8 bg-success rounded-circle d-inline-block me-2"></span>
                    Data Pelamar
                  </p>
                  <h3 class="fw-bold mb-0">{{ (summary.total_pelamar || 0).toLocaleString('id-ID') }}</h3>
                  <span class="fs-2 text-muted">{{ summary.pelamar_bulan_ini || 0 }} berkas pelamar bulan ini</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Second Row: Recent Activities & Weekly Activity Chart -->
    <div class="row">
      <!-- Left: Recent Activity Stream (5 Cols) -->
      <div class="col-lg-5 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <div>
                <h5 class="card-title fw-semibold mb-1">Aktivitas Terkini</h5>
                <p class="card-subtitle mb-0">Alur transaksi persuratan & berkas pelamar terbaru</p>
              </div>
              <router-link to="/surat-masuk" class="btn btn-sm btn-light-primary text-primary">
                Semua
              </router-link>
            </div>

            <div v-if="activities.length === 0" class="text-center py-4 text-muted">
              <i class="ti ti-inbox fs-8 d-block mb-2 opacity-50"></i>
              <span>Belum ada aktivitas tercatat</span>
            </div>

            <div v-else class="activity-stream">
              <div 
                v-for="(item, idx) in activities" 
                :key="item.id || idx" 
                class="d-flex align-items-center py-3"
                :class="{ 'border-bottom': idx < activities.length - 1 }"
              >
                <div 
                  class="flex-shrink-0 rounded-circle round d-flex align-items-center justify-content-center"
                  :class="`bg-light-${item.badge_color} text-${item.badge_color}`"
                  style="width: 44px; height: 44px;"
                >
                  <i :class="`${item.icon} fs-6`"></i>
                </div>
                <div class="ms-3 text-truncate pe-2">
                  <h6 class="mb-0 fw-semibold text-truncate fs-3">{{ item.title }}</h6>
                  <span class="fs-2 text-muted text-truncate d-block">{{ item.subtitle }}</span>
                  <span class="fs-2 text-primary d-block">{{ item.user }}</span>
                </div>
                <div class="ms-auto flex-shrink-0 text-end">
                  <span class="badge bg-light text-muted fs-2 px-2 py-1">{{ item.date_formatted }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Weekly Activity Gradient Chart (7 Cols) -->
      <div class="col-lg-7 d-flex align-items-stretch">
        <div class="card w-100 bg-light-primary overflow-hidden border-0">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div>
                <h5 class="card-title fw-semibold mb-1">Aktivitas 7 Hari Terakhir</h5>
                <div class="d-flex align-items-center gap-2 text-muted fs-2">
                  <span class="round-8 bg-primary rounded-circle d-inline-block"></span>
                  <span>Total {{ weeklyTotal }} transaksi (surat & pelamar) seminggu terakhir</span>
                </div>
              </div>
              <div>
                <button class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center p-2 shadow-none" @click="fetchWeeklyChart" title="Refresh">
                  <i class="ti ti-refresh fs-5"></i>
                </button>
              </div>
            </div>
          </div>
          <div id="weekly-area-chart" ref="weeklyChartEl" style="min-height: 290px;"></div>
        </div>
      </div>
    </div>

    <!-- Third Row: Recent Letters & Applicants Tabbed Table (12 Cols) -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="d-md-flex align-items-center justify-content-between mb-4">
              <div>
                <h5 class="card-title fw-semibold mb-1">Daftar Data Terkini</h5>
                <p class="card-subtitle mb-0">Rangkuman surat masuk, surat keluar, dan berkas pelamar terbaru</p>
              </div>
              <div class="mt-3 mt-md-0">
                <ul class="nav nav-pills" role="tablist">
                  <li class="nav-item">
                    <a 
                      class="nav-link px-3 py-2 cursor-pointer" 
                      :class="{ 'active': activeTab === 'all' }" 
                      @click="activeTab = 'all'"
                    >
                      <span>Semua Data</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a 
                      class="nav-link px-3 py-2 cursor-pointer" 
                      :class="{ 'active': activeTab === 'surat_masuk' }" 
                      @click="activeTab = 'surat_masuk'"
                    >
                      <span>Surat Masuk</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a 
                      class="nav-link px-3 py-2 cursor-pointer" 
                      :class="{ 'active': activeTab === 'surat_keluar' }" 
                      @click="activeTab = 'surat_keluar'"
                    >
                      <span>Surat Keluar</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a 
                      class="nav-link px-3 py-2 cursor-pointer" 
                      :class="{ 'active': activeTab === 'pelamar' }" 
                      @click="activeTab = 'pelamar'"
                    >
                      <span>Data Pelamar</span>
                    </a>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Table View -->
            <div class="table-responsive">
              <table class="table align-middle mb-0 text-nowrap">
                <thead>
                  <tr class="text-muted fw-semibold">
                    <th scope="col" class="ps-0">Perihal / Nama & Nomor</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Pihak / Institusi</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Status / Keterangan</th>
                    <th scope="col" class="text-end pe-0">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="filteredLetters.length === 0">
                    <td colspan="6" class="text-center py-4 text-muted">
                      Tidak ada data untuk ditampilkan
                    </td>
                  </tr>
                  <tr v-for="letter in filteredLetters" :key="letter.id">
                    <td class="ps-0">
                      <div class="d-flex align-items-center gap-3">
                        <div 
                          class="rounded-circle round d-flex align-items-center justify-content-center flex-shrink-0"
                          :class="getLetterIconClass(letter.type)"
                          style="width: 40px; height: 40px;"
                        >
                          <i :class="getLetterIcon(letter.type)"></i>
                        </div>
                        <div class="text-truncate" style="max-width: 320px;">
                          <h6 class="mb-0 fw-semibold text-truncate">{{ letter.perihal }}</h6>
                          <span class="fs-2 text-muted text-truncate d-block">{{ letter.nomor_surat }}</span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span 
                        class="badge rounded-pill"
                        :class="getLetterBadgeClass(letter.type)"
                      >
                        {{ getLetterTypeLabel(letter.type) }}
                      </span>
                    </td>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="fw-semibold text-truncate" style="max-width: 240px;" :title="letter.pihak">{{ letter.pihak }}</span>
                        <span class="fs-2 text-muted">{{ letter.pihak_label }}</span>
                      </div>
                    </td>
                    <td>
                      <span>{{ formatDate(letter.tanggal_surat) }}</span>
                    </td>
                    <td>
                      <span 
                        class="badge rounded-pill"
                        :class="`bg-light-${letter.status_color} text-${letter.status_color}`"
                      >
                        <span :class="`round-8 bg-${letter.status_color} rounded-circle d-inline-block me-1`"></span>
                        {{ letter.status }}
                      </span>
                    </td>
                    <td class="text-end pe-0">
                      <router-link 
                        :to="getLetterDetailRoute(letter)" 
                        class="btn btn-sm btn-light text-primary shadow-none"
                      >
                        <i class="ti ti-eye me-1"></i>Detail
                      </router-link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Fourth Row: Distribution Section & Quick Access Cards -->
    <div class="row">
      <!-- Left: Distribution per Bagian Seksi (RadialBar) (7 Cols) -->
      <div class="col-lg-7 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <div>
                <h5 class="card-title fw-semibold mb-1">Distribusi Surat per Bagian / Seksi</h5>
                <p class="card-subtitle mb-0">Proporsi permohonan surat keluar berdasarkan unit pemohon</p>
              </div>
            </div>

            <div class="row align-items-center">
              <div class="col-md-7">
                <div id="distribution-radial-chart" ref="distributionChartEl" style="min-height: 280px;"></div>
              </div>
              <div class="col-md-5">
                <div class="d-flex flex-column gap-2" style="max-height: 290px; overflow-y: auto;">
                  <div 
                    v-for="(label, idx) in distributionData.labels" 
                    :key="idx"
                    class="d-flex align-items-center justify-content-between p-2 rounded bg-light border"
                  >
                    <div class="d-flex align-items-center gap-2 text-truncate pe-2">
                      <span 
                        class="round-8 rounded-circle d-inline-block flex-shrink-0"
                        :style="{ backgroundColor: distributionColors[idx % distributionColors.length] }"
                      ></span>
                      <span class="fs-2 fw-semibold text-truncate" :title="label">{{ label }}</span>
                    </div>
                    <span class="badge bg-white text-dark shadow-sm fs-2 fw-bold flex-shrink-0">
                      {{ distributionData.raw_counts[idx] || 0 }}
                    </span>
                  </div>
                  <div v-if="distributionData.labels.length === 0" class="text-center py-4 text-muted fs-2">
                    Belum ada data bagian / seksi di database
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer bg-transparent border-top py-3">
            <div class="hstack gap-4 justify-content-center flex-wrap">
              <div class="d-flex align-items-center gap-2">
                <span class="round-8 bg-success rounded-circle d-inline-block"></span>
                <span class="fs-2 text-muted">Disposisi Selesai: <strong class="text-body">{{ distributionData.total_disposisi_selesai }}</strong></span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="round-8 bg-warning rounded-circle d-inline-block"></span>
                <span class="fs-2 text-muted">Belum Disposisi: <strong class="text-body">{{ distributionData.total_disposisi_pending }}</strong></span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="round-8 bg-primary rounded-circle d-inline-block"></span>
                <span class="fs-2 text-muted">Tingkat Penyelesaian: <strong class="text-body">{{ distributionData.persentase_selesai }}%</strong></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Quick Navigation & Services (5 Cols) -->
      <div class="col-lg-5 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-1">Pintasan Layanan</h5>
            <p class="card-subtitle mb-4">Navigasi cepat ke modul utama sistem IN-HC</p>

            <div class="pb-3 border-bottom">
              <div class="d-flex align-items-center mb-2">
                <span class="badge bg-light-primary text-primary">Persuratan</span>
                <router-link to="/surat-masuk" class="fs-2 ms-auto text-primary fw-semibold text-decoration-none">
                  Buka Modul &rarr;
                </router-link>
              </div>
              <h6 class="mb-1 fw-semibold">Surat Masuk & Disposisi</h6>
              <span class="fs-2 text-muted d-block">Pencatatan surat masuk, riwayat alur disposisi, dan pelacakan tindak lanjut surat.</span>
            </div>

            <div class="py-3 border-bottom">
              <div class="d-flex align-items-center mb-2">
                <span class="badge bg-light-success text-success">Persuratan</span>
                <router-link to="/surat-keluar" class="fs-2 ms-auto text-success fw-semibold text-decoration-none">
                  Buka Modul &rarr;
                </router-link>
              </div>
              <h6 class="mb-1 fw-semibold">Penomoran Surat Keluar</h6>
              <span class="fs-2 text-muted d-block">Penerbitan nomor surat otomatis berurutan per tahun beserta pengarsipan berkas lampiran.</span>
            </div>

            <div class="py-3 border-bottom">
              <div class="d-flex align-items-center mb-2">
                <span class="badge bg-light-info text-info">Rekrutmen</span>
                <router-link to="/pelamar" class="fs-2 ms-auto text-info fw-semibold text-decoration-none">
                  Buka Modul &rarr;
                </router-link>
              </div>
              <h6 class="mb-1 fw-semibold">Pengelolaan Data Pelamar</h6>
              <span class="fs-2 text-muted d-block">Pencatatan berkas pelamar, nomor urut lamaran per tahun, kualifikasi jenjang pendidikan, dan arsip CV.</span>
            </div>

            <div class="pt-3">
              <div class="d-flex align-items-center mb-2">
                <span class="badge bg-light-warning text-warning">Master Data</span>
                <router-link to="/users" class="fs-2 ms-auto text-warning fw-semibold text-decoration-none">
                  Buka Modul &rarr;
                </router-link>
              </div>
              <h6 class="mb-1 fw-semibold">Pengaturan Pegawai & Unit Kerja</h6>
              <span class="fs-2 text-muted d-block">Pengelolaan data master pengguna, struktur jabatan, unit kerja, dan bagian seksi.</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import api from '../utils/api'
import { useCustomizer } from '../composables/useCustomizer'

const { state: customizerState } = useCustomizer()

// User info
const currentUser = ref(null)
const userProfileImg = ref('/dist/images/profile/user-1.jpg')
const onImageError = () => {
  userProfileImg.value = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(currentUser.value?.nama || 'User') + '&background=615dff&color=fff'
}

// Current Year & Date
const currentYear = new Date().getFullYear()
const selectedYear = ref(currentYear)

const currentDateFormatted = computed(() => {
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  return new Date().toLocaleDateString('id-ID', options)
})

const greetingText = computed(() => {
  const hour = new Date().getHours()
  if (hour < 11) return 'Selamat Pagi'
  if (hour < 15) return 'Selamat Siang'
  if (hour < 18) return 'Selamat Sore'
  return 'Selamat Malam'
})

// Summary Metrics
const summary = ref({
  total_surat: 0,
  total_surat_masuk: 0,
  total_surat_keluar: 0,
  total_disposisi: 0,
  total_pelamar: 0,
  surat_masuk_bulan_ini: 0,
  surat_keluar_bulan_ini: 0,
  disposisi_bulan_ini: 0,
  pelamar_bulan_ini: 0,
  total_bulan_ini: 0,
  total_bulan_lalu: 0,
  growth_percentage: 0,
  surat_masuk_terdisposisi: 0,
  persentase_disposisi: 0
})

// Activities & Recent Items
const activities = ref([])
const activeTab = ref('all')
const recentSurat = ref({
  all: [],
  surat_masuk: [],
  surat_keluar: [],
  pelamar: []
})

const filteredLetters = computed(() => {
  if (activeTab.value === 'surat_masuk') return recentSurat.value.surat_masuk || []
  if (activeTab.value === 'surat_keluar') return recentSurat.value.surat_keluar || []
  if (activeTab.value === 'pelamar') return recentSurat.value.pelamar || []
  return recentSurat.value.all || []
})

const getLetterIconClass = (type) => {
  if (type === 'surat_masuk') return 'bg-light-danger text-danger'
  if (type === 'surat_keluar') return 'bg-light-primary text-primary'
  if (type === 'pelamar') return 'bg-light-success text-success'
  return 'bg-light-info text-info'
}

const getLetterIcon = (type) => {
  if (type === 'surat_masuk') return 'ti ti-mail fs-5'
  if (type === 'surat_keluar') return 'ti ti-file-export fs-5'
  if (type === 'pelamar') return 'ti ti-user-check fs-5'
  return 'ti ti-file fs-5'
}

const getLetterBadgeClass = (type) => {
  if (type === 'surat_masuk') return 'bg-light-danger text-danger'
  if (type === 'surat_keluar') return 'bg-light-primary text-primary'
  if (type === 'pelamar') return 'bg-light-success text-success'
  return 'bg-light-info text-info'
}

const getLetterTypeLabel = (type) => {
  if (type === 'surat_masuk') return 'Surat Masuk'
  if (type === 'surat_keluar') return 'Surat Keluar'
  if (type === 'pelamar') return 'Data Pelamar'
  return 'Lainnya'
}

const getLetterDetailRoute = (letter) => {
  if (letter.type === 'surat_masuk') return `/surat-masuk/${letter.id}/detail`
  if (letter.type === 'surat_keluar') return '/surat-keluar'
  if (letter.type === 'pelamar') return '/pelamar'
  return '/'
}

// Weekly Total
const weeklyTotal = ref(0)

// Distribution Data
const distributionColors = ['#5D87FF', '#49BEFF', '#13DEB9', '#FFAE1F', '#FA896B', '#7C5CFC', '#FF6692', '#36B37E', '#4E73DF', '#36B9CC', '#E83E8C', '#6F42C1']
const distributionData = ref({
  labels: [],
  series: [],
  raw_counts: [],
  total_disposisi_selesai: 0,
  total_disposisi_pending: 0,
  persentase_selesai: 0
})

// Chart instances
let monthlyChartInstance = null
let weeklyChartInstance = null
let distributionChartInstance = null

const monthlyChartEl = ref(null)
const weeklyChartEl = ref(null)
const distributionChartEl = ref(null)
const loadingChart = ref(false)

// Date Formatter Helper
const formatDate = (dateString) => {
  if (!dateString) return '-'
  try {
    const d = new Date(dateString)
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
  } catch (e) {
    return dateString
  }
}

// 1. Fetch Summary
const fetchSummary = async () => {
  try {
    const res = await api.post('/dashboard/summary')
    if (res.data?.success) {
      summary.value = res.data.data
    }
  } catch (err) {
    console.error('Error fetching dashboard summary:', err)
  }
}

// 2. Fetch Monthly Chart
const fetchMonthlyChart = async () => {
  loadingChart.value = true
  try {
    const res = await api.post('/dashboard/chart-monthly', { year: selectedYear.value })
    if (res.data?.success) {
      renderMonthlyChart(res.data.data)
    }
  } catch (err) {
    console.error('Error fetching monthly chart:', err)
  } finally {
    loadingChart.value = false
  }
}

// 3. Fetch Weekly Chart
const fetchWeeklyChart = async () => {
  try {
    const res = await api.post('/dashboard/chart-weekly')
    if (res.data?.success) {
      weeklyTotal.value = res.data.data.total_weekly || 0
      renderWeeklyChart(res.data.data)
    }
  } catch (err) {
    console.error('Error fetching weekly chart:', err)
  }
}

// 4. Fetch Distribution Chart
const fetchDistributionChart = async () => {
  try {
    const res = await api.post('/dashboard/chart-distribution')
    if (res.data?.success) {
      distributionData.value = res.data.data
      renderDistributionChart(res.data.data)
    }
  } catch (err) {
    console.error('Error fetching distribution chart:', err)
  }
}

// 5. Fetch Recent Activities & Letters
const fetchRecentData = async () => {
  try {
    const [actRes, suratRes] = await Promise.all([
      api.post('/dashboard/recent-activities'),
      api.post('/dashboard/recent-surat')
    ])

    if (actRes.data?.success) {
      activities.value = actRes.data.data
    }
    if (suratRes.data?.success) {
      recentSurat.value = suratRes.data.data
    }
  } catch (err) {
    console.error('Error fetching recent dashboard data:', err)
  }
}

// Render Monthly Chart (Line / Smooth Area)
const renderMonthlyChart = (data) => {
  if (!window.ApexCharts) return

  const isDark = customizerState.themeMode === 'dark'
  const textColor = isDark ? '#7c8fac' : '#adb0bb'
  const borderColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)'

  const options = {
    series: data.series,
    chart: {
      type: 'line',
      height: 240,
      fontFamily: "'Plus Jakarta Sans', sans-serif",
      foreColor: textColor,
      toolbar: { show: false },
      zoom: { enabled: false }
    },
    colors: ['#fa896b', '#615dff', '#3dd9eb', '#13deb9'],
    dataLabels: { enabled: false },
    stroke: {
      curve: 'smooth',
      width: 3
    },
    grid: {
      borderColor: borderColor,
      strokeDashArray: 3,
      xaxis: { lines: { show: false } },
      padding: { top: 0, right: 10, bottom: 0, left: 10 }
    },
    xaxis: {
      categories: data.categories,
      axisBorder: { show: false },
      axisTicks: { show: false }
    },
    yaxis: {
      tickAmount: 4,
      labels: {
        formatter: (val) => Math.round(val)
      }
    },
    legend: {
      position: 'top',
      horizontalAlign: 'right',
      floating: true,
      offsetY: -25,
      offsetX: -5
    },
    tooltip: {
      theme: isDark ? 'dark' : 'light'
    },
    markers: {
      size: 4,
      strokeWidth: 2,
      hover: { size: 6 }
    }
  }

  if (monthlyChartInstance) {
    monthlyChartInstance.destroy()
  }

  const el = document.querySelector('#financial-monthly-chart')
  if (el) {
    monthlyChartInstance = new window.ApexCharts(el, options)
    monthlyChartInstance.render()
  }
}

// Render Weekly Area Chart
const renderWeeklyChart = (data) => {
  if (!window.ApexCharts) return

  const isDark = customizerState.themeMode === 'dark'

  const options = {
    series: data.series,
    chart: {
      height: 310,
      type: 'area',
      fontFamily: "'Plus Jakarta Sans', sans-serif",
      foreColor: '#adb0bb',
      toolbar: { show: false },
      sparkline: { enabled: false },
      dropShadow: {
        enabled: true,
        top: 3,
        left: 0,
        blur: 5,
        color: '#615dff',
        opacity: 0.15
      }
    },
    colors: ['#615dff'],
    dataLabels: { enabled: false },
    stroke: {
      curve: 'smooth',
      width: 3
    },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.45,
        opacityTo: 0.05,
        stops: [0, 90, 100]
      }
    },
    grid: {
      show: true,
      borderColor: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(97,93,255,0.08)',
      strokeDashArray: 3
    },
    xaxis: {
      categories: data.categories,
      axisBorder: { show: false },
      axisTicks: { show: false }
    },
    yaxis: {
      tickAmount: 3,
      labels: {
        formatter: (val) => Math.round(val)
      }
    },
    tooltip: {
      theme: isDark ? 'dark' : 'light'
    }
  }

  if (weeklyChartInstance) {
    weeklyChartInstance.destroy()
  }

  const el = document.querySelector('#weekly-area-chart')
  if (el) {
    weeklyChartInstance = new window.ApexCharts(el, options)
    weeklyChartInstance.render()
  }
}

// Render RadialBar Distribution Chart
const renderDistributionChart = (data) => {
  if (!window.ApexCharts) return

  const isDark = customizerState.themeMode === 'dark'

  const countUnits = data.labels.length
  const seriesValues = data.series.length > 0 ? data.series : [0]
  const labelNames = data.labels.length > 0 ? data.labels : ['Belum Ada Bagian']
  const chartColors = labelNames.map((_, idx) => distributionColors[idx % distributionColors.length])

  const options = {
    chart: {
      type: 'radialBar',
      fontFamily: "'Plus Jakarta Sans', sans-serif",
      foreColor: '#adb0bb',
      height: 320
    },
    series: seriesValues,
    colors: chartColors,
    plotOptions: {
      radialBar: {
        hollow: {
          margin: 8,
          size: countUnits > 4 ? '25%' : '35%'
        },
        track: {
          background: isDark ? '#2a3447' : '#f2f6fa'
        },
        dataLabels: {
          name: {
            fontSize: '12px'
          },
          value: {
            fontSize: '13px',
            formatter: (val) => `${val}%`
          },
          total: {
            show: true,
            label: 'Total Bagian',
            formatter: () => `${countUnits} Unit`
          }
        }
      }
    },
    stroke: {
      lineCap: 'round'
    },
    labels: labelNames,
    tooltip: {
      enabled: true,
      theme: isDark ? 'dark' : 'light'
    }
  }

  if (distributionChartInstance) {
    distributionChartInstance.destroy()
  }

  const el = document.querySelector('#distribution-radial-chart')
  if (el) {
    distributionChartInstance = new window.ApexCharts(el, options)
    distributionChartInstance.render()
  }
}

// Watch Theme Changes and re-render charts
watch(() => customizerState.themeMode, () => {
  nextTick(() => {
    fetchMonthlyChart()
    fetchWeeklyChart()
    fetchDistributionChart()
  })
})

onMounted(() => {
  const savedUser = localStorage.getItem('user_data')
  if (savedUser) {
    try {
      currentUser.value = JSON.parse(savedUser)
    } catch (e) {
      console.error(e)
    }
  }

  fetchSummary()
  fetchMonthlyChart()
  fetchWeeklyChart()
  fetchDistributionChart()
  fetchRecentData()
})

onUnmounted(() => {
  if (monthlyChartInstance) monthlyChartInstance.destroy()
  if (weeklyChartInstance) weeklyChartInstance.destroy()
  if (distributionChartInstance) distributionChartInstance.destroy()
})
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}

.activity-stream .round {
  transition: transform 0.2s ease;
}

.activity-stream .d-flex:hover .round {
  transform: scale(1.08);
}

.card {
  transition: box-shadow 0.2s ease;
}

.card:hover {
  box-shadow: 0 10px 30px 0 rgba(0, 0, 0, 0.05);
}

[data-bs-theme="dark"] .card:hover {
  box-shadow: 0 10px 30px 0 rgba(0, 0, 0, 0.25);
}
</style>
