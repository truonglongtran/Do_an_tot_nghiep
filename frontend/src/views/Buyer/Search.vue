<template>
  <div class="container mx-auto px-4 py-8 pt-20">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Search Results</h1>
    <div v-if="products.length === 0" class="text-center text-gray-600">
      No products found
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
  name: 'Search',
  components: { ProductCard },
  data() {
    return {
      products: [],
    };
  },
  watch: {
    '$route.query.q': {
      immediate: true,
      handler(newQuery) {
        console.log('Search route query changed:', newQuery);
        this.handleSearch(newQuery);
      },
    },
  },
  methods: {
    async handleSearch(query) {
      if (!query?.trim()) {
        console.log('Search query is empty, clearing products');
        this.products = [];
        return;
      }
      try {
        console.log('Sending search request with query:', query, 'Token:', localStorage.getItem('token'));
        const response = await axios.get('/buyer/search', {
          params: { q: query },
        });
        console.log('Search response:', response.data);
        this.products = response.data.products || [];
      } catch (error) {
        console.error('Error searching products:', error.response?.data || error.message);
        this.products = [];
      }
    },
  },
};
</script>

<style scoped>
/* Tailwind handles styling */
</style>