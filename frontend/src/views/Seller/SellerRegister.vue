<template>
  <div class="container">
    <h2 class="title">Đăng ký người bán</h2>
    <form @submit.prevent="register" class="form">
      <div class="form-group">
        <label class="label">Email</label>
        <input
          v-model="form.email"
          type="email"
          class="input"
          :disabled="isLoggedIn"
          required
        />
      </div>
      <div v-if="isNewUser" class="form-group">
        <label class="label">Mật khẩu</label>
        <input
          v-model="form.password"
          type="password"
          class="input"
          required
        />
      </div>
      <div v-if="isNewUser" class="form-group">
        <label class="label">Tên người dùng</label>
        <input
          v-model="form.username"
          type="text"
          class="input"
          required
        />
      </div>
      <div v-if="isNewUser" class="form-group">
        <label class="label">Số điện thoại</label>
        <input
          v-model="form.phone_number"
          type="text"
          class="input"
          required
        />
      </div>
      <div class="form-group">
        <label class="label">Tên cửa hàng</label>
        <input
          v-model="form.shop_name"
          type="text"
          class="input"
          required
        />
      </div>
      <div class="form-group">
        <label class="label">Địa chỉ lấy hàng</label>
        <input
          v-model="form.pickup_address"
          type="text"
          class="input"
          required
        />
      </div>
      <div class="form-group">
        <label class="label">Phường/Xã</label>
        <input
          v-model="form.ward"
          type="text"
          class="input"
          required
        />
      </div>
      <div class="form-group">
        <label class="label">Quận/Huyện</label>
        <input
          v-model="form.district"
          type="text"
          class="input"
          required
        />
      </div>
      <div class="form-group">
        <label class="label">Thành phố</label>
        <input
          v-model="form.city"
          type="text"
          class="input"
          required
        />
      </div>
      <div class="form-group">
        <label class="label">Số điện thoại cửa hàng</label>
        <input
          v-model="form.shop_phone_number"
          type="text"
          class="input"
          required
        />
      </div>
      <div class="form-group" v-if="!isLoggedIn">
        <label class="checkbox-label">
          <input
            v-model="isNewUser"
            type="checkbox"
            class="checkbox"
          />
          Đăng ký tài khoản mới
        </label>
      </div>
      <button
        type="submit"
        class="submit-button"
        :disabled="loading"
      >
        {{ loading ? 'Đang đăng ký...' : 'Đăng ký' }}
      </button>
      <div v-if="errors" class="error">
        {{ errors }}
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      form: {
        email: '',
        password: '',
        username: '',
        phone_number: '',
        shop_name: '',
        pickup_address: '',
        ward: '',
        district: '',
        city: '',
        shop_phone_number: '',
      },
      isNewUser: !this.isLoggedIn,
      loading: false,
      errors: null,
    };
  },
  computed: {
    isLoggedIn() {
      return !!localStorage.getItem('token');
    },
  },
  mounted() {
    if (this.isLoggedIn) {
      this.form.email = localStorage.getItem('email') || '';
      this.isNewUser = false;
    }
  },
  methods: {
    async register() {
      this.loading = true;
      this.errors = null;
      try {
        const payload = {
          email: this.form.email,
          shop_name: this.form.shop_name,
          pickup_address: this.form.pickup_address,
          ward: this.form.ward,
          district: this.form.district,
          city: this.form.city,
          shop_phone_number: this.form.shop_phone_number,
          is_new_user: this.isNewUser,
        };
        if (this.isNewUser) {
          payload.password = this.form.password;
          payload.username = this.form.username;
          payload.phone_number = this.form.phone_number || '';
        }
        console.log('Register payload:', payload);
        const response = await axios.post('http://localhost:8000/api/seller/register', payload, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('role', response.data.role);
        localStorage.setItem('loginType', response.data.loginType);
        localStorage.setItem('email', response.data.user.email);
        localStorage.setItem('username', response.data.user.username);
        localStorage.setItem('avatar_url', response.data.user.avatar_url);
        window.dispatchEvent(new Event('storage'));
        this.$router.push('/seller/dashboard');
      } catch (error) {
        this.errors = error.response?.data?.message || 'Lỗi đăng ký';
        console.error('Registration error:', error.response?.data || error.message);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.container {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}

.title {
  font-size: 24px;
  font-weight: bold;
  color: #333;
  margin-bottom: 20px;
  text-align: center;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.label {
  font-size: 14px;
  color: #555;
  margin-bottom: 5px;
}

.input {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
  color: #333;
}

.input:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
}

.input:disabled {
  background-color: #f8f8f8;
  cursor: not-allowed;
}

.checkbox-label {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #555;
}

.checkbox {
  margin-right: 8px;
}

.submit-button {
  padding: 10px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.submit-button:hover {
  background-color: #0056b3;
}

.submit-button:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}

.error {
  color: #dc3545;
  font-size: 14px;
  margin-top: 10px;
  text-align: center;
}
</style>