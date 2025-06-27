<!-- src/views/Buyer/LoyaltyPoints.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Điểm tích lũy</h1>
    <p class="text-xl mb-4">Tổng điểm: {{ totalPoints }}</p>
    <div v-if="transactions.length === 0" class="text-center text-gray-600">
      Không có giao dịch điểm
    </div>
    <div v-else class="space-y-4">
      <div v-for="transaction in transactions" :key="transaction.id" class="border rounded-lg p-4">
        <p class="text-gray-600">Loại: {{ transaction.transaction_type }}</p>
        <p class="text-gray-600">Điểm: {{ transaction.points }}</p>
        <p v-if="transaction.order" class="text-gray-600">Đơn hàng: #{{ transaction.order.id }}</p>
        <p class="text-gray-500 text-sm">{{ formatDate(transaction.created_at) }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import moment from 'moment';

export default {
  name: 'LoyaltyPoints',
  data() {
    return {
      totalPoints: 0,
      transactions: [],
    };
  },
  async created() {
    await this.fetchPoints();
  },
  methods: {
    async fetchPoints() {
      try {
        const response = await axios.get('/buyer/loyalty-points');
        this.totalPoints = response.data.total_points;
        this.transactions = response.data.transactions;
      } catch (error) {
        console.error('Error fetching loyalty points:', error);
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