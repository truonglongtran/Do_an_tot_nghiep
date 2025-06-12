<template>
  <div class="p-8 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Hồ sơ shop</h2>
    <p v-if="errorMessage" class="text-red-500">{{ errorMessage }}</p>
    <div v-if="shop" class="bg-white shadow-sm rounded-lg p-6 space-y-6">
      <!-- Cover Image -->
      <div class="relative">
        <img
          :src="shop.cover_image_url || 'https://via.placeholder.com/800x200'"
          alt="Cover"
          class="w-full h-48 object-cover rounded-lg"
        />
        <div v-if="editingField !== 'cover_image'" class="absolute top-2 right-2">
          <button
            @click="startEditing('cover_image')"
            class="bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700"
          >
            Sửa
          </button>
        </div>
        <div v-else class="absolute top-2 right-2 flex space-x-2">
          <input
            type="file"
            @change="editData.cover_image = $event.target.files[0]"
            accept="image/*"
            class="text-sm"
          />
          <button
            @click="saveEdit('cover_image')"
            class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700"
          >
            Lưu
          </button>
          <button
            @click="cancelEdit"
            class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700"
          >
            Hủy
          </button>
        </div>
      </div>

      <!-- Avatar and Name -->
      <div class="flex items-center space-x-4">
        <div class="relative">
          <img
            :src="shop.avatar_url || 'https://via.placeholder.com/100'"
            alt="Avatar"
            class="w-24 h-24 rounded-full object-cover"
          />
          <div v-if="editingField !== 'avatar'" class="absolute bottom-0 right-0">
            <button
              @click="startEditing('avatar')"
              class="bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700"
            >
              Sửa
            </button>
          </div>
          <div v-else class="absolute bottom-0 right-0 flex space-x-2">
            <input
              type="file"
              @change="editData.avatar = $event.target.files[0]"
              accept="image/*"
              class="text-sm"
            />
            <button
              @click="saveEdit('avatar')"
              class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700"
            >
              Lưu
            </button>
            <button
              @click="cancelEdit"
              class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700"
            >
              Hủy
            </button>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <div v-if="editingField !== 'name'">
            <h3 class="text-xl font-semibold">{{ shop.name }}</h3>
            <button
              @click="startEditing('name')"
              class="text-blue-600 hover:underline text-sm"
            >
              Sửa tên shop
            </button>
          </div>
          <div v-else class="flex items-center space-x-2">
            <input
              v-model="editData.shop_name"
              type="text"
              class="border rounded px-2 py-1"
            />
            <button
              @click="saveEdit('name')"
              class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700"
            >
              Lưu
            </button>
            <button
              @click="cancelEdit"
              class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700"
            >
              Hủy
            </button>
          </div>
        </div>
      </div>

      <!-- Shop Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <p><strong>Ngày tham gia:</strong> {{ formatDate(shop.created_at) }}</p>
          <p><strong>Đánh giá trung bình:</strong> {{ shop.average_rating }} / 5</p>
          <p><strong>Số sản phẩm:</strong> {{ shop.product_count }}</p>
        </div>
        <div>
          <div v-if="editingField !== 'address'" class="space-y-2">
            <p>
              <strong>Địa chỉ:</strong> {{ shop.address.pickup_address }}, {{ shop.address.ward }},
              {{ shop.address.district }}, {{ shop.address.city }}
            </p>
            <button
              @click="startEditing('address')"
              class="text-blue-600 hover:underline text-sm"
            >
              Sửa địa chỉ
            </button>
          </div>
          <div v-else class="space-y-2">
            <div>
              <label class="block text-sm">Địa chỉ</label>
              <input
                v-model="editData.pickup_address"
                type="text"
                class="w-full border rounded px-2 py-1"
              />
            </div>
            <div>
              <label class="block text-sm">Phường/Xã</label>
              <input
                v-model="editData.ward"
                type="text"
                class="w-full border rounded px-2 py-1"
              />
            </div>
            <div>
              <label class="block text-sm">Quận/Huyện</label>
              <input
                v-model="editData.district"
                type="text"
                class="w-full border rounded px-2 py-1"
              />
            </div>
            <div>
              <label class="block text-sm">Tỉnh/Thành phố</label>
              <input
                v-model="editData.city"
                type="text"
                class="w-full border rounded px-2 py-1"
              />
            </div>
            <div class="flex space-x-2">
              <button
                @click="saveEdit('address')"
                class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700"
              >
                Lưu
              </button>
              <button
                @click="cancelEdit"
                class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700"
              >
                Hủy
              </button>
            </div>
          </div>
          <div v-if="editingField !== 'phone_number'" class="mt-2">
            <p><strong>Điện thoại:</strong> {{ shop.phone_number }}</p>
            <button
              @click="startEditing('phone_number')"
              class="text-blue-600 hover:underline text-sm"
            >
              Sửa số điện thoại
            </button>
          </div>
          <div v-else class="mt-2 flex items-center space-x-2">
            <input
              v-model="editData.phone_number"
              type="text"
              class="border rounded px-2 py-1"
            />
            <button
              @click="saveEdit('phone_number')"
              class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700"
            >
              Lưu
            </button>
            <button
              @click="cancelEdit"
              class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700"
            >
              Hủy
            </button>
          </div>
        </div>
      </div>

      <!-- Public Shop Link -->
      <a
        :href="`/shop/${shop.id}`"
        target="_blank"
        class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
      >
        Xem trang shop công khai
      </a>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SellerShopProfile',
  data() {
    return {
      shop: null,
      errorMessage: '',
      editingField: null,
      editData: {},
      originalData: {},
    };
  },
  async mounted() {
    await this.fetchProfile();
  },
  methods: {
    async fetchProfile() {
      const token = localStorage.getItem('token');
      try {
        if (!token) throw new Error('Không tìm thấy token');
        const response = await axios.get('/api/seller/shop/profile', {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (response.data.success) {
          this.shop = response.data.data;
          this.errorMessage = '';
        } else {
          throw new Error(response.data.message);
        }
      } catch (error) {
        this.errorMessage = error.response?.status === 401
          ? 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.'
          : 'Không thể tải hồ sơ shop';
        if (error.response?.status === 401) {
          this.$router.push('/seller/login');
        }
      }
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      });
    },
    startEditing(field) {
      this.editingField = field;
      this.originalData = { ...this.editData };
      if (field === 'name') {
        this.editData = { shop_name: this.shop.name };
      } else if (field === 'address') {
        this.editData = { ...this.shop.address };
      } else if (field === 'phone_number') {
        this.editData = { phone_number: this.shop.phone_number };
      } else if (field === 'avatar' || field === 'cover_image') {
        this.editData = {};
      }
    },
    async saveEdit(field) {
      const token = localStorage.getItem('token');
      try {
        const formData = new FormData();
        if (field === 'avatar' && this.editData.avatar) {
          formData.append('avatar', this.editData.avatar);
        } else if (field === 'cover_image' && this.editData.cover_image) {
          formData.append('cover_image', this.editData.cover_image);
        } else {
          for (const [key, value] of Object.entries(this.editData)) {
            formData.append(key, value);
          }
        }
        await axios.post('/api/seller/shop/profile', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        await this.fetchProfile();
        this.editingField = null;
        this.editData = {};
      } catch (error) {
        this.errorMessage = 'Lỗi khi cập nhật hồ sơ shop';
      }
    },
    cancelEdit() {
      this.editingField = null;
      this.editData = {};
    },
  },
};
</script>