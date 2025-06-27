<!-- src/views/Buyer/ProfileEdit.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-6">Thông tin cá nhân</h1>
    <div v-if="loading" class="text-center">
      <svg class="animate-spin w-8 h-8 mx-auto text-orange-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
    </div>
    <div v-else-if="error" class="text-red-500 text-center mb-6">
      {{ error }}
    </div>
    <div v-else class="space-y-8">
      <!-- Profile Display/Edit -->
      <div class="border rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold text-gray-700">Thông tin tài khoản</h2>
          <button
            v-if="!isEditing"
            @click="isEditing = true"
            class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600"
          >
            Sửa
          </button>
        </div>
        <div v-if="!isEditing" class="space-y-4">
          <div class="flex items-center space-x-4">
            <img
              :src="form.avatar_url || 'https://via.placeholder.com/100'"
              alt="Avatar"
              class="w-20 h-20 rounded-full object-cover"
            />
            <div>
              <p class="text-sm font-medium text-gray-700">Ảnh đại diện</p>
            </div>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-700">Tên người dùng</p>
            <p class="text-gray-900">{{ form.username }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-700">Email</p>
            <p class="text-gray-900">{{ form.email }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-700">Số điện thoại</p>
            <p class="text-gray-900">{{ form.phone_number || 'Chưa cung cấp' }}</p>
          </div>
        </div>
        <form v-else @submit.prevent="updateProfile" class="space-y-4">
          <!-- Avatar Upload -->
          <div class="flex items-center space-x-4">
            <img
              :src="form.avatar_url || 'https://via.placeholder.com/100'"
              alt="Avatar"
              class="w-20 h-20 rounded-full object-cover"
            />
            <div>
              <label class="block text-sm font-medium text-gray-700">Ảnh đại diện</label>
              <input
                type="file"
                accept="image/*"
                @change="handleAvatarChange"
                class="mt-1 block w-full text-sm text-gray-500"
              />
            </div>
          </div>
          <!-- Username -->
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Tên người dùng</label>
            <input
              id="username"
              v-model="form.username"
              type="text"
              class="mt-1 block w-full border rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500"
              required
            />
          </div>
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="mt-1 block w-full border rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500"
              required
            />
          </div>
          <!-- Phone Number -->
          <div>
            <label for="phone_number" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
            <input
              id="phone_number"
              v-model="form.phone_number"
              type="tel"
              class="mt-1 block w-full border rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500"
            />
          </div>
          <div class="flex space-x-2">
            <button
              type="submit"
              :disabled="saving"
              class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 disabled:bg-gray-300"
            >
              {{ saving ? 'Đang lưu...' : 'Lưu thay đổi' }}
            </button>
            <button
              type="button"
              @click="cancelEdit"
              class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400"
            >
              Hủy
            </button>
          </div>
        </form>
      </div>
      <!-- Address Management -->
      <div class="border rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Quản lý địa chỉ giao hàng</h2>
        <AddressesComponent />
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Addresses from './Addresses.vue';

export default {
  name: 'ProfileEdit',
  components: {
    AddressesComponent: Addresses,
  },
  data() {
    return {
      form: {
        username: '',
        email: '',
        phone_number: '',
        avatar_url: '',
        avatar_file: null,
      },
      originalForm: null, // Lưu trạng thái ban đầu để hủy chỉnh sửa
      loading: true,
      saving: false,
      error: null,
      isEditing: false,
    };
  },
  async created() {
    await this.fetchProfile();
  },
  methods: {
    async fetchProfile() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/buyer/user', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        const { username, email, phone_number, avatar_url } = response.data.data;
        this.form = { username, email, phone_number, avatar_url, avatar_file: null };
        this.originalForm = { ...this.form }; // Lưu trạng thái ban đầu
      } catch (err) {
        this.error = err.response?.data?.message || 'Lỗi tải thông tin cá nhân.';
        console.error('Error fetching profile:', err.response?.data || err);
      } finally {
        this.loading = false;
      }
    },
    handleAvatarChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.form.avatar_file = file;
        this.form.avatar_url = URL.createObjectURL(file);
      }
    },
    async updateProfile() {
      this.saving = true;
      this.error = null;
      try {
        const formData = new FormData();
        formData.append('username', this.form.username);
        formData.append('email', this.form.email);
        if (this.form.phone_number) {
          formData.append('phone_number', this.form.phone_number);
        }
        if (this.form.avatar_file) {
          formData.append('avatar', this.form.avatar_file);
        }

        const response = await axios.post('/buyer/user/update', formData, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'multipart/form-data',
          },
        });

        // Cập nhật localStorage
        localStorage.setItem('username', response.data.user.username);
        localStorage.setItem('email', response.data.user.email);
        localStorage.setItem('avatar_url', response.data.user.avatar_url);
        if (response.data.user.phone_number) {
          localStorage.setItem('phone_number', response.data.user.phone_number);
        }

        // Phát sự kiện cập nhật
        this.$emit('update:user', response.data.user);
        this.originalForm = { ...response.data.user, avatar_file: null }; // Cập nhật trạng thái gốc
        this.isEditing = false; // Thoát chế độ chỉnh sửa
        alert('Cập nhật thông tin thành công!');
      } catch (error) {
        this.error = error.response?.data?.message || 'Lỗi cập nhật thông tin.';
        console.error('Error updating profile:', error.response?.data || error);
      } finally {
        this.saving = false;
      }
    },
    cancelEdit() {
      this.form = { ...this.originalForm, avatar_file: null }; // Khôi phục trạng thái ban đầu
      this.isEditing = false;
      this.error = null;
    },
  },
};
</script>

<style scoped>
/* Tailwind handles most styling */
</style>