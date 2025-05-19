<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý voucher</h1>
    <div class="flex justify-between items-center">
      <FilterSearch
        :filters="filters"
        :searchPlaceholder="'Tìm kiếm theo mã voucher...'"
        v-model:currentFilter="currentFilter"
        v-model:searchQuery="searchQuery"
        @search="applySearch"
      />
      <button
        @click="openAddForm"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Thêm voucher
      </button>
    </div>
    <VoucherForm
      v-if="showFormModal"
      :voucher="editingVoucher"
      :shops="shops"
      :products="products"
      :shippingPartners="shippingPartners"
      :show="showFormModal"
      @close="showFormModal = false"
      @submit="handleVoucherFormSubmit"
    />
    <p v-if="filteredVouchers.length === 0" class="text-center text-gray-500">
      Không tìm thấy voucher nào.
    </p>
    <table v-else class="min-w-full table-auto border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th
            v-for="column in tableColumns"
            :key="column.key"
            class="px-4 py-2 border"
          >
            {{ column.label }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(voucher, index) in filteredVouchers"
          :key="voucher.id"
          class="hover:bg-gray-50 transition"
        >
          <td class="px-4 py-2 border text-center">{{ index + 1 }}</td>
          <td class="px-4 py-2 border text-center">{{ voucher.code || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center capitalize">{{ voucher.discount_type || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center">{{ formatDiscountValue(voucher) }}</td>
          <td class="px-4 py-2 border text-center capitalize">{{ voucher.voucher_type || 'N/A' }}</td>
          <td
            v-if="tableColumns.some(c => c.key === 'shop')"
            class="px-4 py-2 border text-center"
          >
            {{ getShopNames(voucher) }}
          </td>
          <td
            v-if="tableColumns.some(c => c.key === 'shipping')"
            class="px-4 py-2 border text-center"
          >
            {{ getShippingPartners(voucher) }}
          </td>
          <td
            v-if="tableColumns.some(c => c.key === 'product')"
            class="px-4 py-2 border text-center"
          >
            {{ getProductNames(voucher) }}
          </td>
          <td class="px-4 py-2 border text-center space-x-2">
            <button
              @click="openEditForm(voucher)"
              class="text-yellow-600 hover:underline"
            >
              Sửa
            </button>
            <button
              @click="openDetailsModal(voucher)"
              class="text-blue-600 hover:underline"
            >
              Chi tiết
            </button>
            <button
              @click="openConfirmModal('delete', voucher)"
              class="text-red-600 hover:underline"
            >
              Xóa
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <ConfirmModal
      :show="showConfirmModal"
      :title="confirmTitle"
      :message="confirmMessage"
      :confirmText="'Xác nhận'"
      :cancelText="'Hủy'"
      @confirm="handleConfirm"
      @cancel="showConfirmModal = false"
    />

    <VoucherDetailsModal
      :show="showDetailsModal"
      :voucher="selectedVoucher"
      @close="showDetailsModal = false"
    />
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';
import ConfirmModal from './component/AdminConfirmModal.vue';
import VoucherForm from './component/VoucherForm.vue';
import VoucherDetailsModal from './component/VoucherDetailsModal.vue';

export default {
  name: 'AdminVouchers',
  components: { FilterSearch, ConfirmModal, VoucherForm, VoucherDetailsModal },
  data() {
    return {
      vouchers: [],
      allVouchers: [],
      shops: [],
      products: [],
      shippingPartners: [],
      searchQuery: '',
      currentFilter: 'all',
      showConfirmModal: false,
      confirmAction: null,
      confirmVoucher: null,
      confirmTitle: 'Xác nhận',
      confirmMessage: '',
      showFormModal: false,
      editingVoucher: null,
      showDetailsModal: false,
      selectedVoucher: null,
    };
  },
  computed: {
    filters() {
      const typeFilters = [
        { key: 'all', label: 'Tất cả', count: this.allVouchers.length },
        ...['platform', 'shop', 'shipping', 'product'].map((type) => ({
          key: type,
          label: type.charAt(0).toUpperCase() + type.slice(1),
          count: this.filterCountByType(type),
        })),
      ];
      return typeFilters;
    },
    filteredVouchers() {
      const q = this.searchQuery.toLowerCase();
      return this.allVouchers.filter((voucher) => {
        const matchQuery =
          (voucher.code || '').toLowerCase().includes(q) ||
          (voucher.voucher_type || '').toLowerCase().includes(q) ||
          (voucher.shop_voucher?.some(sv => sv.shop?.shop_name?.toLowerCase().includes(q)) || false) ||
          (voucher.shipping_voucher?.shipping_partners?.some(sp => sp.shipping_partner?.name?.toLowerCase().includes(q)) || false) ||
          (voucher.products?.some(p => p.product?.name?.toLowerCase().includes(q)) || false);
        const matchFilter =
          this.currentFilter === 'all' || voucher.voucher_type === this.currentFilter;
        return matchQuery && matchFilter;
      });
    },
    tableColumns() {
      const baseColumns = [
        { key: 'stt', label: 'STT' },
        { key: 'code', label: 'Mã' },
        { key: 'discount_type', label: 'Loại giảm giá' },
        { key: 'discount_value', label: 'Giá trị' },
        { key: 'voucher_type', label: 'Loại voucher' },
        { key: 'actions', label: 'Hành động' },
      ];
      if (this.currentFilter === 'shop') {
        return [...baseColumns.slice(0, -1), { key: 'shop', label: 'Cửa hàng' }, baseColumns[baseColumns.length - 1]];
      } else if (this.currentFilter === 'shipping') {
        return [...baseColumns.slice(0, -1), { key: 'shipping', label: 'Đơn vị vận chuyển' }, baseColumns[baseColumns.length - 1]];
      } else if (this.currentFilter === 'product') {
        return [...baseColumns.slice(0, -1), { key: 'product', label: 'Sản phẩm' }, baseColumns[baseColumns.length - 1]];
      }
      return baseColumns;
    },
  },
  async mounted() {
    await Promise.all([
      this.fetchVouchers(),
      this.fetchShops(),
      this.fetchProducts(),
      this.fetchShippingPartners(),
    ]);
  },
  methods: {
    async fetchVouchers() {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get('http://localhost:8000/api/admin/vouchers', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.vouchers = response.data;
        this.allVouchers = response.data;
        console.log('Vouchers loaded:', this.vouchers);
      } catch (error) {
        console.error('Lỗi khi tải danh sách voucher:', error);
        alert('Không thể tải danh sách voucher.');
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    async fetchShops() {
      const token = localStorage.getItem('token');
      if (!token) {
        alert('Vui lòng đăng nhập để tải danh sách cửa hàng.');
        this.$router.push('/admin/login');
        return;
      }
      try {
        const response = await axios.get('http://localhost:8000/api/admin/shops', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.shops = response.data;
        console.log('Shops loaded:', this.shops);
        if (!this.shops.length) {
          console.warn('Danh sách cửa hàng rỗng.');
          alert('Không có cửa hàng nào trong hệ thống.');
        }
      } catch (error) {
        console.error('Lỗi khi tải danh sách cửa hàng:', error);
        console.error('Response:', error.response?.data);
        alert(`Không thể tải danh sách cửa hàng: ${error.response?.data?.message || error.message}`);
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    async fetchProducts() {
      const token = localStorage.getItem('token');
      if (!token) {
        alert('Vui lòng đăng nhập để tải danh sách sản phẩm.');
        this.$router.push('/admin/login');
        return;
      }
      try {
        const response = await axios.get('http://localhost:8000/api/admin/products', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.products = response.data;
        console.log('Products loaded:', this.products);
        if (!this.products.length) {
          console.warn('Danh sách sản phẩm rỗng.');
          alert('Không có sản phẩm nào trong hệ thống.');
        }
      } catch (error) {
        console.error('Lỗi khi tải danh sách sản phẩm:', error);
        alert(`Không thể tải danh sách sản phẩm: ${error.response?.data?.message || error.message}`);
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    async fetchShippingPartners() {
      const token = localStorage.getItem('token');
      if (!token) {
        alert('Vui lòng đăng nhập để tải danh sách đối tác vận chuyển.');
        this.$router.push('/admin/login');
        return;
      }
      try {
        const response = await axios.get('http://localhost:8000/api/admin/shipping-partners', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.shippingPartners = response.data;
        console.log('Shipping Partners loaded:', this.shippingPartners);
        if (!this.shippingPartners.length) {
          console.warn('Danh sách đối tác vận chuyển rỗng.');
          alert('Không có đối tác vận chuyển nào trong hệ thống.');
        }
      } catch (error) {
        console.error('Lỗi khi tải danh sách đối tác vận chuyển:', error);
        alert(`Không thể tải danh sách đối tác vận chuyển: ${error.response?.data?.message || error.message}`);
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    async handleVoucherFormSubmit(form) {
      const token = localStorage.getItem('token');
      try {
        const response = this.editingVoucher
          ? await axios.put(
              `http://localhost:8000/api/admin/vouchers/${this.editingVoucher.id}`,
              form,
              { headers: { Authorization: `Bearer ${token}` } }
            )
          : await axios.post(
              'http://localhost:8000/api/admin/vouchers',
              form,
              { headers: { Authorization: `Bearer ${token}` } }
            );
        if (this.editingVoucher) {
          const index = this.vouchers.findIndex(v => v.id === this.editingVoucher.id);
          this.vouchers[index] = response.data.voucher || response.data;
          this.allVouchers = [...this.vouchers];
        } else {
          this.vouchers.push(response.data);
          this.allVouchers = [...this.vouchers];
        }
        this.showFormModal = false;
        alert(this.editingVoucher ? 'Cập nhật voucher thành công' : 'Thêm voucher thành công');
      } catch (error) {
        console.error('Lỗi khi xử lý form voucher:', error);
        if (error.response?.status === 422) {
          const errors = error.response.data.errors;
          let errorMessage = 'Lỗi xác thực:\n';
          for (const field in errors) {
            errorMessage += `- ${field}: ${errors[field].join(', ')}\n`;
          }
          alert(errorMessage);
        } else {
          alert('Lỗi: ' + (error.response?.data?.message || error.message));
        }
      }
    },
    async deleteVoucher(voucherId) {
      const token = localStorage.getItem('token');
      try {
        await axios.delete(`http://localhost:8000/api/admin/vouchers/${voucherId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.vouchers = this.vouchers.filter((voucher) => voucher.id !== voucherId);
        this.allVouchers = this.vouchers;
        alert('Đã xóa voucher thành công');
      } catch (error) {
        console.error('Lỗi khi xóa voucher:', error);
        alert('Xóa voucher thất bại');
      }
    },
    openConfirmModal(action, voucher) {
      this.confirmAction = action;
      this.confirmVoucher = voucher;
      if (action === 'delete') {
        this.confirmTitle = 'Xác nhận xóa';
        this.confirmMessage = `Bạn có chắc chắn muốn xóa voucher "${voucher.code || 'N/A'}" không?`;
      }
      this.showConfirmModal = true;
    },
    async handleConfirm() {
      if (this.confirmAction === 'delete') {
        await this.deleteVoucher(this.confirmVoucher.id);
      }
      this.showConfirmModal = false;
      this.confirmAction = null;
      this.confirmVoucher = null;
      this.confirmTitle = 'Xác nhận';
      this.confirmMessage = '';
    },
    openAddForm() {
      this.editingVoucher = null;
      this.showFormModal = true;
    },
    openEditForm(voucher) {
      this.editingVoucher = {
        ...voucher,
        product_ids: voucher.products?.map(p => p.product_id) || [],
        shipping_partner_ids: voucher.shipping_voucher?.shipping_partners?.map(sp => sp.shipping_partner_id) || [],
        shop_voucher: voucher.shop_voucher || [],
      };
      this.showFormModal = true;
    },
    openDetailsModal(voucher) {
      this.selectedVoucher = voucher;
      this.showDetailsModal = true;
    },
    applySearch() {
      console.log('Apply Voucher Search:', this.searchQuery);
    },
    filterCountByType(type) {
      return this.allVouchers.filter((voucher) => voucher.voucher_type === type).length;
    },
    formatDiscountValue(voucher) {
      if (!voucher) return 'N/A';
      return voucher.discount_type === 'percentage'
        ? `${voucher.discount_value}%`
        : `${voucher.discount_value.toLocaleString('vi-VN')} VNĐ`;
    },
    getShopNames(voucher) {
      return voucher.shop_voucher?.map(sv => sv.shop?.shop_name).filter(name => name).join(', ') || 'N/A';
    },
    getShippingPartners(voucher) {
      return voucher.shipping_voucher?.shipping_partners?.map(sp => sp.shipping_partner?.name).filter(name => name).join(', ') || 'N/A';
    },
    getProductNames(voucher) {
      return voucher.products?.map(p => p.product?.name).filter(name => name).join(', ') || 'N/A';
    },
  },
};
</script>

<style scoped>
.transition {
  transition: background-color 0.3s ease;
}
</style>