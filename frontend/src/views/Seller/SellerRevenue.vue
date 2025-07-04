<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Doanh thu</h1>
    <div class="mb-4">
      <label class="mr-2">Chọn khoảng thời gian:</label>
      <select v-model="filter" @change="fetchRevenue" class="p-2 border rounded">
        <option value="daily">Hàng ngày</option>
        <option value="monthly">Hàng tháng</option>
      </select>
    </div>
    <div v-if="error" class="bg-red-100 text-red-700 p-4 rounded mb-4">
      {{ error }}
    </div>
    <div class="grid grid-cols-3 gap-4 mb-4">
      <div class="bg-white p-4 rounded shadow">
        <h3 class="font-bold">Tổng doanh thu</h3>
        <p>{{ formatCurrency(totalRevenue) }}</p>
      </div>
      <div class="bg-white p-4 rounded shadow">
        <h3 class="font-bold">Số đơn hàng</h3>
        <p>{{ totalOrders }}</p>
      </div>
      <div class="bg-white p-4 rounded shadow">
        <h3 class="font-bold">Sản phẩm bán chạy</h3>
        <p>{{ topProduct?.name || 'N/A' }} ({{ topProduct?.total_sold || 0 }})</p>
      </div>
    </div>
    <table class="w-full bg-white rounded shadow">
      <thead>
        <tr>
          <th class="p-2">Mã đơn hàng</th>
          <th class="p-2">Ngày thanh toán</th>
          <th class="p-2">Tổng tiền</th>
          <th class="p-2">Trạng thái</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in orders" :key="order.id">
          <td class="p-2">{{ order.id }}</td>
          <td class="p-2">{{ formatDate(order.settled_at) }}</td>
          <td class="p-2">{{ formatCurrency(order.total) }}</td>
          <td class="p-2">{{ order.settled_status }}</td>
        </tr>
      </tbody>
    </table>
    <button
      @click="exportReport"
      class="mt-4 bg-blue-500 text-white p-2 rounded hover:bg-blue-600"
    >
      Xuất báo cáo
    </button>
  </div>
</template>

<script>
import axios from 'axios';
import { ref } from 'vue';

export default {
  name: 'SellerRevenue',
  setup() {
    const filter = ref('daily');
    const totalRevenue = ref(0);
    const totalOrders = ref(0);
    const topProduct = ref(null);
    const orders = ref([]);
    const error = ref(null);

    const fetchRevenue = async () => {
      try {
        error.value = null;
        const response = await axios.get('/seller/orders/revenue', {
          params: { filter: filter.value },
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        totalRevenue.value = response.data.data.total_revenue;
        totalOrders.value = response.data.data.total_orders;
        topProduct.value = response.data.data.top_product;
        orders.value = response.data.data.orders;
      } catch (err) {
        error.value = err.response?.data?.message || 'Lỗi khi lấy dữ liệu doanh thu';
        console.error('Fetch revenue error:', err);
      }
    };

    const exportReport = async () => {
      try {
        error.value = null;
        const response = await axios.get('/seller/orders/export-report', {
          params: { filter: filter.value },
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          responseType: 'blob', // Expect binary data (CSV file)
        });

        // Create a blob from the response data
        const blob = new Blob([response.data], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `revenue_${filter.value}_${Date.now()}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        alert('Xuất báo cáo thành công');
      } catch (err) {
        error.value = err.response?.data?.message || 'Lỗi khi xuất báo cáo';
        console.error('Export report error:', err);
      }
    };

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
    };

    const formatDate = (date) => {
      return date ? new Date(date).toLocaleDateString('vi-VN') : 'N/A';
    };

    fetchRevenue();

    return { filter, totalRevenue, totalOrders, topProduct, orders, error, fetchRevenue, exportReport, formatCurrency, formatDate };
  },
};
</script>