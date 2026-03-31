# --- Build Stage ---
FROM node:18-alpine AS build

# Set working directory
WORKDIR /app

# Copy package files
COPY package.json package-lock.json* ./

# Install dependencies (using legacy-peer-deps due to older react-scripts)
RUN npm install --legacy-peer-deps

# Copy project files
COPY . .

# Set environment variable needed for older react-scripts on newer Node
ENV NODE_OPTIONS=--openssl-legacy-provider

# Build the React app for production
RUN npm run build


# --- Production Stage ---
FROM nginx:alpine

# Copy the build output from the build stage to nginx's public folder
COPY --from=build /app/build /usr/share/nginx/html

# Expose port 80
EXPOSE 80

# Start nginx
CMD ["nginx", "-g", "daemon off;"]
