<!-- src/views/Buyer/Addresses.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Địa chỉ giao hàng</h1>
    <div class="mb-4">
      <h2 class="text-xl font-semibold mb-2">Thêm địa chỉ mới</h2>
      <form @submit.prevent="addAddress" class="space-y-2">
        <input v-model="newAddress.recipient_name" placeholder="Tên người nhận" class="border rounded-lg p-2 w-full" required />
        <input v-model="newAddress.phone_number" placeholder="Số điện thoại" class="border rounded-lg p-2 w-full" required />
        <input v-model="newAddress.address_line" placeholder="Địa chỉ" class="border rounded-lg p-2 w-full" required />
        <input v-model="newAddress.ward" placeholder="Phường/Xã" class="border rounded-lg p-2 w-full" required />
        <input v-model="newAddress.district" placeholder="Quận/Huyện" class="border rounded-lg p-2 w-full" required />
        <input v-model="newAddress.city" placeholder="Tỉnh/Thành phố" class="border rounded-lg p-2 w-full" required />
        <label class="flex items-center">
          <input v-model="newAddress.is_default" type="checkbox" class="mr-2" />
          Đặt làm mặc định
        </label>
        <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded-lg">Thêm</button>
      </form>
    </div>
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
          <button @click="setDefault(address.id)" :disabled="address.is_default" class="text-orange-500 hover:underline">
            Đặt mặc định
          </button>
          <button @click="deleteAddress(address.id)" :disabled="address.is_default" class="text-red-500 hover:underline">
            Xóa
          </button>
        </div>
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
    };
  },
  async created() {
    await this.fetchAddresses();
  },
  methods: {
    async fetchAddresses() {
      try {
        const response = await axios.get('/api/buyer/addresses');
        this.addresses = response.data.addresses;
      } catch (error) {
        console.error('Error fetching addresses:', error);
      }
    },
    async addAddress() {
      try {
        await axios.post('/api/buyer/addresses', this.newAddress);
        this.newAddress = { recipient_name: '', phone_number: '', address_line: '', ward: '', district: '', city: '', is_default: false };
        await this.fetchAddresses();
      } catch (error) {
        console.error('Error adding address:', error);
      }
    },
    async setDefault(id) {
      try {
        await axios.put(`/api/buyer/addresses/${id}/set-default`);
        await this.fetchAddresses();
      } catch (error) {
        console.error('Error setting default address:', error);
      }
    },
    async deleteAddress(id) {
      try {
        await axios.delete(`/api/buyer/addresses/${id}`);
        await this.fetchAddresses();
      } catch (error) {
        console.error('Error deleting address:', error);
      }
    },
  },
};
</script>

<style scoped>
/* Không cần style vì Tailwind CSS đã xử lý */
</style>