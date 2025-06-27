<!-- src/views/Buyer/OrderTracking.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-6">Theo dõi đơn hàng</h1>
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
      <!-- Pending Orders -->
      <div>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Đơn hàng chờ xác nhận</h2>
        <div v-if="pendingOrders.length === 0" class="text-center text-gray-600">
          Không có đơn hàng nào đang chờ xác nhận
        </div>
        <div v-else class="space-y-4">
          <div v-for="order of pendingOrders" :key="order.id" class="border rounded-lg p-4">
            <p class="font-semibold">Mã đơn: {{ order.id }}</p>
            <p class="text-gray-600">Tổng tiền: {{ formatPrice(order.total) }}</p>
            <p class="text-gray-600">Ngày đặt: {{ formatDate(order.created_at) }}</p>
            <router-link :to="{ path: '/buyer/orders', query: { id: order.id }}" class="text-orange-500 hover:underline">
              Xem chi tiết
            </router-link>
          </div>
        </div>
      </div>

      <!-- In Delivery Orders -->
      <div>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Đơn hàng đang giao</h2>
        <div v-if="inDeliveryOrders.length === 0" class="text-center text-gray-600">
          Không có đơn hàng nào đang giao
        </div>
        <div v-else class="space-y-4">
          <div v-for="order in inDeliveryOrders" :key="order.id" class="border rounded-lg p-4">
            <p class="font-semibold">Mã đơn: {{ order.id }}</p>
            <p class="text-gray-600">Tổng tiền: {{ formatPrice(order.total) }}</p>
            <p class="text-gray-600">Ngày đặt: {{ formatDate(order.created_at) }}</p>
            <p class="text-gray-600">Trạng thái: {{ order.shipping_status === 'processing' ? 'Đang xử lý' : 'Đang giao' }}</p>
            <router-link :to="{ path: '/buyer/orders', query: { id: order.id }}" class="text-orange-500 hover:underline">
              Xem chi tiết
            </router-link>
          </div>
        </div>
      </div>

      <!-- Completed Orders -->
      <div>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Đơn hàng hoàn thành</h2>
        <div v-if="completedOrders.length === 0" class="text-center text-gray-600">
          Không có đơn hàng nào hoàn thành
        </div>
        <div v-else class="space-y-4">
          <div v-for="order in completedOrders" :key="order.id" class="border rounded-lg p-4">
            <p class="font-semibold">Mã đơn: {{ order.id }}</p>
            <p class="text-gray-600">Tổng tiền: {{ formatPrice(order.total) }}</p>
            <p class="text-gray-600">Ngày đặt: {{ formatDate(order.created_at) }}</p>
            <div class="mt-2">
              <p class="text-gray-600 font-semibold">Sản phẩm:</p>
              <div v-for="item in order.items" :key="item.id" class="flex items-center space-x-2 mt-1">
                <img
                  :src="item.product_variant?.image_url || item.product?.image_url || 'https://via.placeholder.com/50'"
                  :alt="item.product?.name || 'Sản phẩm'"
                  class="w-12 h-12 object-cover rounded"
                />
                <div class="flex-1">
                  <p class="text-gray-600">{{ item.product?.name || 'Sản phẩm' }}</p>
                  <p class="text-orange-500">{{ formatPrice(item.product_variant?.price || item.product?.price || 0) }} x {{ item.quantity }}</p>
                </div>
                <router-link
                  :to="{ path: '/buyer/reviews/create', query: { order_id: order.id, product_id: item.product_id, variant_id: item.product_variant_id }}"
                  class="text-orange-500 hover:underline"
                >
                  Đánh giá
                </router-link>
              </div>
            </div>
            <router-link :to="{ path: '/buyer/orders', query: { id: order.id }}" class="text-orange-500 hover:underline">
              Xem chi tiết
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'OrderTracking',
  data() {
    return {
      orders: [],
      loading: true,
      error: null,
    };
  },
  computed: {
    pendingOrders() {
      return this.orders.filter(order => order.order_status === 'pending');
    },
    inDeliveryOrders() {
      return this.orders.filter(order => ['processing', 'shipping'].includes(order.shipping_status));
    },
    completedOrders() {
      return this.orders.filter(order => order.shipping_status === 'delivered');
    },
  },
  async created() {
    await this.fetchOrders();
  },
  methods: {
    async fetchOrders() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/buyer/orders', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.orders = response.data.orders || [];
      } catch (error) {
        this.error = error.response?.data?.message || 'Lỗi tải đơn hàng.';
        console.error('Error fetching orders:', error.response?.data || error);
      } finally {
        this.loading = false;
      }
    },
    formatPrice(price) {
      return Number(price).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    },
    formatDate(date) {
      const d = new Date(date);
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      return `${day}/${month}/${year}`;
    },
  },
};
</script>

<style scoped>
/* Tailwind handles most styling */
</style>