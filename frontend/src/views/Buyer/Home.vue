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
    <div v-else>
      <!-- Banner: HOME_TOP -->
      <BannerCarousel v-if="bannersByLocation['HOME_TOP']" :banners="bannersByLocation['HOME_TOP']" class="mb-8" />

      <!-- Categories -->
      <div class="mt-8">
        <h2 class="text-2xl font-bold text-orange-500 mb-4">Danh mục</h2>
        <div v-if="categories.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <router-link
            v-for="category in categories"
            :key="category.id"
            :to="'/categories/' + category.slug"
            class="border rounded-lg p-4 text-center hover:shadow-md"
          >
            <p class="text-gray-600 font-semibold">{{ category.name }}</p>
          </router-link>
        </div>
        <p v-else class="text-gray-600">Không có danh mục</p>
      </div>

      <!-- Banner: CAMPAIGN_BANNER -->
      <BannerCarousel v-if="bannersByLocation['CAMPAIGN_BANNER']" :banners="bannersByLocation['CAMPAIGN_BANNER']" class="my-8" />

      <!-- Flash Sales -->
      <div class="mt-8">
        <h2 class="text-2xl font-bold text-orange-500 mb-4">Flash Sale</h2>
        <div v-if="flashSales.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          <ProductCard
            v-for="sale in flashSales"
            :key="sale.id"
            :product="sale.product"
            :variant="sale.product_variant"
            :discount-price="sale.discount_price"
            :stock-limit="sale.stock_limit"
          />
        </div>
        <p v-else class="text-gray-600">Không có flash sale hiện tại</p>
      </div>

      <!-- Recommended Products -->
      <div class="mt-8">
        <h2 class="text-2xl font-bold text-orange-500 mb-4">Sản phẩm đề xuất</h2>
        <div v-if="normalizedRecommendedProducts.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          <ProductCard
            v-for="product in normalizedRecommendedProducts"
            :key="product.id"
            :product="product"
            :variant="product.product_variant"
          />
        </div>
        <p v-else class="text-gray-600">Không có sản phẩm nào để hiển thị</p>
      </div>

      <!-- Banner: HOME_BOTTOM -->
      <BannerCarousel v-if="bannersByLocation['HOME_BOTTOM']" :banners="bannersByLocation['HOME_BOTTOM']" class="my-8" />
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import BannerCarousel from './BannerCarousel.vue';
import ProductCard from './ProductCard.vue';

export default {
  name: 'Home',
  components: { BannerCarousel, ProductCard },
  data() {
    return {
      banners: [],
      bannersByLocation: {},
      categories: [],
      flashSales: [],
      recommendedProducts: [],
      normalizedRecommendedProducts: [],
      loading: false,
      error: null,
    };
  },
  async created() {
    await this.fetchHomeData();
  },
  methods: {
    async fetchHomeData() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/buyer/home');
        console.log('API Response:', response.data);
        this.banners = response.data.banners || [];
        this.categories = response.data.categories || [];
        this.flashSales = response.data.flashSales || [];
        this.recommendedProducts = response.data.recommendedProducts || [];

        // Nhóm banner theo location_code
        this.bannersByLocation = this.banners.reduce((acc, banner) => {
          const code = banner.location_code || 'default';
          if (!acc[code]) acc[code] = [];
          acc[code].push(banner);
          return acc;
        }, {});

        // Chuẩn hóa recommendedProducts
        this.normalizedRecommendedProducts = Array.isArray(this.recommendedProducts)
          ? this.recommendedProducts
          : Object.values(this.recommendedProducts);
      } catch (error) {
        console.error('Lỗi tải dữ liệu trang chủ:', error);
        this.error = 'Không thể tải dữ liệu. Vui lòng thử lại sau.';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
/* Tailwind CSS handles styling */
</style>