FROM node:18

# Tạo thư mục làm việc trong container
WORKDIR /app

# Copy package.json và package-lock.json vào container
COPY package*.json ./

# Cài đặt dependencies của Vue
RUN npm install

# Copy mã nguồn của Vue vào container
COPY . .

# Expose port cho Vue 3 (5173)
EXPOSE 5173

# Command để chạy Vue 3 (phát triển)
CMD ["npm", "run", "dev"]
