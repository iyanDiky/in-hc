import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { loadScript } from './utils/scripts'

async function start() {
  await loadScript('/dist/libs/sweetalert2/dist/sweetalert2.min.js')
  const app = createApp(App)
  app.use(router)
  await router.isReady()
  app.mount('#app')
}

start().catch(error => {
  console.error(error)
  document.getElementById('app').textContent = 'Aplikasi gagal dimuat. Periksa koneksi lalu muat ulang halaman.'
})
