<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-2xl font-bold mb-6 text-center">Đăng ký Người mua</h2>
      <form @submit.prevent="register">
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
        <div class="mb-4">
          <label class="block text-gray-700">Tên người dùng</label>
          <input
            v-model="form.username"
            type="text"
            class="w-full p-2 border rounded"
            :class="{ 'border-red-500': errors.username }"
            required
          />
          <p v-if="errors.username" class="text-red-500 text-sm mt-1">{{ errors.username }}</p>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700">Số điện thoại</label>
          <input
            v-model="form.phone_number"
            type="text"
            class="w-full p-2 border rounded"
            :class="{ 'border-red-500': errors.phone_number }"
            required
          />
          <p v-if="errors.phone_number" class="text-red-500 text-sm mt-1">{{ errors.phone_number }}</p>
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
          {{ isLoading ? 'Đang đăng ký...' : 'Đăng ký' }}
        </button>
        <p class="mt-4 text-center">
          Đã có tài khoản? <router-link to="/buyer/login" class="text-blue-500 hover:underline">Đăng nhập</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useRouter } from 'vue-router';

export default {
  name: 'BuyerRegister',
  setup() {
    const router = useRouter();
    return { router };
  },
  data() {
    return {
      form: {
        email: '',
        username: '',
        phone_number: '',
        password: '',
      },
      errors: {
        email: '',
        username: '',
        phone_number: '',
        password: '',
      },
      isLoading: false,
    };
  },
  methods: {
    async register() {
      this.isLoading = true;
      this.errors = { email: '', username: '', phone_number: '', password: '' };

      try {
        const response = await axios.post('http://localhost:8000/api/buyer/register', this.form, {
          headers: {
            'Content-Type': 'application/json',
          },
        });
        if (response.status === 201 && response.data.success) {
          localStorage.setItem('token', response.data.token);
          localStorage.setItem('email', response.data.user.email);
          localStorage.setItem('username', response.data.user.username);
          localStorage.setItem('avatar_url', response.data.user.avatar_url);
          localStorage.setItem('role', response.data.role);
          localStorage.setItem('loginType', 'buyer');
          window.dispatchEvent(new Event('storage'));
          this.router.push('/');
        } else {
          this.errors.email = response.data.message || 'Đã có lỗi xảy ra khi đăng ký';
        }
      } catch (error) {
        console.error('Lỗi đăng ký:', error.response?.data || error.message);
        const message = error.response?.data?.message || 'Đã có lỗi xảy ra';
        if (error.response?.status === 422) {
          const errors = error.response.data.errors || {};
          this.errors.email = errors.email?.[0] || '';
          this.errors.username = errors.username?.[0] || '';
          this.errors.phone_number = errors.phone_number?.[0] || '';
          this.errors.password = errors.password?.[0] || '';
          if (!Object.values(errors).length) {
            this.errors.email = message;
          }
        } else {
          this.errors.email = message;
        }
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>