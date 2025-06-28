<template>
  <div class="container">
    <h2>Hồ sơ shop</h2>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
    <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
    <div v-if="shop" class="card">
      <!-- Cover Image -->
      <div class="relative">
        <img
          :src="getImageUrl(shop.cover_image_url)"
          alt="Cover"
          class="cover-image"
          @error="handleImageError"
        />
        <div v-if="editingField !== 'cover_image'" class="edit-button">
          <button @click="startEditing('cover_image')" class="btn btn-edit">Sửa</button>
        </div>
        <div v-else class="edit-controls">
          <input
            type="file"
            @change="editData.cover_image = $event.target.files[0]"
            accept="image/*"
            class="file-input"
          />
          <div class="button-group">
            <button @click="saveEdit('cover_image')" :disabled="loading" class="btn btn-save">
              <span v-if="loading" class="spinner"></span>
              Lưu
            </button>
            <button @click="cancelEdit" class="btn btn-cancel">Hủy</button>
          </div>
        </div>
      </div>

      <!-- Avatar and Name -->
      <div class="profile-header">
        <!-- Avatar -->
        <div class="avatar-container">
          <img
            :src="getImageUrl(shop.avatar_url)"
            alt="Avatar"
            class="avatar"
            @error="handleImageError"
          />
          <div v-if="editingField !== 'avatar'" class="edit-button avatar-edit">
            <button @click="startEditing('avatar')" class="btn btn-edit">Sửa</button>
          </div>
          <div v-else class="edit-controls avatar-edit">
            <input
              type="file"
              @change="editData.avatar = $event.target.files[0]"
              accept="image/*"
              class="file-input"
            />
            <div class="button-group">
              <button @click="saveEdit('avatar')" :disabled="loading" class="btn btn-save">
                <span v-if="loading" class="spinner"></span>
                Lưu
              </button>
              <button @click="cancelEdit" class="btn btn-cancel">Hủy</button>
            </div>
          </div>
        </div>
        <!-- Name -->
        <div class="name-container">
          <div v-if="editingField !== 'name'" class="name-display">
            <h3>{{ shop.name }}</h3>
            <button @click="startEditing('name')" class="btn btn-edit">Sửa tên shop</button>
          </div>
          <div v-else class="name-edit">
            <input v-model="editData.shop_name" type="text" />
            <div class="button-group">
              <button @click="saveEdit('name')" :disabled="loading" class="btn btn-save">
                <span v-if="loading" class="spinner"></span>
                Lưu
              </button>
              <button @click="cancelEdit" class="btn btn-cancel">Hủy</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Shop Info -->
      <div class="info-grid">
        <div>
          <p><strong>Ngày tham gia:</strong> {{ formatDate(shop.created_at) }}</p>
          <p><strong>Đánh giá trung bình:</strong> {{ shop.average_rating }} / 5</p>
          <p><strong>Số sản phẩm:</strong> {{ shop.product_count }}</p>
        </div>
        <div>
          <div v-if="editingField !== 'address'" class="address-container">
            <p>
              <strong>Địa chỉ:</strong> {{ shop.address.pickup_address }}, {{ shop.address.ward }},
              {{ shop.address.district }}, {{ shop.address.city }}
            </p>
            <button @click="startEditing('address')" class="btn btn-edit">Sửa địa chỉ</button>
          </div>
          <div v-else class="form-grid">
            <div class="form-group">
              <label>Địa chỉ</label>
              <input v-model="editData.pickup_address" type="text" />
            </div>
            <div class="form-group">
              <label>Phường/Xã</label>
              <input v-model="editData.ward" type="text" />
            </div>
            <div class="form-group">
              <label>Quận/Huyện</label>
              <input v-model="editData.district" type="text" />
            </div>
            <div class="form-group">
              <label>Tỉnh/Thành phố</label>
              <input v-model="editData.city" type="text" />
            </div>
            <div class="button-group">
              <button @click="saveEdit('address')" :disabled="loading" class="btn btn-save">
                <span v-if="loading" class="spinner"></span>
                Lưu
              </button>
              <button @click="cancelEdit" class="btn btn-cancel">Hủy</button>
            </div>
          </div>
          <div v-if="editingField !== 'phone_number'" class="phone-container">
            <p><strong>Điện thoại:</strong> {{ shop.phone_number }}</p>
            <button @click="startEditing('phone_number')" class="btn btn-edit">Sửa số điện thoại</button>
          </div>
          <div v-else class="phone-container">
            <input v-model="editData.phone_number" type="text" />
            <div class="button-group">
              <button @click="saveEdit('phone_number')" :disabled="loading" class="btn btn-save">
                <span v-if="loading" class="spinner"></span>
                Lưu
              </button>
              <button @click="cancelEdit" class="btn btn-cancel">Hủy</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Public Shop Link -->
      <a :href="`/shop/${shop.id}`" target="_blank" class="btn btn-primary">Xem trang shop công khai</a>
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
      successMessage: '',
      editingField: null,
      editData: {},
      originalData: {},
      loading: false,
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
        const response = await axios.get('/seller/shop/profile', {
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
          window.location.href = '/seller/login';
        }
      }
    },
    getImageUrl(imgUrl) {
      if (!imgUrl) {
        return 'https://via.placeholder.com/150?text=Ảnh+Không+Tìm+Thấy';
      }
      if (/^https?:\/\//.test(imgUrl)) {
        return `${imgUrl}?t=${new Date().getTime()}`;
      }
      const baseUrl = import.meta.env.VITE_STORAGE_BASE_URL || 'http://localhost:8000/storage';
      const cleanImgUrl = imgUrl.replace(/^\/?(storage\/)?/, '');
      return `${baseUrl}/${cleanImgUrl}?t=${new Date().getTime()}`;
    },
    handleImageError(event) {
      event.target.src = 'https://via.placeholder.com/150?text=Ảnh+Không+Tìm+Thấy';
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
        this.errorMessage = '';
        this.successMessage = '';
        this.loading = true;
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
        const response = await axios.post('/seller/shop/profile', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        await this.fetchProfile();
        this.editingField = null;
        this.editData = {};
        this.successMessage = 'Cập nhật hồ sơ shop thành công';
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Lỗi khi cập nhật hồ sơ shop';
      } finally {
        this.loading = false;
      }
    },
    cancelEdit() {
      this.editingField = null;
      this.editData = {};
    },
  },
};
</script>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

h2 {
  font-size: 24px;
  font-weight: bold;
  color: #333;
  margin-bottom: 20px;
}

h3 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 10px;
}

.card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 20px;
}

.error-message {
  background: #ffe6e6;
  color: #d32f2f;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.success-message {
  background: #e6ffe6;
  color: #2e7d32;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.cover-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
}

.profile-header {
  display: flex;
  align-items: flex-start;
  gap: 30px;
  margin: 30px 0;
}

.avatar-container {
  position: relative;
  flex-shrink: 0;
  width: 120px;
}

.avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 10px;
}

.edit-button {
  position: absolute;
  top: 10px;
  right: 10px;
}

.avatar-edit {
  position: static;
  margin-top: 10px;
}

.edit-controls {
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: flex-start;
}

.button-group {
  display: flex;
  gap: 10px;
}

.file-input {
  font-size: 14px;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  width: 100%;
}

.name-container {
  display: flex;
  flex-direction: column;
  gap: 15px;
  flex-grow: 1;
}

.name-display {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.name-edit {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.name-edit input {
  width: 100%;
  max-width: 300px;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.address-container, .phone-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 14px;
  font-weight: 500;
  color: #555;
  margin-bottom: 5px;
}

.form-group input,
.name-edit input,
.phone-container input {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.form-group input:focus,
.name-edit input:focus,
.phone-container input:focus {
  border-color: #0288d1;
  outline: none;
}

.btn {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  border: none;
  transition: background-color 0.3s;
}

.btn-primary {
  background: #0288d1;
  color: #fff;
}

.btn-primary:hover {
  background: #0277bd;
}

.btn-save {
  background: #2e7d32;
  color: #fff;
}

.btn-save:disabled {
  background: #a5d6a7;
  cursor: not-allowed;
}

.btn-save:hover {
  background: #1b5e20;
}

.btn-cancel {
  background: #757575;
  color: #fff;
}

.btn-cancel:hover {
  background: #616161;
}

.btn-edit {
  background: #0288d1;
  color: #fff;
}

.btn-edit:hover {
  background: #0277bd;
}

.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #fff;
  border-top: 2px solid transparent;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 8px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .info-grid {
    grid-template-columns: 1fr;
  }
  .profile-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 20px;
  }
  .avatar-container {
    width: 100%;
    max-width: 200px;
  }
  .name-edit input {
    max-width: 100%;
  }
}
</style>