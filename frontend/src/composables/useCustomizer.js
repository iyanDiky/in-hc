import { reactive, watch } from 'vue'

const getSavedState = (key, defaultValue) => {
  const saved = localStorage.getItem(key)
  return saved !== null ? saved : defaultValue
}

const state = reactive({
  themeMode: getSavedState('themeMode', 'light'),
  themeColor: getSavedState('themeColor', 'blue_theme'),
  containerOption: getSavedState('containerOption', 'full'),
  sidebarType: getSavedState('sidebarType', 'full'),
  cardWith: getSavedState('cardWith', 'shadow')
})

// Watchers to save to localStorage
watch(() => state.themeMode, (val) => localStorage.setItem('themeMode', val))
watch(() => state.themeColor, (val) => localStorage.setItem('themeColor', val))
watch(() => state.containerOption, (val) => localStorage.setItem('containerOption', val))
watch(() => state.sidebarType, (val) => localStorage.setItem('sidebarType', val))
watch(() => state.cardWith, (val) => localStorage.setItem('cardWith', val))

// Watchers for side effects (DOM manipulation that can't be handled by Vue bindings in MainLayout)
watch([() => state.themeMode, () => state.themeColor], ([mode, color]) => {
  applyThemeCss(mode, color)
}, { immediate: true })

watch(() => state.cardWith, (val) => {
  if (val === 'border') {
    document.body.classList.add('cardwithborder')
  } else {
    document.body.classList.remove('cardwithborder')
  }
}, { immediate: true })

function applyThemeCss(mode, color) {
  const linkEl = document.getElementById('themeColors')
  if (!linkEl) return

  let cssFileName = 'style.min.css'
  if (mode === 'dark') {
    cssFileName = 'style-dark.min.css'
  } else {
    let colorName = color ? color.replace('_theme', '') : ''
    if (colorName && colorName !== 'blue') {
      cssFileName = `style-${colorName}.min.css`
    } else {
      cssFileName = 'style.min.css'
    }
  }

  linkEl.href = `/dist/css/${cssFileName}`
  document.documentElement.setAttribute('data-bs-theme', mode)
}

export const useCustomizer = () => {
  return {
    state
  }
}
