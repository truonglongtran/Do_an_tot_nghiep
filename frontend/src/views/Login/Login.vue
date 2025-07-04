<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-2xl font-bold mb-6 text-center">Đăng nhập {{ roleLabel }}</h2>
      <form @submit.prevent="login" novalidate>
        <div class="mb-4">
          <label class="block text-gray-700">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full p-2 border rounded"
            :class="{ 'border-red-500': errors.email }"
          />
          <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
        </div>
        <div class="mb-6">
          <label class="block text-gray-700">Mật khẩu</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full p-2 border rounded"
            :class="{ 'border-red-500': errors.password }"
          />
          <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
        </div>
        <button
          type="submit"
          class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600 disabled:bg-blue-300"
          :disabled="isLoading"
        >
          {{ isLoading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
        </button>
      </form>
      <p v-if="showRegisterLink" class="mt-4 text-center">
        Chưa có tài khoản? 
        <router-link :to="registerPath" class="text-blue-500 hover:underline">
          Đăng ký {{ roleLabel }}
        </router-link>
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';

export default {
  name: 'Login',
  setup() {
    const router = useRouter();
    const route = useRoute();
    return { router, route };
  },
  computed: {
    roleLabel() {
      const type = this.route.path.split('/')[1]; // 'admin', 'seller', or 'buyer'
      return type === 'admin' ? 'Admin' : type === 'seller' ? 'Người bán' : 'Người mua';
    },
    loginEndpoint() {
      const type = this.route.path.split('/')[1]; // 'admin', 'seller', or 'buyer'
      return `http://localhost:8000/api/${type}/login`;
    },
    showRegisterLink() {
      const type = this.route.path.split('/')[1];
      return type === 'buyer' || type === 'seller'; // Only show register link for buyer and seller
    },
    registerPath() {
      const type = this.route.path.split('/')[1];
      return type === 'buyer' ? '/buyer/register' : '/seller/register';
    },
  },
  data() {
    return {
      form: {
        email: '',
        password: '',
      },
      errors: {
        email: '',
        password: '',
      },
      isLoading: false,
    };
  },
  methods: {
    async login() {
      // Reset lỗi trước khi kiểm tra
      this.errors = { email: '', password: '' };

      // Kiểm tra trường email và password
      if (!this.form.email) {
        this.errors.email = 'Vui lòng nhập email';
        return;
      }
      if (!this.form.password) {
        this.errors.password = 'Vui lòng nhập mật khẩu';
        return;
      }

      this.isLoading = true;

      try {
        console.log('Gửi yêu cầu tới:', this.loginEndpoint);
        console.log('Dữ liệu gửi:', JSON.stringify(this.form));
        const response = await axios.post(this.loginEndpoint, this.form, {
          headers: {
            'Content-Type': 'application/json',
          },
        });
        console.log('Phản hồi đầy đủ:', response);
        console.log('Dữ liệu phản hồi:', response.data);
        if (response.status === 200 && response.data.token) {
          localStorage.setItem('token', response.data.token);
          localStorage.setItem('email', response.data.user.email);
          localStorage.setItem('username', response.data.user.username || 'Người dùng');
          localStorage.setItem('avatar_url', response.data.user.avatar_url || 'https://via.placeholder.com/50');
          localStorage.setItem('role', response.data.role);
          localStorage.setItem('loginType', this.route.path.split('/')[1]);
          console.log('localStorage username:', localStorage.getItem('username'));
          window.dispatchEvent(new Event('storage'));
          const loginType = this.route.path.split('/')[1];
          if (loginType === 'admin') {
            this.router.push('/admin/dashboard');
          } else if (loginType === 'seller') {
            this.router.push('/seller/dashboard');
          } else {
            this.router.push('/');
          }
        } else {
          console.log('Lỗi: Phản hồi không hợp lệ, status:', response.status, 'data:', response.data);
          this.errors.email = response.data.message || 'Đã có lỗi xảy ra khi đăng nhập';
        }
      } catch (error) {
        console.error('Lỗi đăng nhập:', error.response?.data || error.message);
        const message = error.response?.data?.message || 'Đã có lỗi xảy ra';
        this.errors.email = message;
        if (message.includes('Thông tin đăng nhập')) {
          this.errors.password = 'Email hoặc mật khẩu không đúng';
        }
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>