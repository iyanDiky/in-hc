<template>
  <!-- Customizer Toggle Button -->
  <button class="btn btn-primary p-3 rounded-circle d-flex align-items-center justify-content-center customizer-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#customizerOffcanvas" aria-controls="customizerOffcanvas">
    <i class="ti ti-settings fs-7" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Settings"></i>
  </button>
  
  <!-- Customizer Offcanvas -->
  <div class="offcanvas offcanvas-end customizer" tabindex="-1" id="customizerOffcanvas" aria-labelledby="customizerOffcanvasLabel" data-simplebar>
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
      <h4 class="offcanvas-title fw-semibold" id="customizerOffcanvasLabel">Settings</h4>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
      
      <!-- Theme Option -->
      <div class="theme-option pb-4">
        <h6 class="fw-semibold fs-4 mb-1">Theme Option</h6>
        <div class="d-flex align-items-center gap-3 my-3">
          <a href="javascript:void(0)" @click="setThemeMode('light')" :class="{'text-primary': state.themeMode === 'light'}" class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center gap-2 light-theme text-dark">
            <i class="ti ti-brightness-up fs-7" :class="{'text-primary': state.themeMode === 'light'}"></i>
            <span class="text-dark">Light</span>
          </a>
          <a href="javascript:void(0)" @click="setThemeMode('dark')" :class="{'text-primary': state.themeMode === 'dark'}" class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center gap-2 dark-theme text-dark">
            <i class="ti ti-moon fs-7" :class="{'text-primary': state.themeMode === 'dark'}"></i>
            <span class="text-dark">Dark</span>
          </a>
        </div>
      </div>

      <!-- Theme Colors -->
      <div class="theme-colors pb-4">
        <h6 class="fw-semibold fs-4 mb-1">Theme Colors</h6>
        <div class="d-flex align-items-center gap-3 my-3">
          <ul class="list-unstyled mb-0 d-flex gap-3 flex-wrap change-colors">
            <li class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center justify-content-center">
              <a href="javascript:void(0)" @click="setThemeColor('blue_theme')" :class="{'active-theme': state.themeColor === 'blue_theme'}" class="rounded-circle position-relative d-block customizer-bgcolor skin1-bluetheme-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="BLUE_THEME">
                <i class="ti ti-check text-white d-flex align-items-center justify-content-center fs-5" v-if="state.themeColor === 'blue_theme'"></i>
              </a>
            </li>
            <li class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center justify-content-center">
              <a href="javascript:void(0)" @click="setThemeColor('aqua_theme')" :class="{'active-theme': state.themeColor === 'aqua_theme'}" class="rounded-circle position-relative d-block customizer-bgcolor skin2-aquatheme-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="AQUA_THEME">
                <i class="ti ti-check text-white d-flex align-items-center justify-content-center fs-5" v-if="state.themeColor === 'aqua_theme'"></i>
              </a>
            </li>
            <li class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center justify-content-center">
              <a href="javascript:void(0)" @click="setThemeColor('purple_theme')" :class="{'active-theme': state.themeColor === 'purple_theme'}" class="rounded-circle position-relative d-block customizer-bgcolor skin3-purpletheme-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="PURPLE_THEME">
                <i class="ti ti-check text-white d-flex align-items-center justify-content-center fs-5" v-if="state.themeColor === 'purple_theme'"></i>
              </a>
            </li>
            <li class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center justify-content-center">
              <a href="javascript:void(0)" @click="setThemeColor('green_theme')" :class="{'active-theme': state.themeColor === 'green_theme'}" class="rounded-circle position-relative d-block customizer-bgcolor skin4-greentheme-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="GREEN_THEME">
                <i class="ti ti-check text-white d-flex align-items-center justify-content-center fs-5" v-if="state.themeColor === 'green_theme'"></i>
              </a>
            </li>
            <li class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center justify-content-center">
              <a href="javascript:void(0)" @click="setThemeColor('cyan_theme')" :class="{'active-theme': state.themeColor === 'cyan_theme'}" class="rounded-circle position-relative d-block customizer-bgcolor skin5-cyantheme-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="CYAN_THEME">
                <i class="ti ti-check text-white d-flex align-items-center justify-content-center fs-5" v-if="state.themeColor === 'cyan_theme'"></i>
              </a>
            </li>
            <li class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center justify-content-center">
              <a href="javascript:void(0)" @click="setThemeColor('orange_theme')" :class="{'active-theme': state.themeColor === 'orange_theme'}" class="rounded-circle position-relative d-block customizer-bgcolor skin6-orangetheme-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="ORANGE_THEME">
                <i class="ti ti-check text-white d-flex align-items-center justify-content-center fs-5" v-if="state.themeColor === 'orange_theme'"></i>
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Container Option -->
      <div class="container-option pb-4">
        <h6 class="fw-semibold fs-4 mb-1">Container Option</h6>
        <div class="d-flex align-items-center gap-3 my-3">
          <a href="javascript:void(0)" @click="setContainerOption('boxed')" :class="{'text-primary': state.containerOption === 'boxed'}" class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center gap-2 boxed-width text-dark">              
            <i class="ti ti-layout-distribute-vertical fs-7" :class="{'text-primary': state.containerOption === 'boxed'}"></i>
            <span class="text-dark">Boxed</span>
          </a>
          <a href="javascript:void(0)" @click="setContainerOption('full')" :class="{'text-primary': state.containerOption === 'full'}" class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center gap-2 full-width text-dark">
            <i class="ti ti-layout-distribute-horizontal fs-7" :class="{'text-primary': state.containerOption === 'full'}"></i>
            <span class="text-dark">Full</span>
          </a>
        </div>
      </div>

      <!-- Sidebar Type -->
      <div class="sidebar-type pb-4">
        <h6 class="fw-semibold fs-4 mb-1">Sidebar Type</h6>
        <div class="d-flex align-items-center gap-3 my-3">
          <a href="javascript:void(0)" @click="setSidebarType('full')" :class="{'text-primary': state.sidebarType === 'full'}" class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center gap-2 fullsidebar text-dark">              
            <i class="ti ti-layout-sidebar-right fs-7" :class="{'text-primary': state.sidebarType === 'full'}"></i>
            <span class="text-dark">Full</span>
          </a>
          <a href="javascript:void(0)" @click="setSidebarType('mini-sidebar')" :class="{'text-primary': state.sidebarType === 'mini-sidebar'}" class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center text-dark sidebartoggler gap-2">
            <i class="ti ti-layout-sidebar fs-7" :class="{'text-primary': state.sidebarType === 'mini-sidebar'}"></i>
            <span class="text-dark">Collapse</span>
          </a>
        </div>
      </div>

      <!-- Card With -->
      <div class="card-with pb-4">
        <h6 class="fw-semibold fs-4 mb-1">Card With</h6>
        <div class="d-flex align-items-center gap-3 my-3">
          <a href="javascript:void(0)" @click="setCardWith('border')" :class="{'text-primary': state.cardWith === 'border'}" class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center gap-2 text-dark cardborder">              
            <i class="ti ti-border-outer fs-7" :class="{'text-primary': state.cardWith === 'border'}"></i>
            <span class="text-dark">Border</span>
          </a>
          <a href="javascript:void(0)" @click="setCardWith('shadow')" :class="{'text-primary': state.cardWith === 'shadow'}" class="rounded-2 p-9 customizer-box hover-img d-flex align-items-center gap-2 cardshadow text-dark">
            <i class="ti ti-border-none fs-7" :class="{'text-primary': state.cardWith === 'shadow'}"></i>
            <span class="text-dark">Shadow</span>
          </a>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { useCustomizer } from '../composables/useCustomizer'

const { state } = useCustomizer()

const setThemeMode = (mode) => { state.themeMode = mode }
const setThemeColor = (color) => { state.themeColor = color }
const setContainerOption = (option) => { state.containerOption = option }
const setSidebarType = (type) => { state.sidebarType = type }
const setCardWith = (type) => { state.cardWith = type }
</script>
