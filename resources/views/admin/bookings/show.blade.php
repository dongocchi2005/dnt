@extends('layouts.admin')

@section('title', 'Chi tiết đặt lịch')

@section('content')
<div class="container mx-auto px-4 py-8 text-bl">

    <a href="{{ route('admin.bookings.index') }}"
       class="text-sm text-bl/80 hover:text-bl">
        ← Trở về danh sách
    </a>

    <div class="mt-4">
        <h2 class="text-xl font-bold mb-4 text-bl">
            Đặt lịch #{{ $booking->id }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Cột trái --}}
            <div>
                <h3 class="font-semibold text-bl/90">Khách hàng</h3>
                <p class="text-bl">
                    {{ $booking->customer_name ?? ($booking->user->name ?? 'Khách vãng lai') }}
                </p>
                <p class="text-sm text-bl/70">
                    {{ $booking->phone ?? ($booking->user->email ?? '') }}
                </p>

                <h3 class="font-semibold mt-4 text-bl/90">Thiết bị</h3>
                <p class="text-bl">{{ $booking->device_name }}</p>
                <p class="text-sm text-bl/70">{{ $booking->device_issue }}</p>
            </div>

            {{-- Cột phải --}}
            <div>
                <h3 class="font-semibold text-bl/90">Chi tiết</h3>

                <p class="text-bl">
                    Service ID: {{ $booking->service_id }}
                </p>

                <p class="text-bl">
                    Ngày đặt:
                    {{ optional($booking->booking_date)->format('d/m/Y H:i')
                        ?? optional($booking->created_at)->format('d/m/Y H:i') }}
                </p>

                <p class="text-bl">
                    Time slot: {{ $booking->time_slot }}
                </p>

                <p class="mt-4 text-bl flex items-center gap-2">
                    Trạng thái hiện tại:

                    @if($booking->status_key === 'completed')
                        <span class="px-2 py-1 rounded-full bg-green-500/20 text-green-300 border border-green-500/30 text-xs">
                            {{ $booking->status_label }}
                        </span>
                    @elseif($booking->status_key === 'confirmed')
                        <span class="px-2 py-1 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 text-xs">
                            {{ $booking->status_label }}
                        </span>
                    @elseif($booking->status_key === 'cancelled')
                        <span class="px-2 py-1 rounded-full bg-red-500/20 text-red-300 border border-red-500/30 text-xs">
                            {{ $booking->status_label }}
                        </span>
                    @else
                        <span class="px-2 py-1 rounded-full bg-yellow-500/20 text-yellow-300 border border-yellow-500/30 text-xs">
                            {{ $booking->status_label }}
                        </span>
                    @endif
                </p>

                <form method="POST"
                      action="{{ route('admin.bookings.update', $booking->id) }}"
                      class="mt-4">
                    @csrf
                    @method('PATCH')

                    <label class="block mb-2 text-bl/80">
                        Đổi trạng thái
                    </label>

                    @php
                        $options = [
                            'pending'   => 'Đang chờ',
                            'confirmed' => 'Đang Sửa Chữa',
                            'completed' => 'Đã hoàn thành',
                            'cancelled' => 'Đã hủy',
                        ];
                    @endphp

                    <div class="flex flex-wrap gap-2 items-center">
                        <select
                            name="status"
                            class="border border-white/30 bg-gray-800 text-bl px-2 py-1 rounded appearance-none"
                        >
                            @foreach($options as $val => $text)
                                <option value="{{ $val }}" {{ $booking->status_key === $val ? 'selected' : '' }} class="bg-gray-800 text-bl">
                                    {{ $text }}
                                </option>
                            @endforeach
                        </select>

                        <input
                            type="number"
                            step="0.01"
                            name="price"
                            value="{{ $booking->price ?? '' }}"
                            placeholder="Giá (VND)"
                            class="border border-white/30 bg-transparent text-bl px-2 py-1 rounded w-40 placeholder-white/50"
                        />

                        <button
                            type="submit"
                            class="px-3 py-1 bg-blue-600 text-bl rounded hover:bg-blue-700">
                            Cập nhật
                        </button>
                    </div>

                    <div class="mt-4">
                        <label class="block mb-2 text-bl/80">
                            Ghi chú kỹ thuật / Lỗi đã sửa
                        </label>
                        <textarea
                            name="repair_note"
                            rows="4"
                            class="w-full border border-white/30 bg-transparent text-bl px-3 py-2 rounded placeholder-white/50"
                            placeholder="Ví dụ: Thay pin, vệ sinh socket sạc, fix lỗi loa rè, cập nhật firmware..."
                        >{{ old('repair_note', $booking->repair_note) }}</textarea>
                    </div>

                    <p class="mt-2 text-xs text-bl/60">
                        Lưu ý: Nhớ Nhập Giá nếu thay đổi trạng thái thành "Đã hoàn thành".
                    </p>
                </form>
            </div>

        </div>
    </div>
</div>
<div class="mt-6 p-4 border border-white/20 rounded bg-white/5">
    <h3 class="font-bold mb-2 text-bl">Ảnh thiết bị</h3>
    @if($booking->attachments && $booking->attachments->count())
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($booking->attachments as $attachment)
                @php $url = \App\Support\MediaHelper::mediaUrl($attachment->path); @endphp
                <a href="{{ $url }}" target="_blank" class="block">
                    <img
                        src="{{ $url }}"
                        alt="{{ $attachment->original_name ?? 'Ảnh thiết bị' }}"
                        class="w-full h-32 object-cover rounded border border-white/20"
                    />
                </a>
            @endforeach
        </div>
    @else
        <div class="text-bl/50">Chưa có ảnh thiết bị.</div>
    @endif
</div>

<div class="mt-6 p-4 border border-white/20 rounded bg-white/5">
    <h3 class="font-bold mb-2 text-bl">Xác nhận thanh toán</h3>

    @php $ps = $booking->payment_status ?? 'pending'; @endphp

    <div class="mb-3 text-bl">
        <span class="text-bl/70">Trạng thái:</span>
        @if($ps === 'completed')
            <span class="ml-2 text-green-400 font-bold">Đã xác nhận</span>
        @elseif($ps === 'failed')
            <span class="ml-2 text-red-400 font-bold">Từ chối</span>
        @else
            <span class="ml-2 text-yellow-300 font-bold">Chờ xác nhận</span>
        @endif
    </div>

    @if($booking->payment_proof)
        <div class="mt-3">
            <div class="mb-2 text-bl/70">Ảnh chuyển khoản:</div>

            @php $paymentProofUrl = \App\Support\MediaHelper::mediaUrl($booking->payment_proof); @endphp
            <a href="{{ $paymentProofUrl }}" target="_blank">
                <img src="{{ $paymentProofUrl }}"
                     style="max-width:520px;border-radius:10px;border:1px solid rgba(255,255,255,.2)">
            </a>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.bookings.payment.confirm', $booking) }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-green-600 text-bl rounded hover:bg-green-700">
                    Xác nhận
                </button>
            </form>

            <form method="POST" action="{{ route('admin.bookings.payment.reject', $booking) }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-bl rounded hover:bg-red-700">
                    Từ chối
                </button>
            </form>
        </div>
    @else
        <div class="text-bl/50">Chưa có ảnh chuyển khoản.</div>
    @endif
</div>


@endsection
