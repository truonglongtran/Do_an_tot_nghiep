<template>
  <div class="container mx-auto">
    <h2 class="text-2xl font-bold mb-6">Quản lý đơn hàng</h2>
    <div class="bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-semibold mb-4">Danh sách đơn hàng</h3>
      <table class="w-full table-auto">
        <thead>
          <tr class="bg-gray-200">
            <th class="p-2">ID</th>
            <th class="p-2">Người mua</th>
            <th class="p-2">Tổng tiền</th>
            <th class="p-2">Trạng thái đơn</th>
            <th class="p-2">Trạng thái vận chuyển</th>
            <th class="p-2">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id" class="border-b">
            <td class="p-2">{{ order.id }}</td>
            <td class="p-2">{{ order.buyer ? order.buyer.email : 'N/A' }}</td>
            <td class="p-2">{{ formatCurrency(order.total_amount) }}</td>
            <td class="p-2">{{ order.order_status }}</td>
            <td class="p-2">
              <select
                v-model="order.shipping_status"
                @change="updateShippingStatus(order.id, $event.target.value)"
                class="border rounded p-1"
              >
                <option value="pending">Chờ xử lý</option>
                <option value="processing">Đang xử lý</option>
                <option value="shipping">Đang vận chuyển</option>
                <option value="delivered">Đã giao</option>
                <option value="failed">Thất bại</option>
                <option value="return">Hoàn trả</option>
              </select>
            </td>
            <td class="p-2">
              <router-link
                :to="`/seller/orders/${order.id}`"
                class="text-blue-500 hover:underline"
              >
                Xem chi tiết
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SellerOrders',
  data() {
    return {
      orders: [],
    };
  },
  methods: {
    async fetchOrders() {
      try {
        const token = localStorage.getItem('token');
        if (!token) throw new Error('No token found');

        const response = await axios.get('http://localhost:8000/api/seller/orders', {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
        });
        this.orders = response.data.data;
        console.log('Danh sách đơn hàng:', this.orders);
      } catch (error) {
        console.error('Lỗi khi tải đơn hàng:', error);
        alert('Không thể tải dữ liệu: ' + (error.response?.data?.message || 'Lỗi không xác định'));
      }
    },
    async updateShippingStatus(orderId, shippingStatus) {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.put(
          `http://localhost:8000/api/seller/orders/${orderId}/shipping-status`,
          { shipping_status: shippingStatus },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
            },
          }
        );
        alert('Cập nhật trạng thái vận chuyển thành công');
        console.log('Phản hồi cập nhật:', response.data);
      } catch (error) {
        console.error('Lỗi khi cập nhật trạng thái vận chuyển:', error);
        alert('Cập nhật thất bại: ' + (error.response?.data?.message || 'Lỗi không xác định'));
      }
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    },
  },
  mounted() {
    this.fetchOrders();
  },
};
</script>