<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Revenue;
use App\Models\User;
use App\Notifications\PaymentProofUploaded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function pay(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // Ensure the booking belongs to the authenticated user
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if payment is already completed
        if ($booking->payment_status === 'completed') {
            return redirect()->route('booking.history')->with('info', 'Đơn hàng đã được thanh toán.');
        }

        // Update booking with transaction reference
        $transactionId = $booking->id . '_' . time();
        $booking->update([
            'transaction_id' => $transactionId,
            'payment_method' => 'vietqr',
            'payment_status' => 'pending'
        ]);

        // Generate VietQR code data
        $qrData = $this->generateVietQRData($booking);

        return view('frontend.payment', compact('booking', 'qrData'));
    }

    private function generateVietQRData($booking)
    {
        // VietQR format for personal bank accounts
        // You need to replace these with your actual bank account details
        $bankId = config('vietqr.bank_id', '970422'); // Example: Vietcombank
        $accountNumber = config('vietqr.account_number', '1234567890'); // Your bank account number
        $accountName = config('vietqr.account_name', 'NGUYEN VAN A'); // Account holder name
        $amount = $booking->price;
        $description = 'Thanh toan don hang ' . $booking->id;

        // Build VietQR string according to VietQR specification
        $qrString = "000201"; // Payload Format Indicator
        $qrString .= "010212"; // Point of Initiation Method (dynamic)

        // Merchant Account Information (ID 38)
        $merchantAccountInfo = "0010A000000727"; // GUID for VietQR
        $merchantAccountInfo .= "01" . str_pad(strlen($bankId), 2, '0', STR_PAD_LEFT) . $bankId; // Bank BIN
        $merchantAccountInfo .= "02" . str_pad(strlen($accountNumber), 2, '0', STR_PAD_LEFT) . $accountNumber; // Account Number

        $qrString .= "38" . str_pad(strlen($merchantAccountInfo), 2, '0', STR_PAD_LEFT) . $merchantAccountInfo;

        $qrString .= "52045812"; // Merchant Category Code (5812 = repair services)
        $qrString .= "5303704"; // Transaction Currency (704 = VND)
        $qrString .= "54" . str_pad(strlen((string)$amount), 2, '0', STR_PAD_LEFT) . $amount; // Transaction Amount
        $qrString .= "5802VN"; // Country Code
        $qrString .= "59" . str_pad(strlen($accountName), 2, '0', STR_PAD_LEFT) . $accountName; // Merchant Name
        $qrString .= "62" . str_pad(strlen($description), 2, '0', STR_PAD_LEFT) . $description; // Additional Data

        // Calculate and append CRC
        $qrString .= "6304"; // CRC placeholder
        $crc = $this->calculateCRC($qrString);
        $qrString = substr($qrString, 0, -4) . $crc;

        return $qrString;
    }

    private function calculateCRC($data)
    {
        // Simple CRC16-CCITT calculation for VietQR
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= ord($data[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc <<= 1;
                }
                $crc &= 0xFFFF;
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    private function generateVietQRDataForOrder($order)
    {
        // VietQR format for personal bank accounts
        // You need to replace these with your actual bank account details
        $bankId = config('vietqr.bank_id', '970422'); // Example: Vietcombank
        $accountNumber = config('vietqr.account_number', '1234567890'); // Your bank account number
        $accountName = config('vietqr.account_name', 'NGUYEN VAN A'); // Account holder name
        $amount = $order->total_amount;
        $description = 'Thanh toan don hang ' . $order->id;

        // Build VietQR string according to VietQR specification
        $qrString = "000201"; // Payload Format Indicator
        $qrString .= "010212"; // Point of Initiation Method (dynamic)

        // Merchant Account Information (ID 38)
        $merchantAccountInfo = "0010A000000727"; // GUID for VietQR
        $merchantAccountInfo .= "01" . str_pad(strlen($bankId), 2, '0', STR_PAD_LEFT) . $bankId; // Bank BIN
        $merchantAccountInfo .= "02" . str_pad(strlen($accountNumber), 2, '0', STR_PAD_LEFT) . $accountNumber; // Account Number

        $qrString .= "38" . str_pad(strlen($merchantAccountInfo), 2, '0', STR_PAD_LEFT) . $merchantAccountInfo;

        $qrString .= "52045812"; // Merchant Category Code (5812 = repair services)
        $qrString .= "5303704"; // Transaction Currency (704 = VND)
        $qrString .= "54" . str_pad(strlen((string)$amount), 2, '0', STR_PAD_LEFT) . $amount; // Transaction Amount
        $qrString .= "5802VN"; // Country Code
        $qrString .= "59" . str_pad(strlen($accountName), 2, '0', STR_PAD_LEFT) . $accountName; // Merchant Name
        $qrString .= "62" . str_pad(strlen($description), 2, '0', STR_PAD_LEFT) . $description; // Additional Data

        // Calculate and append CRC
        $qrString .= "6304"; // CRC placeholder
        $crc = $this->calculateCRC($qrString);
        $qrString = substr($qrString, 0, -4) . $crc;

        return $qrString;
    }

    public function payOrder(Request $request)
    {
        $cart = session('cart.items', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống.');
        }

        $selectedKeys = $request->input('selected_keys');
        if (!is_array($selectedKeys)) {
            $selectedKeys = [];
        }

        $cartToPay = $cart;
        if (!empty($selectedKeys)) {
            $cartToPay = array_intersect_key($cart, array_flip(array_map('strval', $selectedKeys)));
            if (empty($cartToPay)) {
                return redirect()->route('cart.index')->with('error', 'Vui lòng chọn sản phẩm để thanh toán.');
            }
        }

        // Check if user has complete shipping information
        $user = auth()->user();
        if (!$user->phone || !$user->address || !$user->city || !$user->district || !$user->ward) {
            return redirect()->route('settings')->with('warning', 'Vui lòng cập nhật đầy đủ thông tin giao hàng (số điện thoại, địa chỉ, tỉnh/thành phố, quận/huyện, phường/xã) trước khi thanh toán.');
        }

        // Get payment method from request, default to vietqr
        $paymentMethod = $request->input('payment_method', 'vietqr');

        // Calculate total amount
        $totalAmount = collect($cartToPay)->sum(fn($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0));

        $order = null;
        DB::transaction(function () use ($cartToPay, $totalAmount, $paymentMethod, &$order) {
            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'payment_status' => $paymentMethod === 'cash_on_delivery' ? 'pending' : 'pending',
                'payment_method' => $paymentMethod,
                'transaction_id' => $paymentMethod === 'cash_on_delivery' ? 'cod_' . time() : 'order_' . time(),
            ]);

            // Create order items
            foreach ($cartToPay as $key => $item) {
                $variantId = $item['variant_id'] ?? null;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => (int)($item['id'] ?? 0),
                    'variant_id' => $variantId ? (int)$variantId : null,
                    'product_name' => $item['name'] ?? 'Sản phẩm',
                    'variant_label' => $item['variant_name'] ?? null,
                    'variant_sku' => $item['variant_sku'] ?? null,
                    'product_image' => $item['image'] ?? '',
                    'price' => $item['price'] ?? 0,
                    'quantity' => $item['qty'] ?? 0,
                    'subtotal' => ($item['price'] ?? 0) * ($item['qty'] ?? 0),
                ]);
            }

            if ($paymentMethod === 'cash_on_delivery') {
                foreach ($order->items as $it) {
                    $qty = (int)($it->quantity ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }
                    if ($it->variant_id) {
                        $variant = \App\Models\ProductVariant::where('id', $it->variant_id)->lockForUpdate()->first();
                        if (!$variant || (int)$variant->stock < $qty) {
                            throw new \RuntimeException('Không đủ tồn kho biến thể.');
                        }
                        $variant->decrement('stock', $qty);
                    } else {
                        $product = \App\Models\Product::where('id', $it->product_id)->lockForUpdate()->first();
                        if ($product && (int)$product->stock > 0 && (int)$product->stock < $qty) {
                            throw new \RuntimeException('Không đủ tồn kho sản phẩm.');
                        }
                        if ($product && (int)$product->stock > 0) {
                            $product->decrement('stock', $qty);
                        }
                    }
                }
            }
        });

        if (!$order) {
            return redirect()->route('cart.index')->with('error', 'Không thể tạo đơn hàng.');
        }

        if (!empty($selectedKeys)) {
            foreach ($selectedKeys as $k) {
                unset($cart[(string)$k]);
            }
            session(['cart.items' => $cart]);
        } else {
            session()->forget('cart.items');
        }

        if ($paymentMethod === 'cash_on_delivery') {
            // For cash on delivery, show order information without QR code
            return view('frontend.payment', compact('order'))
                   ->with('success', 'Đơn hàng đã được tạo thành công. Bạn sẽ thanh toán khi nhận hàng.');
        }

        // For online payment, generate QR code
        $qrData = $this->generateVietQRDataForOrder($order);

        return view('frontend.payment', compact('order', 'qrData'));
    }

    public function payOrderById(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->payment_status === 'completed') {
            return redirect()->route('orders.show', $order->id)
                ->with('info', 'Đơn hàng đã được thanh toán.');
        }

        $order->update([
            'payment_method' => 'vietqr',
            'payment_status' => 'pending',
            'transaction_id' => $order->transaction_id ?: 'order_' . time(),
        ]);

        $qrData = $this->generateVietQRDataForOrder($order);

        return view('frontend.payment', compact('order', 'qrData'));
    }

    public function return(Request $request)
    {
        $vnp_HashSecret = config('vnpay.hash_secret');

        $inputData = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $vnpTranId = $inputData['vnp_TransactionNo'];
        $vnp_BankTranNo = $inputData['vnp_BankTranNo'];
        $vnp_Amount = $inputData['vnp_Amount'];

        $Status = 0;
        $orderId = $inputData['vnp_TxnRef'];

        if ($secureHash == $request->vnp_SecureHash) {
            if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                $Status = 1; // Payment success
            } else {
                $Status = 2; // Payment failed
            }
        } else {
            $Status = 0; // Invalid signature
        }

        // Check if it's an order or booking transaction
        if (str_starts_with($orderId, 'order_')) {
            // Handle order payment
            $orderId = explode('_', $orderId)[1];
            $order = Order::find($orderId);

            if ($order) {
                if ($Status == 1) {
                    $order->update(['payment_status' => 'completed']);
                    // Clear cart after successful payment
                    session()->forget('cart.items');
                    foreach ($order->items as $item) {
                        $qty = (int)($item->quantity ?? 0);
                        if ($qty <= 0) {
                            continue;
                        }
                        if ($item->variant_id) {
                            $variant = \App\Models\ProductVariant::where('id', $item->variant_id)->first();
                            if ($variant && (int)$variant->stock >= $qty) {
                                $variant->decrement('stock', $qty);
                            }
                        } else {
                            $product = Product::find($item->product_id);
                            if ($product && (int)$product->stock > 0) {
                                $product->decrement('stock', $qty);
                            }
                        }
                    }
                    // Add to revenue
                    Revenue::create([
                        'type' => 'sales',
                        'amount' => $order->total_amount,
                    ]);
                    Log::info('Payment successful for order ' . $orderId);
                    return redirect()->route('home')->with('success', 'Thanh toán thành công! Đơn hàng của bạn đang được xử lý.');
                } else {
                    $order->update(['payment_status' => 'failed']);
                    Log::warning('Payment failed for order ' . $orderId);
                    return redirect()->route('cart.index')->with('error', 'Thanh toán thất bại. Vui lòng thử lại.');
                }
            }
        } else {
            // Handle booking payment
            $bookingId = explode('_', $orderId)[0];
            $booking = Booking::find($bookingId);

            if ($booking) {
                if ($Status == 1) {
                    $booking->update([
                        'payment_status' => 'completed',
                        'status' => 'confirmed' // Update booking status to confirmed after payment
                    ]);
                    // Add to revenue
                    Revenue::create([
                        'type' => 'booking',
                        'amount' => $booking->price,
                    ]);
                    Log::info('Payment successful for booking ' . $bookingId);
                    return redirect()->route('booking.history')->with('success', 'Thanh toán thành công!');
                } else {
                    $booking->update(['payment_status' => 'failed']);
                    Log::warning('Payment failed for booking ' . $bookingId);
                    return redirect()->route('booking.history')->with('error', 'Thanh toán thất bại. Vui lòng thử lại.');
                }
            }
        }

        return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng.');
    }

public function uploadPaymentProof(Request $request)
{
//  { dd(
//     $request->all(),
//     $request->hasFile('payment_proof'),
//     $request->file('payment_proof')
// );

    $request->validate([
     'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:10240',

        'type' => 'required|in:order,booking',
        'id' => 'required|integer',
    ]);

    $file = $request->file('payment_proof');
    $filename = time().'_'.$file->getClientOriginalName();

    // LƯU FILE VÀO public/image
    $file->move(public_path('image'), $filename);
    $path = 'image/'.$filename;

    if ($request->type === 'booking') {
        $booking = Booking::findOrFail($request->id);

        // bảo mật: chỉ chủ booking mới upload
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->update([
            'payment_proof' => $path,
            'payment_status' => 'pending',
        ]);

        $admins = User::where('is_admin', true)->get();
Notification::send($admins, new PaymentProofUploaded('booking', $booking));
        return redirect()
            ->route('payment.thankYou', ['type' => 'booking', 'id' => $booking->id])
            ->with('success', 'Đã gửi ảnh chuyển khoản, vui lòng chờ xác nhận.');
    }

    // ===== ORDER =====
    $order = Order::findOrFail($request->id);

    if ($order->user_id !== auth()->id()) {
        abort(403);
    }

    $order->update([
        'payment_proof' => $path,
        'payment_status' => 'pending',
    ]);

   $admins = User::where('is_admin', true)->get();
Notification::send($admins, new PaymentProofUploaded('order', $order));


    return redirect()
        ->route('payment.thankYou', ['type' => 'order', 'id' => $order->id])
        ->with('success', 'Đã gửi ảnh chuyển khoản, vui lòng chờ xác nhận.');
}



public function thankYou(string $type, int $id)
{
    if ($type === 'order') {
        $order = Order::findOrFail($id);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('frontend.payment.thank-you', [
            'type' => 'order',
            'order' => $order,
        ]);
    }

    if ($type === 'booking') {
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        return view('frontend.payment.thank-you', [
            'type' => 'booking',
            'booking' => $booking,
        ]);
    }

    abort(404);
}

}
