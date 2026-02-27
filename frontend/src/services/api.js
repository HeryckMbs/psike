import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Products
export const productsApi = {
  getAll: (params = {}) => api.get('/products', { params }),
  getById: (id) => api.get(`/products/${id}`),
}

// Categories
export const categoriesApi = {
  getAll: () => api.get('/categories'),
}

// Orders
export const ordersApi = {
  create: (data) => api.post('/orders', data),
  getById: (id) => api.get(`/orders/${id}`),
}

// Blog
export const blogApi = {
  getAll: (params = {}) => api.get('/posts', { params }),
  getBySlug: (slug) => api.get(`/posts/${slug}`),
}

export default api
