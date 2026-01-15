<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Hiển thị trang liên hệ
     */
    public function index()
    {
        return view('frontend.contact'); 
    }

    /**
     * Xử lý gửi form liên hệ
     */
    public function send(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'name' => 'required|string|max:255',
            'email'=> 'required|email|max:255',
            'phone'=> 'nullable|string|max:50',
            'message'=>'required|string',
        ]);

        // Cách 1: Gửi email đến admin
        try {
            Mail::send([], [], function($message) use ($request){
                $message->to('admin@example.com') // thay bằng email của bạn
                        ->subject('Liên hệ từ website')
                        ->setBody(
                            "Họ tên: ".$request->name."<br>".
                            "Email: ".$request->email."<br>".
                            "Điện thoại: ".$request->phone."<br>".
                            "Nội dung: ".$request->message,
                            'text/html'
                        );
            });

            return back()->with('success', 'Gửi liên hệ thành công! Chúng tôi sẽ liên hệ sớm.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gửi liên hệ thất bại. Vui lòng thử lại!');
        }

        // Cách 2: Lưu vào database (nếu muốn)
        // Contact::create($request->all());
    }
}
