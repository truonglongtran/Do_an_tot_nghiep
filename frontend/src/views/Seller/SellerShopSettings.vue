<template>
  <div class="container">
    <h2>Thiết lập shop</h2>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
    <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
    <div v-if="settings" class="card">
      <!-- Contact Info -->
      <div>
        <h3>Thông tin liên hệ</h3>
        <div class="contact-container">
          <p><strong>Email:</strong> {{ settings.contact.email }}</p>
          <div v-if="editingField !== 'contact'">
            <p><strong>Số điện thoại:</strong> {{ settings.contact.phone_number }}</p>
            <button @click="startEditing('contact')" class="btn btn-edit">Sửa số điện thoại</button>
          </div>
          <div v-else class="form-group">
            <label>Số điện thoại</label>
            <input v-model="editData.phone_number" type="text" />
            <div class="edit-controls">
              <button @click="saveEdit('contact')" :disabled="loading" class="btn btn-save">
                <span v-if="loading" class="spinner"></span>
                Lưu
              </button>
              <button @click="cancelEdit" class="btn btn-cancel">Hủy</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Address -->
      <div>
        <h3>Địa chỉ nhận hàng / đổi trả</h3>
        <div v-if="editingField !== 'address'" class="address-container">
          <p>
            {{ settings.address.pickup_address }}, {{ settings.address.ward }},
            {{ settings.address.district }}, {{ settings.address.city }}
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
          <div class="edit-controls">
            <button @click="saveEdit('address')" :disabled="loading" class="btn btn-save">
              <span v-if="loading" class="spinner"></span>
              Lưu
            </button>
            <button @click="cancelEdit" class="btn btn-cancel">Hủy</button>
          </div>
        </div>
      </div>

      <!-- Shipping Settings -->
      <div>
        <h3 class="toggle-header" @click="toggleShipping">
          Cài đặt vận chuyển
          <span>{{ showShipping ? '▲' : '▼' }}</span>
        </h3>
        <div v-if="showShipping" class="shipping-container">
          <div v-if="loading" class="text-center">Đang tải...</div>
          <div v-else-if="settings.shipping.partners.length === 0" class="text-center text-gray-500">
            Không tìm thấy đơn vị vận chuyển nào.
          </div>
          <div v-else class="space-y-4">
            <div v-for="partner in settings.shipping.partners" :key="partner.id" class="partner-card">
              <div>
                <h4 class="text-lg font-semibold">{{ partner.name }}</h4>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  :checked="partner.is_active"
                  @change="updateShippingPartners"
                  class="sr-only peer"
                />
                <div
                  class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"
                ></div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Security -->
      <div>
        <h3>Bảo mật</h3>
        <div v-if="editingField !== 'password'">
          <button @click="startEditing('password')" class="btn btn-edit">Đổi mật khẩu</button>
        </div>
        <div v-else class="form-grid">
          <div class="form-group">
            <label>Mật khẩu mới</label>
            <input v-model="editData.password" type="password" />
          </div>
          <div class="form-group">
            <label>Xác nhận mật khẩu</label>
            <input v-model="editData.password_confirmation" type="password" />
          </div>
          <div class="edit-controls">
            <button @click="saveEdit('password')" :disabled="loading" class="btn btn-save">
              <span v-if="loading" class="spinner"></span>
              Lưu
            </button>
            <button @click="cancelEdit" class="btn btn-cancel">Hủy</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SellerShopSettings',
  data() {
    return {
      settings: null,
      errorMessage: '',
      successMessage: '',
      editingField: null,
      editData: {},
      originalData: {},
      showShipping: false,
      loading: false,
    };
  },
  async mounted() {
    await this.fetchSettings();
  },
  methods: {
    async fetchSettings() {
      const token = localStorage.getItem('token');
      try {
        if (!token) throw new Error('Không tìm thấy token');
        this.loading = true;
        const response = await axios.get('/seller/shop/settings', {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (response.data.success) {
          this.settings = response.data.data;
          this.errorMessage = '';
        } else {
          throw new Error(response.data.message);
        }
      } catch (error) {
        this.errorMessage = error.response?.status === 401
          ? 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.'
          : 'Không thể tải thiết lập shop';
        if (error.response?.status === 401) {
          window.location.href = '/seller/login';
        }
      } finally {
        this.loading = false;
      }
    },
    toggleShipping() {
      this.showShipping = !this.showShipping;
    },
    async updateShippingPartners() {
      const token = localStorage.getItem('token');
      try {
        this.errorMessage = '';
        this.successMessage = '';
        this.loading = true;
        const activePartners = this.settings.shipping.partners
          .filter(p => p.is_active)
          .map(p => p.id);
        await axios.post('/seller/shop/settings', {
          shipping_partners: activePartners,
        }, {
          headers: { Authorization: `Bearer ${token}` },
        });
        await this.fetchSettings();
        this.successMessage = 'Cập nhật đơn vị vận chuyển thành công';
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Lỗi khi cập nhật đơn vị vận chuyển';
      } finally {
        this.loading = false;
      }
    },
    startEditing(field) {
      this.editingField = field;
      this.originalData = { ...this.editData };
      if (field === 'contact') {
        this.editData = { phone_number: this.settings.contact.phone_number };
      } else if (field === 'address') {
        this.editData = { ...this.settings.address };
      } else if (field === 'password') {
        this.editData = { password: '', password_confirmation: '' };
      }
    },
    async saveEdit(field) {
      const token = localStorage.getItem('token');
      try {
        this.errorMessage = '';
        this.successMessage = '';
        this.loading = true;
        const formData = new FormData();
        for (const [key, value] of Object.entries(this.editData)) {
          formData.append(key, value);
        }
        await axios.post('/seller/shop/settings', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        await this.fetchSettings();
        this.editingField = null;
        this.editData = {};
        this.successMessage = 'Cập nhật thiết lập shop thành công';
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Lỗi khi cập nhật thiết lập shop';
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
  margin-bottom: 15px;
}

h4 {
  font-size: 16px;
  font-weight: 600;
}

.toggle-header {
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
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

.contact-container, .address-container, .shipping-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
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

.form-group input {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.form-group input:focus {
  border-color: #0288d1;
  outline: none;
}

.edit-controls {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.btn {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  border: none;
  transition: background-color 0.3s;
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

.partner-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.text-center {
  text-align: center;
  padding: 20px;
}

.text-gray-500 {
  color: #6b7280;
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
  .form-grid {
    grid-template-columns: 1fr;
  }
  .partner-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
}
</style>