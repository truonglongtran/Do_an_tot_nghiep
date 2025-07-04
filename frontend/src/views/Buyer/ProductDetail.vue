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
    <div v-else-if="product">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Product Images -->
        <div class="relative">
          <div class="relative w-full h-96 transition-opacity duration-300">
            <img
              :src="getImageUrl(selectedImage)"
              :alt="product.name"
              class="w-full h-full object-cover rounded-lg"
              @error="handleImageError($event, selectedImage, 'main')"
            />
            <!-- Image Counter -->
            <div
              class="absolute top-2 right-2 bg-black bg-opacity-50 text-white text-sm px-2 py-1 rounded"
            >
              {{ currentImageIndex + 1 }}/{{ allImages.length }}
            </div>
            <!-- Variant Label (if applicable) -->
            <div
              v-if="variantLabel"
              class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-center py-2 rounded-b-lg"
            >
              {{ variantLabel }}
            </div>
          </div>
          <!-- Navigation Arrows -->
          <button
            @click="prevImage"
            class="absolute top-1/2 left-2 transform -translate-y-1/2 bg-gray-800 bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75"
            :disabled="currentImageIndex === 0"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button
            @click="nextImage"
            class="absolute top-1/2 right-2 transform -translate-y-1/2 bg-gray-800 bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75"
            :disabled="currentImageIndex === allImages.length - 1"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <!-- Thumbnails (only active variant images, if variants exist) -->
          <div v-if="hasVariants" class="flex overflow-x-auto space-x-2 mt-4 pb-2 scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200">
            <img
              v-for="(variant, index) in activeVariants"
              :key="variant.id"
              :src="getImageUrl(variant.image_url || `https://via.placeholder.com/150?text=Variant-${index + 1}`)"
              :alt="variant.sku || 'Biến thể'"
              class="w-16 h-16 object-cover rounded cursor-pointer flex-shrink-0"
              :class="{ 'border-2 border-orange-500': index === variantImageIndex, 'opacity-50': variant.stock === 0 }"
              @click="selectVariant(index)"
              @error="handleImageError($event, variant.image_url, 'variant')"
            />
          </div>
        </div>

        <!-- Product Details -->
        <div>
          <h1 class="text-2xl font-bold text-orange-500 mb-2">{{ product.name }}</h1>
          <div class="flex items-center mb-4">
            <span class="text-orange-500 font-bold text-xl">
              {{ hasVariants ? (selectedVariant?.price ? formatPrice(selectedVariant.price) + 'đ' : 'Chưa có giá') : (product.price ? formatPrice(product.price) + 'đ' : 'Chưa có giá') }}
            </span>
            <span class="ml-2 text-gray-500 text-sm">Đã bán {{ product.sold_count || 0 }}</span>
          </div>
          <!-- Variant Selection (if variants exist) -->
          <div v-if="hasVariants" class="mb-4">
            <label class="block text-gray-600 mb-2">Biến thể:</label>
            <select v-model="selectedVariant" class="border rounded-lg p-2 w-full" @change="updateImageFromVariant">
              <option v-for="variant in activeVariants" :key="variant.id" :value="variant" :disabled="variant.stock === 0">
                {{ variant.sku || 'Biến thể' }} {{ variant.stock === 0 ? '(Hết hàng)' : '' }}
              </option>
            </select>
          </div>
          <div class="flex items-center mb-4">
            <label class="text-gray-600 mr-2">Số lượng:</label>
            <button @click="quantity--" :disabled="quantity <= 1" class="px-2 py-1 bg-gray-200">-</button>
            <span class="mx-2">{{ quantity }}</span>
            <button
              @click="quantity++"
              :disabled="quantity >= (hasVariants ? selectedVariant?.stock || 0 : product.stock || 0)"
              class="px-2 py-1 bg-gray-200"
            >
              +
            </button>
          </div>
          <button
            @click="addToCart"
            class="bg-orange-500 text-white px-4 py-2 rounded-lg"
            :disabled="hasVariants && (!selectedVariant || selectedVariant.stock === 0)"
          >
            Thêm vào giỏ hàng
          </button>
        </div>
      </div>

      <!-- Product Information -->
      <div class="mt-8 border-t pt-4">
        <h2 class="text-xl font-bold text-orange-500 mb-2">Thông tin sản phẩm</h2>
        <div class="text-gray-600 space-y-2">
          <p v-if="product.description">
            <span class="font-semibold">Mô tả:</span> {{ product.description }}
          </p>
          <p v-if="product.category">
            <span class="font-semibold">Danh mục:</span> {{ product.category }}
          </p>
          <p v-if="product.brand">
            <span class="font-semibold">Thương hiệu:</span> {{ product.brand }}
          </p>
          <p v-if="product.created_at">
            <span class="font-semibold">Ngày tạo:</span> {{ formatDate(product.created_at) }}
          </p>
          <p v-if="!product.description && !product.category && !product.brand && !product.created_at">
            Không có thông tin bổ sung
          </p>
        </div>
      </div>

      <!-- Shop Info -->
      <div class="mt-8 border-t pt-4">
        <h2 class="text-xl font-bold text-orange-500 mb-2">Thông tin cửa hàng</h2>
        <p class="text-gray-600">Cửa hàng: {{ product.shop?.shop_name || 'Không có thông tin' }}</p>
        <router-link v-if="product.shop?.id" :to="'/shops/' + product.shop.id" class="text-orange-500 hover:underline">
          Xem cửa hàng
        </router-link>
      </div>

      <!-- Reviews -->
      <div class="mt-8 border-t pt-4">
        <h2 class="text-xl font-bold text-orange-500 mb-2">Đánh giá sản phẩm</h2>
        <div v-if="!product.reviews?.length" class="text-gray-600">
          Chưa có đánh giá
        </div>
        <div v-else class="space-y-4">
          <div v-for="review in product.reviews" :key="review.id" class="border rounded-lg p-4">
            <p class="text-sm text-black-500 font-semibold mb-1">{{ review.username || 'Ẩn danh' }}</p>
            <p class="text-gray-600">{{ review.comment || 'Không có bình luận' }}</p>
            <p class="text-yellow-500">Đánh giá: {{ review.rating }}/5</p>
            <div v-if="parsedReviewImages(review.images)?.length" class="flex overflow-x-auto space-x-2 pb-2 scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200">
              <img
                v-for="(img, index) in parsedReviewImages(review.images).slice(0, 5)"
                :key="img"
                :src="getImageUrl(img)"
                :alt="'Review image ' + index"
                class="w-16 h-16 object-cover rounded flex-shrink-0"
                @error="handleImageError($event, img, 'review_image_' + index)"
              />
            </div>
            <p class="text-gray-500 text-sm">{{ formatDate(review.created_at) }}</p>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="text-center text-gray-600">
      Không tìm thấy sản phẩm
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ProductDetail',
  data() {
    return {
      product: null,
      selectedImage: '',
      currentImageIndex: 0,
      variantImageIndex: -1,
      selectedVariant: null,
      quantity: 1,
      loading: false,
      error: null,
    };
  },
  computed: {
    allImages() {
      if (!this.product) return [];
      const productImages = this.parseImages(this.product.images) || [];
      if (!this.hasVariants) return productImages.length ? productImages : ['https://via.placeholder.com/400'];
      const variantImages = this.activeVariants.map((v, i) => v.image_url || `https://via.placeholder.com/150?text=Variant-${i + 1}`) || [];
      return [...productImages, ...variantImages].length ? [...productImages, ...variantImages] : ['https://via.placeholder.com/400'];
    },
    variantLabel() {
      if (!this.hasVariants || this.variantImageIndex < 0) return '';
      const variant = this.activeVariants[this.variantImageIndex];
      return variant ? `${variant.sku || 'Biến thể'}${variant.stock === 0 ? ' (Hết hàng)' : ''}` : '';
    },
    hasVariants() {
      return this.activeVariants.length > 0;
    },
    activeVariants() {
      return this.product?.product_variants?.filter(v => v.status === 'active') || [];
    },
  },
  async created() {
    await this.fetchProduct();
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
    async fetchProduct() {
      this.loading = true;
      this.error = null;
      try {
        const productId = this.$route.params.id;
        console.log('Fetching product with ID:', productId);
        const response = await axios.get(`/buyer/products/${productId}`, {
          headers: {
            Accept: 'application/json',
          },
        });
        console.log('API Response:', response.data);
        this.product = response.data.product || null;
        if (this.product) {
          this.selectedImage = this.allImages[0] || 'https://via.placeholder.com/400';
          this.currentImageIndex = 0;
          this.variantImageIndex = -1;
          this.selectedVariant = this.hasVariants
            ? this.activeVariants.find(v => v.stock > 0) || this.activeVariants[0] || null
            : null;
          console.log('Initial product data:', {
            productId,
            product: this.product,
            variants: this.activeVariants,
            selectedVariant: this.selectedVariant,
            allImages: this.allImages,
            hasVariants: this.hasVariants,
          });
        } else {
          throw new Error('Không tìm thấy sản phẩm');
        }
      } catch (error) {
        console.error('Error fetching product:', error);
        this.error = error.response?.status === 404 ? 'Sản phẩm không tồn tại' : 'Lỗi tải sản phẩm. Vui lòng thử lại.';
      } finally {
        this.loading = false;
      }
    },
    async addToCart() {
      if (this.hasVariants && !this.selectedVariant) {
        this.error = 'Vui lòng chọn biến thể sản phẩm';
        console.error('No variant selected');
        return;
      }
      if (this.hasVariants && this.selectedVariant.stock === 0) {
        this.error = 'Biến thể này đã hết hàng';
        console.error('Selected variant is out of stock:', this.selectedVariant);
        return;
      }
      this.loading = true;
      try {
        const payload = this.hasVariants
          ? { product_variant_id: this.selectedVariant.id, quantity: this.quantity }
          : { product_id: this.product.id, quantity: this.quantity };
        console.log('Add to cart payload:', payload);
        const response = await axios.post('/buyer/cart/add', payload);
        console.log('Add to cart response:', response.data);
        this.$router.push('/cart');
      } catch (error) {
        console.error('Error adding to cart:', error.response?.data || error.message);
        this.error = error.response?.data?.error || 'Không thể thêm vào giỏ hàng. Vui lòng thử lại.';
      } finally {
        this.loading = false;
      }
    },
    formatDate(date) {
      return new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
      }).format(new Date(date));
    },
    formatPrice(price) {
      return Number(price).toLocaleString('vi-VN');
    },
    parseImages(images) {
      if (!images) return [];
      if (Array.isArray(images)) return images;
      try {
        return JSON.parse(images) || [];
      } catch (e) {
        console.error('Error parsing images:', e);
        return [];
      }
    },
    parsedReviewImages(images) {
      return this.parseImages(images);
    },
    prevImage() {
      if (this.currentImageIndex > 0) {
        this.currentImageIndex--;
        this.selectedImage = this.allImages[this.currentImageIndex];
        const productImagesLength = this.parseImages(this.product.images).length;
        if (this.hasVariants && this.currentImageIndex >= productImagesLength) {
          this.variantImageIndex = this.currentImageIndex - productImagesLength;
          this.selectedVariant = this.activeVariants[this.variantImageIndex] || null;
        } else {
          this.variantImageIndex = -1;
          this.selectedVariant = this.hasVariants ? this.activeVariants.find(v => v.stock > 0) || this.activeVariants[0] || null : null;
        }
        console.log('prevImage:', {
          selectedImage: this.selectedImage,
          currentImageIndex: this.currentImageIndex,
          variantImageIndex: this.variantImageIndex,
          variantLabel: this.variantLabel,
          selectedVariant: this.selectedVariant,
        });
      }
    },
    nextImage() {
      if (this.currentImageIndex < this.allImages.length - 1) {
        this.currentImageIndex++;
        this.selectedImage = this.allImages[this.currentImageIndex];
        const productImagesLength = this.parseImages(this.product.images).length;
        if (this.hasVariants && this.currentImageIndex >= productImagesLength) {
          this.variantImageIndex = this.currentImageIndex - productImagesLength;
          this.selectedVariant = this.activeVariants[this.variantImageIndex] || null;
        } else {
          this.variantImageIndex = -1;
          this.selectedVariant = this.hasVariants ? this.activeVariants.find(v => v.stock > 0) || this.activeVariants[0] || null : null;
        }
        console.log('nextImage:', {
          selectedImage: this.selectedImage,
          currentImageIndex: this.currentImageIndex,
          variantImageIndex: this.variantImageIndex,
          variantLabel: this.variantLabel,
          selectedVariant: this.selectedVariant,
        });
      }
    },
    selectVariant(index) {
      this.variantImageIndex = index;
      const variant = this.activeVariants[index];
      this.selectedVariant = variant;
      this.selectedImage = variant.image_url || `https://via.placeholder.com/150?text=Variant-${index + 1}`;
      this.currentImageIndex = this.allImages.indexOf(this.selectedImage);
      console.log('selectVariant:', {
        selectedImage: this.selectedImage,
        currentImageIndex: this.currentImageIndex,
        variantImageIndex: this.variantImageIndex,
        variantLabel: this.variantLabel,
        selectedVariant: this.selectedVariant,
      });
    },
    updateImageFromVariant() {
      if (this.selectedVariant) {
        const index = this.activeVariants.findIndex(v => v.id === this.selectedVariant.id);
        if (index >= 0) {
          this.variantImageIndex = index;
          this.selectedImage = this.selectedVariant.image_url || `https://via.placeholder.com/150?text=Variant-${index + 1}`;
          this.currentImageIndex = this.allImages.indexOf(this.selectedImage);
          console.log('updateImageFromVariant:', {
            selectedImage: this.selectedImage,
            currentImageIndex: this.currentImageIndex,
            variantImageIndex: this.variantImageIndex,
            variantLabel: this.variantLabel,
            selectedVariant: this.selectedVariant,
          });
        }
      }
    },
  },
};
</script>

<style scoped>
/* Tùy chỉnh thanh cuộn */
.scrollbar-thin {
  scrollbar-width: thin;
}
.scrollbar-thin::-webkit-scrollbar {
  height: 8px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #9ca3af;
  border-radius: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background-color: #e5e7eb;
  border-radius: 4px;
}
/* Transition cho ảnh lớn */
.transition-opacity {
  transition: opacity 0.3s ease-in-out;
}
</style>