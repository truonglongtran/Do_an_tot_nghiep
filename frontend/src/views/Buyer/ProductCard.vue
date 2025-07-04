<template>
  <div class="border rounded-lg p-4 hover:shadow-md">
    <router-link :to="'/products/' + product.id" class="block">
      <img
        :src="getImageUrl(variant?.image_url || 'https://via.placeholder.com/150?text=Product')"
        :alt="product.name"
        class="w-full h-32 object-cover rounded mb-2"
        @error="handleImageError($event, variant?.image_url, 'product')"
      />
      <h3 class="text-sm font-semibold truncate">{{ product.name }}</h3>
      <div v-if="discountPrice" class="flex items-center space-x-2">
        <p class="text-orange-500 font-bold">{{ formatPrice(discountPrice) }}đ</p>
        <p class="text-gray-500 line-through text-sm">{{ formatPrice(lowestPrice) }}đ</p>
      </div>
      <p v-else-if="lowestPrice > 0" class="text-orange-500 font-bold">{{ formatPrice(lowestPrice) }}đ</p>
      <p v-else class="text-gray-500 text-sm">Giá không khả dụng</p>
      <p class="text-gray-500 text-sm">Đã bán {{ product.sold_count }}</p>
      <p v-if="stockLimit" class="text-red-500 text-sm">Còn {{ stockLimit }} sản phẩm</p>
      <p v-if="product.shop" class="text-gray-600 text-sm truncate">Cửa hàng: {{ product.shop.shop_name }}</p>
    </router-link>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ProductCard',
  props: {
    product: {
      type: Object,
      required: true,
    },
    variant: {
      type: Object,
      default: null,
    },
    discountPrice: {
      type: [Number, String],
      default: null,
    },
    stockLimit: {
      type: [Number, String],
      default: null,
    },
  },
  computed: {
    lowestPrice() {
      return (
        Number(this.product.lowest_price) ||
        Number(this.product.price) ||
        Number(this.variant?.price) ||
        0
      );
    },
    canAddToCart() {
      return this.variant?.id || (this.product.price && !this.product.variants?.length);
    },
  },
  methods: {
    getImageUrl(imgUrl) {
      console.log('Đường dẫn ảnh đầu vào:', imgUrl);
      if (!imgUrl) {
        console.warn('Không có đường dẫn ảnh, sử dụng ảnh placeholder');
        return 'https://via.placeholder.com/150?text=Ảnh+Không+Tìm+Thấy';
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
      event.target.src = 'https://via.placeholder.com/150?text=Ảnh+Không+Tìm+Thấy';
    },
    formatPrice(price) {
      return Number(price).toLocaleString('vi-VN');
    },
    async addToCart() {
      try {
        const payload = this.variant?.id
          ? { product_variant_id: this.variant.id, quantity: 1 }
          : { product_id: this.product.id, quantity: 1 };
        const response = await axios.post('/buyer/cart/add', payload);
        this.$emit('success', response.data.message);
      } catch (error) {
        console.error('Error adding to cart:', error.response?.data || error.message);
        this.$emit('error', error.response?.data?.error || 'Lỗi khi thêm vào giỏ hàng');
      }
    },
  },
};
</script>

<style scoped>
/* Không cần style vì Tailwind CSS đã xử lý */
</style>