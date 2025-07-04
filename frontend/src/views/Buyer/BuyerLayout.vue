<template>
  <div class="min-h-screen flex flex-col">
    <Header :is-logged-in="isLoggedIn" :user="user" @open-chat-modal="handleOpenChatModal" />
    <main class="flex-1 pt-16">
      <router-view @open-chat-modal="handleOpenChatModal" />
    </main>
    <Footer />
    <!-- Bottom Navigation Bar (Mobile) -->
    <nav class="md:hidden fixed bottom-0 w-full bg-white shadow-lg flex justify-around py-2 z-50">
      <router-link to="/" class="flex flex-col items-center text-gray-600 hover:text-orange-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 5v6h4v-6m-6 0h6" />
        </svg>
        <span class="text-xs">Trang chủ</span>
      </router-link>
      <router-link to="/category/all" class="flex flex-col items-center text-gray-600 hover:text-orange-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
        <span class="text-xs">Danh mục</span>
      </router-link>
      <router-link to="/cart" class="flex flex-col items-center text-gray-600 hover:text-orange-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span class="text-xs">Giỏ hàng</span>
      </router-link>
      <router-link to="/buyer/messages" class="flex flex-col items-center text-gray-600 hover:text-orange-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5v-4l7-7 7 7v4h-4" />
        </svg>
        <span class="text-xs">Tin nhắn</span>
      </router-link>
      <router-link to="/buyer/dashboard" class="flex flex-col items-center text-gray-600 hover:text-orange-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <span class="text-xs">Tài khoản</span>
      </router-link>
    </nav>
    <!-- Chat Modal -->
    <ChatModal
      :is-open="activeModal === 'chat'"
      :selected-chat="selectedChat"
      @update:selected-chat="updateSelectedChat"
      @update-chat-unread="updateChatUnread"
      @close="closeChatModal"
    />
  </div>
</template>

<script>
import Header from './component/Header.vue';
import Footer from './component/Footer.vue';
import ChatModal from './component/ChatModal.vue';
import { EventBus } from './component/ChatModal.vue';

export default {
  components: { Header, Footer, ChatModal },
  data() {
    return {
      activeModal: null,
      selectedChat: { seller: {}, shop: {}, messages: [], unread_count: 0 },
    };
  },
  computed: {
    isLoggedIn() {
      return !!localStorage.getItem('token');
    },
    user() {
      return {
        email: localStorage.getItem('email') || '',
        avatar_url: localStorage.getItem('avatar_url') || 'https://via.placeholder.com/50',
        username: localStorage.getItem('username') || 'Người dùng',
      };
    },
  },
  created() {
    EventBus.on('open-chat-modal', this.handleOpenChatModal);
    EventBus.on('update-chat', this.handleUpdateChat);
    EventBus.on('close-chat-modal', this.closeChatModal);
  },
  beforeDestroy() {
    EventBus.off('open-chat-modal', this.handleOpenChatModal);
    EventBus.off('update-chat', this.handleUpdateChat);
    EventBus.off('close-chat-modal', this.closeChatModal);
  },
  methods: {
    handleOpenChatModal(chat) {
      if (this.$route.path === '/buyer/messages') {
        return;
      }
      this.selectedChat = { ...chat, messages: [] };
      this.activeModal = 'chat';
    },
    updateSelectedChat(updatedChat) {
      this.selectedChat = updatedChat;
    },
    updateChatUnread(sellerId, unreadCount) {
      // Không cần cập nhật danh sách chats ở đây, để Header.vue xử lý
      EventBus.emit('update-chat-unread', { sellerId, unreadCount });
    },
    handleUpdateChat(updatedChat) {
      if (this.activeModal === 'chat' && this.selectedChat.seller.id === updatedChat.seller.id) {
        this.selectedChat = updatedChat;
      }
    },
    closeChatModal() {
      this.activeModal = null;
    },
  },
};
</script>

<style scoped>
/* Đảm bảo z-index của modal cao hơn nav */
</style>