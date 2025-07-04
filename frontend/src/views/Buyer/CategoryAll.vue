<!-- src/views/Buyer/CategoryAll.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Tất cả danh mục</h1>
    <div v-if="categories.length === 0" class="text-center text-gray-600">
      Không có danh mục
    </div>
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <router-link
        v-for="category in categories"
        :key="category.id"
        :to="'/categories/' + category.slug"
        class="border rounded-lg p-4 text-center hover:shadow-md"
      >
        <p class="text-gray-600 font-semibold">{{ category.name }}</p>
      </router-link>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'CategoryAll',
  data() {
    return {
      categories: [],
    };
  },
  async created() {
    await this.fetchCategories();
  },
  methods: {
    async fetchCategories() {
      try {
        const response = await axios.get('/buyer/categories');
        this.categories = response.data.categories;
      } catch (error) {
        console.error('Error fetching categories:', error);
      }
    },
  },
};
</script>

<style scoped>
/* Không cần style vì Tailwind CSS đã xử lý */
</style>