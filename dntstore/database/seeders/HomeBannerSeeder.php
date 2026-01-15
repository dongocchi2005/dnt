<?php

namespace Database\Seeders;

use App\Models\HomeBanner;
use Illuminate\Database\Seeder;

class HomeBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HomeBanner::create([
            'title' => 'Trung tâm sửa chữa đồ công nghệ',
            'subtitle' => 'Kiểm tra – báo lỗi rõ ràng. Sửa nhanh – bảo hành đầy đủ. Linh kiện chuẩn.',
            'image' => 'image/bnxin.png',
            'button_text' => 'Đặt lịch sửa ngay',
            'button_link' => '/booking/create',
           
          
        ]);

        HomeBanner::create([
            'title' => 'Sửa chữa chuyên nghiệp',
            'subtitle' => 'Đội ngũ kỹ thuật viên giàu kinh nghiệm, trang bị công nghệ hiện đại.',
            'image' => 'image/bnxin1.png',
            'button_text' => 'Liên hệ ngay',
            'button_link' => '/contact',
       
        ]);

        HomeBanner::create([
            'title' => 'Bảo hành dài hạn',
            'subtitle' => 'Cam kết bảo hành đầy đủ cho tất cả linh kiện thay thế.',
            'image' => 'image/bnxin2.png',
            'button_text' => 'Xem dịch vụ',
            'button_link' => '/services',
           
         
        ]);
    }
}
