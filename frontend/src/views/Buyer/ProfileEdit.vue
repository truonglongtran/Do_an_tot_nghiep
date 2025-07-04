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
              :src="avatarUrlWithCacheBust"
              alt="Avatar"
              class="w-20 h-20 rounded-full object-cover"
              @error="handleImageError"
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
              :src="avatarUrlWithCacheBust"
              alt="Avatar"
              class="w-20 h-20 rounded-full object-cover"
              @error="handleImageError"
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
      previewUrl: null,
      originalForm: null,
      loading: true,
      saving: false,
      error: null,
      isEditing: false,
      cacheBuster: Date.now(),
    };
  },
  computed: {
    apiBaseUrl() {
      return import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
    },
    storageBaseUrl() {
      return import.meta.env.VITE_STORAGE_BASE_URL || 'http://localhost:8000/storage';
    },
    avatarUrlWithCacheBust() {
      // Use previewUrl for selected file
      if (this.previewUrl) {
        console.log('Using preview URL for avatar:', this.previewUrl);
        return this.previewUrl;
      }
      // Handle server-provided avatar_url
      if (!this.form.avatar_url) {
        console.warn('No avatar_url, using placeholder');
        return 'https://via.placeholder.com/100';
      }
      // Handle external URLs
      if (/^https?:\/\//.test(this.form.avatar_url)) {
        console.log('Using external avatar URL:', this.form.avatar_url);
        return `${this.form.avatar_url}?t=${this.cacheBuster}`;
      }
      // Handle local storage URLs
      let cleanUrl = this.form.avatar_url;
      while (cleanUrl.startsWith('/storage/') || cleanUrl.startsWith('storage/')) {
        cleanUrl = cleanUrl.replace(/^\/?storage\//, '');
      }
      const finalUrl = `${this.storageBaseUrl}/${cleanUrl}?t=${this.cacheBuster}`;
      console.log('Constructed avatar URL:', finalUrl);
      return finalUrl;
    },
  },
  async created() {
    console.log('VITE_API_BASE_URL:', import.meta.env.VITE_API_BASE_URL);
    console.log('VITE_STORAGE_BASE_URL:', import.meta.env.VITE_STORAGE_BASE_URL);
    await this.fetchProfile();
  },
  methods: {
    async fetchProfile() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get(`${this.apiBaseUrl}/buyer/user`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        const { username, email, phone_number, avatar_url } = response.data.data;
        this.form = { username, email, phone_number, avatar_url: avatar_url || '', avatar_file: null };
        this.originalForm = { ...this.form };
        this.previewUrl = null; // Reset preview
        console.log('Profile fetched:', { username, email, phone_number, avatar_url });
        if (!avatar_url) {
          console.warn('No avatar_url provided by API, using placeholder');
        } else {
          console.log('Attempting to load avatar:', this.avatarUrlWithCacheBust);
        }
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
        this.previewUrl = URL.createObjectURL(file);
        console.log('Avatar selected:', file.name, 'Preview URL:', this.previewUrl);
      } else {
        this.previewUrl = null;
      }
    },
    handleImageError(event) {
      console.error('Avatar load error:', {
        src: event.target.src,
        userId: this.form.user_id || 'unknown',
        error: 'Image failed to load, reverting to placeholder',
        avatarUrl: this.form.avatar_url,
        previewUrl: this.previewUrl,
        constructedUrl: this.avatarUrlWithCacheBust,
        apiBaseUrl: this.apiBaseUrl,
        storageBaseUrl: this.storageBaseUrl,
      });
      if (event.target.src !== 'https://via.placeholder.com/100') {
        event.target.src = 'https://via.placeholder.com/100';
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
          console.log('Uploading avatar:', this.form.avatar_file.name);
        }

        const response = await axios.post(`${this.apiBaseUrl}/buyer/user`, formData, {
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
        } else {
          localStorage.removeItem('phone_number');
        }

        // Cập nhật form và originalForm
        this.form = {
          username: response.data.user.username,
          email: response.data.user.email,
          phone_number: response.data.user.phone_number,
          avatar_url: response.data.user.avatar_url || '',
          avatar_file: null,
        };
        this.originalForm = { ...this.form };
        this.previewUrl = null; // Reset preview after upload
        this.cacheBuster = Date.now();
        this.isEditing = false;
        console.log('Profile updated:', response.data.user);
        alert('Cập nhật thông tin thành công!');
      } catch (error) {
        this.error = error.response?.data?.message || 'Lỗi cập nhật thông tin.';
        console.error('Error updating profile:', error.response?.data || error);
      } finally {
        this.saving = false;
      }
    },
    cancelEdit() {
      this.form = { ...this.originalForm, avatar_file: null };
      this.previewUrl = null;
      this.isEditing = false;
      this.error = null;
    },
  },
};
</script>

<style scoped>
/* Tailwind handles most styling */
</style>