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
        </nav>
      </header>
      <!--  Header End -->
      
      <div class="container-fluid">
        <RouterView />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterView, RouterLink, useRoute } from 'vue-router'

const route = useRoute()

const isSidebarMini = ref(false)
const isSidebarShow = ref(false)

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

onMounted(() => {
  // Set initial state
  handleResize()
  window.addEventListener('resize', handleResize)

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
