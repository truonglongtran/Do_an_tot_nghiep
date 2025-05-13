<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-2xl font-bold mb-6 text-center">Đăng nhập {{ roleLabel }}</h2>
      <form @submit.prevent="login">
        <div class="mb-4">
          <label class="block text-gray-700">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full p-2 border rounded"
            :class="{ 'border-red-500': errors.email }"
            required
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
            required
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
      const type = this.route.path.split('/')[1]; // 'admin', 'seller', hoặc 'buyer'
      return type === 'admin' ? 'Admin' : type === 'seller' ? 'Người bán' : 'Người mua';
    },
    loginEndpoint() {
      const type = this.route.path.split('/')[1]; // Lấy 'admin', 'seller', hoặc 'buyer'
      return `http://localhost:8000/api/${type}/login`; // Tạo URL đúng
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
  this.isLoading = true;
  this.errors = { email: '', password: '' };

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
      localStorage.setItem('role', response.data.role);
      localStorage.setItem('loginType', this.route.path.split('/')[1]);
      const loginType = this.route.path.split('/')[1];
      if (loginType === 'admin') {
        this.router.push('/admin/dashboard');
      } else if (loginType === 'seller') {
        this.router.push('/seller/dashboard');
      } else if (loginType === 'buyer') {
        this.router.push('/buyer/dashboard');
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