<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-2xl max-h-[80vh] overflow-y-auto">
      <h2 class="text-xl font-bold mb-4">{{ editingVoucher ? 'Sửa Voucher' : 'Thêm Voucher' }}</h2>
      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Mã voucher</label>
          <input
            v-model="form.code"
            type="text"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3 py-2 text-sm"
            required
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Loại voucher</label>
          <select
            v-model="form.voucher_type"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3 py-2 text-sm"
            required
            @change="resetTypeSpecificFields"
          >
            <option value="platform">Platform</option>
            <option value="shop">Shop</option>
            <option value="product">Product</option>
            <option value="shipping">Shipping</option>
          </select>
        </div>
        <div v-if="form.voucher_type === 'shop'" class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Cửa hàng</label>
          <multiselect
            v-model="selectedShops"
            :options="shops"
            :multiple="true"
            :close-on-select="false"
            :clear-on-select="false"
            :preserve-search="true"
            placeholder="Chọn cửa hàng"
            label="shop_name"
            track-by="id"
            :required="form.voucher_type === 'shop'"
            class="mt-1 relative"
            @input="updateShopIds"
          >
            <template #noResult>Không tìm thấy cửa hàng.</template>
          </multiselect>
          <p v-if="!shops.length" class="text-red-500 text-sm mt-1">Không có cửa hàng nào được tải.</p>
        </div>
        <div v-if="form.voucher_type === 'product'" class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Sản phẩm</label>
          <multiselect
            v-model="selectedProducts"
            :options="products"
            :multiple="true"
            :close-on-select="false"
            :clear-on-select="false"
            :preserve-search="true"
            placeholder="Chọn sản phẩm"
            label="name"
            track-by="id"
            :required="form.voucher_type === 'product'"
            class="mt-1 relative"
            @input="updateProductIds"
          >
            <template #noResult>Không tìm thấy sản phẩm.</template>
          </multiselect>
          <p v-if="!products.length" class="text-red-500 text-sm mt-1">Không có sản phẩm nào được tải.</p>
        </div>
        <div v-if="form.voucher_type === 'shipping'" class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Chỉ áp dụng phí vận chuyển</label>
          <input
            v-model="form.shipping_only"
            type="checkbox"
            class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
        </div>
        <div v-if="form.voucher_type === 'shipping'" class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Đối tác vận chuyển (tùy chọn)</label>
          <div class="mt-1 max-h-48 overflow-y-auto border border-gray-300 rounded-md p-2">
            <label v-for="partner in shippingPartners" :key="partner.id" class="flex items-center">
              <input
                type="checkbox"
                :value="partner.id"
                v-model="form.shipping_partner_ids"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <span class="ml-2 text-sm text-gray-700">{{ partner.name || 'N/A' }}</span>
            </label>
          </div>
          <p v-if="!shippingPartners.length" class="text-red-500 text-sm mt-1">Không có đối tác vận chuyển nào được tải.</p>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Loại giảm giá</label>
          <select
            v-model="form.discount_type"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3 py-2 text-sm"
            required
          >
            <option value="percentage">Phần trăm</option>
            <option value="fixed">Cố định</option>
          </select>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Giá trị giảm giá</label>
          <input
            v-model.number="form.discount_value"
            type="number"
            min="0"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3 py-2 text-sm"
            required
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Số tiền đơn hàng tối thiểu</label>
          <input
            v-model.number="form.min_order_amount"
            type="number"
            min="0"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3 py-2 text-sm"
            required
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Giới hạn sử dụng</label>
          <input
            v-model.number="form.usage_limit"
            type="number"
            min="0"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3 py-2 text-sm"
            required
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Ngày bắt đầu</label>
          <input
            v-model="form.start_date"
            type="date"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3 py-2 text-sm"
            required
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Ngày kết thúc</label>
          <input
            v-model="form.end_date"
            type="date"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3 py-2 text-sm"
            required
          />
        </div>
        <div class="flex justify-end space-x-2">
          <button
            type="button"
            @click="$emit('close')"
            class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400"
          >
            Hủy
          </button>
          <button
            type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          >
            {{ editingVoucher ? 'Cập nhật' : 'Thêm' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VoucherForm',
  props: {
    show: Boolean,
    voucher: Object,
    shops: Array,
    products: Array,
    shippingPartners: Array,
  },
  data() {
    return {
      form: {
        code: '',
        voucher_type: 'platform',
        shop_ids: [],
        product_ids: [],
        shipping_partner_ids: [],
        shipping_only: false,
        discount_type: 'percentage',
        discount_value: 0,
        min_order_amount: 0,
        usage_limit: 0,
        start_date: '',
        end_date: '',
      },
      selectedShops: [],
      selectedProducts: [],
    };
  },
  watch: {
      voucher: {
          immediate: true,
          handler(newVoucher) {
              if (newVoucher) {
                  this.form = {
                      code: newVoucher.code || '',
                      voucher_type: newVoucher.voucher_type || 'platform',
                      shop_ids: newVoucher.shop_voucher?.map(sv => sv.shop_id) || [],
                      product_ids: newVoucher.products?.map(p => p.product_id) || [],
                      shipping_partner_ids: newVoucher.shipping_voucher?.shipping_partners?.map(sp => sp.shipping_partner_id) || [],
                      shipping_only: newVoucher.shipping_voucher?.shipping_only || false,
                      discount_type: newVoucher.discount_type || 'percentage',
                      discount_value: newVoucher.discount_value || 0,
                      min_order_amount: newVoucher.min_order_amount || 0,
                      usage_limit: newVoucher.usage_limit || 0,
                      start_date: newVoucher.start_date ? newVoucher.start_date.split(' ')[0] : '',
                      end_date: newVoucher.end_date ? newVoucher.end_date.split(' ')[0] : '',
                  };
                  this.selectedShops = this.shops.filter(s => this.form.shop_ids.includes(s.id));
                  this.selectedProducts = this.products.filter(p => this.form.product_ids.includes(p.id));
              } else {
                  this.resetForm();
              }
          },
      },
      selectedShops: {
          handler() {
              this.updateShopIds();
          },
          deep: true,
      },
      selectedProducts: {
          handler() {
              this.updateProductIds();
          },
          deep: true,
      },
  },
  mounted() {
    console.log('VoucherForm mounted - Shops:', this.shops);
    console.log('VoucherForm mounted - Products:', this.products);
    console.log('VoucherForm mounted - ShippingPartners:', this.shippingPartners);
  },
  methods: {
    resetForm() {
      this.form = {
        code: '',
        voucher_type: 'platform',
        shop_ids: [],
        product_ids: [],
        shipping_partner_ids: [],
        shipping_only: false,
        discount_type: 'percentage',
        discount_value: 0,
        min_order_amount: 0,
        usage_limit: 0,
        start_date: '',
        end_date: '',
      };
      this.selectedShops = [];
      this.selectedProducts = [];
    },
    resetTypeSpecificFields() {
      this.form.shop_ids = [];
      this.form.product_ids = [];
      this.form.shipping_partner_ids = [];
      this.form.shipping_only = false;
      this.selectedShops = [];
      this.selectedProducts = [];
    },
    updateShopIds() {
        this.form.shop_ids = this.selectedShops.map(shop => shop.id).filter(id => id !== undefined && id !== null);
        console.log('updateShopIds - Selected Shops:', this.selectedShops);
        console.log('updateShopIds - Updated shop_ids:', this.form.shop_ids);
    },

    updateProductIds() {
        this.form.product_ids = this.selectedProducts.map(product => product.id).filter(id => id !== undefined && id !== null);
        console.log('updateProductIds - Selected Products:', this.selectedProducts);
        console.log('updateProductIds - Updated product_ids:', this.form.product_ids);
    },

    submitForm() {
        console.log('submitForm - Selected Shops:', this.selectedShops);
        console.log('submitForm - form.shop_ids:', this.form.shop_ids);
        console.log('submitForm - Selected Products:', this.selectedProducts);
        console.log('submitForm - form.product_ids:', this.form.product_ids);
        if (!this.form.code) {
            alert('Vui lòng nhập mã voucher.');
            return;
        }
        if (!this.form.voucher_type) {
            alert('Vui lòng chọn loại voucher.');
            return;
        }
        if (this.form.voucher_type === 'shop' && !this.form.shop_ids.length) {
            alert('Vui lòng chọn ít nhất một cửa hàng.');
            return;
        }
        if (this.form.voucher_type === 'product' && !this.form.product_ids.length) {
            alert('Vui lòng chọn ít nhất một sản phẩm.');
            return;
        }

        const payload = {
            code: this.form.code,
            voucher_type: this.form.voucher_type,
            discount_type: this.form.discount_type,
            discount_value: this.form.discount_value,
            min_order_amount: this.form.min_order_amount,
            usage_limit: this.form.usage_limit,
            start_date: this.form.start_date,
            end_date: this.form.end_date,
        };

        if (this.form.voucher_type === 'shop') {
            payload.shop_ids = this.form.shop_ids;
        }
        if (this.form.voucher_type === 'product') {
            payload.product_ids = this.form.product_ids;
        }
        if (this.form.voucher_type === 'shipping') {
            payload.shipping_only = this.form.shipping_only;
            payload.shipping_partner_ids = this.form.shipping_partner_ids;
        }

        console.log('Submitting voucher form:', payload);
        this.$emit('submit', payload);
    }
  },
};
</script>

<style scoped>
.max-h-[80vh] {
  max-height: 80vh;
}
.max-h-48 {
  max-height: 12rem;
}
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #9ca3af #f1f5f9;
}
.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #9ca3af;
  border-radius: 4px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>