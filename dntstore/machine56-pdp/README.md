# Machine56-style Product Detail Page (Demo)

Demo này dựng riêng một Product Detail Page phong cách Techwear/Cyberpunk (Machine56-esque) bằng **React (Vite)** + **Node.js (Express)** và **mock data** để xem UI chạy ngay.

## Cấu trúc

- `client/`: React UI (CSS Modules)
- `server/`: Express API (mock data)

## API

- `GET /api/products/:productId`  
  Trả về JSON theo format yêu cầu: `id, name, code, price, oldPrice, description, mainImage, thumbnailImages, styles, sizes, materialsInfo, decorationText`.

- `POST /api/cart/add`  
  Nhận `{ productId, styleId, size, quantity }`, trả về `{ success: true, message }`.

## Chạy demo (Windows)

Lưu ý: trên Windows có thể bị chặn `npm` (PowerShell execution policy). Dùng `npm.cmd` thay cho `npm`.

### 1) Chạy backend

```bash
cd machine56-pdp/server
npm.cmd install
npm.cmd run dev
```

Backend mặc định chạy `http://localhost:4000`.

### 2) Chạy frontend

```bash
cd machine56-pdp/client
npm.cmd install
npm.cmd run dev
```

Frontend mặc định chạy `http://localhost:5173` và proxy `/api` sang backend.

## Ghi chú

- Dữ liệu demo nằm tại `server/src/mock/product.js`.
- UI có: gallery + thumbnails, “float box” vật liệu, style cards, size grid, qty selector, nút BUY NOW (gọi mock API).

