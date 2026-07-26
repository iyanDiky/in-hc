import axios from 'axios'
import router from '../router'

const api = axios.create({
  baseURL: 'http://localhost:8000/api'
})

// Interceptor untuk menyisipkan Bearer token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Interceptor untuk menangani error respons
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      // Token tidak valid atau kedaluwarsa
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user_data')
      
      // Jika errornya dari request selain login, redirect
      if (router.currentRoute.value.name !== 'Login') {
        window.Swal.fire({
          icon: 'warning',
          title: 'Sesi Berakhir',
          text: 'Sesi Anda telah berakhir. Silakan login kembali.'
        }).then(() => {
          router.push('/login')
        })
      }
    }
    return Promise.reject(error)
  }
)

export default api
