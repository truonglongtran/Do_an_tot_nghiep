<template>
  <div class="p-4">
    <h2 class="text-2xl font-bold mb-4">Danh sách cửa hàng</h2>
    <table class="table-auto w-full border">
      <thead class="bg-gray-200">
        <tr>
          <th class="p-2 border">ID</th>
          <th class="p-2 border">Tên cửa hàng</th>
          <th class="p-2 border">Chủ sở hữu (email)</th>
          <th class="p-2 border">Địa chỉ</th>
          <th class="p-2 border">Trạng thái</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="shop in shops" :key="shop.id" class="hover:bg-gray-100">
          <td class="p-2 border">{{ shop.id }}</td>
          <td class="p-2 border">{{ shop.shop_name }}</td>
          <td class="p-2 border">{{ shop.owner?.email }}</td>
          <td class="p-2 border">
            {{ [shop.pickup_address, shop.ward, shop.district, shop.city].filter(Boolean).join(', ') }}
          </td>
          <td class="p-2 border">{{ shop.status }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminShops',
  data() {
    return {
      shops: [],
    };
  },
  async mounted() {
    const token = localStorage.getItem('token');
    try {
      const response = await axios.get('http://localhost:8000/api/admin/shops', {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      this.shops = response.data;
    } catch (error) {
      console.error('Lỗi khi tải danh sách shops:', error);
    }
  },
};
</script>
