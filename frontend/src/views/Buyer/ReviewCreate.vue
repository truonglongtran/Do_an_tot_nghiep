<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-6">Đánh giá sản phẩm</h1>
    <div v-if="loading" class="text-center">
      <svg class="animate-spin w-8 h-8 mx-auto text-orange-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
    </div>
    <div v-else-if="error" class="text-red-500 text-center mb-6 bg-red-100 p-4 rounded-lg">
      {{ error }}
    </div>
    <div v-else-if="!canReview" class="text-red-500 text-center mb-6 bg-red-100 p-4 rounded-lg">
      Sản phẩm này đã được đánh giá
    </div>
    <div v-else class="border rounded-lg p-6 bg-white shadow-sm">
      <div class="flex items-center space-x-4 mb-4">
        <img
          :src="product.image_url || 'https://via.placeholder.com/50'"
          :alt="product.name"
          class="w-16 h-16 object-cover rounded"
        />
        <div>
          <p class="font-semibold">{{ product.name }}</p>
        </div>
      </div>
      <form @submit.prevent="submitReview" class="space-y-4">
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
          <router-link
            to="/buyer/order-tracking"
            class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400"
          >
            Hủy
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ReviewCreate',
  data() {
    return {
      product: { name: '', image_url: '' },
      form: {
        rating: 5,
        comment: '',
        images: [],
      },
      loading: true,
      saving: false,
      error: null,
      canReview: false,
    };
  },
  async created() {
    await this.fetchProductInfo();
  },
  methods: {
    async fetchProductInfo() {
      this.loading = true;
      this.error = null;
      try {
        const { order_id, product_id } = this.$route.query;
        if (!order_id || !product_id) {
          throw new Error('Thiếu thông tin đơn hàng hoặc sản phẩm');
        }
        console.log('Fetching product info with:', { order_id, product_id });
        const response = await axios.get(`/buyer/orders/${order_id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        if (!response.data.success) {
          throw new Error(response.data.message || 'Lỗi tải thông tin đơn hàng');
        }
        const order = response.data.order;
        const item = order.items.find(i => i.product_id == product_id);
        if (!item) {
          throw new Error('Sản phẩm không tìm thấy trong đơn hàng');
        }
        this.canReview = !item.is_reviewed;
        this.product = item.product || { name: 'Sản phẩm', image_url: '' };
      } catch (error) {
        this.error = error.response?.data?.message || error.message || 'Lỗi tải thông tin sản phẩm';
        console.error('Error fetching product info:', {
          message: error.message,
          status: error.response?.status,
          data: error.response?.data,
        });
        if (error.response?.status === 401) {
          this.error = 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.';
          setTimeout(() => {
            localStorage.removeItem('token');
            localStorage.removeItem('role');
            localStorage.removeItem('loginType');
            this.$router.push('/buyer/login');
          }, 2000);
        }
      } finally {
        this.loading = false;
      }
    },
    handleImageChange(event) {
      const files = Array.from(event.target.files);
      this.form.images = files.map(file => ({
        file,
        preview: URL.createObjectURL(file),
      }));
    },
    async submitReview() {
      this.saving = true;
      this.error = null;
      try {
        const formData = new FormData();
        formData.append('order_id', this.$route.query.order_id);
        formData.append('product_id', this.$route.query.product_id);
        formData.append('rating', this.form.rating);
        formData.append('comment', this.form.comment);
        this.form.images.forEach((img, index) => {
          formData.append(`images[${index}]`, img.file);
        });

        console.log('Submitting review with data:', {
          order_id: this.$route.query.order_id,
          product_id: this.$route.query.product_id,
          rating: this.form.rating,
          comment: this.form.comment,
          images: this.form.images.map(img => img.file.name),
        });

        const response = await axios.post('/buyer/reviews', formData, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        if (response.data.success) {
          this.$router.push('/buyer/reviews');
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
  },
};
</script>

<style scoped>
/* Tailwind handles most styling */
</style>