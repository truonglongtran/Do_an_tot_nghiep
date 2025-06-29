<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="loading" class="text-center">
      <svg class="animate-spin w-8 h-8 mx-auto text-orange-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
    </div>
    <div v-else-if="error" class="text-red-500 text-center mb-4">
      {{ error }}
    </div>
    <div v-else-if="shop" class="space-y-4">
      <!-- Shop Info -->
      <div
        class="relative flex items-center space-x-4 p-6 rounded-xl bg-cover bg-center"
        :style="{
          backgroundImage: shop.cover_image_url
            ? `url(${getImageUrl(shop.cover_image_url)})`
            : 'linear-gradient(to right, #f97316, #ea580c)',
        }"
      >
        <div class="absolute inset-0 bg-black/40 rounded-xl"></div> <!-- Overlay for readability -->
        <div class="relative flex items-center space-x-4 z-10">
          <img
            :src="getImageUrl(shop.avatar_url)"
            :alt="shop.shop_name"
            class="w-16 h-16 rounded-full border-2 border-white"
            @error="handleImageError($event, shop.avatar_url, 'shop_avatar')"
          />
          <div>
            <h1 class="text-2xl font-bold text-white">{{ shop.shop_name }}</h1>
            <p class="text-gray-200">{{ shop.pickup_address }}</p>
          </div>
          <button
            v-if="shop.is_following"
            @click="unfollowShop"
            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors"
          >
            Bỏ theo dõi
          </button>
          <button
            v-else
            @click="followShop"
            class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors"
          >
            Theo dõi
          </button>
          <button
            v-if="isLoggedInComputed"
            @click="openChatModal"
            class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors"
            :disabled="!shop.seller_id"
            :title="!shop.seller_id ? 'Không thể nhắn tin: Người bán không khả dụng' : ''"
          >
            Nhắn tin
          </button>
          <button
            v-if="isLoggedInComputed"
            @click="openDisputeModal"
            class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors"
            :disabled="!shop.seller_id"
            :title="!shop.seller_id ? 'Không thể khiếu nại: Người bán không khả dụng' : ''"
          >
            Khiếu nại
          </button>
        </div>
      </div>

      <!-- Dispute Modal -->
      <div
        v-if="isDisputeModalOpen"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-[80] animate-fade-in"
      >
        <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-2xl">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-orange-500">Gửi Khiếu Nại</h2>
            <button @click="closeDisputeModal" class="text-gray-600 hover:text-gray-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div v-if="disputeError" class="text-red-500 text-sm mb-4">{{ disputeError }}</div>
          <div v-if="disputeLoading" class="text-center">
            <svg class="animate-spin w-6 h-6 mx-auto text-orange-500" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
          </div>
          <div v-else>
            <div class="mb-4">
              <label class="block text-gray-700 font-semibold mb-2">Chọn Đơn Hàng</label>
              <select
                v-model="disputeForm.order_id"
                class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                :disabled="orders.length === 0"
              >
                <option value="" disabled>Chọn một đơn hàng</option>
                <option v-for="order in orders" :key="order.id" :value="order.id">
                  Đơn hàng #{{ order.id }} - {{ formatDate(order.created_at) }}
                </option>
              </select>
              <p v-if="orders.length === 0" class="text-gray-600 text-sm mt-2">
                Không có đơn hàng nào liên quan đến cửa hàng này
              </p>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-semibold mb-2">Lý Do Khiếu Nại</label>
              <textarea
                v-model="disputeForm.reason"
                placeholder="Mô tả lý do khiếu nại (10-1000 ký tự)"
                class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                rows="5"
              ></textarea>
            </div>
            <div class="flex justify-end space-x-2">
              <button
                @click="closeDisputeModal"
                class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors"
              >
                Hủy
              </button>
              <button
                @click="submitDispute"
                class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors"
                :disabled="!disputeForm.order_id || !disputeForm.reason.trim() || disputeSubmitting"
              >
                <span v-if="disputeSubmitting" class="spinner"></span>
                Gửi
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Shop Banner: SHOP_TOP -->
      <BannerCarousel
        v-if="bannersByLocation['SHOP_TOP']"
        :banners="bannersByLocation['SHOP_TOP']"
        class="my-8"
      />

      <!-- Products -->
      <div class="border-t pt-4">
        <h2 class="text-xl font-bold text-orange-500 mb-2">Sản phẩm</h2>
        <div v-if="shop.products.length === 0" class="text-gray-600">
          Không có sản phẩm
        </div>
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          <template v-for="(item, index) in mixedItems">
            <ProductCard
              v-if="item.type === 'product'"
              :key="`product-${item.data.id}`"
              :product="item.data"
              :variant="item.data.product_variant"
              :discount-price="calculateDiscountPrice(item.data)"
              :stock-limit="item.data.product_variant?.stock || null"
              @success="handleSuccess"
              @error="handleError"
            />
            <div
              v-else-if="item.type === 'banner'"
              :key="`banner-${index}`"
              class="col-span-2 sm:col-span-3 md:col-span-4"
            >
              <BannerCarousel :banners="[item.data]" />
            </div>
          </template>
        </div>
      </div>

      <!-- Shop Banner: SHOP_BOTTOM -->
      <BannerCarousel
        v-if="bannersByLocation['SHOP_BOTTOM']"
        :banners="bannersByLocation['SHOP_BOTTOM']"
        class="my-8"
      />
    </div>
    <div v-else class="text-center text-gray-600">
      Không tìm thấy cửa hàng
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import BannerCarousel from './BannerCarousel.vue';
import ProductCard from './ProductCard.vue';
import { EventBus } from './component/ChatModal.vue';

export default {
  name: 'Shop',
  components: { BannerCarousel, ProductCard },
  data() {
    return {
      shop: null,
      banners: [],
      bannersByLocation: {},
      mixedItems: [],
      loading: false,
      error: null,
      isDisputeModalOpen: false,
      disputeForm: {
        order_id: '',
        reason: '',
      },
      orders: [],
      disputeLoading: false,
      disputeSubmitting: false,
      disputeError: null,
    };
  },
  computed: {
    isLoggedInComputed() {
      return !!localStorage.getItem('token');
    },
  },
  async created() {
    await this.fetchShop();
  },
  methods: {
    async fetchShop() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get(`/buyer/shops/${this.$route.params.id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Shop API response:', JSON.stringify(response.data, null, 2));
        this.shop = response.data.shop;
        this.banners = response.data.banners || [];
        this.bannersByLocation = this.banners.reduce((acc, banner) => {
          const code = banner.location_code || 'default';
          if (!acc[code]) acc[code] = [];
          acc[code].push({
            ...banner,
            img_url: this.getImageUrl(banner.img_url),
          });
          return acc;
        }, {});
        this.mixItems();
      } catch (error) {
        console.error('Error fetching shop:', error.response?.data || error.message);
        this.error = error.response?.data?.error || 'Lỗi tải thông tin cửa hàng';
        if (error.response?.status === 404) {
          this.$router.push('/not-found');
        }
      } finally {
        this.loading = false;
      }
    },
    mixItems() {
      const products = (this.shop.products || []).map(product => ({ type: 'product', data: product }));
      const campaignBanners = (this.bannersByLocation['SHOP_CAMPAIGN'] || []).map(banner => ({ type: 'banner', data: banner }));
      this.mixedItems = [...products];
      if (campaignBanners.length > 0) {
        campaignBanners.forEach(banner => {
          const insertIndex = Math.floor(Math.random() * (this.mixedItems.length + 1));
          this.mixedItems.splice(insertIndex, 0, banner);
        });
      }
    },
    async followShop() {
      this.loading = true;
      this.error = null;
      try {
        await axios.post(`/buyer/shops/${this.$route.params.id}/follow`, {}, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.shop.is_following = true;
      } catch (error) {
        console.error('Error following shop:', error.response?.data || error.message);
        this.error = error.response?.data?.error || 'Lỗi khi theo dõi cửa hàng';
      } finally {
        this.loading = false;
      }
    },
    async unfollowShop() {
      this.loading = true;
      this.error = null;
      try {
        await axios.delete(`/buyer/shops/${this.$route.params.id}/unfollow`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.shop.is_following = false;
      } catch (error) {
        console.error('Error unfollowing shop:', error.response?.data || error.message);
        this.error = error.response?.data?.error || 'Lỗi khi bỏ theo dõi cửa hàng';
      } finally {
        this.loading = false;
      }
    },
    async openChatModal() {
      if (!this.isLoggedInComputed) {
        this.$router.push('/buyer/login');
        return;
      }
      if (!this.shop || !this.shop.seller_id) {
        console.error('Invalid shop or seller_id:', this.shop);
        this.error = 'Không thể mở trò chuyện: Người bán không khả dụng';
        return;
      }
      const newChat = {
        seller: { id: this.shop.seller_id },
        shop: { shop_name: this.shop.shop_name, avatar_url: this.shop.avatar_url },
        messages: [],
        unread_count: 0,
      };
      console.log('Opening chat modal from Shop.vue:', JSON.stringify(newChat, null, 2));
      this.$emit('open-chat-modal', newChat);
      EventBus.emit('open-chat-modal', newChat);
    },
    async openDisputeModal() {
      if (!this.isLoggedInComputed) {
        this.$router.push('/buyer/login');
        return;
      }
      if (!this.shop || !this.shop.seller_id) {
        console.error('Invalid shop or seller_id:', this.shop);
        this.error = 'Không thể khiếu nại: Người bán không khả dụng';
        return;
      }
      this.isDisputeModalOpen = true;
      this.disputeError = null;
      this.disputeForm = { order_id: '', reason: '' };
      await this.fetchOrders();
    },
    async fetchOrders() {
      this.disputeLoading = true;
      try {
        const response = await axios.get(`/buyer/orders?seller_id=${this.shop.seller_id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Orders API response:', JSON.stringify(response.data, null, 2));
        this.orders = response.data.orders || [];
      } catch (error) {
        console.error('Error fetching orders:', error.response?.data || error.message);
        this.disputeError = error.response?.data?.error || 'Lỗi tải danh sách đơn hàng';
        this.orders = [];
      } finally {
        this.disputeLoading = false;
      }
    },
    async submitDispute() {
      if (!this.disputeForm.order_id || !this.disputeForm.reason.trim() || this.disputeSubmitting) return;
      this.disputeSubmitting = true;
      this.disputeError = null;
      try {
        const response = await axios.post(`/buyer/shops/${this.$route.params.id}/dispute`, this.disputeForm, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Dispute API response:', JSON.stringify(response.data, null, 2));
        this.handleSuccess('Khiếu nại đã được gửi thành công');
        this.closeDisputeModal();
      } catch (error) {
        console.error('Error submitting dispute:', error.response?.data || error.message);
        this.disputeError = error.response?.data?.error || 'Lỗi khi gửi khiếu nại';
        if (error.response?.status === 409) {
          this.disputeError = 'Đơn hàng này đã có khiếu nại đang chờ xử lý';
        }
      } finally {
        this.disputeSubmitting = false;
      }
    },
    closeDisputeModal() {
      this.isDisputeModalOpen = false;
      this.disputeForm = { order_id: '', reason: '' };
      this.orders = [];
      this.disputeError = null;
    },
    getImageUrl(imgUrl) {
      if (!imgUrl) {
        console.warn('Không có đường dẫn ảnh, sử dụng ảnh placeholder');
        return 'https://via.placeholder.com/1200x400?text=Ảnh+Không+Tìm+Thấy';
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
      event.target.src = type.includes('avatar')
        ? 'https://via.placeholder.com/50?text=Ảnh+Không+Tìm+Thấy'
        : 'https://via.placeholder.com/1200x400?text=Ảnh+Không+Tìm+Thấy';
    },
    calculateDiscountPrice(product) {
      const lowestPrice = product.lowest_price || product.price;
      return null; // No discount logic implemented yet
    },
    handleSuccess(message) {
      this.error = null;
      alert(message); // Replace with UI notification
    },
    handleError(error) {
      this.error = error;
    },
    formatDate(date) {
      if (!date) return '';
      const d = new Date(date);
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      const hours = String(d.getHours()).padStart(2, '0');
      const minutes = String(d.getMinutes()).padStart(2, '0');
      return `${day}/${month}/${year} ${hours}:${minutes}`;
    },
  },
};
</script>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #f97316;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #ea580c;
}
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #f97316 #f1f1f1;
}

.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #fff;
  border-top: 2px solid transparent;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 8px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>