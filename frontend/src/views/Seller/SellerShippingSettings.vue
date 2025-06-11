<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold">Cài đặt vận chuyển</h1>
    <div v-if="isLoading" class="text-center">Đang tải...</div>
    <div v-else-if="shippingPartners.length === 0" class="text-center text-gray-500">
      Không tìm thấy đơn vị vận chuyển nào.
    </div>
    <div v-else class="space-y-4">
      <div v-for="partner in shippingPartners" :key="partner.id" class="flex items-center justify-between p-4 border rounded-lg">
        <div>
          <h3 class="text-lg font-semibold">{{ partner.name }}</h3>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input
            type="checkbox"
            :checked="partner.is_used"
            @change="toggleShippingPartner(partner.id, $event.target.checked)"
            class="sr-only peer"
          />
          <div
            class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"
          ></div>
        </label>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SellerShippingSettings',
  data() {
    return {
      shippingPartners: [],
      isLoading: false,
    };
  },
  methods: {
    async fetchShippingPartners() {
      this.isLoading = true;
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          alert('Vui lòng đăng nhập lại');
          this.$router.push('/seller/login');
          return;
        }

        const response = await axios.get('http://localhost:8000/api/seller/shipping-partners', {
          headers: { Authorization: `Bearer ${token}` },
        });

        this.shippingPartners = response.data.data || [];
        console.log('Fetched shipping partners:', this.shippingPartners);
      } catch (error) {
        console.error('Error fetching shipping partners:', error);
        alert('Không thể tải danh sách đơn vị vận chuyển: ' + (error.response?.data?.message || 'Lỗi không xác định'));
      } finally {
        this.isLoading = false;
      }
    },
    async toggleShippingPartner(shippingPartnerId, isChecked) {
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          alert('Vui lòng đăng nhập lại');
          this.$router.push('/seller/login');
          return;
        }

        const response = await axios.post(
          'http://localhost:8000/api/seller/shipping-partners/toggle',
          { shipping_partner_id: shippingPartnerId },
          { headers: { Authorization: `Bearer ${token}` } }
        );

        // Cập nhật trạng thái is_used trong frontend
        const partner = this.shippingPartners.find(p => p.id === shippingPartnerId);
        if (partner) {
          partner.is_used = response.data.is_used;
        }

        alert(response.data.message);
      } catch (error) {
        console.error('Error toggling shipping partner:', error);
        alert('Lỗi khi cập nhật trạng thái: ' + (error.response?.data?.message || 'Lỗi không xác định'));
      }
    },
  },
  mounted() {
    this.fetchShippingPartners();
  },
};
</script>

<style scoped>
/* Tùy chỉnh thêm nếu cần */
</style>