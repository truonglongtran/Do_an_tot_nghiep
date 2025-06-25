<!-- src/views/Buyer/Orders.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Đơn hàng</h1>
    <div v-if="orders.length === 0" class="text-center text-gray-600">
      Không có đơn hàng
    </div>
    <div v-else class="space-y-4">
      <div v-for="order in orders" :key="order.id" class="border rounded-lg p-4">
        <p class="font-semibold">Đơn hàng #{{ order.id }}</p>
        <p class="text-gray-600">Trạng thái: {{ order.order_status }}</p>
        <p class="text-gray-600">Vận chuyển: {{ order.shipping_status }}</p>
        <p v-if="order.voucher" class="text-gray-600">Mã giảm giá: {{ order.voucher.code }}</p>
        <div class="mt-2">
          <p class="text-gray-600 font-semibold">Sản phẩm:</p>
          <div v-for="item in order.items" :key="item.id" class="flex items-center space-x-2 mt-1">
            <img
              :src="item.product_variant?.image_url || 'https://via.placeholder.com/50'"
              :alt="item.product.name"
              class="w-12 h-12 object-cover rounded"
            />
            <div>
              <p class="text-gray-600">{{ item.product.name }}</p>
              <p class="text-orange-500">{{ item.product_variant?.price }}đ x {{ item.quantity }}</p>
            </div>
          </div>
        </div>
        <p class="text-gray-500 text-sm mt-2">{{ formatDate(order.created_at) }}</p>
        <router-link :to="'/orders/' + order.id" class="text-orange-500 hover:underline">
          Xem chi tiết
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import moment from 'moment';

export default {
  name: 'Orders',
  data() {
    return {
      orders: [],
    };
  },
  async created() {
    await this.fetchOrders();
  },
  methods: {
    async fetchOrders() {
      try {
        const response = await axios.get('/api/buyer/orders');
        this.orders = response.data.orders;
      } catch (error) {
        console.error('Error fetching orders:', error);
      }
    },
    formatDate(date) {
      return moment(date).format('DD/MM/YYYY HH:mm');
    },
  },
};
</script>

<style scoped>
/* Không cần style vì Tailwind CSS đã xử lý */
</style>