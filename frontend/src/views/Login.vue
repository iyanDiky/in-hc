<template>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <div class="position-relative overflow-hidden radial-gradient min-vh-100">
      <div class="position-relative z-index-5">
        <div class="row">
          <div class="col-xl-7 col-xxl-8">
            <a href="javascript:void(0)" class="text-nowrap logo-img d-block px-4 py-9 w-100">
              <img src="/dist/images/logos/dhc/dark-logo.png" alt="DHC Logo" style="height: 42px; object-fit: contain;" />
            </a>
            <div class="d-none d-xl-flex align-items-center justify-content-center" style="height: calc(100vh - 80px);">
              <img src="/dist/images/backgrounds/login-security.svg" alt="" class="img-fluid" width="500">
            </div>
          </div>
          <div class="col-xl-5 col-xxl-4">
            <div class="authentication-login min-vh-100 bg-body row justify-content-center align-items-center p-4">
              <div class="col-sm-8 col-md-6 col-xl-9">
                <h2 class="mb-3 fs-7 fw-bolder">Welcome to IN-HC</h2>
                <p class="mb-9">Sistem Pengelolaan Data User</p>
                
                <form @submit.prevent="handleLogin">
                  <div class="mb-3">
                    <label for="inputUsername" class="form-label">Username</label>
                    <input type="text" class="form-control" id="inputUsername" v-model="form.username" required>
                  </div>
                  <div class="mb-4">
                    <label for="inputPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="inputPassword" v-model="form.password" required>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Sign In
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../utils/api'

const router = useRouter()
const form = ref({
  username: '',
  password: ''
})
const loading = ref(false)

const handleLogin = async () => {
  loading.value = true
  try {
    const res = await api.post('/login', form.value)
    if (res.data.success) {
      localStorage.setItem('auth_token', res.data.data.access_token)
      localStorage.setItem('user_data', JSON.stringify(res.data.data.user))
      
      window.Swal.fire({
        icon: 'success',
        title: 'Login Berhasil',
        text: 'Selamat datang kembali!',
        timer: 1500,
        showConfirmButton: false
      }).then(() => {
        router.push('/')
      })
    }
  } catch (error) {
    window.Swal.fire({
      icon: 'error',
      title: 'Login Gagal',
      text: error.response?.data?.message || 'Terjadi kesalahan pada sistem'
    })
  } finally {
    loading.value = false
  }
}
</script>
