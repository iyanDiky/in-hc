<template>
  <div 
    class="page-wrapper" 
    id="main-wrapper" 
    data-theme="blue_theme" 
    data-layout="vertical" 
    :data-sidebartype="isSidebarMini ? 'mini-sidebar' : 'full'" 
    data-sidebar-position="fixed" 
    data-header-position="fixed"
    :class="{'mini-sidebar': isSidebarMini, 'show-sidebar': isSidebarShow}"
  >
    <!-- Sidebar Start -->
    <aside class="left-sidebar">
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <router-link to="/" class="text-nowrap logo-img">
            <h2>IN-HC</h2>
          </router-link>
          <div class="close-btn d-lg-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse" @click="toggleSidebarShow">
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
              <router-link class="sidebar-link" to="/jabatan" aria-expanded="false">
                <span><i class="ti ti-briefcase"></i></span>
                <span class="hide-menu">Jabatan</span>
              </router-link>
            </li>
            <li class="sidebar-item" :class="{ 'selected': route.path === '/unit-kerja' }">
              <router-link class="sidebar-link" to="/unit-kerja" aria-expanded="false">
                <span><i class="ti ti-building"></i></span>
                <span class="hide-menu">Unit Kerja</span>
              </router-link>
            </li>
            <li class="sidebar-item" :class="{ 'selected': route.path === '/bagian-seksi' }">
              <router-link class="sidebar-link" to="/bagian-seksi" aria-expanded="false">
                <span><i class="ti ti-users"></i></span>
                <span class="hide-menu">Bagian Seksi</span>
              </router-link>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Persuratan</span>
            </li>
            <li class="sidebar-item" :class="{ 'selected': route.path === '/surat-masuk' }">
              <router-link class="sidebar-link" to="/surat-masuk" aria-expanded="false">
                <span><i class="ti ti-mail"></i></span>
                <span class="hide-menu">Surat Masuk</span>
              </router-link>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Pengaturan</span>
            </li>
            <li class="sidebar-item" :class="{ 'selected': route.path === '/users' }">
              <router-link class="sidebar-link" to="/users" aria-expanded="false">
                <span><i class="ti ti-user-circle"></i></span>
                <span class="hide-menu">Users</span>
              </router-link>
            </li>
          </ul>
        </nav>
      </div>
    </aside>
    <!--  Sidebar End -->

    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <header class="app-header"> 
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link sidebartoggler nav-icon-hover ms-n3" id="headerCollapse" href="javascript:void(0)" @click.prevent="toggleSidebar">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
          </ul>
          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
              <li class="nav-item dropdown">
                <a class="nav-link pe-0" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                  <div class="d-flex align-items-center">
                    <div class="user-profile-img">
                      <img src="/dist/images/profile/user-1.jpg" class="rounded-circle" width="35" height="35" alt="user" />
                    </div>
                  </div>
                </a>
                <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="profile-dropdown position-relative" data-simplebar>
                    <div class="py-3 px-7 pb-0">
                      <h5 class="mb-0 fs-5 fw-semibold">User Profile</h5>
                    </div>
                    <div class="d-flex align-items-center py-9 mx-7 border-bottom">
                      <img src="/dist/images/profile/user-1.jpg" class="rounded-circle" width="80" height="80" alt="user" />
                      <div class="ms-3">
                        <h5 class="mb-1 fs-4">{{ user?.nama || 'User' }}</h5>
                        <span class="mb-1 d-block text-dark">{{ user?.level === 'admin' ? 'Administrator' : 'User' }}</span>
                        <p class="mb-0 d-flex text-dark align-items-center gap-2">
                          <i class="ti ti-id fs-4"></i> {{ user?.npp || '-' }}
                        </p>
                      </div>
                    </div>
                    <div class="message-body">
                      <div class="d-grid py-4 px-7 pt-8 gap-3">
                        <button @click="showChangePasswordModal = true" class="btn btn-primary">Ubah Password</button>
                        <button @click="handleLogout" class="btn btn-outline-primary">Log Out</button>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->
      
      <div class="container-fluid">
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
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterView, RouterLink, useRoute, useRouter } from 'vue-router'
import api from '../utils/api'

const route = useRoute()
const router = useRouter()

const user = ref(null)

const isSidebarMini = ref(false)
const isSidebarShow = ref(false)

const showChangePasswordModal = ref(false)
const changePasswordForm = ref({
  old_password: '',
  new_password: '',
  confirm_password: ''
})

const toggleSidebar = () => {
  // Check if screen is small
  if (window.innerWidth < 1300) {
    isSidebarShow.value = !isSidebarShow.value
  } else {
    isSidebarMini.value = !isSidebarMini.value
  }
}

const toggleSidebarShow = () => {
  isSidebarShow.value = !isSidebarShow.value
}

const handleResize = () => {
  if (window.innerWidth < 1300) {
    isSidebarMini.value = true
  } else {
    isSidebarMini.value = false
    isSidebarShow.value = false
  }
}

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
  // Set initial state
  handleResize()
  window.addEventListener('resize', handleResize)

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
      await loadScript('/dist/js/custom.js')
    } catch (e) {
      console.error('Failed to load template scripts', e)
    }
  }

  loadScripts()
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>
