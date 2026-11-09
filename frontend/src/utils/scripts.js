const pending = new Map()

// Share pending loads between navigations and surface failures instead of polling forever.
export function loadScript(src) {
  if (pending.has(src)) return pending.get(src)
  const promise = new Promise((resolve, reject) => {
    const script = document.createElement('script')
    const fail = () => {
      clearTimeout(timer)
      script.remove()
      reject(new Error(`Gagal memuat ${src}`))
    }
    const timer = setTimeout(fail, 15000)
    script.src = src
    script.onload = () => {
      clearTimeout(timer)
      resolve()
    }
    script.onerror = fail
    document.head.appendChild(script)
  }).catch(error => {
    pending.delete(src)
    throw error
  })
  pending.set(src, promise)
  return promise
}

export async function loadPagePlugins(routeName) {
  await Promise.all([
    loadScript('/dist/libs/jquery/dist/jquery.min.js'),
    loadScript('/dist/libs/bootstrap/dist/js/bootstrap.bundle.min.js'),
    loadScript('/dist/libs/simplebar/dist/simplebar.min.js'),
  ])
  if (routeName === 'Dashboard' || routeName === 'DashboardAlias') {
    await loadScript('/dist/libs/apexcharts/dist/apexcharts.min.js')
    return
  }
  // DataTables and Select2 require jQuery to have finished executing.
  await Promise.all([
    loadScript('/dist/libs/select2/dist/js/select2.full.min.js'),
    loadScript('/dist/libs/datatables.net/js/jquery.dataTables.min.js'),
  ])
  await loadScript('/dist/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js')
}
