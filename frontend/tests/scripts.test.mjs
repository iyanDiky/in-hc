import test from 'node:test'
import assert from 'node:assert/strict'
import { loadScript, loadPagePlugins } from '../src/utils/scripts.js'

const requested = []
let rejectNext = false
global.document = {
  createElement: () => ({ remove() {} }),
  head: {
    appendChild(script) {
      requested.push(script.src)
      const fail = rejectNext
      rejectNext = false
      queueMicrotask(() => fail ? script.onerror() : script.onload())
    },
  },
}

test('concurrent script loads share one download', async () => {
  const first = loadScript('/test/shared.js')
  assert.equal(first, loadScript('/test/shared.js'))
  await first
  assert.equal(requested.filter(src => src === '/test/shared.js').length, 1)
})

test('failed download can be retried', async () => {
  rejectNext = true
  await assert.rejects(loadScript('/test/retry.js'))
  await loadScript('/test/retry.js')
  assert.equal(requested.filter(src => src === '/test/retry.js').length, 2)
})

test('dashboard loads charts only; table adapter follows its dependency', async () => {
  requested.length = 0
  await loadPagePlugins('Dashboard')
  assert.equal(requested.at(-1), '/dist/libs/apexcharts/dist/apexcharts.min.js')
  assert.equal(requested.some(src => src.includes('datatables') || src.includes('select2')), false)
  requested.length = 0
  await loadPagePlugins('SuratMasuk')
  assert.equal(requested.filter(src => src.includes('select2')).length, 1)
  assert.ok(requested.indexOf('/dist/libs/datatables.net/js/jquery.dataTables.min.js') < requested.indexOf('/dist/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js'))
})
