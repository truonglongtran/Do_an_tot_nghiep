<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Giỏ hàng</h1>
    <div v-if="loading" class="text-center">
      <svg class="animate-spin w-8 h-8 mx-auto text-orange-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
    </div>
    <div v-else-if="error" class="text-red-500 text-center mb-4">
      {{ error }}
    </div>
    <div v-else-if="carts.length === 0" class="text-center text-gray-600">
      Giỏ hàng trống
    </div>
    <div v-else class="space-y-8">
      <!-- Nhóm sản phẩm theo shop -->
      <div v-for="(shopItems, shopId) in groupedCarts" :key="shopId" class="border rounded-lg p-4">
        <!-- Tiêu đề shop -->
        <div class="flex items-center space-x-2 mb-4">
          <input
            type="checkbox"
            :id="'shop-' + shopId"
            v-model="selectedShops[shopId]"
            @change="toggleShopSelection(shopId)"
            class="h-5 w-5 text-orange-500"
          />
          <label :for="'shop-' + shopId" class="text-lg font-semibold text-gray-700">
            {{ shopItems[0].product_variant?.product?.shop?.shop_name || 'Cửa hàng không xác định' }}
          </label>
        </div>
        <!-- Sản phẩm trong shop -->
        <div class="space-y-4">
          <div v-for="cart in shopItems" :key="cart.id" class="flex items-center space-x-4 border-t pt-4">
            <input
              type="checkbox"
              :id="'cart-' + cart.id"
              v-model="selectedItems[cart.id]"
              @change="updateShopSelection(shopId)"
              class="h-5 w-5 text-orange-500"
            />
            <img
              :src="cart.product_variant?.image_url || 'https://via.placeholder.com/100'"
              :alt="cart.product_variant?.product?.name || 'Sản phẩm không xác định'"
              class="w-20 h-20 object-cover rounded"
            />
            <div class="flex-1">
              <p class="font-semibold">{{ cart.product_variant?.product?.name || 'Sản phẩm không xác định' }}</p>
              <p class="text-gray-600">Cửa hàng: {{ cart.product_variant?.product?.shop?.shop_name || 'Không xác định' }}</p>
              <p class="text-orange-500">{{ formatPrice(cart.product_variant?.price || 0) }}đ</p>
            </div>
            <div class="flex items-center space-x-2">
              <button
                type="button"
                @click="updateQuantity($event, cart.id, cart.quantity - 1)"
                :disabled="cart.quantity <= 1"
                class="px-2 py-1 bg-gray-200"
              >
                -
              </button>
              <span>{{ cart.quantity }}</span>
              <button
                type="button"
                @click="updateQuantity($event, cart.id, cart.quantity + 1)"
                :disabled="cart.product_variant?.stock <= cart.quantity"
                class="px-2 py-1 bg-gray-200"
              >
                +
              </button>
              <button
                type="button"
                @click="removeFromCart($event, cart.id)"
                class="text-red-500 hover:underline"
              >
                Xóa
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- Footer: Chọn tất cả và thanh toán -->
      <div class="mt-4 flex justify-between items-center border-t pt-4">
        <div class="flex items-center space-x-2">
          <input
            type="checkbox"
            id="select-all"
            v-model="selectAll"
            @change="toggleSelectAll"
            class="h-5 w-5 text-orange-500"
          />
          <label for="select-all" class="text-gray-700">Chọn tất cả</label>
        </div>
        <div class="text-right relative">
          <p class="text-lg font-semibold flex items-center">
            Tổng thanh toán ({{ selectedItemsCount }} sản phẩm):
            <span class="text-orange-500 ml-1">{{ formatPrice(totalPrice) }}đ</span>
            <button
              type="button"
              @click.stop="togglePriceDetails"
              class="ml-2 text-gray-600 hover:text-orange-500"
            >
              <svg class="w-5 h-5" :class="{ 'rotate-180': showPriceDetails }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </p>
          <!-- Dropdown chi tiết giá -->
          <div
            v-if="showPriceDetails"
            class="absolute right-0 mt-2 w-80 bg-white border rounded-lg shadow-lg p-4 z-[60] popup-with-arrow"
            @click.stop
          >
            <h3 class="text-lg font-bold text-orange-500 mb-2">Chi tiết giá</h3>
            <div v-if="selectedItemsCount === 0" class="text-center text-gray-600">
              Chưa chọn sản phẩm nào
            </div>
            <div v-else class="space-y-2 max-h-64 overflow-y-auto custom-scrollbar">
              <div v-for="cart in selectedCarts" :key="cart.id" class="border-b pb-2">
                <p class="font-semibold text-sm truncate">{{ cart.product_variant?.product?.name || 'Sản phẩm không xác định' }}</p>
                <p class="text-gray-600 text-xs">
                  {{ formatPrice(cart.product_variant?.price || 0) }}đ x {{ cart.quantity }} = {{ formatPrice((cart.product_variant?.price || 0) * cart.quantity) }}đ
                </p>
              </div>
            </div>
            <p class="text-right font-semibold mt-2">
              Tổng: <span class="text-orange-500">{{ formatPrice(totalPrice) }}đ</span>
            </p>
          </div>
          <button
            type="button"
            @click="checkout($event)"
            :disabled="selectedItemsCount === 0"
            class="mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg disabled:bg-gray-300"
          >
            Mua hàng
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Cart',
  data() {
    return {
      carts: [],
      selectedItems: {},
      selectedShops: {},
      selectAll: false,
      showPriceDetails: false,
      loading: false,
      error: null,
    };
  },
  computed: {
    groupedCarts() {
      return this.carts.reduce((acc, cart) => {
        const shopId = cart.product_variant?.product?.shop?.id || 'unknown';
        if (!acc[shopId]) {
          acc[shopId] = [];
        }
        acc[shopId].push(cart);
        return acc;
      }, {});
    },
    selectedItemsCount() {
      return Object.values(this.selectedItems).filter(Boolean).length;
    },
    selectedCarts() {
      return this.carts.filter(cart => this.selectedItems[cart.id]);
    },
    totalPrice() {
      console.log('Calculating totalPrice, selectedItems:', this.selectedItems);
      const total = Object.keys(this.selectedItems).reduce((sum, cartId) => {
        if (this.selectedItems[cartId]) {
          const cart = this.carts.find(c => c.id === Number(cartId));
          if (cart) {
            const price = Number(cart.product_variant?.price || 0);
            const quantity = Number(cart.quantity || 1);
            console.log(`Cart ${cartId}: price=${price}, quantity=${quantity}, subtotal=${price * quantity}`);
            return sum + price * quantity;
          }
        }
        return sum;
      }, 0);
      console.log('Total price:', total);
      return total;
    },
  },
  async created() {
    await this.fetchCart();
    document.addEventListener('click', this.closePriceDetails);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.closePriceDetails);
  },
  methods: {
    async fetchCart() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/buyer/cart', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Cart API response:', response.data);
        this.carts = response.data.carts || [];
        this.selectedItems = this.carts.reduce((acc, cart) => {
          acc[cart.id] = false;
          return acc;
        }, {});
        this.selectedShops = Object.keys(this.groupedCarts).reduce((acc, shopId) => {
          acc[shopId] = false;
          return acc;
        }, {});
        console.log('Initialized selectedItems:', this.selectedItems);
        console.log('Initialized selectedShops:', this.selectedShops);
      } catch (error) {
        console.error('Error fetching cart:', error.response?.data || error.message);
        this.error = error.response?.data?.error || 'Lỗi tải giỏ hàng. Vui lòng thử lại.';
      } finally {
        this.loading = false;
      }
    },
    async updateQuantity(event, id, quantity) {
      event.preventDefault();
      if (quantity < 1) return;

      const cart = this.carts.find(c => c.id === id);
      if (cart && cart.product_variant && quantity > cart.product_variant.stock) {
        this.error = 'Số lượng vượt quá tồn kho';
        return;
      }

      const index = this.carts.findIndex(c => c.id === id);
      if (index !== -1) {
        this.$set(this.carts[index], 'quantity', quantity);
      }

      try {
        console.log('Updating cart item:', { id, quantity });
        const response = await axios.put(`/buyer/cart/${id}`, { quantity }, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Update cart response:', response.data);
        if (response.data.cart) {
          this.$set(this.carts, index, response.data.cart);
        } else {
          await this.fetchCart();
        }
      } catch (error) {
        console.error('Error updating cart:', error.response?.data || error.message);
        this.error = error.response?.data?.error || `Không thể cập nhật số lượng (Mã lỗi: ${error.response?.status || 'Không xác định'}). Vui lòng thử lại.`;
        await this.fetchCart();
      }
    },
    async removeFromCart(event, id) {
      event.preventDefault();
      this.loading = true;
      this.error = null;
      try {
        console.log('Removing cart item:', { id });
        const response = await axios.delete(`/buyer/cart/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Remove cart response:', response.data);
        await this.fetchCart();
      } catch (error) {
        console.error('Error removing from cart:', error.response?.data || error.message);
        this.error = error.response?.data?.error || 'Không thể xóa mục khỏi giỏ hàng. Vui lòng thử lại.';
      } finally {
        this.loading = false;
      }
    },
    toggleShopSelection(shopId) {
      const isSelected = this.selectedShops[shopId];
      this.groupedCarts[shopId].forEach(cart => {
        this.selectedItems[cart.id] = isSelected;
      });
      this.updateSelectAll();
    },
    updateShopSelection(shopId) {
      const allSelected = this.groupedCarts[shopId].every(cart => this.selectedItems[cart.id]);
      this.selectedShops[shopId] = allSelected;
      this.updateSelectAll();
    },
    toggleSelectAll() {
      Object.keys(this.selectedItems).forEach(cartId => {
        this.selectedItems[cartId] = this.selectAll;
      });
      Object.keys(this.selectedShops).forEach(shopId => {
        this.selectedShops[shopId] = this.selectAll;
      });
    },
    updateSelectAll() {
      this.selectAll = Object.values(this.selectedItems).every(Boolean);
      this.$forceUpdate();
    },
    async checkout(event) {
      event.preventDefault();
      const selectedCartIds = Object.keys(this.selectedItems)
        .filter(cartId => this.selectedItems[cartId])
        .map(Number);
      if (selectedCartIds.length === 0) {
        this.error = 'Vui lòng chọn ít nhất một sản phẩm để thanh toán';
        return;
      }

      // Group selected cart IDs by shop
      const cartsByShop = this.selectedCarts.reduce((acc, cart) => {
        const shopId = cart.product_variant?.product?.shop?.id || 'unknown';
        if (!acc[shopId]) {
          acc[shopId] = {
            owner_id: cart.product_variant?.product?.shop?.owner_id,
            cart_ids: [],
          };
        }
        acc[shopId].cart_ids.push(cart.id);
        return acc;
      }, {});

      try {
        console.log('Navigating to order create with carts by shop:', cartsByShop);
        this.$router.push({
          path: '/orders/create',
          query: { cartsByShop: JSON.stringify(cartsByShop) },
        });
      } catch (error) {
        console.error('Error during checkout navigation:', error.message);
        this.error = 'Lỗi khi chuyển đến trang tạo đơn hàng. Vui lòng thử lại.';
      }
    },
    formatPrice(price) {
      return Number(price).toLocaleString('vi-VN');
    },
    togglePriceDetails() {
      console.log('Toggling price details, current state:', this.showPriceDetails);
      this.showPriceDetails = !this.showPriceDetails;
    },
    closePriceDetails(event) {
      const priceDetailsPopup = event.target.closest('.popup-with-arrow');
      const toggleButton = event.target.closest('button');
      if (!priceDetailsPopup && !toggleButton) {
        console.log('Closing price details');
        this.showPriceDetails = false;
      }
    },
  },
};
</script>

<style scoped>
.popup-with-arrow::before {
  content: '';
  position: absolute;
  top: -8px;
  right: 12px;
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-bottom: 8px solid white;
  z-index: 61;
}
.popup-with-arrow::after {
  content: '';
  position: absolute;
  top: -9px;
  right: 12px;
  width: 0;
  height: 0;
  border-left: 9px solid transparent;
  border-right: 9px solid transparent;
  border-bottom: 9px solid #e5e7eb;
  z-index: 60;
}

/* Tùy chỉnh thanh cuộn */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #f97316;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #ea580c;
}
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #f97316 #f1f1f1;
}
</style>