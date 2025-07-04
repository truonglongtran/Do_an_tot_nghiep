<template>
  <div
    v-if="isOpen"
    class="fixed bottom-4 right-4 w-80 bg-white border border-gray-200 rounded-xl shadow-2xl z-[70] flex flex-col animate-fade-in"
    style="height: 400px;"
  >
    <div class="flex items-center justify-between p-3 border-b bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-t-xl">
      <div class="flex items-center space-x-2">
        <img
          :src="getImageUrl(selectedChat.shop?.avatar_url)"
          :alt="selectedChat.shop?.shop_name || 'Shop'"
          class="w-8 h-8 rounded-full object-cover border border-white"
          @error="handleImageError($event, selectedChat.shop?.avatar_url, 'chat_modal_avatar')"
        />
        <span class="font-semibold truncate text-sm">{{ selectedChat.shop?.shop_name || 'Shop' }}</span>
      </div>
      <button @click="closeChatModal" class="text-white hover:text-gray-200 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <div class="flex-1 p-4 overflow-y-auto custom-scrollbar" ref="chatMessages">
      <div v-if="errorMessage" class="text-center text-red-500 text-sm font-medium">
        {{ errorMessage }}
      </div>
      <div v-else-if="loadingMessages" class="text-center text-gray-500 text-sm">
        <svg class="animate-spin w-6 h-6 mx-auto" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        Đang tải tin nhắn...
      </div>
      <div v-else-if="selectedChat.messages.length === 0" class="text-center text-gray-600 text-sm">
        Chưa có tin nhắn
      </div>
      <div v-else v-for="message in selectedChat.messages" :key="message.created_at" class="mb-4">
        <div
          :class="{
            'flex justify-end': message.sender_type === 'buyer',
            'flex justify-start': message.sender_type === 'seller',
          }"
        >
          <div class="message-bubble transition-transform hover:scale-105">
            <p
              :class="{
                'sent': message.sender_type === 'buyer',
                'received': message.sender_type === 'seller',
              }"
            >
              {{ message.content }}
            </p>
            <span class="message-time">{{ formatDate(message.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="p-3 border-t bg-gray-50">
      <div class="flex items-center space-x-2">
        <input
          type="text"
          v-model="newMessage"
          placeholder="Nhập tin nhắn..."
          class="flex-1 p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all"
          @keypress.enter="sendMessage"
          :disabled="errorMessage || loadingMessages"
        />
        <button
          @click="sendMessage"
          class="bg-orange-500 text-white p-2 rounded-lg hover:bg-orange-600 transition-colors"
          :disabled="!newMessage.trim() || sending || errorMessage || loadingMessages"
        >
          <span v-if="sending" class="spinner"></span>
          Gửi
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

// Tạo EventBus đơn giản
const EventBus = {
  listeners: {},
  on(event, callback) {
    if (!this.listeners[event]) {
      this.listeners[event] = [];
    }
    this.listeners[event].push(callback);
  },
  emit(event, ...args) {
    if (this.listeners[event]) {
      this.listeners[event].forEach(callback => callback(...args));
    }
  },
  off(event, callback) {
    if (this.listeners[event]) {
      this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
    }
  },
};

export { EventBus };

export default {
  name: 'ChatModal',
  props: {
    isOpen: {
      type: Boolean,
      default: false,
    },
    selectedChat: {
      type: Object,
      default: () => ({ seller: {}, shop: {}, messages: [], unread_count: 0 }),
    },
  },
  data() {
    return {
      newMessage: '',
      sending: false,
      pollingInterval: null,
      errorMessage: null,
      loadingMessages: false,
    };
  },
  watch: {
    isOpen(newVal) {
      console.log('ChatModal isOpen changed:', newVal, 'selectedChat:', JSON.stringify(this.selectedChat, null, 2));
      if (newVal) {
        if (!this.selectedChat.seller?.id || isNaN(this.selectedChat.seller.id) || this.selectedChat.seller.id <= 0) {
          this.errorMessage = 'Người bán không hợp lệ';
          this.stopPolling();
        } else {
          this.errorMessage = null;
          this.fetchChatMessages(this.selectedChat.seller.id);
          this.startPolling();
        }
      } else {
        this.stopPolling();
        this.errorMessage = null;
      }
    },
  },
  methods: {
    async fetchChatMessages(sellerId) {
      this.loadingMessages = true;
      try {
        console.log('Fetching chat messages for sellerId:', sellerId);
        const response = await axios.get(`/buyer/chats/detail?seller_id=${sellerId}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Chat messages response:', JSON.stringify(response.data, null, 2));
        this.errorMessage = null;
        const updatedChat = {
          ...this.selectedChat,
          seller: response.data.data.seller || this.selectedChat.seller,
          shop: response.data.data.shop || this.selectedChat.shop,
          messages: response.data.data.messages || [],
        };
        this.$emit('update:selectedChat', updatedChat);
        EventBus.emit('update-chat', updatedChat);
        if (this.$refs.chatMessages) {
          this.$nextTick(() => {
            this.$refs.chatMessages.scrollTop = this.$refs.chatMessages.scrollHeight;
          });
        }
        if (this.selectedChat.unread_count > 0) {
          await axios.put(`/buyer/chats/${sellerId}/read`, {}, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          });
          this.$emit('update-chat-unread', sellerId, 0);
          EventBus.emit('update-chat-unread', { sellerId, unreadCount: 0 });
        }
      } catch (error) {
        console.error('Error fetching chat messages:', error.response?.data || error.message);
        if (error.response?.status === 400 && error.response?.data?.message === 'Người bán không tồn tại hoặc không hợp lệ') {
          this.errorMessage = 'Người bán không tồn tại hoặc không hợp lệ';
        } else if (error.response?.status === 404 && error.response?.data?.message === 'Cuộc trò chuyện chưa tồn tại. Gửi tin nhắn để bắt đầu!') {
          const updatedChat = { ...this.selectedChat, messages: [] };
          this.$emit('update:selectedChat', updatedChat);
          EventBus.emit('update-chat', updatedChat);
        } else if (error.response?.status === 401) {
          this.$router.push('/buyer/login');
        } else {
          this.errorMessage = 'Lỗi tải tin nhắn, vui lòng thử lại';
        }
      } finally {
        this.loadingMessages = false;
      }
    },
    async sendMessage() {
      if (!this.newMessage.trim() || this.sending || this.errorMessage || this.loadingMessages) return;
      this.sending = true;
      try {
        const response = await axios.post(
          '/buyer/chats/send',
          { receiver_id: this.selectedChat.seller.id, content: this.newMessage },
          { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
        );
        console.log('Send message response:', JSON.stringify(response.data, null, 2));
        this.errorMessage = null;
        const updatedChat = {
          ...this.selectedChat,
          messages: [...this.selectedChat.messages, response.data.data],
        };
        this.$emit('update:selectedChat', updatedChat);
        EventBus.emit('update-chat', updatedChat);
        EventBus.emit('message-sent', this.selectedChat.seller.id);
        this.newMessage = '';
        this.$nextTick(() => {
          if (this.$refs.chatMessages) {
            this.$refs.chatMessages.scrollTop = this.$refs.chatMessages.scrollHeight;
          }
        });
      } catch (error) {
        console.error('Error sending message:', error.response?.data || error.message);
        if (error.response?.status === 401) {
          this.$router.push('/buyer/login');
        } else {
          this.errorMessage = 'Lỗi gửi tin nhắn, vui lòng thử lại';
        }
      } finally {
        this.sending = false;
      }
    },
    startPolling() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
      }
      this.pollingInterval = setInterval(() => {
        if (this.isOpen && this.selectedChat.seller.id && !this.errorMessage) {
          this.fetchChatMessages(this.selectedChat.seller.id);
        }
      }, 5000);
    },
    stopPolling() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
        this.pollingInterval = null;
      }
    },
    closeChatModal() {
      this.$emit('close');
      EventBus.emit('close-chat-modal');
      this.stopPolling();
      this.errorMessage = null;
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
  },
};
</script>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
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

.message-bubble {
  max-width: 70%;
  display: flex;
  flex-direction: column;
  width: 100%;
}

.message-bubble p {
  display: inline-block;
  padding: 10px 14px;
  border-radius: 16px;
  font-size: 14px;
  margin: 0;
  line-height: 1.5;
  max-width: 100%;
  overflow-wrap: break-word;
  word-break: break-all;
  transition: background-color 0.2s, transform 0.2s;
}

.message-bubble p.sent {
  background: #f97316;
  color: #fff;
  border-bottom-right-radius: 4px;
}

.message-bubble p.received {
  background: #fed7aa;
  color: #1f2937;
  border-bottom-left-radius: 4px;
}

.message-time {
  font-size: 11px;
  color: #6b7280;
  margin-top: 6px;
  text-align: inherit;
}

.flex.justify-end .message-bubble {
  align-items: flex-end;
}

.flex.justify-start .message-bubble {
  align-items: flex-start;
}

.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #fff;
  border-top: 2px solid transparent;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 8px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>