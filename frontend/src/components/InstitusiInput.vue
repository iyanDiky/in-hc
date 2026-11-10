<template>
  <div>
    <div class="position-relative">
    <input
      :id="id" type="text" class="form-control" :value="modelValue" maxlength="255"
      placeholder="Ketik nama institusi / universitas" autocomplete="off"
      role="combobox" aria-autocomplete="list" :aria-expanded="open"
      :aria-controls="`${id}-options`" :aria-describedby="`${id}-help`"
      :aria-activedescendant="open && active >= 0 ? `${id}-option-${active}` : undefined"
      @input="$emit('update:modelValue', $event.target.value)"
      @focus="focused = true; selectedName = null; scheduleSearch()" @blur="focused = false"
      @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)"
      @keydown.enter.prevent="chooseActive()" @keydown.esc.prevent="dismissed = true"
    >
    <ul v-if="open" :id="`${id}-options`" class="list-group shadow institution-options" role="listbox">
      <li v-for="(option, index) in options" :id="`${id}-option-${index}`" :key="option.id"
        role="option" :aria-selected="active === index" :class="['list-group-item list-group-item-action', { active: active === index }]"
        @pointerdown.prevent="select(option)" @mouseenter="active = index">
        {{ option.nama }}
      </li>
    </ul>
    </div>
    <div :id="`${id}-help`" class="form-text" aria-live="polite">
      <span v-if="loading">Mencari institusi…</span>
      <span v-else-if="error">Saran belum tersedia. Kamu tetap bisa mengetik nama institusi.</span>
      <span v-else-if="modelValue.trim()">Pilih saran, atau biarkan nama yang diketik. Nama baru tersimpan saat data pelamar disimpan.</span>
      <span v-else>Ketik minimal 2 karakter untuk mencari. Nama yang belum ada juga bisa digunakan.</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import api from '../utils/api'

const props = defineProps({ modelValue: { type: String, default: '' }, id: { type: String, default: 'institusi-pelamar' } })
const emit = defineEmits(['update:modelValue'])
const options = ref([])
const focused = ref(false)
const active = ref(-1)
const dismissed = ref(false)
const loading = ref(false)
const error = ref(false)
const open = computed(() => focused.value && !dismissed.value && options.value.length > 0)
let timer
let controller
let revision = 0
let selectedName = null

function cancel() {
  clearTimeout(timer)
  controller?.abort()
  revision++
}

function scheduleSearch() {
  cancel()
  dismissed.value = false
  options.value = []
  active.value = -1
  error.value = false
  loading.value = false
  if (props.modelValue === selectedName) {
    dismissed.value = true
    return
  }
  const term = props.modelValue.trim().replace(/\s+/g, ' ')
  if (!focused.value || term.length < 2) return
  const current = revision
  loading.value = true
  timer = setTimeout(async () => {
    controller = new AbortController()
    try {
      const response = await api.post('/institusi-pendidikan/search', { search: term }, { signal: controller.signal })
      if (current === revision) options.value = response.data.data
    } catch (failure) {
      if (current === revision && !controller.signal.aborted) error.value = true
    } finally {
      if (current === revision) loading.value = false
    }
  }, 300)
}

function select(option) {
  cancel()
  selectedName = option.nama
  emit('update:modelValue', option.nama)
  dismissed.value = true
  options.value = []
  loading.value = false
}

function move(direction) {
  dismissed.value = false
  if (options.value.length) {
    active.value = active.value < 0
      ? (direction > 0 ? 0 : options.value.length - 1)
      : (active.value + direction + options.value.length) % options.value.length
  }
}

function chooseActive() {
  if (open.value && active.value >= 0) select(options.value[active.value])
  else dismissed.value = true
}

watch(() => props.modelValue, scheduleSearch)
onBeforeUnmount(cancel)
</script>

<style scoped>
.institution-options {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 1056;
  max-height: 220px;
  overflow-y: auto;
}
.institution-options li { cursor: pointer; }
</style>
