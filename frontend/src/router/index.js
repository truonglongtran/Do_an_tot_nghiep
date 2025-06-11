import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login/Login.vue';
import AdminLayout from '../views/Admin/AdminLayout.vue';
import SellerLayout from '../views/Seller/SellerLayout.vue';
import BuyerDashboard from '../views/Buyer/BuyerDashboard.vue';

const routes = [
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: Login,
    meta: { requiresGuest: true },
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
    meta: { requiresAuth: true, roles: ['superadmin', 'admin', 'moderator'], loginPath: '/admin/login' },
    children: [
      {
        path: 'dashboard',
        component: () => import('@/views/Admin/AdminDashboard.vue'),
        meta: {
          roles: ['superadmin', 'admin', 'moderator'],
          permissions: {
            superadmin: ['view'],
            admin: ['view'],
            moderator: ['view'],
          },
        },
      },
      {
        path: 'users',
        component: () => import('@/views/Admin/AdminUsers.vue'),
        meta: {
          roles: ['superadmin', 'admin'],
          permissions: {
            superadmin: ['view', 'create', 'update', 'delete', 'updateStatus'],
            admin: ['view', 'create', 'update', 'updateStatus'],
          },
        },
      },
      {
        path: 'shops',
        component: () => import('@/views/Admin/AdminShop.vue'),
        meta: {
          roles: ['superadmin', 'admin', 'moderator'],
          permissions: {
            superadmin: ['view', 'updateStatus', 'delete'],
            admin: ['view', 'updateStatus'],
            moderator: ['view'],
          },
        },
      },
      {
        path: 'admins',
        component: () => import('@/views/Admin/AdminAdmins.vue'),
        meta: {
          roles: ['superadmin'],
          permissions: {
            superadmin: ['view', 'create', 'update', 'delete', 'updateStatus'],
          },
        },
      },
      {
        path: 'products',
        component: () => import('@/views/Admin/AdminProducts.vue'),
        meta: {
          roles: ['superadmin', 'admin', 'moderator'],
          permissions: {
            superadmin: ['view', 'updateStatus', 'delete'],
            admin: ['view', 'updateStatus', 'delete'],
            moderator: ['view', 'updateStatus'],
          },
        },
      },
      {
        path: 'orders',
        component: () => import('@/views/Admin/AdminOrders.vue'),
        meta: {
          roles: ['superadmin', 'admin', 'moderator'],
          permissions: {
            superadmin: ['view', 'updateStatus', 'delete'],
            admin: ['view', 'updateStatus'],
            moderator: ['view'],
          },
        },
      },
      {
        path: 'disputes',
        component: () => import('@/views/Admin/AdminDisputes.vue'),
        meta: {
          roles: ['superadmin', 'admin', 'moderator'],
          permissions: {
            superadmin: ['view', 'updateStatus', 'delete'],
            admin: ['view', 'updateStatus'],
            moderator: ['view'],
          },
        },
      },
      {
        path: 'vouchers',
        component: () => import('@/views/Admin/AdminVouchers.vue'),
        meta: {
          roles: ['superadmin', 'admin', 'moderator'],
          permissions: {
            superadmin: ['view', 'create', 'update', 'delete'],
            admin: ['view', 'create', 'update', 'delete'],
            moderator: ['view', 'create', 'update'],
          },
        },
      },
      {
        path: 'payments',
        component: () => import('@/views/Admin/AdminPayments.vue'),
        meta: {
          roles: ['superadmin', 'admin', 'moderator'],
          permissions: {
            superadmin: ['view', 'create', 'update', 'delete', 'updateStatus'],
            admin: ['view', 'create', 'update', 'updateStatus'],
            moderator: ['view'],
          },
        },
      },
      {
        path: 'reviews',
        component: () => import('@/views/Admin/AdminReviews.vue'),
        meta: {
          roles: ['superadmin', 'admin', 'moderator'],
          permissions: {
            superadmin: ['view', 'create', 'update', 'delete'],
            admin: ['view', 'create', 'update', 'delete'],
            moderator: ['view', 'create', 'update', 'delete'],
          },
        },
        children: [
          {
            path: ':shopId',
            name: 'ShopReviews',
            component: () => import('@/views/Admin/AdminShopReviews.vue'),
            meta: {
              roles: ['superadmin', 'admin', 'moderator'],
              permissions: {
                superadmin: ['view', 'create', 'update', 'delete'],
                admin: ['view', 'create', 'update', 'delete'],
                moderator: ['view', 'create', 'update', 'delete'],
              },
            },
          },
        ],
      },
      {
        path: 'reports',
        component: () => import('@/views/Admin/AdminReports.vue'),
        meta: {
          roles: ['superadmin', 'admin'],
          permissions: {
            superadmin: ['view'],
            admin: ['view'],
          },
        },
      },
      {
        path: 'shipping-partners',
        component: () => import('@/views/Admin/AdminShippingPartners.vue'),
        meta: {
          roles: ['superadmin', 'admin', 'moderator'],
          permissions: {
            superadmin: ['view', 'create', 'update', 'delete'],
            admin: ['view', 'create', 'update'],
            moderator: ['view'],
          },
        },
      },
      {
        path: 'banners',
        component: () => import('@/views/Admin/AdminBanners.vue'),
        meta: {
          roles: ['superadmin', 'admin', 'moderator'],
          permissions: {
            superadmin: ['view', 'create', 'update', 'delete'],
            admin: ['view', 'create', 'update', 'delete'],
            moderator: ['view', 'create', 'update'],
          },
        },
      },
    ],
  },
  {
    path: '/seller',
    component: SellerLayout,
    meta: { requiresAuth: true, roles: ['seller'], loginPath: '/seller/login' },
    children: [
      {
        path: 'dashboard',
        component: () => import('@/views/Seller/SellerDashboard.vue'),
        meta: {
          roles: ['seller'],
          permissions: {
            seller: ['view'],
          },
        },
      },
      {
        path: 'orders',
        component: () => import('@/views/Seller/SellerOrders.vue'),
        meta: {
          roles: ['seller'],
          permissions: {
            seller: ['view', 'update'],
          },
        },
      },
    ],
  },  
  {
    path: '/buyer/dashboard',
    name: 'BuyerDashboard',
    component: BuyerDashboard,
    meta: { requiresAuth: true, roles: ['buyer', 'seller'], loginPath: '/buyer/login' },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/admin/login',
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

  console.log('Navigating to:', to.path, 'From:', from.path, 'Token:', !!token, 'Role:', role, 'LoginType:', loginType);

  // Prevent access to login page if already authenticated
  if (to.meta.requiresGuest && token && loginType) {
    if (loginType === 'admin') {
      return next('/admin/dashboard');
    } else if (loginType === 'seller') {
      return next('/seller/dashboard');
    } else {
      return next('/buyer/dashboard');
    }
  }

  // Handle routes requiring authentication
  if (to.meta.requiresAuth) {
    if (!token || !role || !loginType) {
      console.log('No token/role/loginType, redirecting to:', to.meta.loginPath || '/buyer/login');
      return next(to.meta.loginPath || '/buyer/login');
    }

    const allowedRoles = Array.isArray(to.meta.roles) ? to.meta.roles : [to.meta.roles];
    if (!allowedRoles.includes(role)) {
      console.log('Role not allowed:', role, 'Redirecting based on loginType:', loginType);
      if (loginType === 'admin') {
        return next('/admin/dashboard');
      } else if (loginType === 'seller') {
        return next('/seller/dashboard');
      } else {
        return next('/buyer/dashboard');
      }
    }

    // Check permissions if defined
    if (to.meta.permissions) {
      const permissions = to.meta.permissions[role] || [];
      if (permissions.length === 0 && to.path !== '/admin/dashboard') {
        console.log('No permissions for:', role, 'Redirecting to /admin/dashboard');
        return next('/admin/dashboard');
      }
    }

    return next();
  }

  // Allow access to public routes
  return next();
});

export default router;