# Deploy lên VPS

Chạy trong thư mục project trên VPS (ví dụ: `/var/www/dntstore/dntstore`).

## Cách chạy nhanh

```bash
cd /var/www/dntstore/dntstore
BRANCH=main bash scripts/deploy.sh
```

## Biến môi trường

- `BRANCH`: nhánh cần deploy (mặc định `main`).
- `APP_DIR`: đường dẫn thư mục project (mặc định là thư mục hiện tại).

Ví dụ:

```bash
APP_DIR=/var/www/dntstore/dntstore BRANCH=main bash /var/www/dntstore/dntstore/scripts/deploy.sh
```

## Nếu bị lỗi quyền ghi

```bash
cd /var/www/dntstore/dntstore
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

