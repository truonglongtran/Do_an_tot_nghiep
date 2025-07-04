<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Đơn hàng</h1>
    <div v-if="loading" class="text-center">
      <svg class="animate-spin w-8 h-8 mx-auto text-orange-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
    </div>
    <div v-else-if="error" class="text-red-500 text-center mb-6">
      {{ error }}
    </div>
    <div v-else-if="filteredOrders.length === 0" class="text-center text-gray-600">
      Không có đơn hàng
    </div>
    <div v-else class="space-y-4">
      <div v-for="order in filteredOrders" :key="order.id" class="border rounded-lg p-4">
        <p class="font-semibold">Đơn hàng #{{ order.id }}</p>
        <p class="text-gray-600">Trạng thái: {{ order.order_status }}</p>
        <p class="text-gray-600">Vận chuyển: {{ order.shipping_status }}</p>
        <p class="text-gray-600">Tổng tiền: {{ formatPrice(order.total) }}</p>
        <p v-if="order.voucher" class="text-gray-600">Mã giảm giá: {{ order.voucher.code }}</p>
        <div class="mt-2">
          <p class="text-gray-600 font-semibold">Sản phẩm:</p>
          <div v-for="item in order.items" :key="item.id" class="flex items-center space-x-2 mt-1">
            <img
              :src="getImageUrl(item.product_variant?.image_url || item.product?.image_url || 'https://via.placeholder.com/50')"
              :alt="item.product?.name || 'Sản phẩm'"
              class="w-12 h-12 object-cover rounded"
              @error="handleImageError($event, item.product_variant?.image_url || item.product?.image_url, 'order_item_' + item.id)"
            />
            <div>
              <p class="text-gray-600">{{ item.product?.name || 'Sản phẩm' }}</p>
              <p class="text-orange-500">{{ formatPrice(item.product_variant?.price || item.product?.price || 0) }} x {{ item.quantity }}</p>
            </div>
          </div>
        </div>
        <p class="text-gray-500 text-sm mt-2">{{ formatDate(order.created_at) }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Orders',
  data() {
    return {
      orders: [],
      loading: true,
      error: null,
    };
  },
  computed: {
    filteredOrders() {
      const orderId = this.$route.query.id;
      if (orderId) {
        return this.orders.filter(order => order.id == orderId);
      }
      return this.orders;
    },
  },
  async created() {
    await this.fetchOrders();
  },
  methods: {
    getImageUrl(imgUrl) {
      if (!imgUrl) {
        console.warn('Không có đường dẫn ảnh, sử dụng ảnh placeholder');
        return 'https://via.placeholder.com/50?text=Ảnh+Không+Tìm+Thấy';
      }
      if (/^https?:\/\//.test(imgUrl)) {
        console.log('Sử dụng URL bên ngoài:', imgUrl);
        return `${imgUrl}?t=${new Date().getTime()}`;
      }
      const baseUrl = import.meta.env.VITE_STORAGE_BASE_URL || 'http://localhost:8000/storage';
      const cleanImgUrl = imgUrl.replace(/^\/?(storage\/)?/, '');
      const finalUrl = `${baseUrl}/${cleanImgUrl}?t=${new Date().getTime()}`;
      console.log('Đường dẫn ảnh đã tạo:', finalUrl);
      return finalUrl;
    },
    handleImageError(event, imgUrl, type) {
      console.error(`Lỗi tải ảnh ${type}:`, {
        img_url: imgUrl,
        attempted_url: event.target.src,
        storage_base_url: import.meta.env.VITE_STORAGE_BASE_URL,
      });
      event.target.src = 'https://via.placeholder.com/50?text=Ảnh+Không+Tìm+Thấy';
    },
    async fetchOrders() {
      this.loading = true;
      this.error = null;
      try {
        const orderId = this.$route.query.id;
        const endpoint = orderId ? `/buyer/orders/${orderId}` : '/buyer/orders';
        const response = await axios.get(endpoint, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.orders = orderId ? [response.data.order] : response.data.orders || [];
      } catch (error) {
        this.error = error.response?.data?.message || 'Lỗi tải đơn hàng.';
        console.error('Error fetching orders:', error.response?.data || error);
      } finally {
        this.loading = false;
      }
    },
    formatPrice(price) {
      return price != null
        ? Number(price).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' })
        : 'N/A';
    },
    formatDate(date) {
      const d = new Date(date);
      const formatter = new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
      });
      return formatter.format(d).replace(',', '');
    },
  },
};
</script>

<style scoped>
/* Tailwind handles most styling */
</style>