<template>
  <div class="p-8 space-y-6">
    <div class="mx-auto">
      <div v-if="$route.name !== 'ShopReviews'">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
          Danh sách đánh giá theo cửa hàng
        </h2>
        <FilterSearch
          :filters="filters"
          :searchPlaceholder="'Tìm theo tên cửa hàng...'"
          v-model:currentFilter="currentFilter"
          v-model:searchQuery="searchQuery"
          @search="applySearch"
        />
        <p v-if="errorMessage" class="text-center text-red-500 mt-4">
          {{ errorMessage }}
        </p>
        <p v-else-if="filteredShops.length === 0" class="text-center text-gray-500 mt-4">
          Không tìm thấy cửa hàng nào.
        </p>
        <table v-else class="table-auto w-full border bg-white shadow-sm rounded-lg overflow-hidden">
          <thead class="bg-gray-200">
            <tr>
              <th class="p-3 border-b text-left text-gray-700">Tên cửa hàng</th>
              <th class="p-3 border-b text-left text-gray-700">Số lượng đánh giá (Tốt/Xấu)</th>
              <th class="p-3 border-b text-left text-gray-700">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(shop, index) in filteredShops" :key="shop.id" class="hover:bg-gray-50">
              <td class="p-3 border-b">{{ shop.shop_name || 'N/A' }}</td>
              <td class="p-3 border-b">
                <span class="text-green-600">{{ shop.good_reviews || 0 }}</span> /
                <span class="text-red-600">{{ shop.bad_reviews || 0 }}</span>
              </td>
              <td class="p-3 border-b text-center">
                <router-link
                  v-if="shop.id"
                  :to="{ name: 'ShopReviews', params: { shopId: shop.id } }"
                  class="text-blue-600 hover:underline"
                >
                  Xem đánh giá
                </router-link>
                <span v-else class="text-gray-500">ID không hợp lệ</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';

export default {
  name: 'AdminReviews',
  components: { FilterSearch },
  data() {
    return {
      shops: [],
      allShops: [],
      searchQuery: '',
      currentFilter: 'all',
      errorMessage: '',
      statusText: {
        pending: 'Chờ duyệt',
        active: 'Hoạt động',
        banned: 'Bị khóa',
      },
    };
  },
  computed: {
    filters() {
      return [
        { key: 'all', label: 'Tất cả', count: this.allShops.length },
        ...['pending', 'active', 'banned'].map((s) => ({
          key: s,
          label: this.statusText[s],
          count: this.allShops.filter((shop) => shop.status === s).length,
        })),
      ];
    },
    filteredShops() {
      const q = this.searchQuery.toLowerCase().trim();
      return this.allShops.filter((shop) => {
        const matchesSearch = !q || (shop.shop_name || '').toLowerCase().includes(q);
        const matchesFilter = this.currentFilter === 'all' || shop.status === this.currentFilter;
        return matchesSearch && matchesFilter;
      });
    },
  },
  async mounted() {
    await this.fetchShops();
  },
  methods: {
    async fetchShops() {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get('http://localhost:8000/api/admin/reviews', {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log('Backend response:', response.data); // Debug backend data
        // Temporarily force frontend calculation to ensure correct counts
        this.shops = await Promise.all(response.data.map(async (shop) => {
          const reviewsResponse = await axios.get(`http://localhost:8000/api/admin/reviews/${shop.id}`, {
            headers: { Authorization: `Bearer ${token}` },
          });
          const reviews = reviewsResponse.data;
          console.log(`Shop ${shop.id} reviews:`, reviews); // Debug reviews
          const goodReviews = reviews.filter((r) => Number(r.rating) >= 3).length;
          const badReviews = reviews.filter((r) => Number(r.rating) < 3 && r.rating !== null).length;
          console.log(`Shop ${shop.id} - Good: ${goodReviews}, Bad: ${badReviews}`); // Debug counts
          return { ...shop, good_reviews: goodReviews, bad_reviews: badReviews };
        }));
        this.allShops = this.shops;
        this.errorMessage = '';
      } catch (error) {
        console.error('Lỗi khi tải danh sách cửa hàng:', error);
        this.errorMessage = error.response?.status === 401
          ? 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.'
          : 'Không thể tải danh sách cửa hàng. Vui lòng thử lại sau.';
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    applySearch() {
      // Filtering handled by filteredShops computed property
    },
  },
};
</script>