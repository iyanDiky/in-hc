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

  // Template CSS naming convention: 
  // Light: style.min.css (blue), style-aqua.min.css, style-purple.min.css, etc
  // Dark: style-dark.min.css (blue), style-aqua-dark.min.css, etc.
  
  let colorName = color.replace('_theme', '')
  if (colorName === 'blue') colorName = '' // blue is the default style
  else colorName = `-${colorName}`

  let modeSuffix = mode === 'dark' ? '-dark' : ''
  
  // Special case for default blue light theme which is just style.min.css
  let cssFileName = `style${colorName}${modeSuffix}.min.css`
  if (cssFileName === 'style.min.css' && mode === 'dark') {
     cssFileName = 'style-dark.min.css'
  }

  linkEl.href = `/dist/css/${cssFileName}`
}

export const useCustomizer = () => {
  return {
    state
  }
}
