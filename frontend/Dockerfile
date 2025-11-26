FROM node:20-slim

WORKDIR /app

# Install dependencies
COPY package.json package-lock.json ./
RUN npm install

# Copy source
COPY . .

# Expose Vite port
EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host"]