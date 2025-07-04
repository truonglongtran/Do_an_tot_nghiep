<template>
  <div class="container">
    <h2>Quản lý chat</h2>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
    <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
    <div v-if="isLoading" class="text-center">Đang tải...</div>
    <div v-else class="chat-container">
      <!-- Danh sách Buyer -->
      <div class="conversation-list">
        <div
          v-for="conversation in conversations"
          :key="conversation.buyer.id"
          class="conversation-item"
          :class="{ 'selected': selectedBuyerId === conversation.buyer.id }"
          @click="selectBuyer(conversation.buyer.id)"
        >
          <div class="buyer-info">
            <img
              :src="getImageUrl(conversation.buyer?.avatar_url)"
              :alt="conversation.buyer?.username || 'Người mua'"
              class="buyer-avatar"
              @error="handleImageError($event, conversation.buyer?.avatar_url, 'buyer_avatar_' + conversation.buyer.id)"
            />
            <div class="buyer-details">
              <h3>{{ conversation.buyer?.username || 'Người mua' }}</h3>
              <p class="last-message">{{ conversation.last_message?.content || 'Chưa có tin nhắn' }}</p>
              <p class="timestamp">{{ formatDateTime(conversation.last_message?.created_at) }}</p>
            </div>
          </div>
          <div v-if="conversation.unread_count > 0" class="unread-badge">
            {{ conversation.unread_count }}
          </div>
        </div>
        <div v-if="conversations.length === 0" class="text-center text-gray-500">
          Không có cuộc trò chuyện nào.
        </div>
      </div>

      <!-- Cửa sổ chat -->
      <div class="chat-window" v-if="selectedBuyerId">
        <div class="chat-header">
          <img
            :src="getImageUrl(selectedBuyer?.avatar_url)"
            :alt="selectedBuyer?.username || 'Người mua'"
            class="buyer-avatar"
            @error="handleImageError($event, selectedBuyer?.avatar_url, 'buyer_avatar_' + selectedBuyer?.id)"
          />
          <h3>{{ selectedBuyer?.username || 'Chọn một người mua để xem tin nhắn' }}</h3>
        </div>
        <div class="chat-messages" ref="chatMessages">
          <div
            v-for="(message, index) in selectedMessages"
            :key="message.created_at"
            class="message-bubble"
            :class="{
              'sent': message.sender_type === 'seller',
              'received': message.sender_type === 'buyer'
            }"
          >
            <p>{{ message.content }}</p>
            <span class="message-time">{{ formatDateTime(message.created_at) }}</span>
          </div>
          <div v-if="selectedMessages.length === 0" class="text-center text-gray-500">
            Chưa có tin nhắn. Gửi tin nhắn để bắt đầu cuộc trò chuyện!
          </div>
        </div>
        <div class="chat-input">
          <input
            v-model="newMessage"
            type="text"
            placeholder="Nhập tin nhắn..."
            @keyup.enter="sendMessage"
          />
          <button
            @click="sendMessage"
            :disabled="sending || !newMessage.trim() || !selectedBuyerId"
            class="btn btn-send"
          >
            <span v-if="sending" class="spinner"></span>
            Gửi
          </button>
        </div>
      </div>
      <div v-else class="chat-placeholder">
        <p>Chọn một người mua để bắt đầu trò chuyện.</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';

export default {
  name: 'SellerMessages',
  setup() {
    const conversations = ref([]);
    const selectedBuyerId = ref(null);
    const selectedMessages = ref([]);
    const newMessage = ref('');
    const isLoading = ref(false);
    const sending = ref(false);
    const errorMessage = ref('');
    const successMessage = ref('');
    const chatMessages = ref(null);
    const currentSellerId = ref(null);
    const pollingInterval = ref(null);

    const fetchConversations = async () => {
      isLoading.value = true;
      errorMessage.value = '';
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          throw new Error('Không tìm thấy token');
        }
        console.log('Fetching conversations with token:', token);
        const response = await axios.get('/seller/messages', {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log('API response for conversations:', JSON.stringify(response.data, null, 2));
        conversations.value = response.data.data.conversations || [];
        currentSellerId.value = response.data.data.current_seller_id;
        console.log('Conversations:', JSON.stringify(conversations.value, null, 2));
        console.log('Current Seller ID:', currentSellerId.value);
        if (conversations.length === 0) {
          console.warn('No conversations found for seller');
        }
      } catch (error) {
        console.error('Error fetching conversations:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Lỗi khi tải danh sách trò chuyện';
        if (error.response?.status === 401) {
          console.log('Unauthorized, redirecting to login');
          localStorage.removeItem('token');
          window.location.href = '/seller/login';
        }
      } finally {
        isLoading.value = false;
      }
    };

    const selectBuyer = async (buyerId) => {
      console.log('Selecting buyer with ID:', buyerId);
      if (!buyerId) {
        errorMessage.value = 'Vui lòng chọn một người mua để xem tin nhắn';
        selectedMessages.value = [];
        selectedBuyerId.value = null;
        stopPolling();
        return;
      }

      selectedBuyerId.value = buyerId;
      errorMessage.value = '';
      await fetchMessages(buyerId);
      startPolling(buyerId);
    };

    const fetchMessages = async (buyerId) => {
      try {
        const token = localStorage.getItem('token');
        console.log('Fetching messages for buyerId:', buyerId, 'with token:', token);
        const response = await axios.get(`/seller/messages/detail?buyer_id=${buyerId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log('API response for messages:', JSON.stringify(response.data, null, 2));
        selectedMessages.value = response.data.data.messages || [];
        await markMessagesAsRead(buyerId);
        const conversation = conversations.value.find(c => c.buyer.id === buyerId);
        if (conversation) {
          conversation.unread_count = 0;
          console.log('Updated conversation:', JSON.stringify(conversation, null, 2));
        }
        nextTick(() => {
          if (chatMessages.value) {
            chatMessages.value.scrollTop = chatMessages.value.scrollHeight;
          }
        });
      } catch (error) {
        console.error('Error fetching messages:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Lỗi khi tải tin nhắn';
        if (error.response?.status === 404 && error.response?.data?.message === 'Cuộc trò chuyện chưa tồn tại. Gửi tin nhắn để bắt đầu!') {
          errorMessage.value = 'Cuộc trò chuyện chưa tồn tại. Gửi tin nhắn để bắt đầu!';
          selectedMessages.value = [];
        }
      }
    };

    const sendMessage = async () => {
      if (!newMessage.value.trim() || !selectedBuyerId.value || sending.value) {
        console.warn('Cannot send message: newMessage or selectedBuyerId is empty', {
          newMessage: newMessage.value,
          selectedBuyerId: selectedBuyerId.value,
        });
        errorMessage.value = 'Vui lòng chọn người mua và nhập tin nhắn';
        return;
      }
      sending.value = true;
      errorMessage.value = '';
      try {
        const token = localStorage.getItem('token');
        console.log('Sending message to buyerId:', selectedBuyerId.value, 'with content:', newMessage.value);
        const response = await axios.post(
          '/seller/messages/send',
          {
            receiver_id: selectedBuyerId.value,
            content: newMessage.value,
          },
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        console.log('API response for sent message:', JSON.stringify(response.data, null, 2));
        selectedMessages.value.push(response.data.data);
        newMessage.value = '';
        const conversation = conversations.value.find(c => c.buyer.id === selectedBuyerId.value);
        if (conversation) {
          conversation.last_message = response.data.data;
        } else {
          const buyerResponse = await axios.get(`/seller/buyers?buyer_id=${selectedBuyerId.value}`, {
            headers: { Authorization: `Bearer ${token}` },
          });
          console.log('API response for buyer:', JSON.stringify(buyerResponse.data, null, 2));
          const buyerData = buyerResponse.data.data.find(b => b.id === selectedBuyerId.value) || {
            id: selectedBuyerId.value,
            username: 'Unknown',
            avatar_url: null,
          };
          conversations.value.push({
            buyer: { id: selectedBuyerId.value, username: buyerData.username, avatar_url: buyerData.avatar_url },
            last_message: response.data.data,
            unread_count: 0,
          });
        }
        console.log('Updated conversations:', JSON.stringify(conversations.value, null, 2));
        nextTick(() => {
          if (chatMessages.value) {
            chatMessages.value.scrollTop = chatMessages.value.scrollHeight;
          }
        });
        successMessage.value = 'Tin nhắn đã được gửi';
        setTimeout(() => (successMessage.value = ''), 3000);
        await fetchConversations();
      } catch (error) {
        console.error('Error sending message:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Lỗi khi gửi tin nhắn';
        if (error.response?.status === 401) {
          localStorage.removeItem('token');
          window.location.href = '/seller/login';
        }
      } finally {
        sending.value = false;
      }
    };

    const markMessagesAsRead = async (buyerId) => {
      try {
        const token = localStorage.getItem('token');
        console.log('Marking messages as read for buyerId:', buyerId);
        await axios.put(`/seller/messages/${buyerId}/read`, {}, {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log('Messages marked as read for buyerId:', buyerId);
      } catch (error) {
        console.error('Error marking messages as read:', error.response?.data || error.message);
      }
    };

    const startPolling = (buyerId) => {
      if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
      }
      pollingInterval.value = setInterval(() => {
        if (selectedBuyerId.value === buyerId) {
          fetchMessages(buyerId);
        }
      }, 5000);
    };

    const stopPolling = () => {
      if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
      }
    };

    const getImageUrl = (imgUrl) => {
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
    };

    const handleImageError = (event, imgUrl, type) => {
      console.error(`Lỗi tải ảnh ${type}:`, {
        img_url: imgUrl,
        attempted_url: event.target.src,
        storage_base_url: import.meta.env.VITE_STORAGE_BASE_URL,
      });
      event.target.src = 'https://via.placeholder.com/50?text=Ảnh+Không+Tìm+Thấy';
    };

    const formatDateTime = (date) => {
      if (!date) return 'N/A';
      return new Date(date).toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      });
    };

    const selectedBuyer = computed(() => {
      const conversation = conversations.value.find(c => c.buyer.id === selectedBuyerId.value);
      console.log('Selected buyer:', JSON.stringify(conversation?.buyer || null, null, 2));
      return conversation ? conversation.buyer : null;
    });

    onMounted(() => {
      console.log('Component mounted, fetching conversations');
      fetchConversations();
    });

    onUnmounted(() => {
      console.log('Component unmounted, stopping polling');
      stopPolling();
      selectedBuyerId.value = null;
      selectedMessages.value = [];
    });

    return {
      conversations,
      selectedBuyerId,
      selectedMessages,
      newMessage,
      isLoading,
      sending,
      errorMessage,
      successMessage,
      chatMessages,
      currentSellerId,
      selectBuyer,
      sendMessage,
      getImageUrl,
      handleImageError,
      formatDateTime,
      selectedBuyer,
    };
  },
};
</script>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}
h2 {
  font-size: 24px;
  font-weight: bold;
  color: #0288d1;
  margin-bottom: 20px;
}
.error-message {
  background: #ffe6e6;
  color: #d32f2f;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 20px;
}
.success-message {
  background: #e6ffe6;
  color: #2e7d32;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 20px;
}
.chat-container {
  display: flex;
  gap: 20px;
  height: calc(100vh - 200px);
}
.conversation-list {
  width: 300px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow-y: auto;
}
.conversation-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border-bottom: 1px solid #e5e7eb;
  cursor: pointer;
  transition: background-color 0.3s;
}
.conversation-item:hover {
  background: #f5f5f5;
}
.conversation-item.selected {
  background: #e3f2fd;
}
.buyer-info {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-grow: 1;
}
.buyer-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}
.buyer-details {
  flex-grow: 1;
}
.buyer-details h3 {
  font-size: 16px;
  font-weight: 600;
  color: #333;
}
.last-message {
  font-size: 14px;
  color: #6b7280;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 180px;
}
.timestamp {
  font-size: 12px;
  color: #9ca3af;
}
.unread-badge {
  background: #d32f2f;
  color: #fff;
  border-radius: 12px;
  padding: 2px 8px;
  font-size: 12px;
  font-weight: bold;
}
.chat-window {
  flex-grow: 1;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
}
.chat-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 15px;
  border-bottom: 1px solid #e5e7eb;
}
.chat-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #0288d1;
}
.chat-messages {
  flex-grow: 1;
  padding: 15px;
  overflow-y: auto;
}
.message-bubble {
  margin-bottom: 15px;
  max-width: 70%;
  width: fit-content;
  display: flex;
  flex-direction: column;
}
.message-bubble.sent {
  margin-left: auto;
}
.message-bubble.received {
  margin-right: auto;
}
.message-bubble p {
  display: inline-block;
  padding: 8px 12px;
  border-radius: 12px;
  font-size: 14px;
  margin: 0;
  line-height: 1.4;
  max-width: 100%;
  word-wrap: break-word;
}
.message-bubble.sent p {
  background: #0288d1;
  color: #fff;
  border-bottom-right-radius: 4px;
}
.message-bubble.received p {
  background: #e3f2fd;
  color: #333;
  border-bottom-left-radius: 4px;
}
.message-time {
  font-size: 11px;
  color: #9ca3af;
  margin-top: 6px;
  text-align: inherit;
}
.message-bubble.sent .message-time {
  text-align: right;
}
.message-bubble.received .message-time {
  text-align: left;
}
.chat-input {
  display: flex;
  gap: 10px;
  padding: 15px;
  border-top: 1px solid #e5e7eb;
}
.chat-input input {
  flex-grow: 1;
  padding: 10px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.3s;
}
.chat-input input:focus {
  border-color: #0288d1;
  outline: none;
}
.btn {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  border: none;
  transition: background-color 0.3s;
}
.btn-send {
  background: #0288d1;
  color: #fff;
}
.btn-send:hover {
  background: #0277bd;
}
.btn-send:disabled {
  background: #90caf9;
  cursor: not-allowed;
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
.chat-placeholder {
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.text-center {
  text-align: center;
  padding: 20px;
}
.text-gray-500 {
  color: #6b7280;
}
@media (max-width: 768px) {
  .chat-container {
    flex-direction: column;
  }
  .conversation-list {
    width: 100%;
    max-height: 200px;
  }
  .chat-window {
    display: none;
  }
  .chat-window.active {
    display: flex;
  }
}
</style>