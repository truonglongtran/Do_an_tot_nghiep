<template>
  <div class="p-4">
    <h2 class="text-2xl font-bold mb-4">Danh sách cửa hàng</h2>
    <FilterSearch
      :filters="filters"
      :searchPlaceholder="'Tìm theo email, tên cửa hàng, hoặc đơn vị vận chuyển...'"
      v-model:currentFilter="currentFilter"
      v-model:searchQuery="searchQuery"
      @search="applySearch"
    />
    <p v-if="filteredShops.length === 0" class="text-center text-gray-500 mt-4">
      Không tìm thấy cửa hàng nào.
    </p>
    <table v-else class="table-auto w-full border mt-4">
      <thead class="bg-gray-200">
        <tr>
          <th class="p-2 border">STT</th>
          <th class="p-2 border">Tên cửa hàng</th>
          <th class="p-2 border">Chủ sở hữu (email)</th>
          <th class="p-2 border">SĐT</th>
          <th class="p-2 border">Ảnh đại diện</th>
          <th class="p-2 border">Ảnh bìa</th>
          <th class="p-2 border">Địa chỉ</th>
          <th class="p-2 border">Đơn vị vận chuyển</th>
          <th class="p-2 border">Trạng thái</th>
          <th class="p-2 border">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(shop, index) in filteredShops" :key="shop.id" class="hover:bg-gray-100">
          <td class="p-2 border">{{ index + 1 }}</td>
          <td class="p-2 border">{{ shop.shop_name || 'N/A' }}</td>
          <td class="p-2 border">{{ shop.owner?.email || 'N/A' }}</td>
          <td class="p-2 border">{{ shop.phone_number || 'N/A' }}</td>
          <td class="p-2 border">
            <img
              :src="shop.avatar_url || 'https://via.placeholder.com/48'"
              alt="Avatar"
              class="w-12 h-12 object-cover rounded-full"
            />
          </td>
          <td class="p-2 border">
            <img
              :src="shop.cover_image_url || 'https://via.placeholder.com/80x48'"
              alt="Cover"
              class="w-20 h-12 object-cover rounded"
            />
          </td>
          <td class="p-2 border">{{ formatAddress(shop) }}</td>
          <td class="p-2 border">
            <ul>
              <li v-for="partner in shop.shipping_partners || []" :key="partner.id">
                {{ partner.name || 'N/A' }}
              </li>
            </ul>
          </td>
          <td class="p-2 border">
            <select
              v-model="shop.status"
              @change="updateStatus(shop)"
              class="border p-1 rounded"
            >
              <option value="pending">Chờ duyệt</option>
              <option value="active">Hoạt động</option>
              <option value="banned">Bị khóa</option>
            </select>
          </td>
          <td class="px-4 py-2 border text-center">
            <button
              @click="deleteShop(shop.id)"
              class="text-red-600 hover:underline"
            >
              Xóa
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue'; // Giữ nguyên nếu đúng đường dẫn

export default {
  name: 'AdminShops',
  components: { FilterSearch },
  data() {
    return {
      shops: [],
      allShops: [],
      searchQuery: '',
      currentFilter: 'all',
      shippingTypes: [],
      statusText: {
        pending: 'Chờ duyệt',
        active: 'Hoạt động',
        banned: 'Bị khóa',
      },
    };
  },
  computed: {
    filters() {
      const statusFilters = [
        { key: 'all', label: 'Tất cả', count: this.allShops.length },
        ...['pending', 'active', 'banned'].map((s) => ({
          key: s,
          label: this.statusText[s],
          count: this.filterCountByStatus(s),
        })),
      ];
      const shippingFilters = this.shippingTypes.map((p) => ({
        key: p,
        label: p,
        count: this.filterCountByPartner(p),
      }));
      const filters = [...statusFilters, ...shippingFilters];
      console.log('Shop Filters:', filters);
      return filters;
    },
    filteredShops() {
      const q = this.searchQuery.toLowerCase();
      console.log('Filtering Shops:', q, 'Filter:', this.currentFilter);
      const result = this.allShops.filter((shop) => {
        const matchQuery =
          (shop.shop_name || '').toLowerCase().includes(q) ||
          (shop.owner?.email || '').toLowerCase().includes(q) ||
          (shop.shipping_partners || []).some((p) => (p.name || '').toLowerCase().includes(q));
        const matchFilter =
          this.currentFilter === 'all' ||
          shop.status === this.currentFilter ||
          (shop.shipping_partners || []).some((p) => p.name === this.currentFilter);
        return matchQuery && matchFilter;
      });
      console.log('Filtered Shops:', result);
      return result;
    },
  },
  async mounted() {
    await this.fetchShops();
  },
  methods: {
    async fetchShops() {
      const token = localStorage.getItem('token');
      console.log('Token:', token);
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get('http://localhost:8000/api/admin/shops', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.shops = response.data;
        this.allShops = response.data;
        this.shippingTypes = this.extractShippingPartners(response.data);
        console.log('Shops:', this.shops);
        console.log('All Shops:', this.allShops);
        console.log('Shipping Types:', this.shippingTypes);
      } catch (error) {
        console.error('Lỗi khi tải danh sách cửa hàng:', error);
        alert('Không thể tải danh sách cửa hàng.');
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    extractShippingPartners(shops) {
      const set = new Set();
      shops.forEach((shop) => {
        (shop.shipping_partners || []).forEach((p) => set.add(p.name));
      });
      const types = Array.from(set);
      console.log('Shipping Types:', types);
      return types;
    },
    formatAddress(shop) {
      return [
        shop.pickup_address || '',
        shop.ward || '',
        shop.district || '',
        shop.city || '',
      ]
        .filter(Boolean)
        .join(', ');
    },
    async updateStatus(shop) {
      const token = localStorage.getItem('token');
      try {
        await axios.put(
          `http://localhost:8000/api/admin/shops/${shop.id}/status`,
          { status: shop.status },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        alert('Cập nhật trạng thái thành công');
      } catch (error) {
        console.error('Lỗi cập nhật trạng thái:', error);
        alert('Cập nhật trạng thái thất bại');
      }
    },
    async deleteShop(shopId) {
      const token = localStorage.getItem('token');
      if (confirm('Bạn có chắc chắn muốn xóa cửa hàng này không?')) {
        try {
          await axios.delete(`http://localhost:8000/api/admin/shops/${shopId}`, {
            headers: { Authorization: `Bearer ${token}` },
          });
          this.shops = this.shops.filter((shop) => shop.id !== shopId);
          this.allShops = this.allShops.filter((shop) => shop.id !== shopId);
          alert('Đã xóa cửa hàng thành công');
        } catch (error) {
          console.error('Lỗi khi xóa shop:', error);
          alert('Xóa cửa hàng thất bại');
        }
      }
    },
    setFilter(filter) {
      console.log('Set Shop Filter:', filter);
      this.currentFilter = filter;
      this.applySearch();
    },
    applySearch() {
      console.log('Apply Shop Search:', this.searchQuery);
      // Không cần gán this.shops, computed filteredShops sẽ tự động cập nhật
    },
    filterCountByStatus(status) {
      const count = this.allShops.filter((shop) => shop.status === status).length;
      console.log(`Count Status (${status}):`, count);
      return count;
    },
    filterCountByPartner(partner) {
      const count = this.allShops.filter((shop) =>
        partner === 'all'
          ? true
          : (shop.shipping_partners || []).some((p) => p.name === partner)
      ).length;
      console.log(`Count Partner (${partner}):`, count);
      return count;
    },
  },
};
</script>