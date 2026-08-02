<template>
  <div 
    class="page-wrapper" 
    id="main-wrapper" 
    :data-theme="customizerState.themeColor" 
    data-layout="vertical" 
    :data-sidebartype="currentSidebarType" 
    data-sidebar-position="fixed" 
    data-header-position="fixed"
    :class="{'mini-sidebar': currentSidebarType === 'mini-sidebar', 'show-sidebar': isSidebarShow}"
  >
    <!-- Sidebar Start -->
    <aside class="left-sidebar" :class="{'sidebar-open': isSidebarShow}">
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <router-link to="/" class="text-nowrap logo-img" @click="onMenuClick">
            <h2>IN-HC</h2>
          </router-link>
          <div class="close-btn d-lg-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse" @click="closeSidebar">
            <i class="ti ti-x fs-8 text-muted"></i>
          </div>
        </div>
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
          <ul id="sidebarnav">
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Master Data</span>
            </li>
            <li class="sidebar-item" :class="{ 'selected': route.path === '/jabatan' }">
              <router-link class="sidebar-link" to="/jabatan" aria-expanded="false" @click="onMenuClick">
                <span><i class="ti ti-briefcase"></i></span>
                <span class="hide-menu">Jabatan</span>
              </router-link>
            </li>
            <li class="sidebar-item" :class="{ 'selected': route.path === '/unit-kerja' }">
              <router-link class="sidebar-link" to="/unit-kerja" aria-expanded="false" @click="onMenuClick">
                <span><i class="ti ti-building"></i></span>
                <span class="hide-menu">Unit Kerja</span>
              </router-link>
            </li>
            <li class="sidebar-item" :class="{ 'selected': route.path === '/bagian-seksi' }">
              <router-link class="sidebar-link" to="/bagian-seksi" aria-expanded="false" @click="onMenuClick">
                <span><i class="ti ti-users"></i></span>
                <span class="hide-menu">Bagian Seksi</span>
              </router-link>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Persuratan</span>
            </li>
            <li class="sidebar-item" :class="{ 'selected': route.path.startsWith('/surat-masuk') }">
              <router-link class="sidebar-link" to="/surat-masuk" aria-expanded="false" @click="onMenuClick">
                <span><i class="ti ti-mail"></i></span>
                <span class="hide-menu">Surat Masuk</span>
              </router-link>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Pengaturan</span>
            </li>
            <li class="sidebar-item" :class="{ 'selected': route.path === '/users' }">
              <router-link class="sidebar-link" to="/users" aria-expanded="false" @click="onMenuClick">
                <span><i class="ti ti-user-circle"></i></span>
                <span class="hide-menu">Users</span>
              </router-link>
            </li>
          </ul>
        </nav>
      </div>
    </aside>
    <!--  Sidebar End -->

    <!-- Mobile Backdrop Overlay -->
    <div 
      v-if="isSidebarShow" 
      class="dark-transparent active" 
      @click="closeSidebar"
    ></div>

    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <header class="app-header"> 
        <nav class="navbar navbar-light w-100 d-flex align-items-center justify-content-between px-0">
          <!-- Left: Hamburger Menu & Mobile Brand -->
          <div class="d-flex align-items-center gap-2">
            <a class="nav-link sidebartoggler nav-icon-hover cursor-pointer p-2 rounded-circle" id="headerCollapse" href="javascript:void(0)" @click.prevent="toggleSidebar">
              <i class="ti ti-menu-2 fs-6"></i>
            </a>
            <div class="d-block d-lg-none">
              <span class="fw-bold fs-4 text-dark mb-0">IN-HC</span>
            </div>
          </div>

          <!-- Right: User Profile -->
          <div class="d-flex align-items-center">
            <div class="dropdown">
              <a class="nav-link cursor-pointer d-flex align-items-center gap-2 p-1 pe-0" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="d-none d-md-block text-end me-1 lh-sm">
                  <span class="d-block fw-semibold text-dark fs-3">{{ user?.nama || 'User' }}</span>
                  <span class="d-block text-muted fs-2">{{ user?.level === 'admin' ? 'Administrator' : 'User' }}</span>
                </div>
                <div class="user-profile-img">
                  <img src="/dist/images/profile/user-1.jpg" class="rounded-circle shadow-sm" width="38" height="38" alt="user" />
                </div>
              </a>
              <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                <div class="profile-dropdown position-relative" data-simplebar>
                  <div class="py-3 px-7 pb-0">
                    <h5 class="mb-0 fs-5 fw-semibold">User Profile</h5>
                  </div>
                  <div class="d-flex align-items-center py-9 mx-7 border-bottom">
                    <img src="/dist/images/profile/user-1.jpg" class="rounded-circle" width="70" height="70" alt="user" />
                    <div class="ms-3">
                      <h5 class="mb-1 fs-4 text-truncate" style="max-width: 170px;">{{ user?.nama || 'User' }}</h5>
                      <span class="mb-1 d-block badge bg-light-primary text-primary fw-medium">{{ user?.level === 'admin' ? 'Administrator' : 'User' }}</span>
                      <p class="mb-0 d-flex text-muted align-items-center gap-1 fs-2">
                        <i class="ti ti-id fs-3"></i> {{ user?.npp || '-' }}
                      </p>
                    </div>
                  </div>
                  <div class="message-body">
                    <div class="d-grid py-4 px-7 pt-6 gap-2">
                      <button @click="showChangePasswordModal = true" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="ti ti-key fs-4"></i> Ubah Password
                      </button>
                      <button @click="handleLogout" class="btn btn-danger btn-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="ti ti-logout fs-4"></i> Log Out
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </nav>
      </header>
      <!--  Header End -->
      
      <div class="container-fluid" :class="{'mw-100': customizerState.containerOption === 'full'}">
        <RouterView />
      </div>
    </div>
  </div>

  <!-- Change Password Modal -->
  <div v-if="showChangePasswordModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Ubah Password</h5>
          <button type="button" class="btn-close" @click="showChangePasswordModal = false"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Password Lama</label>
            <input type="password" class="form-control" v-model="changePasswordForm.old_password" placeholder="Masukkan password lama">
          </div>
          <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <input type="password" class="form-control" v-model="changePasswordForm.new_password" placeholder="Masukkan password baru">
          </div>
          <div class="mb-3">
            <label class="form-label">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" v-model="changePasswordForm.confirm_password" placeholder="Ketik ulang password baru">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" @click="showChangePasswordModal = false">Batal</button>
          <button type="button" class="btn btn-primary" @click="submitChangePassword">Yakin</button>
        </div>
      </div>
    </div>
  </div>
  
  <Customizer />
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { RouterView, RouterLink, useRoute, useRouter } from 'vue-router'
import api from '../utils/api'
import Customizer from '../components/Customizer.vue'
import { useCustomizer } from '../composables/useCustomizer'

const route = useRoute()
const router = useRouter()

const user = ref(null)

const { state: customizerState } = useCustomizer()
const isSidebarShow = ref(false)
const isMobile = ref(false)

const currentSidebarType = computed(() => {
  if (isMobile.value) {
    return 'mini-sidebar'
  }
  return customizerState.sidebarType || 'full'
})

const showChangePasswordModal = ref(false)
const changePasswordForm = ref({
  old_password: '',
  new_password: '',
  confirm_password: ''
})

const checkScreenSize = () => {
  isMobile.value = window.innerWidth < 1200
  if (!isMobile.value) {
    isSidebarShow.value = false
  }
}

const toggleSidebar = () => {
  if (isMobile.value) {
    isSidebarShow.value = !isSidebarShow.value
  } else {
    customizerState.sidebarType = customizerState.sidebarType === 'mini-sidebar' ? 'full' : 'mini-sidebar'
  }
}

const closeSidebar = () => {
  isSidebarShow.value = false
}

const onMenuClick = () => {
  if (isMobile.value) {
    closeSidebar()
  }
}

watch(() => route.path, () => {
  if (isMobile.value) {
    closeSidebar()
  }
})

const handleLogout = async () => {
  try {
    await api.post('/logout')
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user_data')
    router.push('/login')
  }
}

const submitChangePassword = async () => {
  if (changePasswordForm.value.new_password !== changePasswordForm.value.confirm_password) {
    window.Swal.fire({
      icon: 'error',
      title: 'Validasi Gagal',
      text: 'Konfirmasi password salah'
    })
    return
  }

  try {
    const res = await api.post('/change-password', {
      old_password: changePasswordForm.value.old_password,
      new_password: changePasswordForm.value.new_password
    })
    
    if (res.data.success) {
      window.Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Password berhasil diubah, silakan login kembali',
        timer: 1500,
        showConfirmButton: false
      }).then(() => {
        showChangePasswordModal.value = false
        changePasswordForm.value = { old_password: '', new_password: '', confirm_password: '' }
        handleLogout()
      })
    }
  } catch (error) {
    window.Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: error.response?.data?.message || 'Terjadi kesalahan'
    })
  }
}

onMounted(() => {
  checkScreenSize()
  window.addEventListener('resize', checkScreenSize)

  // Load user data
  const userData = localStorage.getItem('user_data')
  if (userData) {
    user.value = JSON.parse(userData)
  }

  const loadScript = (src) => {
    return new Promise((resolve, reject) => {
      if (document.querySelector(`script[src="${src}"]`)) {
        resolve()
        return
      }
      const script = document.createElement('script')
      script.src = src
      script.onload = resolve
      script.onerror = reject
      document.body.appendChild(script)
    })
  }

  const loadScripts = async () => {
    try {
      await loadScript('/dist/libs/jquery/dist/jquery.min.js')
      await loadScript('/dist/libs/simplebar/dist/simplebar.min.js')
      await loadScript('/dist/libs/bootstrap/dist/js/bootstrap.bundle.min.js')
      
      // We skip app.min.js because we handle sidebar state natively in Vue now
      await loadScript('/dist/js/app-style-switcher.js')
      await loadScript('/dist/libs/select2/dist/js/select2.full.min.js')
      await loadScript('/dist/libs/select2/dist/js/select2.min.js')
      await loadScript('/dist/libs/datatables.net/js/jquery.dataTables.min.js')
      await loadScript('/dist/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js')
      await loadScript('/dist/js/custom.js')
    } catch (e) {
      console.error('Failed to load template scripts', e)
    }
  }

  loadScripts()
})

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize)
})
</script>

<style scoped>
/* App Header & Navbar Precision Styling */
.app-header {
  min-height: 70px;
  height: 70px;
  background: #fff;
  position: fixed;
  top: 0;
  right: 0;
  z-index: 1000;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  padding: 0 24px;
}

.app-header .navbar {
  min-height: 70px;
  height: 70px;
  flex-wrap: nowrap;
}

.app-header .user-profile-img img {
  object-fit: cover;
  border: 2px solid #eaeff4;
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.app-header .user-profile-img img:hover {
  border-color: var(--bs-primary);
  transform: scale(1.05);
}

/* User Profile Dropdown Styling */
.app-header .dropdown-menu {
  position: absolute !important;
  right: 0 !important;
  left: auto !important;
  min-width: 290px !important;
  max-width: calc(100vw - 32px) !important;
  margin-top: 8px !important;
  border: 1px solid #ebf1f6;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

/* Responsive Mobile Sidebar & Header Fix */
@media (max-width: 1199.98px) {
  .app-header {
    width: 100% !important;
    padding: 0 16px !important;
  }

  #main-wrapper .left-sidebar {
    left: -270px !important;
    position: fixed !important;
    top: 0 !important;
    bottom: 0 !important;
    height: 100vh !important;
    z-index: 1050 !important;
    transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    background-color: #fff !important;
  }

  #main-wrapper.show-sidebar .left-sidebar,
  #main-wrapper .left-sidebar.sidebar-open {
    left: 0 !important;
  }

  #main-wrapper .body-wrapper {
    margin-left: 0 !important;
  }

  .dark-transparent.active {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    transition: opacity 0.3s ease;
  }
}
</style>
