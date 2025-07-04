<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Thông báo</h1>
    <div v-if="notifications.length === 0" class="text-center text-gray-600">
      Không có thông báo
    </div>
    <div v-else class="space-y-4">
      <div v-for="notification in notifications" :key="notification.id" class="border rounded-lg p-4" :class="{ 'bg-gray-100': !notification.is_read }">
        <p class="font-semibold">{{ notification.title }}</p>
        <p class="text-gray-600">{{ notification.message }}</p>
        <p class="text-gray-500 text-sm">{{ formatDate(notification.created_at) }}</p>
        <button v-if="!notification.is_read" @click="markAsRead(notification.id)" class="text-orange-500 hover:underline">
          Đánh dấu đã đọc
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BuyerNotifications',
  data() {
    return {
      notifications: [],
    };
  },
  async created() {
    await this.fetchNotifications();
  },
  methods: {
    async fetchNotifications() {
      try {
        const response = await axios.get('/buyer/notifications');
        this.notifications = response.data.notifications;
      } catch (error) {
        console.error('Error fetching notifications:', error);
      }
    },
    async markAsRead(id) {
      try {
        await axios.put(`/buyer/notifications/${id}/read`);
        this.notifications = this.notifications.map(n => n.id === id ? { ...n, is_read: true } : n);
      } catch (error) {
        console.error('Error marking notification as read:', error);
      }
    },
    formatDate(date) {
      const d = new Date(date);
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0'); // getMonth() trả về 0-11
      const year = d.getFullYear();
      const hours = String(d.getHours()).padStart(2, '0');
      const minutes = String(d.getMinutes()).padStart(2, '0');
      return `${day}/${month}/${year} ${hours}:${minutes}`;
    },
  },
};
</script>

<style scoped>
/* Không cần style vì Tailwind CSS đã xử lý */
</style>