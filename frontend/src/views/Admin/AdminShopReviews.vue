<template>
  <div class="p-8 space-y-6">
    <div>
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
          Đánh giá của cửa hàng: {{ shopName }}
        </h2>
        <router-link
          to="/admin/reviews"
          class="text-blue-600 hover:underline text-sm font-medium"
        >
          Quay lại danh sách cửa hàng
        </router-link>
      </div>
      <FilterSearch
        :filters="filters"
        :searchPlaceholder="'Tìm theo tên sản phẩm...'"
        :showDateRange="true"
        v-model:currentFilter="ratingFilter"
        v-model:searchQuery="searchQuery"
        v-model:startDate="startDate"
        v-model:endDate="endDate"
        @search="applySearch"
      />
      <p v-if="errorMessage" class="text-center text-red-500 mt-4">
        {{ errorMessage }}
      </p>
      <p v-else-if="filteredReviews.length === 0" class="text-center text-gray-500 mt-4">
        Không có đánh giá nào phù hợp.
      </p>
      <table v-else class="table-auto w-full border bg-white shadow-sm rounded-lg overflow-hidden">
        <thead class="bg-gray-200">
          <tr>
            <th class="p-3 border-b text-left text-gray-700">STT</th>
            <th class="p-3 border-b text-left text-gray-700">Người đánh giá</th>
            <th class="p-3 border-b text-left text-gray-700">Sản phẩm</th>
            <th class="p-3 border-b text-left text-gray-700">Điểm số</th>
            <th class="p-3 border-b text-left text-gray-700">Bình luận</th>
            <th class="p-3 border-b text-left text-gray-700">Hình ảnh</th>
            <th class="p-3 border-b text-left text-gray-700">Hành động</th>
            <th class="p-3 border-b text-left text-gray-700">Ngày tạo</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(review, index) in filteredReviews" :key="review.id" class="hover:bg-gray-50">
            <td class="p-3 border-b">{{ index + 1 }}</td>
            <td class="p-3 border-b">{{ review.buyer?.email || 'N/A' }}</td>
            <td class="p-3 border-b">{{ review.product?.name || 'N/A' }}</td>
            <td class="p-3 border-b">{{ review.rating || 'N/A' }}</td>
            <td class="p-3 border-b">{{ review.comment || 'N/A' }}</td>
            <td class="p-3 border-b">
              <div v-if="review.images && parseImages(review.images).length > 0" class="flex space-x-2">
                <img
                  v-for="(image, imgIndex) in parseImages(review.images)"
                  :key="imgIndex"
                  :src="image"
                  alt="Review Image"
                  class="w-12 h-12 object-cover rounded"
                />
              </div>
              <span v-else>Không có</span>
            </td>
            <td class="p-3 border-b text-center">
              <button
                @click="openReviewModal(review)"
                class="text-blue-600 hover:underline"
              >
                Xem chi tiết
              </button>
            </td>
            <td class="p-3 border-b">{{ formatDate(review.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal for Review Details -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeReviewModal"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 relative">
        <button
          @click="closeReviewModal"
          class="absolute top-4 right-4 text-gray-500 hover:text-gray-700"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <h3 class="text-xl font-bold text-gray-800 mb-4">
          Chi tiết đánh giá
        </h3>
        <div v-if="selectedReview" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <h4 class="text-md font-semibold text-gray-700">Người đánh giá</h4>
              <p class="text-gray-600">{{ selectedReview.buyer?.email || 'N/A' }}</p>
            </div>
            <div>
              <h4 class="text-md font-semibold text-gray-700">Sản phẩm</h4>
              <p class="text-gray-600">{{ selectedReview.product?.name || 'N/A' }}</p>
            </div>
            <div>
              <h4 class="text-md font-semibold text-gray-700">Điểm số</h4>
              <p class="text-gray-600">{{ selectedReview.rating || 'N/A' }}</p>
            </div>
            <div>
              <h4 class="text-md font-semibold text-gray-700">Ngày tạo</h4>
              <p class="text-gray-600">{{ formatDate(selectedReview.created_at) }}</p>
            </div>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Bình luận</h4>
            <p class="text-gray-600">{{ selectedReview.comment || 'N/A' }}</p>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Hình ảnh</h4>
            <div v-if="selectedReview.images && parseImages(selectedReview.images).length > 0" class="flex flex-wrap gap-4">
              <img
                v-for="(image, imgIndex) in parseImages(selectedReview.images)"
                :key="imgIndex"
                :src="image"
                alt="Review Image"
                class="w-24 h-24 object-cover rounded"
              />
            </div>
            <p v-else class="text-gray-600">Không có hình ảnh</p>
          </div>
        </div>
        <div class="mt-6 flex justify-end">
          <button
            @click="closeReviewModal"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
          >
            Đóng
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';

export default {
  name: 'AdminShopReviews',
  components: { FilterSearch },
  data() {
    return {
      reviews: [],
      shopName: '',
      errorMessage: '',
      searchQuery: '',
      ratingFilter: 'all',
      startDate: '',
      endDate: '',
      showModal: false,
      selectedReview: null,
    };
  },
  computed: {
    filters() {
      return [
        { key: 'all', label: 'Tất cả đánh giá', count: this.reviews.length },
        {
          key: 'good',
          label: 'Đánh giá tốt (≥ 3)',
          count: this.reviews.filter((r) => r.rating >= 3).length,
        },
        {
          key: 'bad',
          label: 'Đánh giá xấu (< 3)',
          count: this.reviews.filter((r) => r.rating < 3).length,
        },
        { key: 'rating-1', label: '1 sao', count: this.reviews.filter((r) => r.rating === 1).length },
        { key: 'rating-2', label: '2 sao', count: this.reviews.filter((r) => r.rating === 2).length },
        { key: 'rating-3', label: '3 sao', count: this.reviews.filter((r) => r.rating === 3).length },
        { key: 'rating-4', label: '4 sao', count: this.reviews.filter((r) => r.rating === 4).length },
        { key: 'rating-5', label: '5 sao', count: this.reviews.filter((r) => r.rating === 5).length },
      ];
    },
    filteredReviews() {
      const q = this.searchQuery.toLowerCase().trim();
      return this.reviews.filter((review) => {
        const matchesSearch = !q || (review.product?.name || '').toLowerCase().includes(q);
        let matchesRating = true;
        if (this.ratingFilter === 'good') {
          matchesRating = review.rating >= 3;
        } else if (this.ratingFilter === 'bad') {
          matchesRating = review.rating < 3;
        } else if (this.ratingFilter.startsWith('rating-')) {
          const rating = parseInt(this.ratingFilter.split('-')[1]);
          matchesRating = review.rating === rating;
        }
        let matchesDate = true;
        if (this.startDate || this.endDate) {
          const reviewDate = new Date(review.created_at);
          if (this.startDate) {
            const start = new Date(this.startDate);
            matchesDate = matchesDate && reviewDate >= start;
          }
          if (this.endDate) {
            const end = new Date(this.endDate);
            end.setHours(23, 59, 59, 999); // Include entire end day
            matchesDate = matchesDate && reviewDate <= end;
          }
        }
        return matchesSearch && matchesRating && matchesDate;
      });
    },
  },
  async mounted() {
    await this.fetchReviews();
  },
  methods: {
    async fetchReviews() {
      const token = localStorage.getItem('token');
      const shopId = this.$route.params.shopId;
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get(`http://localhost:8000/api/admin/reviews/${shopId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.reviews = response.data;
        this.shopName = this.reviews.length > 0 && this.reviews[0].product?.shop
          ? this.reviews[0].product.shop.shop_name
          : 'Unknown Shop';
        this.errorMessage = '';
      } catch (error) {
        console.error('Error fetching reviews:', error.response || error);
        this.errorMessage = error.response?.status === 401
          ? 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.'
          : 'Không thể tải danh sách đánh giá. Vui lòng thử lại sau.';
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    parseImages(images) {
      if (!images) return [];
      if (Array.isArray(images)) return images;
      try {
        return JSON.parse(images) || [];
      } catch (e) {
        return [];
      }
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      });
    },
    applySearch() {
      // Filtering handled by filteredReviews computed property
    },
    openReviewModal(review) {
      this.selectedReview = review;
      this.showModal = true;
    },
    closeReviewModal() {
      this.showModal = false;
      this.selectedReview = null;
    },
  },
};
</script>