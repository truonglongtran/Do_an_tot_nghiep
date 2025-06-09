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
        v-if="hasPermission('create')"
        @click="openAddForm"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Thêm voucher
      </button>
    </div>
    <FormModal
      v-if="showFormModal"
      :show="showFormModal"
      :title="editingVoucher ? 'Sửa Voucher' : 'Thêm Voucher'"
      :fields="voucherFormFields"
      :initialData="formData"
      :isEdit="!!editingVoucher"
      @close="closeFormModal"
      @submit="handleVoucherFormSubmit"
      ref="formModal"
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
              v-if="hasPermission('update')"
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
              v-if="hasPermission('delete')"
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

    <GenericDetailsModal
      :show="showDetailsModal"
      :data="selectedVoucher"
      :fields="voucherDetailFields"
      title="Chi tiết Voucher"
      @close="showDetailsModal = false"
    />
  </div>
</template>

<script>
import axios from 'axios';
import { useRouter } from 'vue-router';
import FilterSearch from './component/AdminFilterSearch.vue';
import ConfirmModal from './component/AdminConfirmModal.vue';
import FormModal from './component/FormModal.vue';
import GenericDetailsModal from './component/GenericDetailsModal.vue';

export default {
  name: 'AdminVouchers',
  components: { FilterSearch, ConfirmModal, FormModal, GenericDetailsModal },
  setup() {
    const router = useRouter();
    return { router };
  },
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
      formData: {},
    };
  },
  computed: {
    voucherFormFields() {
      return [
        {
          name: 'code',
          label: 'Mã voucher',
          type: 'text',
          required: true,
          placeholder: 'Nhập mã voucher',
        },
        {
          name: 'voucher_type',
          label: 'Loại voucher',
          type: 'select',
          options: [
            { value: 'platform', label: 'Platform' },
            { value: 'shop', label: 'Shop' },
            { value: 'product', label: 'Product' },
            { value: 'shipping', label: 'Shipping' },
          ],
          required: true,
          placeholder: 'Chọn loại voucher',
          onChange: (value) => {
            console.log('Voucher type changed to:', value);
            this.resetTypeSpecificFields(value);
          },
        },
        {
          name: 'shop_ids',
          label: 'Cửa hàng',
          type: 'multiselect',
          options: this.shops.map(shop => ({ value: shop.id, label: shop.shop_name })),
          labelKey: 'label',
          trackBy: 'value',
          placeholder: 'Chọn cửa hàng',
          required: true,
          noResultText: 'Không tìm thấy cửa hàng',
          emptyOptionsText: 'Không có cửa hàng nào được tải',
        },
        {
          name: 'product_ids',
          label: 'Sản phẩm',
          type: 'multiselect',
          options: this.products.map(product => ({ value: product.id, label: product.name })),
          labelKey: 'label',
          trackBy: 'value',
          placeholder: 'Chọn sản phẩm',
          required: true,
          noResultText: 'Không tìm thấy sản phẩm',
          emptyOptionsText: 'Không có sản phẩm nào được tải',
        },
        {
          name: 'shipping_only',
          label: 'Chỉ áp dụng phí vận chuyển',
          type: 'checkbox',
          defaultValue: false,
        },
        {
          name: 'shipping_partner_ids',
          label: 'Đối tác vận chuyển',
          type: 'checkbox-group',
          options: this.shippingPartners.map(partner => ({
            value: partner.id,
            label: partner.name,
          })),
          defaultValue: [],
          required: false,
        },
        {
          name: 'discount_type',
          label: 'Loại giảm giá',
          type: 'select',
          options: [
            { value: 'percentage', label: 'Phần trăm' },
            { value: 'fixed', label: 'Cố định' },
          ],
          required: true,
          placeholder: 'Chọn loại giảm giá',
        },
        {
          name: 'discount_value',
          label: 'Giá trị giảm giá',
          type: 'number',
          required: true,
          defaultValue: 0,
          placeholder: 'Nhập giá trị giảm giá',
        },
        {
          name: 'min_order_amount',
          label: 'Số tiền đơn hàng tối thiểu',
          type: 'number',
          required: true,
          defaultValue: 0,
          placeholder: 'Nhập số tiền tối thiểu',
        },
        {
          name: 'usage_limit',
          label: 'Giới hạn sử dụng',
          type: 'number',
          required: true,
          defaultValue: 0,
          placeholder: 'Nhập giới hạn sử dụng (0 = không giới hạn)',
        },
        {
          name: 'start_date',
          label: 'Ngày bắt đầu',
          type: 'date',
          required: true,
        },
        {
          name: 'end_date',
          label: 'Ngày kết thúc',
          type: 'date',
          required: true,
        },
      ];
    },
    voucherDetailFields() {
      const baseFields = [
        {
          label: 'Mã voucher',
          key: 'code',
          type: 'text',
        },
        {
          label: 'Loại voucher',
          key: 'voucher_type',
          type: 'text',
          customFormat: (value) => value ? value.charAt(0).toUpperCase() + value.slice(1) : 'Không có',
        },
        {
          label: 'Loại giảm giá',
          key: 'discount_type',
          type: 'text',
          customFormat: (value) => value === 'percentage' ? 'Phần trăm' : value === 'fixed' ? 'Cố định' : 'Không có',
        },
        {
          label: 'Giá trị giảm giá',
          key: 'discount_value',
          type: 'text',
          customFormat: (value, data) => {
            if (!value || !data.discount_type) return 'Không có';
            return data.discount_type === 'percentage'
              ? `${value}%`
              : `${value.toLocaleString('vi-VN')} VNĐ`;
          },
        },
        {
          label: 'Số tiền đơn hàng tối thiểu',
          key: 'min_order_amount',
          type: 'text',
          customFormat: (value) => value ? `${value.toLocaleString('vi-VN')} VNĐ` : 'Không có',
        },
        {
          label: 'Giới hạn sử dụng',
          key: 'usage_limit',
          type: 'text',
          customFormat: (value) => value === 0 ? 'Không giới hạn' : value.toString(),
        },
        {
          label: 'Số lần đã sử dụng',
          key: 'used_count',
          type: 'text',
          customFormat: (value) => value != null ? value.toString() : '0',
        },
        {
          label: 'Ngày bắt đầu',
          key: 'start_date',
          type: 'date',
        },
        {
          label: 'Ngày kết thúc',
          key: 'end_date',
          type: 'date',
        },
      ];

      const typeSpecificFields = [];
      if (this.selectedVoucher && this.selectedVoucher.voucher_type) {
        const voucherType = this.selectedVoucher.voucher_type;
        if (voucherType === 'shop') {
          typeSpecificFields.push({
            label: 'Cửa hàng',
            key: 'shop_voucher',
            type: 'list',
            listItemKey: 'shop.shop_name',
          });
        } else if (voucherType === 'product') {
          typeSpecificFields.push({
            label: 'Sản phẩm',
            key: 'products',
            type: 'list',
            listItemKey: 'product.name',
          });
        } else if (voucherType === 'shipping') {
          typeSpecificFields.push(
            {
              label: 'Đối tác vận chuyển',
              key: 'shipping_voucher.shipping_partners',
              type: 'list',
              listItemKey: 'shipping_partner.name',
            },
            {
              label: 'Chỉ áp dụng phí vận chuyển',
              key: 'shipping_voucher.shipping_only',
              type: 'boolean',
              customFormat: (value) => value ? 'Có' : 'Không',
            }
          );
        }
      }

      return [...baseFields, ...typeSpecificFields];
    },
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
          (voucher.shop_voucher?.some((sv) => sv.shop?.shop_name?.toLowerCase().includes(q)) || false) ||
          (voucher.shipping_voucher?.shipping_partners?.some((sp) => sp.shipping_partner?.name?.toLowerCase().includes(q)) || false) ||
          (voucher.products?.some((p) => p.product?.name?.toLowerCase().includes(q)) || false);
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
    console.log('Mounted AdminVouchers, Role:', localStorage.getItem('role'));
    console.log('Has view permission:', this.hasPermission('view'));
    if (!this.hasPermission('view')) {
      console.warn('Không có quyền view, chuyển hướng về dashboard');
      this.$router.push('/admin/dashboard');
      return;
    }
    await Promise.all([
      this.fetchVouchers(),
      this.fetchShops(),
      this.fetchProducts(),
      this.fetchShippingPartners(),
    ]);
  },
  methods: {
    hasPermission(action) {
      const role = localStorage.getItem('role');
      const matchedRoute = this.router.getRoutes().find((r) => r.path === '/admin/vouchers');
      if (!matchedRoute || !matchedRoute.meta || !matchedRoute.meta.permissions) {
        console.warn('Không tìm thấy meta.permissions cho /admin/vouchers');
        return false;
      }
      const hasPermission = matchedRoute.meta.permissions[role]?.includes(action) || false;
      console.log(`Quyền ${action} cho role ${role}:`, hasPermission);
      return hasPermission;
    },
    formatDateForInput(dateStr) {
      if (!dateStr) return '';
      try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return '';
        return date.toISOString().split('T')[0];
      } catch (e) {
        console.error('Error parsing date:', dateStr, e);
        return '';
      }
    },
    async fetchVouchers() {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get('/admin/vouchers', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.vouchers = response.data;
        this.allVouchers = response.data;
        console.log('Vouchers loaded:', this.vouchers);
        this.vouchers.forEach(v => console.log(`Voucher ${v.code}: start_date=${v.start_date}, end_date=${v.end_date}`));
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
        const response = await axios.get('/admin/shops', {
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
        const response = await axios.get('/admin/products', {
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
        const response = await axios.get('/admin/shipping-partners', {
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
      if (!this.hasPermission(this.editingVoucher ? 'update' : 'create')) {
        alert(`Bạn không có quyền ${this.editingVoucher ? 'sửa' : 'tạo'} voucher.`);
        return;
      }
      console.log('Submitting form:', form, 'Editing:', !!this.editingVoucher);
      try {
        const response = this.editingVoucher
          ? await axios.put(
              `/admin/vouchers/${this.editingVoucher.id}`,
              form,
              { headers: { Authorization: `Bearer ${token}` } }
            )
          : await axios.post(
              '/admin/vouchers',
              form,
              { headers: { Authorization: `Bearer ${token}` } }
            );
        if (this.editingVoucher) {
          const index = this.vouchers.findIndex((v) => v.id === this.editingVoucher.id);
          this.vouchers[index] = response.data.voucher || response.data;
          this.allVouchers = [...this.vouchers];
        } else {
          this.vouchers.push(response.data);
          this.allVouchers = [...this.vouchers];
        }
        this.closeFormModal();
        alert(
          this.editingVoucher ? 'Cập nhật voucher thành công' : 'Thêm voucher thành công'
        );
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
      if (!this.hasPermission('delete')) {
        alert('Bạn không có quyền xóa voucher.');
        return;
      }
      try {
        await axios.delete(`/admin/vouchers/${voucherId}`, {
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
      if (action === 'delete' && !this.hasPermission('delete')) {
        alert('Bạn không có quyền xóa voucher.');
        return;
      }
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
      if (!this.hasPermission('create')) {
        alert('Bạn không có quyền tạo voucher.');
        return;
      }
      if (!this.shops.length || !this.products.length || !this.shippingPartners.length) {
        alert('Vui lòng đợi dữ liệu cửa hàng, sản phẩm và đối tác vận chuyển được tải.');
        return;
      }
      console.log('Opening add form');
      this.editingVoucher = null;
      this.formData = {};
      this.showFormModal = true;
      this.$refs.formModal.initializeForm(this.formData);
    },
    openEditForm(voucher) {
      if (!this.hasPermission('update')) {
        alert('Bạn không có quyền sửa voucher.');
        return;
      }
      if (!this.shops.length || !this.products.length || !this.shippingPartners.length) {
        alert('Vui lòng đợi dữ liệu cửa hàng, sản phẩm và đối tác vận chuyển được tải.');
        return;
      }
      console.log('Opening edit form for voucher:', voucher);
      this.editingVoucher = {
        id: voucher.id,
        code: voucher.code || '',
        voucher_type: voucher.voucher_type || 'platform',
        shop_ids: voucher.shop_voucher?.map((sv) => sv.shop_id) || [],
        product_ids: voucher.products?.map((p) => p.product_id) || [],
        shipping_partner_ids:
          voucher.shipping_voucher?.shipping_partners?.map((sp) => sp.shipping_partner_id) || [],
        shipping_only: voucher.shipping_voucher?.shipping_only || false,
        discount_type: voucher.discount_type || 'fixed',
        discount_value: voucher.discount_value || 0,
        min_order_amount: voucher.min_order_amount || 0,
        usage_limit: voucher.usage_limit || 0,
        start_date: this.formatDateForInput(voucher.start_date),
        end_date: this.formatDateForInput(voucher.end_date),
      };
      this.formData = { ...this.editingVoucher };
      this.showFormModal = true;
      this.$refs.formModal.initializeForm(this.formData);
    },
    closeFormModal() {
      console.log('Closing form modal');
      this.showFormModal = false;
      this.editingVoucher = null;
      this.formData = {};
      this.$refs.formModal.initializeForm(this.formData);
    },
    openDetailsModal(voucher) {
      console.log('Opening details modal for voucher:', voucher);
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
      return (
        voucher.shop_voucher?.map((sv) => sv.shop?.shop_name).filter((name) => name).join(', ') ||
        'N/A'
      );
    },
    getShippingPartners(voucher) {
      return (
        voucher.shipping_voucher?.shipping_partners
          ?.map((sp) => sp.shipping_partner?.name)
          .filter((name) => name)
          .join(', ') || 'N/A'
      );
    },
    getProductNames(voucher) {
      return (
        voucher.products?.map((p) => p.product?.name).filter((name) => name).join(', ') || 'N/A'
      );
    },
    resetTypeSpecificFields(voucherType) {
      console.log('Resetting fields for voucher type:', voucherType);
      const resetData = {
        shop_ids: [],
        product_ids: [],
        shipping_partner_ids: [],
        shipping_only: false,
        voucher_type: voucherType || 'platform',
        code: this.formData.code || '',
        discount_type: this.formData.discount_type || 'fixed',
        discount_value: this.formData.discount_value || 0,
        min_order_amount: this.formData.min_order_amount || 0,
        usage_limit: this.formData.usage_limit || 0,
        start_date: this.formData.start_date || '',
        end_date: this.formData.end_date || '',
      };
      this.formData = { ...this.formData, ...resetData };
      this.$refs.formModal.initializeForm(this.formData);
    },
  },
};
</script>

<style scoped>
.transition {
  transition: background-color 0.3s ease;
}
</style>