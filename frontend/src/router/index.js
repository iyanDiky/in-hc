import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '../layout/MainLayout.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue')
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
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

router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('auth_token')
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'Login' })
  } else if (to.name === 'Login' && isAuthenticated) {
    next({ name: 'Home' })
  } else {
    next()
  }
})

export default router
