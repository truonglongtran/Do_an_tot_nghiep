import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login/Login.vue';
import AdminLayout from '../views/Admin/AdminLayout.vue';
import SellerDashboard from '../views/Seller/SellerDashboard.vue';
import BuyerDashboard from '../views/Buyer/BuyerDashboard.vue';

const routes = [
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: Login,
  },
  {
    path: '/seller/login',
    name: 'SellerLogin',
    component: Login,
  },
  {
    path: '/buyer/login',
    name: 'BuyerLogin',
    component: Login,
  },
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      { path: 'dashboard', component: () => import('@/views/Admin/Dashboard.vue') },
      { path: 'users', component: () => import('@/views/Admin/Users.vue') },
      // Thêm các route khác tương tự...
    ]
  },
  
  {
    path: '/seller/dashboard',
    name: 'SellerDashboard',
    component: SellerDashboard,
    meta: { requiresAuth: true, role: 'seller', loginPath: '/seller/login' },
  },
  {
    path: '/buyer/dashboard',
    name: 'BuyerDashboard',
    component: BuyerDashboard,
    meta: { requiresAuth: true, role: ['buyer', 'seller'], loginPath: '/buyer/login' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  const role = localStorage.getItem('role');
  const loginType = localStorage.getItem('loginType');

  if (to.meta.requiresAuth && !token) {
    // Chuyển hướng đến trang đăng nhập dựa trên meta.loginPath
    const loginPath = to.meta.loginPath || '/buyer/login';
    next(loginPath);
  } else if (to.meta.requiresAuth) {
    // Kiểm tra vai trò
    const allowedRoles = Array.isArray(to.meta.role) ? to.meta.role : [to.meta.role];
    if (!allowedRoles.includes(role)) {
      // Chuyển hướng đến dashboard phù hợp với loginType
      if (loginType === 'admin') {
        next('/admin/dashboard');
      } else if (loginType === 'seller') {
        next('/seller/dashboard');
      } else {
        next('/buyer/dashboard');
      }
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;