<!-- src/views/Buyer/Addresses.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="loading" class="text-center">
      <svg class="animate-spin w-8 h-8 mx-auto text-orange-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
    </div>
    <div v-else-if="error" class="text-red-500 text-center mb-6">
      {{ error }}
    </div>
    <div v-else>
      <!-- Form thêm địa chỉ (hiển thị khi nhấn nút "Thêm địa chỉ") -->
      <div v-if="isAdding" class="mb-4">
        <h2 class="text-xl font-semibold mb-2">Thêm địa chỉ mới</h2>
        <form @submit.prevent="addAddress" class="space-y-2">
          <input
            v-model="newAddress.recipient_name"
            placeholder="Tên người nhận"
            class="border rounded-lg p-2 w-full"
            required
          />
          <input
            v-model="newAddress.phone_number"
            placeholder="Số điện thoại"
            class="border rounded-lg p-2 w-full"
            required
          />
          <input
            v-model="newAddress.address_line"
            placeholder="Địa chỉ"
            class="border rounded-lg p-2 w-full"
            required
          />
          <input
            v-model="newAddress.ward"
            placeholder="Phường/Xã"
            class="border rounded-lg p-2 w-full"
            required
          />
          <input
            v-model="newAddress.district"
            placeholder="Quận/Huyện"
            class="border rounded-lg p-2 w-full"
            required
          />
          <input
            v-model="newAddress.city"
            placeholder="Tỉnh/Thành phố"
            class="border rounded-lg p-2 w-full"
            required
          />
          <label class="flex items-center">
            <input v-model="newAddress.is_default" type="checkbox" class="mr-2" />
            Đặt làm mặc định
          </label>
          <div class="flex space-x-2">
            <button
              type="submit"
              :disabled="saving"
              class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 disabled:bg-gray-300"
            >
              {{ saving ? 'Đang thêm...' : 'Thêm' }}
            </button>
            <button
              type="button"
              @click="cancelAdd"
              class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400"
            >
              Hủy
            </button>
          </div>
        </form>
      </div>
      <!-- Danh sách địa chỉ -->
      <div v-if="addresses.length === 0" class="text-center text-gray-600">
        Không có địa chỉ
      </div>
      <div v-else class="space-y-4">
        <div v-for="address in addresses" :key="address.id" class="border rounded-lg p-4 flex justify-between items-center">
          <div>
            <p class="font-semibold">{{ address.recipient_name }}</p>
            <p class="text-gray-600">{{ address.phone_number }}</p>
            <p class="text-gray-600">{{ address.address_line }}, {{ address.ward }}, {{ address.district }}, {{ address.city }}</p>
            <p v-if="address.is_default" class="text-orange-500 font-semibold">Mặc định</p>
          </div>
          <div class="space-x-2">
            <button
              @click="setDefault(address.id)"
              :disabled="address.is_default"
              class="text-orange-500 hover:underline"
            >
              Đặt mặc định
            </button>
            <button
              @click="deleteAddress(address.id)"
              :disabled="address.is_default"
              class="text-red-500 hover:underline"
            >
              Xóa
            </button>
          </div>
        </div>
      </div>
      <!-- Nút thêm địa chỉ -->
      <div class="mt-4">
        <button
          v-if="!isAdding"
          @click="isAdding = true"
          class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600"
        >
          Thêm địa chỉ
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Addresses',
  data() {
    return {
      addresses: [],
      newAddress: {
        recipient_name: '',
        phone_number: '',
        address_line: '',
        ward: '',
        district: '',
        city: '',
        is_default: false,
      },
      loading: true,
      saving: false,
      error: null,
      isAdding: false,
    };
  },
  async created() {
    await this.fetchAddresses();
  },
  methods: {
    async fetchAddresses() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/buyer/addresses', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.addresses = response.data.addresses || [];
      } catch (error) {
        this.error = error.response?.data?.message || 'Lỗi tải danh sách địa chỉ.';
        console.error('Error fetching addresses:', error.response?.data || error);
      } finally {
        this.loading = false;
      }
    },
    async addAddress() {
      this.saving = true;
      this.error = null;
      try {
        await axios.post('/buyer/addresses', this.newAddress, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.newAddress = {
          recipient_name: '',
          phone_number: '',
          address_line: '',
          ward: '',
          district: '',
          city: '',
          is_default: false,
        };
        this.isAdding = false;
        await this.fetchAddresses();
        alert('Thêm địa chỉ thành công!');
      } catch (error) {
        this.error = error.response?.data?.message || 'Lỗi khi thêm địa chỉ.';
        console.error('Error adding address:', error.response?.data || error);
      } finally {
        this.saving = false;
      }
    },
    async setDefault(id) {
      try {
        await axios.put(`/buyer/addresses/${id}/set-default`, null, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        await this.fetchAddresses();
        alert('Đặt địa chỉ mặc định thành công!');
      } catch (error) {
        this.error = error.response?.data?.message || 'Lỗi khi đặt địa chỉ mặc định.';
        console.error('Error setting default address:', error.response?.data || error);
      }
    },
    async deleteAddress(id) {
      try {
        await axios.delete(`/buyer/addresses/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        await this.fetchAddresses();
        alert('Xóa địa chỉ thành công!');
      } catch (error) {
        this.error = error.response?.data?.message || 'Lỗi khi xóa địa chỉ.';
        console.error('Error deleting address:', error.response?.data || error);
      }
    },
    cancelAdd() {
      this.newAddress = {
        recipient_name: '',
        phone_number: '',
        address_line: '',
        ward: '',
        district: '',
        city: '',
        is_default: false,
      };
      this.isAdding = false;
      this.error = null;
    },
  },
};
</script>

<style scoped>
/* Tailwind handles most styling */
</style>