<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use App\Models\Post;

    class PostSeeder extends Seeder
    {
        public function run()
        {
Post::updateOrCreate(
    ['slug' => 'gioi-thieu-ve-dnt-store'],
    [
        'title' => 'Giới thiệu về DNT Store',
        'content' => 'DNT Store là trung tâm sửa chữa điện tử hàng đầu tại Việt Nam...',
        'excerpt' => 'Tìm hiểu về dịch vụ sửa chữa chuyên nghiệp của chúng tôi.',
        'image' => 'image/banner.jpg',
        'is_active' => 1,
    ]
);

Post::updateOrCreate(
    ['slug' => 'cach-bao-quan-dien-thoai-dung-cach'],
    [
        'title' => 'Cách bảo quản điện thoại đúng cách',
        'content' => 'Để điện thoại luôn bền bỉ, bạn cần chú ý đến các yếu tố sau...',
        'excerpt' => 'Mẹo bảo quản điện thoại để kéo dài tuổi thọ.',
        'image' => 'image/banner3.jpg',
        'is_active' => 1,
    ]
);

        }
    }
