<!-- src/views/Buyer/CategoryDetail.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 v-if="category" class="text-3xl font-bold text-orange-500 mb-4">{{ category.name }}</h1>
    <div v-if="products.length === 0" class="text-center text-gray-600">
      Không có sản phẩm trong danh mục này
    </div>
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
      <ProductCard v-for="product in products" :key="product.id" :product="product" :variant="product.product_variant" />
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import ProductCard from './ProductCard.vue';

export default {
  name: 'CategoryDetail',
  components: { ProductCard },
  data() {
    return {
      category: null,
      products: [],
    };
  },
  async created() {
    await this.fetchCategory();
  },
  methods: {
    async fetchCategory() {
      try {
        const response = await axios.get(`/buyer/categories/${this.$route.params.slug}`);
        this.category = response.data.category;
        this.products = response.data.products;
      } catch (error) {
        console.error('Error fetching category:', error);
        this.$router.push('/not-found');
      }
    },
  },
};
</script>

<style scoped>
/* Không cần style vì Tailwind CSS đã xử lý */
</style>