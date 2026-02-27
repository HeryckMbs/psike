import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Layout from '../components/Layout.vue'
import Dashboard from '../views/Dashboard.vue'
import OrdersKanban from '../views/Orders/Kanban.vue'
import ProductsList from '../views/Products/List.vue'
import ProductForm from '../views/Products/Form.vue'
import CategoriesList from '../views/Categories/List.vue'
import CategoryForm from '../views/Categories/Form.vue'
import BoxesList from '../views/Boxes/List.vue'
import BoxForm from '../views/Boxes/Form.vue'
import BlogList from '../views/Blog/List.vue'
import CostTypesIndex from '../views/CostTypes/Index.vue'
import { useAuthStore } from '../store/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/',
    component: Layout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: Dashboard
      },
      {
        path: 'orders',
        name: 'OrdersKanban',
        component: OrdersKanban
      },
      {
        path: 'products',
        name: 'ProductsList',
        component: ProductsList
      },
      {
        path: 'products/new',
        name: 'ProductNew',
        component: ProductForm
      },
      {
        path: 'products/:id/edit',
        name: 'ProductEdit',
        component: ProductForm,
        props: true
      },
      {
        path: 'categories',
        name: 'CategoriesList',
        component: CategoriesList
      },
      {
        path: 'categories/new',
        name: 'CategoryNew',
        component: CategoryForm
      },
      {
        path: 'categories/:id/edit',
        name: 'CategoryEdit',
        component: CategoryForm,
        props: true
      },
      {
        path: 'boxes',
        name: 'BoxesList',
        component: BoxesList
      },
      {
        path: 'boxes/new',
        name: 'BoxNew',
        component: BoxForm
      },
      {
        path: 'boxes/:id/edit',
        name: 'BoxEdit',
        component: BoxForm,
        props: true
      },
      {
        path: 'blog',
        name: 'BlogList',
        component: BlogList
      },
      {
        path: 'cost-types',
        name: 'CostTypes',
        component: CostTypesIndex
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory('/admin'),
  routes
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.path === '/login' && authStore.isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router
