<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeBanner extends Model
{
    use HasFactory;

    // Tên bảng trong database, nếu đặt tên theo chuẩn Laravel thì không cần khai báo
    protected $table = 'home_banners';

    // Khóa chính, mặc định là 'id'
    protected $primaryKey = 'id';

    // Nếu bảng không có created_at, updated_at
    public $timestamps = true;

    // Các trường có thể gán giá trị (mass assignable)
    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'link',
       
    ];

    // Có thể thêm các scope hoặc function tuỳ nhu cầu
    // public function scopeActive($query)
    // {
    //     return $query->where('status', 1)->orderBy('sort_order', 'asc');
    // }
}
