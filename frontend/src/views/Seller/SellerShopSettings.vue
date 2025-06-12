<template>
  <div class="p-8 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Thiết lập shop</h2>
    <p v-if="errorMessage" class="text-red-500">{{ errorMessage }}</p>
    <div v-if="settings" class="bg-white shadow-sm rounded-lg p-6 space-y-6">
      <!-- Contact Info -->
      <div>
        <h3 class="text-lg font-semibold">Thông tin liên hệ</h3>
        <div class="space-y-2">
          <p><strong>Email:</strong> {{ settings.contact.email }}</p>
          <div class="flex items-center space-x-2">
            <div v-if="editingField !== 'contact'">
              <p><strong>Số điện thoại:</strong> {{ settings.contact.phone_number }}</p>
              <button
                @click="startEditing('contact')"
                class="text-blue-600 hover:underline text-sm"
              >
                Sửa số điện thoại
              </button>
            </div>
            <div v-else class="flex items-center space-x-2">
              <input
                v-model="editData.phone_number"
                type="text"
                class="border rounded px-2 py-1"
              />
              <button
                @click="saveEdit('contact')"
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
      </div>

      <!-- Address -->
      <div>
        <h3 class="text-lg font-semibold">Địa chỉ nhận hàng / đổi trả</h3>
        <div v-if="editingField !== 'address'" class="space-y-2">
          <p>
            {{ settings.address.pickup_address }}, {{ settings.address.ward }},
            {{ settings.address.district }}, {{ settings.address.city }}
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
      </div>

      <!-- Shipping Settings -->
      <div>
        <h3 class="text-lg font-semibold cursor-pointer" @click="toggleShipping">
          Cài đặt vận chuyển
          <span>{{ showShipping ? '▲' : '▼' }}</span>
        </h3>
        <div v-if="showShipping" class="mt-2 space-y-2">
          <div v-for="partner in settings.shipping.partners" :key="partner.id" class="flex items-center">
            <input
              type="checkbox"
              v-model="partner.is_active"
              @change="updateShippingPartners"
              class="mr-2"
            />
            <span>{{ partner.name }}</span>
          </div>
        </div>
      </div>

      <!-- Security -->
      <div>
        <h3 class="text-lg font-semibold">Bảo mật</h3>
        <div v-if="editingField !== 'password'" class="mt-2">
          <button
            @click="startEditing('password')"
            class="text-blue-600 hover:underline text-sm"
          >
            Đổi mật khẩu
          </button>
        </div>
        <div v-else class="space-y-2">
          <div>
            <label class="block text-sm">Mật khẩu mới</label>
            <input
              v-model="editData.password"
              type="password"
              class="w-full border rounded px-2 py-1"
            />
          </div>
          <div>
            <label class="block text-sm">Xác nhận mật khẩu</label>
            <input
              v-model="editData.password_confirmation"
              type="password"
              class="w-full border rounded px-2 py-1"
            />
          </div>
          <div class="flex space-x-2">
            <button
              @click="saveEdit('password')"
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
      editingField: null,
      editData: {},
      originalData: {},
      showShipping: false,
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
        const response = await axios.get('/api/seller/shop/settings', {
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
          this.$router.push('/seller/login');
        }
      }
    },
    toggleShipping() {
      this.showShipping = !this.showShipping;
    },
    async updateShippingPartners() {
      const token = localStorage.getItem('token');
      try {
        const activePartners = this.settings.shipping.partners
          .filter(p => p.is_active)
          .map(p => p.id);
        await axios.post('/api/seller/shop/settings', {
          shipping_partners: activePartners,
        }, {
          headers: { Authorization: `Bearer ${token}` },
        });
        await this.fetchSettings();
      } catch (error) {
        this.errorMessage = 'Lỗi khi cập nhật đơn vị vận chuyển';
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
        const formData = new FormData();
        for (const [key, value] of Object.entries(this.editData)) {
          formData.append(key, value);
        }
        await axios.post('/api/seller/shop/settings', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        await this.fetchSettings();
        this.editingField = null;
        this.editData = {};
      } catch (error) {
        this.errorMessage = 'Lỗi khi cập nhật thiết lập shop';
      }
    },
    cancelEdit() {
      this.editingField = null;
      this.editData = {};
    },
  },
};
</script>