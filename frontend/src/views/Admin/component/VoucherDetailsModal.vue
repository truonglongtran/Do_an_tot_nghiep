<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
      <h2 class="text-xl font-bold mb-4">Chi tiết voucher</h2>
      <div class="space-y-4">
        <!-- Các trường chung -->
        <p><strong>ID:</strong> {{ voucher?.id || 'Không có' }}</p>
        <p><strong>Mã:</strong> {{ voucher?.code || 'Không có' }}</p>
        <p>
          <strong>Loại giảm giá:</strong>
          {{ voucher?.discount_type ? voucher.discount_type.charAt(0).toUpperCase() + voucher.discount_type.slice(1) : 'Không có' }}
        </p>
        <p><strong>Giá trị giảm:</strong> {{ formatDiscountValue(voucher) }}</p>
        <p>
          <strong>Đơn hàng tối thiểu:</strong>
          {{ voucher?.min_order_amount ? voucher.min_order_amount.toLocaleString('vi-VN') + ' VNĐ' : 'Không có' }}
        </p>
        <p><strong>Ngày bắt đầu:</strong> {{ formatDate(voucher?.start_date) || 'Không có' }}</p>
        <p><strong>Ngày kết thúc:</strong> {{ formatDate(voucher?.end_date) || 'Không có' }}</p>
        <p><strong>Giới hạn sử dụng:</strong> {{ voucher?.usage_limit || '0' }}</p>
        <p><strong>Số lần đã sử dụng:</strong> {{ voucher?.used_count || '0' }}</p>
        <p>
          <strong>Loại voucher:</strong>
          {{ voucher?.voucher_type ? voucher.voucher_type.charAt(0).toUpperCase() + voucher.voucher_type.slice(1) : 'Không có' }}
        </p>

        <!-- Trường riêng cho shop -->
        <div v-if="voucher?.voucher_type === 'shop' && voucher?.shop_voucher?.length">
          <p><strong>Cửa hàng áp dụng:</strong></p>
          <ul class="list-disc pl-5">
            <li v-for="shop in voucher.shop_voucher" :key="shop.shop_id">
              {{ shop.shop?.name || shop.shop?.shop_name || 'Không có tên cửa hàng' }}
            </li>
          </ul>
        </div>

        <!-- Trường riêng cho product -->
        <div v-if="voucher?.voucher_type === 'product' && voucher?.products?.length">
          <p><strong>Sản phẩm áp dụng:</strong></p>
          <ul class="list-disc pl-5">
            <li v-for="product in voucher.products" :key="product.product_id">
              {{ product.product?.name || 'Không có tên sản phẩm' }}
            </li>
          </ul>
        </div>

        <!-- Trường riêng cho shipping -->
        <div v-if="voucher?.voucher_type === 'shipping'">
          <p>
            <strong>Chỉ áp dụng cho phí vận chuyển:</strong>
            {{ voucher?.shipping_voucher?.shipping_only ? 'Có' : 'Không' }}
          </p>
          <div v-if="voucher?.shipping_voucher?.shipping_partners?.length">
            <p><strong>Đối tác vận chuyển áp dụng:</strong></p>
            <ul class="list-disc pl-5">
              <li v-for="partner in voucher.shipping_voucher.shipping_partners" :key="partner.shipping_partner_id">
                {{ partner.shipping_partner?.name || 'Không có tên đối tác' }}
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="mt-6 flex justify-end">
        <button
          @click="$emit('close')"
          class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
        >
          Đóng
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VoucherDetailsModal',
  props: {
    show: Boolean,
    voucher: Object,
  },
  mounted() {
    console.log('VoucherDetailsModal - Voucher:', this.voucher);
    if (this.voucher?.voucher_type === 'shop') {
      console.log('Shop Voucher Data:', this.voucher.shop_voucher);
    }
  },
  methods: {
    formatDate(date) {
      if (!date) return 'Không có';
      return new Date(date).toLocaleString('vi-VN');
    },
    formatDiscountValue(voucher) {
      if (!voucher) return 'Không có';
      return voucher.discount_type === 'percentage'
        ? `${voucher.discount_value}%`
        : `${voucher.discount_value.toLocaleString('vi-VN')} VNĐ`;
    },
  },
};
</script>