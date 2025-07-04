<!-- src/views/Buyer/Reviews.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Đánh giá của tôi</h1>
    <div v-if="reviews.length === 0" class="text-center text-gray-600">
      Không có đánh giá
    </div>
    <div v-else class="space-y-4">
      <div v-for="review in reviews" :key="review.id" class="border rounded-lg p-4">
        <p class="font-semibold">{{ review.product.name }}</p>
        <p class="text-gray-600">{{ review.comment }}</p>
        <p class="text-yellow-500">Đánh giá: {{ review.rating }}/5</p>
        <div v-if="review.images && review.images.length" class="flex space-x-2 mt-2">
          <img v-for="img in review.images" :key="img" :src="img" class="w-16 h-16 object-cover rounded" />
        </div>
        <p class="text-gray-500 text-sm">{{ formatDate(review.created_at) }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import moment from 'moment';

export default {
  name: 'Reviews',
  data() {
    return {
      reviews: [],
    };
  },
  async created() {
    await this.fetchReviews();
  },
  methods: {
    async fetchReviews() {
      try {
        const response = await axios.get('/buyer/reviews');
        this.reviews = response.data.reviews;
      } catch (error) {
        console.error('Error fetching reviews:', error);
      }
    },
    formatDate(date) {
      return moment(date).format('DD/MM/YYYY HH:mm');
    },
  },
};
</script>

<style scoped>
/* Không cần style vì Tailwind CSS đã xử lý */
</style>