import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '../layout/MainLayout.vue'

const routes = [
  {
    path: '/',
    component: MainLayout,
    children: [
      {
        path: '',
        name: 'Home',
        component: () => import('../views/Jabatan.vue') // default to Jabatan for now
      },
      {
        path: 'jabatan',
        name: 'Jabatan',
        component: () => import('../views/Jabatan.vue')
      },
      {
        path: 'unit-kerja',
        name: 'UnitKerja',
        component: () => import('../views/UnitKerja.vue')
      },
      {
        path: 'bagian-seksi',
        name: 'BagianSeksi',
        component: () => import('../views/BagianSeksi.vue')
      },
      {
        path: 'users',
        name: 'Users',
        component: () => import('../views/Users.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  linkActiveClass: 'active',
  linkExactActiveClass: 'active',
  routes
})

export default router
