<template>
  <div class="p-8 space-y-6">
    <div>
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
          Đánh giá của cửa hàng: {{ shopName }}
        </h2>
        <div class="text-sm text-gray-600">
          <p>Điểm trung bình: <span class="font-semibold">{{ shopStats.average_rating || 0 }}</span></p>
          <p>Đánh giá tốt: <span class="font-semibold text-green-600">{{ shopStats.good_reviews || 0 }}</span></p>
          <p>Đánh giá xấu: <span class="font-semibold text-red-600">{{ shopStats.bad_reviews || 0 }}</span></p>
        </div>
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
            <td class="p-3 border-b text-center space-x-2">
              <button
                @click="openReviewModal(review)"
                class="text-blue-600 hover:underline"
              >
                Xem chi tiết
              </button>
              <!-- Comment nút Ẩn -->
              <!--
              <button
                v-if="!review.is_hidden"
                @click="showConfirmModal('hideReview', review.id)"
                class="text-red-600 hover:underline"
              >
                Ẩn
              </button>
              -->
            </td>
            <td class="p-3 border-b">{{ formatDate(review.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal for Review Details -->
    <GenericDetailsModal
      :show="showModal"
      :data="selectedReview"
      :fields="reviewFields"
      title="Chi tiết đánh giá"
      @close="closeReviewModal"
    />

    <!-- Comment Confirm Modal for Hiding Review -->
    <!--
    <ConfirmModal
      :show="modal.show"
      :title="modal.title"
      :message="modal.message"
      :confirmText="modal.confirmText"
      :cancelText="modal.cancelText"
      @confirm="handleModalConfirm"
      @cancel="handleModalCancel"
    />
    -->
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/SellerFilterSearch.vue';
import GenericDetailsModal from './component/GenericDetailsModal.vue';
// import ConfirmModal from './component/SellerConfirmModal.vue'; // Comment import

export default {
  name: 'SellerReviews',
  components: { FilterSearch, GenericDetailsModal /*, ConfirmModal*/ }, // Comment ConfirmModal
  data() {
    return {
      reviews: [],
      shopName: '',
      shopStats: {
        id: null,
        name: '',
        average_rating: 0,
        good_reviews: 0,
        bad_reviews: 0,
      },
      errorMessage: '',
      searchQuery: '',
      ratingFilter: 'all',
      startDate: '',
      endDate: '',
      showModal: false,
      selectedReview: null,
      modal: {
        show: false,
        title: '',
        message: '',
        confirmText: 'Xác nhận',
        cancelText: 'Hủy',
        action: null,
        data: null,
      },
      reviewFields: [
        { label: 'Người đánh giá', key: 'buyer.email', type: 'text' },
        { label: 'Sản phẩm', key: 'product.name', type: 'text' },
        { label: 'Điểm số', key: 'rating', type: 'text' },
        { label: 'Bình luận', key: 'comment', type: 'text' },
        {
          label: 'Hình ảnh',
          key: 'images',
          type: 'custom',
          customFormat: (images) => {
            const parsedImages = this.parseImages(images);
            return parsedImages.length > 0 ? parsedImages : ['Không có hình ảnh'];
          },
        },
        { label: 'Ngày tạo', key: 'created_at', type: 'date' },
      ],
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
            end.setHours(23, 59, 59, 999);
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
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get('/seller/reviews', {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (response.data.success) {
          this.reviews = response.data.data || [];
          this.shopStats = response.data.shop || {
            id: null,
            name: 'Cửa hàng của bạn',
            average_rating: 0,
            good_reviews: 0,
            bad_reviews: 0,
          };
          this.shopName = this.shopStats.name;
          this.errorMessage = '';
        } else {
          throw new Error(response.data.message || 'Lỗi khi tải đánh giá');
        }
      } catch (error) {
        console.error('Error fetching reviews:', error.response || error);
        this.errorMessage = error.response?.status === 401
          ? 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.'
          : error.message || 'Không thể tải danh sách đánh giá. Vui lòng thử lại sau.';
        if (error.response?.status === 401) {
          this.$router.push('/seller/login');
        }
      }
    },
    /*
    async hideReview(reviewId) {
      const token = localStorage.getItem('token');
      try {
        const response = await axios.patch(`/seller/reviews/${reviewId}/hide`, {}, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (response.data.success) {
          const review = this.reviews.find(r => r.id === reviewId);
          if (review) {
            review.is_hidden = true;
          }
          alert('Ẩn đánh giá thành công');
        } else {
          throw new Error(response.data.message || 'Lỗi khi ẩn đánh giá');
        }
      } catch (error) {
        console.error('Error hiding review:', error.response || error);
        alert('Lỗi khi ẩn đánh giá: ' + (error.response?.data?.message || 'Lỗi hệ thống'));
      }
    },
    showConfirmModal(action, data = null) {
      this.modal = {
        show: true,
        action,
        data,
        title: action === 'hideReview' ? 'Xác nhận ẩn đánh giá' : 'Xác nhận',
        message: action === 'hideReview' ? 'Bạn có chắc chắn muốn ẩn đánh giá này?' : 'Bạn có chắc chắn muốn thực hiện hành động này?',
        confirmText: 'Xác nhận',
        cancelText: 'Hủy',
      };
    },
    handleModalConfirm() {
      if (this.modal.action === 'hideReview') {
        this.hideReview(this.modal.data);
      }
      this.modal.show = false;
      this.modal.action = null;
      this.modal.data = null;
    },
    handleModalCancel() {
      this.modal.show = false;
      this.modal.action = null;
      this.modal.data = null;
    },
    */
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
      return new Date(date).toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      });
    },
    applySearch() {
      // Filtering handled by filteredReviews computed property
    },
    openReviewModal(review) {
      this.selectedReview = { ...review };
      this.showModal = true;
    },
    closeReviewModal() {
      this.showModal = false;
      this.selectedReview = null;
    },
  },
};
</script>