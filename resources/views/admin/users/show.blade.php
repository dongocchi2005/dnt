@extends('layouts.admin')


@section('title', 'Chi tiết người dùng')

@section('page-title', 'Người dùng')

@section('content')
@php
  $role = $user->role ?? (($user->is_admin ?? false) ? 'admin' : 'user');
  $status = $user->status ?? ($user->is_locked ?? false ? 'locked' : 'active');
@endphp

<div class="space-y-6">

  <div class="flex items-center justify-between">
    <a href="{{ route('admin.users.index') }}"
       class="inline-flex items-center gap-2 text-cyan-200 hover:underline">
      <i class="fa-solid fa-arrow-left"></i> Trở về danh sách
    </a>
  </div>

  {{-- Profile --}}
  <div class="cyber-panel cyber-corners p-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/15 flex items-center justify-center">
          <i class="fa-solid fa-user text-cyan-200 text-2xl"></i>
        </div>
        <div>
          <div class="text-2xl font-extrabold neon">{{ $user->name }}</div>
          <div class="text-bl/70">{{ $user->email }}</div>
          <div class="text-bl/45 text-sm">Tạo lúc: {{ optional($user->created_at)->format('d/m/Y H:i') }}</div>
        </div>
      </div>

      <div class="flex flex-wrap gap-2">
        {{-- Role badge --}}
        @if($role === 'admin')
          <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-500/20 text-purple-200 border border-purple-400/25">
            ADMIN
          </span>
        @else
          <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/15 text-cyan-200 border border-cyan-400/25">
            USER
          </span>
        @endif

        {{-- Status badge --}}
        @if($status === 'locked')
          <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-500/15 text-red-200 border border-red-400/25">
            LOCKED
          </span>
        @else
          <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-200 border border-emerald-400/25">
            ACTIVE
          </span>
        @endif
      </div>
    </div>
  </div>

  {{-- Actions --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Update role/status --}}
    <div class="cyber-panel cyber-corners p-6 lg:col-span-1">
      <h3 class="font-extrabold neon mb-4">Cập nhật</h3>

      <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
          <label class="block text-bl/70 text-sm mb-1">Role</label>
          <select name="role"
                  class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/15 outline-none
                         focus:border-cyan-300/50 focus:ring-2 focus:ring-cyan-300/20">
            <option value="user"  {{ $role === 'user' ? 'selected' : '' }}>User</option>
            <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
          </select>
        </div>

        <div>
          <label class="block text-bl/70 text-sm mb-1">Trạng thái</label>
          <select name="status"
                  class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/15 outline-none
                         focus:border-cyan-300/50 focus:ring-2 focus:ring-cyan-300/20">
            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
            <option value="locked" {{ $status === 'locked' ? 'selected' : '' }}>Locked</option>
          </select>
        </div>

        <button type="submit"
                class="w-full px-4 py-2 rounded-xl bg-cyan-500/20 hover:bg-cyan-400
                       text-bl font-bold transition border border-cyan-300/30">
          <i class="fa-solid fa-floppy-disk mr-2"></i>Lưu thay đổi
        </button>
      </form>

      {{-- Quick lock/unlock --}}
      <div class="mt-4">
        <form method="POST"
              action="{{ $status === 'locked'
                ? route('admin.users.unlock', $user->id)
                : route('admin.users.lock', $user->id) }}">
          @csrf
          <button type="submit"
                  class="w-full px-4 py-2 rounded-xl border transition
                         {{ $status === 'locked'
                            ? 'bg-emerald-500/15 hover:bg-emerald-500/25 border-emerald-400/25'
                            : 'bg-red-500/15 hover:bg-red-500/25 border-red-400/25' }}">
            @if($status === 'locked')
              <i class="fa-solid fa-lock-open mr-2 text-emerald-200"></i>Mở khóa
            @else
              <i class="fa-solid fa-lock mr-2 text-red-200"></i>Khóa tài khoản
            @endif
          </button>
        </form>
      </div>
    </div>

    {{-- Orders of this user (optional) --}}
    <div class="cyber-panel cyber-corners p-6 lg:col-span-2">
      <div class="flex items-center justify-between">
        <h3 class="font-extrabold neon">Đơn hàng của người dùng</h3>
        <span class="text-bl/60 text-sm">
          {{ isset($orders) ? $orders->total() ?? $orders->count() : 0 }} đơn
        </span>
      </div>

      @if(isset($orders) && $orders->count())
        <div class="admin-table-mobile-hide mt-4 overflow-x-auto hidden md:block">
          <table class="min-w-full text-sm">
            <thead class="bg-white/5">
              <tr class="text-left text-bl/70">
                <th class="px-4 py-3">Mã</th>
                <th class="px-4 py-3">Tổng</th>
                <th class="px-4 py-3">Thanh toán</th>
                <th class="px-4 py-3">Ngày</th>
                <th class="px-4 py-3 text-right">Xem</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
              @foreach($orders as $o)
                <tr class="hover:bg-white/5 transition">
                  <td class="px-4 py-3 text-bl">#{{ $o->id }}</td>
                  <td class="px-4 py-3 text-bl/80">{{ number_format($o->total_amount) }} VND</td>
                  <td class="px-4 py-3 text-bl/80">{{ $o->payment_status }}</td>
                  <td class="px-4 py-3 text-bl/60">{{ optional($o->created_at)->format('d/m/Y H:i') }}</td>
                  <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.orders.show', $o->id) }}"
                       class="px-3 py-2 rounded-lg bg-white/5 hover:bg-cyan-500/15 border border-white/10 hover:border-cyan-400/25 transition">
                      <i class="fa-solid fa-eye mr-1 text-cyan-200"></i>Chi tiết
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="admin-mobile-cards mt-4 block md:hidden">
          @foreach($orders as $o)
            <div class="admin-mobile-card">
              <div class="admin-mobile-card__head">
                <div class="admin-mobile-card__title text-bl">
                  #{{ $o->id }}
                </div>
                <div class="admin-mobile-card__meta">
                  {{ optional($o->created_at)->format('d/m/Y H:i') }}
                </div>
              </div>

              <div class="admin-mobile-card__body">
                <div class="admin-mobile-field">
                  <div class="admin-mobile-field__label">Tổng</div>
                  <div class="admin-mobile-field__value text-bl/80">{{ number_format($o->total_amount) }} VND</div>
                </div>
                <div class="admin-mobile-field">
                  <div class="admin-mobile-field__label">Thanh toán</div>
                  <div class="admin-mobile-field__value text-bl/80">{{ $o->payment_status }}</div>
                </div>
              </div>

              <div class="admin-mobile-actions">
                <a href="{{ route('admin.orders.show', $o->id) }}"
                   class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-cyan-300 bg-white/5 hover:bg-white/10 transition-colors flex items-center justify-center gap-2 w-full">
                  <i class="fa-solid fa-eye mr-1 text-cyan-200"></i>
                  <span class="admin-action-label">Chi tiết</span>
                </a>
              </div>
            </div>
          @endforeach
        </div>

        @if(method_exists($orders, 'links'))
          <div class="mt-4">
            {{ $orders->links() }}
          </div>
        @endif
      @else
        <div class="mt-4 text-bl/60">Người dùng chưa có đơn hàng.</div>
      @endif
    </div>
  </div>

  {{-- Delete --}}
  <div class="cyber-panel cyber-corners p-6 border border-red-400/20">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
      <div>
        <div class="font-extrabold text-red-200">Xóa người dùng</div>
        <div class="text-bl/60 text-sm">Khuyến nghị dùng soft delete. Không nên xóa admin đang đăng nhập.</div>
      </div>

      <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
            onsubmit="return confirm('Xóa người dùng này?');">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="px-4 py-2 rounded-xl bg-red-500/15 hover:bg-red-500/25 border border-red-400/25 transition">
          <i class="fa-solid fa-trash mr-2 text-red-200"></i>Xóa
        </button>
      </form>
    </div>
  </div>

</div>
@endsection
