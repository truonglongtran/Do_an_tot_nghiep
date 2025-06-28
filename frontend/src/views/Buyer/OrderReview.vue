<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-6">Đánh giá đơn hàng {{ orderId }}</h1>
    <div v-if="loading" class="text-center">
      <svg class="animate-spin w-8 h-8 mx-auto text-orange-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
    </div>
    <div v-else-if="error" class="text-red-500 text-center mb-6 bg-red-100 p-4 rounded-lg">
      {{ error }}
    </div>
    <div v-else-if="!order.items.some(item => !item.is_reviewed)" class="text-green-500 text-center mb-6 bg-green-100 p-4 rounded-lg">
      Tất cả sản phẩm trong đơn hàng đã được đánh giá
    </div>
    <div v-else class="space-y-6">
      <div v-for="item in order.items" :key="item.id" class="border rounded-lg p-6 bg-white shadow-sm">
        <div class="flex items-center space-x-4 mb-4">
          <img
            :src="item.product?.image_url || 'https://via.placeholder.com/50'"
            :alt="item.product?.name || 'Sản phẩm'"
            class="w-16 h-16 object-cover rounded"
          />
          <div>
            <p class="font-semibold">{{ item.product?.name || 'Sản phẩm' }}</p>
            <p class="text-orange-500">{{ formatPrice(item.product_variant?.price || item.product?.price || 0) }} x {{ item.quantity }}</p>
          </div>
        </div>
        <div v-if="!item.is_reviewed" class="space-y-4">
          <button
            v-if="selectedItem !== item.id"
            @click="selectItem(item.id)"
            class="text-orange-500 hover:underline"
          >
            Đánh giá
          </button>
          <form v-if="selectedItem === item.id" @submit.prevent="submitReview(item)" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Đánh giá (1-5)</label>
              <select
                v-model="form.rating"
                class="mt-1 block w-full border rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500"
                required
              >
                <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Nhận xét</label>
              <textarea
                v-model="form.comment"
                placeholder="Nhận xét của bạn về sản phẩm..."
                class="mt-1 block w-full border rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500"
                rows="4"
                required
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Hình ảnh (tùy chọn)</label>
              <input
                type="file"
                accept="image/*"
                multiple
                @change="handleImageChange"
                class="mt-1 block w-full text-sm text-gray-500"
              />
              <div v-if="form.images.length" class="flex space-x-2 mt-2">
                <img v-for="(img, index) in form.images" :key="index" :src="img.preview" class="w-16 h-16 object-cover rounded" />
              </div>
            </div>
            <div class="flex space-x-2">
              <button
                type="submit"
                :disabled="saving"
                class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 disabled:bg-gray-300"
              >
                {{ saving ? 'Đang gửi...' : 'Gửi đánh giá' }}
              </button>
              <button
                type="button"
                @click="selectedItem = null"
                class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400"
              >
                Hủy
              </button>
            </div>
          </form>
        </div>
        <span v-else class="text-gray-500">Đã đánh giá</span>
      </div>
      <router-link
        to="/buyer/order-tracking"
        class="inline-block bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400"
      >
        Quay lại
      </router-link>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'OrderReview',
  data() {
    return {
      orderId: this.$route.params.order_id,
      order: { items: [], reviews: [] },
      selectedItem: null,
      form: {
        rating: 5,
        comment: '',
        images: [],
      },
      loading: true,
      saving: false,
      error: null,
    };
  },
  async created() {
    await this.fetchOrder();
  },
  methods: {
    async fetchOrder() {
      this.loading = true;
      this.error = null;
      try {
        if (!this.orderId || isNaN(this.orderId)) {
          throw new Error('ID đơn hàng không hợp lệ');
        }
        console.log('Fetching order with ID:', this.orderId);
        const response = await axios.get(`/buyer/orders/${this.orderId}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('API response:', response.data);
        if (!response.data.success) {
          throw new Error(response.data.message || 'Lỗi tải thông tin đơn hàng');
        }
        this.order = {
          ...response.data.order,
          items: response.data.order.items.map(item => ({
            ...item,
            is_reviewed: item.is_reviewed,
          })),
        };
      } catch (error) {
        this.error = error.response?.data?.message || error.message || 'Lỗi tải thông tin đơn hàng';
        console.error('Error fetching order:', {
          message: error.message,
          status: error.response?.status,
          data: error.response?.data,
          orderId: this.orderId,
        });
        if (error.response?.status === 401) {
          this.error = 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.';
          setTimeout(() => {
            localStorage.removeItem('token');
            localStorage.removeItem('role');
            localStorage.removeItem('loginType');
            this.$router.push('/buyer/login');
          }, 2000);
        } else if (error.response?.status === 404) {
          this.error = 'Đơn hàng không tồn tại';
        }
      } finally {
        this.loading = false;
      }
    },
    selectItem(itemId) {
      this.selectedItem = itemId;
      this.form = { rating: 5, comment: '', images: [] }; // Reset form
    },
    handleImageChange(event) {
      const files = Array.from(event.target.files);
      this.form.images = files.map(file => ({
        file,
        preview: URL.createObjectURL(file),
      }));
    },
    async submitReview(item) {
      this.saving = true;
      this.error = null;
      try {
        const formData = new FormData();
        formData.append('order_id', this.orderId);
        formData.append('product_id', item.product_id);
        formData.append('rating', this.form.rating);
        formData.append('comment', this.form.comment);
        this.form.images.forEach((img, index) => {
          formData.append(`images[${index}]`, img.file);
        });

        const response = await axios.post('/buyer/reviews', formData, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        console.log('Review submission response:', response.data);
        if (response.data.success) {
          this.selectedItem = null;
          await this.fetchOrder(); // Refresh order to update reviewed status
          alert('Gửi đánh giá thành công!');
        } else {
          throw new Error(response.data.message || 'Lỗi gửi đánh giá');
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Lỗi khi gửi đánh giá';
        console.error('Error submitting review:', {
          message: error.message,
          status: error.response?.status,
          data: error.response?.data,
        });
      } finally {
        this.saving = false;
      }
    },
    formatPrice(price) {
      return Number(price).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    },
  },
};
</script>

<style scoped>
/* Tailwind handles most styling */
</style>