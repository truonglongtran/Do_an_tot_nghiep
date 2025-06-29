<template>
  <header class="bg-white shadow fixed top-0 w-full z-50">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
      <!-- Logo -->
      <router-link to="/" class="text-2xl font-bold text-orange-500">
        <img src="https://via.placeholder.com/100x40?text=Logo" alt="Logo" class="h-10" />
      </router-link>

      <!-- Search Bar -->
      <div class="flex-1 mx-4 relative">
        <input
          type="text"
          placeholder="Tìm kiếm sản phẩm..."
          class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
          v-model="searchQuery"
          @focus="handleFocus"
          @blur="handleBlur"
          @keypress.enter="handleSearch"
        />
        <!-- Search History Dropdown -->
        <div
          v-if="activeModal === 'search' && isLoggedInComputed && searchHistory.length"
          class="absolute top-full left-0 w-full bg-white border rounded-lg shadow-lg mt-1 z-[60] max-h-60 overflow-y-auto custom-scrollbar"
        >
          <ul class="divide-y divide-gray-200">
            <li
              v-for="history in searchHistory"
              :key="history.id"
              class="flex items-center justify-between p-2 hover:bg-gray-100"
            >
              <span
                class="text-gray-600 cursor-pointer flex-auto truncate"
                @click="searchQuery = history.keyword; handleSearch()"
              >
                {{ history.keyword }}
              </span>
              <button
                @click.stop="deleteSearchHistory(history.id)"
                class="text-gray-400 hover:text-red-500 ml-2"
                title="Xóa"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </li>
          </ul>
          <div class="p-2 border-t">
            <button
              @click="clearAllHistory"
              class="w-full text-red-500 hover:text-orange-500 text-sm font-semibold"
              :disabled="clearing"
            >
              {{ clearing ? 'Đang xóa...' : 'Xóa tất cả lịch sử' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center space-x-4">
        <!-- Chat Icon -->
        <div class="relative group" @mouseenter="setActiveModal('chats')" @mouseleave="clearActiveModal">
          <router-link to="/buyer/messages" class="text-gray-600 hover:text-orange-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
              />
            </svg>
            <span v-if="unreadChatsCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
              {{ unreadChatsCount }}
            </span>
          </router-link>
          <div
            v-if="activeModal === 'chats'"
            class="absolute right-0 mt-3 w-80 bg-white border rounded-lg shadow-lg p-4 z-[60] popup-with-arrow"
            @mouseenter="setActiveModal('chats')"
            @mouseleave="clearActiveModal"
          >
            <h3 class="text-lg font-bold text-orange-500 mb-2">Tin nhắn</h3>
            <div v-if="chats.length === 0" class="text-center text-gray-600">
              Không có tin nhắn
            </div>
            <div v-else class="space-y-2 max-h-64 overflow-y-auto custom-scrollbar">
              <div
                v-for="chat in chats"
                :key="chat.seller.id"
                class="flex items-center space-x-2 border rounded p-2 cursor-pointer hover:bg-gray-100"
                :class="{ 'bg-gray-100': chat.unread_count > 0 }"
                @click="openChatModal(chat)"
              >
                <img
                  :src="getImageUrl(chat.shop?.avatar_url)"
                  :alt="chat.shop?.shop_name || 'Shop'"
                  class="w-10 h-10 rounded-full object-cover"
                  @error="handleImageError($event, chat.shop?.avatar_url, 'chat_avatar')"
                />
                <div class="flex-1 chat-message-container">
                  <p class="text-sm font-semibold truncate">{{ chat.shop?.shop_name || 'Shop' }}</p>
                  <p class="text-gray-600 text-xs truncate">{{ chat.last_message?.content || 'Chưa có tin nhắn' }}</p>
                  <p class="text-gray-500 text-xs">{{ formatDate(chat.last_message?.created_at) }}</p>
                </div>
                <span v-if="chat.unread_count > 0" class="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                  {{ chat.unread_count }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Cart -->
        <div class="relative group" @mouseenter="setActiveModal('cart')" @mouseleave="clearActiveModal">
          <router-link to="/cart" class="text-gray-600 hover:text-orange-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
            <span v-if="carts.length > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
              {{ carts.length }}
            </span>
          </router-link>
          <div
            v-if="activeModal === 'cart'"
            class="absolute right-0 mt-3 w-80 bg-white border rounded-lg shadow-lg p-4 z-[60] popup-with-arrow"
            @mouseenter="setActiveModal('cart')"
            @mouseleave="clearActiveModal"
          >
            <h3 class="text-lg font-bold text-orange-500 mb-2">Giỏ hàng</h3>
            <div v-if="carts.length === 0" class="text-center text-gray-600">
              Giỏ hàng trống
            </div>
            <div v-else class="space-y-2 max-h-64 overflow-y-auto custom-scrollbar">
              <div v-for="cart in carts" :key="cart.id" class="flex items-center space-x-2">
                <img
                  :src="getImageUrl(cart.product_variant?.image_url || cart.product?.image_url)"
                  :alt="cart.product_variant?.product?.name || cart.product?.name || 'Sản phẩm'"
                  class="w-10 h-10 object-cover rounded"
                  @error="handleImageError($event, cart.product_variant?.image_url || cart.product?.image_url, 'cart_image')"
                />
                <div class="flex-1">
                  <p class="text-sm font-semibold truncate">{{ cart.product_variant?.product?.name || cart.product?.name || 'Sản phẩm' }}</p>
                  <p class="text-orange-500 text-xs">{{ formatPrice(cart.product_variant?.price || cart.product?.price || 0) }}đ</p>
                  <p class="text-gray-500 text-xs">Số lượng: {{ cart.quantity }}</p>
                </div>
                <button
                  type="button"
                  @click.stop="removeFromCart($event, cart.id)"
                  class="text-red-500 hover:underline text-xs"
                >
                  Xóa
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Notifications -->
        <div class="relative group" @mouseenter="setActiveModal('notifications')" @mouseleave="clearActiveModal">
          <router-link to="/buyer/notifications" class="text-gray-600 hover:text-orange-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
              />
            </svg>
            <span v-if="unreadNotificationsCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
              {{ unreadNotificationsCount }}
            </span>
          </router-link>
          <div
            v-if="activeModal === 'notifications'"
            class="absolute right-0 mt-3 w-80 bg-white border rounded-lg shadow-lg p-4 z-[60] popup-with-arrow"
            @mouseenter="setActiveModal('notifications')"
            @mouseleave="clearActiveModal"
          >
            <h3 class="text-lg font-bold text-orange-500 mb-2">Thông báo</h3>
            <div v-if="notifications.length === 0" class="text-center text-gray-600">
              Không có thông báo
            </div>
            <div v-else class="space-y-2 max-h-64 overflow-y-auto custom-scrollbar">
              <div v-for="notification in notifications" :key="notification.id" class="border rounded p-2" :class="{ 'bg-gray-100': !notification.is_read }">
                <p class="font-semibold text-sm truncate">{{ notification.title }}</p>
                <p class="text-gray-600 text-xs truncate">{{ notification.message }}</p>
                <p class="text-gray-500 text-xs">{{ formatDate(notification.created_at) }}</p>
                <button v-if="!notification.is_read" @click.stop="markAsRead(notification.id)" class="text-orange-500 hover:underline text-xs">
                  Đánh dấu đã đọc
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Support -->
        <router-link to="/support" class="text-gray-600 hover:text-orange-500">
          Hỗ trợ
        </router-link>

        <!-- Seller Channel -->
        <router-link :to="sellerLink" class="text-gray-600 hover:text-orange-500">
          Kênh người bán
        </router-link>

        <!-- User Section (Logged In) -->
        <div v-if="isLoggedInComputed" class="relative group" @mouseenter="setActiveModal('user')" @mouseleave="clearActiveModal">
          <div class="flex items-center space-x-2 cursor-pointer">
            <img
              :src="getImageUrl(user.avatar_url)"
              alt="Avatar"
              class="w-8 h-8 rounded-full"
              @error="handleImageError($event, user.avatar_url, 'user_avatar')"
            />
            <span class="text-gray-600 truncate max-w-[150px]">{{ user.username || 'Người dùng' }}</span>
          </div>
          <div
            v-if="activeModal === 'user'"
            class="absolute right-0 mt-3 w-48 bg-white border rounded-lg shadow-lg p-2 z-[60] popup-with-arrow"
            @mouseenter="setActiveModal('user')"
            @mouseleave="clearActiveModal"
          >
            <router-link to="/buyer/profile" class="block px-4 py-2 text-gray-600 hover:bg-gray-100 hover:text-orange-500">
              Hồ sơ
            </router-link>
            <router-link to="/buyer/order-tracking" class="block px-4 py-2 text-gray-600 hover:bg-gray-100 hover:text-orange-500">
              Theo dõi đơn
            </router-link>
            <button @click="logout" class="block w-full text-left px-4 py-2 text-red-500 hover:bg-gray-100 hover:text-orange-500">
              Đăng xuất
            </button>
          </div>
        </div>

        <!-- Non-logged-in Section -->
        <div v-else class="flex space-x-2">
          <router-link to="/buyer/register" class="text-gray-600 hover:text-orange-500">
            Đăng ký
          </router-link>
          <router-link to="/buyer/login" class="text-gray-600 hover:text-orange-500">
            Đăng nhập
          </router-link>
          <a href="/seller/register" class="text-gray-600 hover:text-orange-500">
            Trở thành người bán
          </a>
        </div>
      </div>
    </div>
    </header>
</template>

<script>
import axios from 'axios';
import { EventBus } from './ChatModal.vue';

export default {
  props: {
    isLoggedIn: {
      type: Boolean,
      default: false,
    },
    user: {
      type: Object,
      default: () => ({ email: '', avatar_url: '', username: '' }),
    },
  },
  data() {
    return {
      searchQuery: '',
      searchHistory: [],
      clearing: false,
      notifications: [],
      carts: [],
      chats: [],
      activeModal: null,
      closeTimeout: null,
      role: localStorage.getItem('role') || null,
    };
  },
  computed: {
    isLoggedInComputed() {
      const isLogged = !!localStorage.getItem('token');
      console.log('isLoggedIn:', isLogged, 'Token:', localStorage.getItem('token'));
      return isLogged;
    },
    unreadNotificationsCount() {
      return this.notifications.filter(n => !n.is_read).length;
    },
    unreadChatsCount() {
      return this.chats.reduce((total, chat) => total + (chat.unread_count || 0), 0);
    },
    sellerLink() {
      return this.role === 'seller' ? '/seller/dashboard' : '/seller/register';
    },
  },
  watch: {
    isLoggedIn(newVal) {
      if (newVal) {
        this.fetchSearchHistory();
        this.fetchNotifications();
        this.fetchCart();
        this.fetchChats();
      } else {
        this.searchHistory = [];
        this.notifications = [];
        this.carts = [];
        this.chats = [];
        this.activeModal = null;
      }
    },
    '$route.query.q': {
      immediate: true,
      handler(newQuery) {
        this.searchQuery = newQuery || '';
      },
    },
    '$route.path': {
      immediate: true,
      handler(newPath) {
        if (newPath === '/buyer/messages') {
          this.activeModal = null;
          EventBus.emit('close-chat-modal');
        }
      },
    },
  },
  created() {
    if (this.isLoggedInComputed) {
      this.fetchNotifications();
      this.fetchCart();
      this.fetchChats();
    }
    EventBus.on('message-sent', this.handleMessageSent);
    EventBus.on('update-chat-unread', this.handleUpdateChatUnread);
  },
  beforeDestroy() {
    EventBus.off('message-sent', this.handleMessageSent);
    EventBus.off('update-chat-unread', this.handleUpdateChatUnread);
  },
  methods: {
    setActiveModal(modal) {
      if (modal === 'chat' && this.$route.path === '/buyer/messages') {
        return;
      }
      if (this.closeTimeout) {
        clearTimeout(this.closeTimeout);
      }
      this.activeModal = modal;
    },
    clearActiveModal() {
      this.closeTimeout = setTimeout(() => {
        this.activeModal = null;
      }, 300);
    },
    async handleFocus() {
      console.log('Search input focused, isLoggedIn:', this.isLoggedInComputed);
      if (this.isLoggedInComputed) {
        this.setActiveModal('search');
        await this.fetchSearchHistory();
      }
    },
    handleSearch() {
      if (this.searchQuery.trim()) {
        console.log('Search triggered with query:', this.searchQuery);
        this.activeModal = null;
        this.$router.push({ path: '/search', query: { q: this.searchQuery } });
      }
    },
    async fetchSearchHistory() {
      if (!this.isLoggedInComputed) {
        console.log('Not logged in, skipping history fetch');
        return;
      }
      try {
        console.log('Fetching search history with token:', localStorage.getItem('token'));
        const response = await axios.get('/buyer/search-history', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Search history response:', response.data);
        this.searchHistory = response.data.history || [];
      } catch (error) {
        console.error('Error fetching search history:', error.response?.data || error.message);
        this.searchHistory = [];
      }
    },
    async deleteSearchHistory(id) {
      try {
        await axios.delete(`/buyer/search-history/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.searchHistory = this.searchHistory.filter((h) => h.id !== id);
      } catch (error) {
        console.error('Error deleting search history:', error.response?.data || error.message);
      }
    },
    async clearAllHistory() {
      this.clearing = true;
      try {
        await axios.delete('/buyer/search-history-delete', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.searchHistory = [];
      } catch (error) {
        console.error('Error clearing search history:', error.response?.data || error.message);
      } finally {
        this.clearing = false;
      }
    },
    async fetchNotifications() {
      if (!this.isLoggedInComputed) {
        return;
      }
      try {
        const response = await axios.get('/buyer/notifications', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.notifications = response.data.notifications || [];
      } catch (error) {
        console.error('Error fetching notifications:', error.response?.data || error.message);
      }
    },
    async markAsRead(id) {
      try {
        await axios.put(`/buyer/notifications/${id}/read`, {}, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.notifications = this.notifications.map(n => n.id === id ? { ...n, is_read: true } : n);
      } catch (error) {
        console.error('Error marking notification as read:', error.response?.data || error.message);
      }
    },
    async fetchCart() {
      if (!this.isLoggedInComputed) {
        return;
      }
      try {
        const response = await axios.get('/buyer/cart', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Cart API response:', response.data);
        this.carts = response.data.carts || [];
      } catch (error) {
        console.error('Error fetching cart:', error.response?.data || error.message);
      }
    },
    async removeFromCart(event, id) {
      event.preventDefault();
      try {
        console.log('Removing cart item:', { id });
        const response = await axios.delete(`/buyer/cart/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Remove cart response:', response.data);
        await this.fetchCart();
      } catch (error) {
        console.error('Error removing from cart:', error.response?.data || error.message);
      }
    },
    async fetchChats() {
      if (!this.isLoggedInComputed) {
        return;
      }
      try {
        const response = await axios.get('/buyer/chats', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Chats API response:', JSON.stringify(response.data, null, 2));
        this.chats = response.data.data.conversations || [];
      } catch (error) {
        console.error('Error fetching chats:', error.response?.data || error.message);
        if (error.response?.status === 401) {
          this.logout();
        }
      }
    },
    async openChatModal(chat) {
      if (this.$route.path === '/buyer/messages') {
        this.$router.push('/buyer/messages');
        return;
      }
      this.$emit('open-chat-modal', { ...chat, messages: [] });
      EventBus.emit('open-chat-modal', { ...chat, messages: [] });
    },
    handleUpdateChatUnread({ sellerId, unreadCount }) {
      this.chats = this.chats.map(c =>
        c.seller.id === sellerId ? { ...c, unread_count: unreadCount } : c
      );
    },
    handleMessageSent(sellerId) {
      this.fetchChats();
    },
    getImageUrl(imgUrl) {
      if (!imgUrl) {
        console.warn('Không có đường dẫn ảnh, sử dụng ảnh placeholder');
        return 'https://via.placeholder.com/50?text=Ảnh+Không+Tìm+Thấy';
      }
      if (/^https?:\/\//.test(imgUrl)) {
        console.log('Sử dụng URL bên ngoài:', imgUrl);
        return `${imgUrl}?t=${new Date().getTime()}`;
      }
      const baseUrl = import.meta.env.VITE_STORAGE_BASE_URL || 'http://localhost:8000/storage';
      const cleanImgUrl = imgUrl.replace(/^\/?(storage\/)?/, '');
      const finalUrl = `${baseUrl}/${cleanImgUrl}?t=${new Date().getTime()}`;
      console.log('Đường dẫn ảnh đã tạo:', finalUrl);
      return finalUrl;
    },
    handleImageError(event, imgUrl, type) {
      console.error(`Lỗi tải ảnh ${type}:`, {
        img_url: imgUrl,
        attempted_url: event.target.src,
        storage_base_url: import.meta.env.VITE_STORAGE_BASE_URL,
      });
      event.target.src = 'https://via.placeholder.com/50?text=Ảnh+Không+Tìm+Thấy';
    },
    formatDate(date) {
      if (!date) return '';
      const d = new Date(date);
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      const hours = String(d.getHours()).padStart(2, '0');
      const minutes = String(d.getMinutes()).padStart(2, '0');
      return `${day}/${month}/${year} ${hours}:${minutes}`;
    },
    formatPrice(price) {
      return Number(price).toLocaleString('vi-VN');
    },
    logout() {
      localStorage.removeItem('token');
      localStorage.removeItem('role');
      localStorage.removeItem('loginType');
      localStorage.removeItem('email');
      localStorage.removeItem('avatar_url');
      localStorage.removeItem('username');
      this.$emit('update:isLoggedIn', false);
      this.$emit('update:user', { email: '', avatar_url: '', username: '' });
      this.$router.push('/buyer/login');
    },
    handleStorageChange() {
      this.role = localStorage.getItem('role') || null;
    },
  },
};
</script>

<style scoped>
.group {
  position: relative;
}

.group::after {
  content: '';
  position: absolute;
  top: 0;
  left: -10px;
  right: -10px;
  bottom: -10px;
  z-index: 50;
  pointer-events: none;
}

.group > div {
  pointer-events: auto;
}

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

.chat-message-container {
  max-width: 180px;
  min-width: 0;
}

.chat-message-container p.text-gray-600 {
  overflow-wrap: break-word;
  word-break: break-all;
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
}
</style>