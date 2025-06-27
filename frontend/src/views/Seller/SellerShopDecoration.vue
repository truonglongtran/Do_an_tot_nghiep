<template>
  <div class="container">
    <h2>Trang trí shop</h2>
    <div v-if="error" class="error-message">{{ error }}</div>
    <div v-if="success" class="success-message">{{ success }}</div>

    <!-- Form thêm banner -->
    <div class="card">
      <h3>Thêm banner mới</h3>
      <form @submit.prevent="addBanner" class="form-grid">
        <div class="form-group">
          <label>Tiêu đề</label>
          <input v-model="form.title" type="text" required />
        </div>
        <div class="form-group">
          <label>Hình ảnh</label>
          <input type="file" accept="image/*" @change="form.image = $event.target.files[0]" required />
        </div>
        <div class="form-group">
          <label>Link</label>
          <input v-model="form.link" type="url" />
        </div>
        <div class="form-group">
          <label>Trạng thái</label>
          <select v-model="form.status" required>
            <option value="active">Kích hoạt</option>
            <option value="inactive">Không kích hoạt</option>
          </select>
        </div>
        <div class="form-group">
          <label>Vị trí hiển thị</label>
          <select v-model="form.location_id" required>
            <option v-for="location in locations" :key="location.id" :value="location.id">
              {{ location.location_name }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label>Thứ tự</label>
          <input v-model.number="form.position" type="number" min="1" required />
        </div>
        <button type="submit" :disabled="loading" class="btn btn-primary">
          <span v-if="loading" class="spinner"></span>
          Thêm banner
        </button>
      </form>
    </div>

    <!-- Danh sách banner -->
    <div class="card">
      <h3>Danh sách banner</h3>
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Tiêu đề</th>
              <th>Hình ảnh</th>
              <th>Vị trí</th>
              <th>Thứ tự</th>
              <th>Trạng thái</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="banner in banners" :key="banner.id">
              <td>{{ banner.title }}</td>
              <td><img :src="banner.img_url" alt="Banner" /></td>
              <td>{{ banner.placements[0]?.location?.location_name || 'N/A' }}</td>
              <td>{{ banner.placements[0]?.display_order || 'N/A' }}</td>
              <td>{{ banner.placements[0]?.is_active ? 'Kích hoạt' : 'Không kích hoạt' }}</td>
              <td>
                <button @click="openEditModal(banner)" class="btn btn-edit">Sửa</button>
                <button @click="confirmDeleteBanner(banner.id)" class="btn btn-delete">Xóa</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal xác nhận xóa -->
    <div v-if="showDeleteConfirm" class="modal">
      <div class="modal-content">
        <h3>Xác nhận xóa</h3>
        <p>Bạn có chắc muốn xóa banner này?</p>
        <div class="modal-actions">
          <button @click="deleteBanner" class="btn btn-delete">Xóa</button>
          <button @click="showDeleteConfirm = false" class="btn btn-cancel">Hủy</button>
        </div>
      </div>
    </div>

    <!-- Modal chỉnh sửa banner -->
    <div v-if="showEditModal" class="modal">
      <div class="modal-content">
        <h3>Chỉnh sửa banner</h3>
        <form @submit.prevent="updateBanner" class="form-grid">
          <div class="form-group">
            <label>Tiêu đề</label>
            <input v-model="editForm.title" type="text" required />
          </div>
          <div class="form-group">
            <label>Hình ảnh</label>
            <input type="file" accept="image/*" @change="editForm.image = $event.target.files[0]" />
            <img v-if="editForm.img_url" :src="editForm.img_url" alt="Banner Preview" />
          </div>
          <div class="form-group">
            <label>Link</label>
            <input v-model="editForm.link" type="url" />
          </div>
          <div class="form-group">
            <label>Trạng thái</label>
            <select v-model="editForm.status" required>
              <option value="active">Kích hoạt</option>
              <option value="inactive">Không kích hoạt</option>
            </select>
          </div>
          <div class="form-group">
            <label>Vị trí hiển thị</label>
            <select v-model="editForm.location_id" required>
              <option v-for="location in locations" :key="location.id" :value="location.id">
                {{ location.location_name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Thứ tự</label>
            <input v-model.number="editForm.position" type="number" min="1" required />
          </div>
          <div class="form-actions">
            <button type="submit" :disabled="loading" class="btn btn-primary">
              <span v-if="loading" class="spinner"></span>
              Lưu
            </button>
            <button @click="showEditModal = false" class="btn btn-cancel">Hủy</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ref } from 'vue';

export default {
  name: 'SellerShopDecoration',
  setup() {
    const form = ref({
      title: '',
      image: null,
      link: '',
      status: 'active',
      location_id: null,
      position: 1,
    });
    const editForm = ref({});
    const banners = ref([]);
    const locations = ref([]);
    const error = ref(null);
    const success = ref(null);
    const loading = ref(false);
    const showEditModal = ref(false);
    const showDeleteConfirm = ref(false);
    const deleteBannerId = ref(null);

    const fetchDecoration = async () => {
      try {
        error.value = null;
        success.value = null;
        const response = await axios.get('/seller/shop/decoration', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        banners.value = response.data.data.banners;
        locations.value = response.data.data.locations;
      } catch (err) {
        error.value = err.response?.data?.message || 'Lỗi khi lấy dữ liệu trang trí shop';
        if (err.response?.status === 401) {
          localStorage.removeItem('token');
          window.location.href = '/seller/login';
        }
      }
    };

    const addBanner = async () => {
      try {
        error.value = null;
        success.value = null;
        loading.value = true;
        const formData = new FormData();
        formData.append('title', form.value.title);
        formData.append('image', form.value.image);
        if (form.value.link) formData.append('link', form.value.link);
        formData.append('status', form.value.status);
        formData.append('location_id', form.value.location_id);
        formData.append('position', form.value.position);

        const response = await axios.post('/seller/shop/banners', formData, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'multipart/form-data',
          },
        });

        banners.value.push(response.data.data);
        form.value = { title: '', image: null, link: '', status: 'active', location_id: null, position: 1 };
        success.value = 'Thêm banner thành công';
      } catch (err) {
        error.value = err.response?.data?.message || 'Lỗi khi thêm banner';
        if (err.response?.status === 401) {
          localStorage.removeItem('token');
          window.location.href = '/seller/login';
        }
      } finally {
        loading.value = false;
      }
    };

    const openEditModal = (banner) => {
      editForm.value = {
        id: banner.id,
        title: banner.title,
        img_url: banner.img_url,
        image: null,
        link: banner.link_url,
        status: banner.placements[0]?.is_active ? 'active' : 'inactive',
        location_id: banner.placements[0]?.location_id || null,
        position: banner.placements[0]?.display_order || 1,
      };
      showEditModal.value = true;
    };

    const updateBanner = async () => {
      try {
        error.value = null;
        success.value = null;
        loading.value = true;
        const formData = new FormData();
        formData.append('title', editForm.value.title);
        if (editForm.value.image) formData.append('image', editForm.value.image);
        if (editForm.value.link) formData.append('link', editForm.value.link);
        formData.append('status', editForm.value.status);
        formData.append('location_id', editForm.value.location_id);
        formData.append('position', editForm.value.position);

        const response = await axios.put(`/seller/shop/banners/${editForm.value.id}`, formData, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'multipart/form-data',
          },
        });

        const index = banners.value.findIndex(b => b.id === editForm.value.id);
        banners.value[index] = response.data.data;
        showEditModal.value = false;
        success.value = 'Cập nhật banner thành công';
      } catch (err) {
        error.value = err.response?.data?.message || 'Lỗi khi cập nhật banner';
        if (err.response?.status === 401) {
          localStorage.removeItem('token');
          window.location.href = '/seller/login';
        }
      } finally {
        loading.value = false;
      }
    };

    const confirmDeleteBanner = (id) => {
      deleteBannerId.value = id;
      showDeleteConfirm.value = true;
    };

    const deleteBanner = async () => {
      try {
        error.value = null;
        success.value = null;
        loading.value = true;
        await axios.delete(`/seller/shop/banners/${deleteBannerId.value}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        banners.value = banners.value.filter(b => b.id !== deleteBannerId.value);
        showDeleteConfirm.value = false;
        success.value = 'Xóa banner thành công';
      } catch (err) {
        error.value = err.response?.data?.message || 'Lỗi khi xóa banner';
        if (err.response?.status === 401) {
          localStorage.removeItem('token');
          window.location.href = '/seller/login';
        }
      } finally {
        loading.value = false;
      }
    };

    fetchDecoration();

    return {
      form,
      editForm,
      banners,
      locations,
      error,
      success,
      loading,
      showEditModal,
      showDeleteConfirm,
      addBanner,
      openEditModal,
      updateBanner,
      confirmDeleteBanner,
      deleteBanner,
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
  color: #333;
  margin-bottom: 20px;
}

h3 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 15px;
}

.card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 20px;
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

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 14px;
  font-weight: 500;
  color: #555;
  margin-bottom: 5px;
}

.form-group input,
.form-group select {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus {
  border-color: #0288d1;
  outline: none;
}

.form-group input[type="file"] {
  padding: 5px;
}

.form-group img {
  max-width: 80px;
  margin-top: 10px;
  border-radius: 4px;
}

.btn {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  border: none;
  transition: background-color 0.3s;
}

.btn-primary {
  background: #0288d1;
  color: #fff;
}

.btn-primary:hover {
  background: #0277bd;
}

.btn-primary:disabled {
  background: #90caf9;
  cursor: not-allowed;
}

.btn-edit {
  background: #0288d1;
  color: #fff;
}

.btn-edit:hover {
  background: #0277bd;
}

.btn-delete {
  background: #d32f2f;
  color: #fff;
}

.btn-delete:hover {
  background: #b71c1c;
}

.btn-cancel {
  background: #757575;
  color: #fff;
}

.btn-cancel:hover {
  background: #616161;
}

.table-responsive {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background: #f5f5f5;
  font-weight: 600;
}

td img {
  max-width: 60px;
  border-radius: 4px;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  max-width: 500px;
  width: 100%;
}

.form-actions, .modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
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

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  th, td {
    font-size: 12px;
    padding: 8px;
  }
}
</style>