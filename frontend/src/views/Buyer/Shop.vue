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
      <div class="flex items-center space-x-4">
        <img :src="shop.avatar_url || 'https://via.placeholder.com/50'" :alt="shop.shop_name" class="w-16 h-16 rounded-full" />
        <div>
          <h1 class="text-2xl font-bold text-orange-500">{{ shop.shop_name }}</h1>
          <p class="text-gray-600">{{ shop.pickup_address }}</p>
        </div>
        <button v-if="shop.is_following" @click="unfollowShop" class="bg-gray-500 text-white px-4 py-2 rounded-lg">
          Bỏ theo dõi
        </button>
        <button v-else @click="followShop" class="bg-orange-500 text-white px-4 py-2 rounded-lg">
          Theo dõi
        </button>
      </div>

      <!-- Shop Banner: SHOP_TOP -->
      <BannerCarousel v-if="bannersByLocation['SHOP_TOP']" :banners="bannersByLocation['SHOP_TOP']" class="my-8" />

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
      <BannerCarousel v-if="bannersByLocation['SHOP_BOTTOM']" :banners="bannersByLocation['SHOP_BOTTOM']" class="my-8" />
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
    };
  },
  async created() {
    await this.fetchShop();
  },
  methods: {
    async fetchShop() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get(`/buyer/shops/${this.$route.params.id}`);
        this.shop = response.data.shop;
        this.banners = response.data.banners || [];

        // Nhóm banner theo location_code
        this.bannersByLocation = this.banners.reduce((acc, banner) => {
          const code = banner.location_code || 'default';
          if (!acc[code]) acc[code] = [];
          acc[code].push(banner);
          return acc;
        }, {});

        // Tạo danh sách mixedItems (sản phẩm xen kẽ với banner SHOP_CAMPAIGN)
        this.mixItems();
      } catch (error) {
        console.error('Error fetching shop:', error.response?.data || error.message);
        this.error = error.response?.data?.error || 'Error loading shop information.';
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

      // Chèn banner ngẫu nhiên vào danh sách sản phẩm
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
        await axios.post(`/buyer/shops/${this.$route.params.id}/follow`);
        this.shop.is_following = true;
      } catch (error) {
        console.error('Error following shop:', error.response?.data || error.message);
        this.error = error.response?.data?.error || 'Error following shop';
      } finally {
        this.loading = false;
      }
    },
    async unfollowShop() {
      this.loading = true;
      this.error = null;
      try {
        await axios.delete(`/buyer/shops/${this.$route.params.id}/unfollow`);
        this.shop.is_following = false;
      } catch (error) {
        console.error('Error unfollowing shop:', error.response?.data || error.message);
        this.error = error.response?.data?.error || 'Error unfollowing shop';
      } finally {
        this.loading = false;
      }
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
  },
};
</script>

<style scoped>
/* Tailwind CSS handles styling */
</style>