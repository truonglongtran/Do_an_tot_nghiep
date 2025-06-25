<!-- src/views/Buyer/BuyerMessages.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Tin nhắn</h1>
    <div class="mb-4">
      <form @submit.prevent="sendMessage" class="flex space-x-2">
        <input v-model="newMessage.shop_id" type="number" placeholder="ID cửa hàng" class="border rounded-lg p-2 flex-1" required />
        <input v-model="newMessage.content" placeholder="Nội dung tin nhắn" class="border rounded-lg p-2 flex-1" required />
        <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded-lg">Gửi</button>
      </form>
    </div>
    <div v-if="messages.length === 0" class="text-center text-gray-600">
      Không có tin nhắn
    </div>
    <div v-else class="space-y-4">
      <div v-for="message in messages" :key="message.id" class="border rounded-lg p-4" :class="{ 'bg-gray-100': !message.is_read }">
        <p class="text-gray-600">{{ message.content }}</p>
        <p class="text-gray-500 text-sm">Từ: {{ message.sender.email }} | Đến: {{ message.receiver.email }}</p>
        <p class="text-gray-500 text-sm">{{ formatDate(message.created_at) }}</p>
        <button v-if="!message.is_read && message.receiver_id === userId" @click="markAsRead(message.id)" class="text-orange-500 hover:underline">
          Đánh dấu đã đọc
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import moment from 'moment';

export default {
  name: 'BuyerMessages',
  data() {
    return {
      messages: [],
      userId: localStorage.getItem('user_id'),
      newMessage: {
        shop_id: '',
        content: '',
      },
    };
  },
  async created() {
    await this.fetchMessages();
  },
  methods: {
    async fetchMessages() {
      try {
        const response = await axios.get('/api/buyer/messages');
        this.messages = response.data.messages;
      } catch (error) {
        console.error('Error fetching messages:', error);
      }
    },
    async sendMessage() {
      try {
        await axios.post('/api/buyer/messages/send', this.newMessage);
        this.newMessage = { shop_id: '', content: '' };
        await this.fetchMessages();
      } catch (error) {
        console.error('Error sending message:', error);
      }
    },
    async markAsRead(id) {
      try {
        await axios.put(`/api/buyer/messages/${id}/read`);
        this.messages = this.messages.map(m => m.id === id ? { ...m, is_read: true } : m);
      } catch (error) {
        console.error('Error marking message as read:', error);
      }
    },
    formatDate(date) {
      return moment(date).format('DD/MM/YYYY HH:mm');
    },
  },
};
</script>

<style scoped>
/* Không cần style vì Tailwind CSS đã xử lý */
</style>