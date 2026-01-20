@extends('layouts.admin')

@section('title', 'Quản lý người dùng')
@section('page-title', 'Người dùng')

@section('content')
<div class="space-y-6">

  <div class="cyber-panel">
    <div class="admin-panel-head">
      <div>
        <h1 class="text-2xl font-bold text-bl font-display neon">Quản lý người dùng</h1>
        <p class="text-bl/60 mt-1">Danh sách tài khoản, phân quyền, khóa/mở khóa</p>
      </div>
      <form method="GET" action="{{ route('admin.users.index') }}" class="admin-form-grid">
        <div class="admin-form-field">
          <label class="sr-only" for="userSearch">Tìm người dùng</label>
          <input
            id="userSearch"
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Tìm theo tên / email..."
            class="admin-input"
          >
        </div>
        <div class="admin-form-field">
          <label class="sr-only" for="userRole">Role</label>
          <select id="userRole" name="role" class="admin-input">
            <option value="">Tất cả role</option>
            <option value="admin" {{ request('role')==='admin' ? 'selected' : '' }}>Admin</option>
            <option value="user" {{ request('role')==='user' ? 'selected' : '' }}>User</option>
          </select>
        </div>
        <div class="admin-form-field">
          <label class="sr-only" for="userStatus">Trạng thái</label>
          <select id="userStatus" name="status" class="admin-input">
            <option value="">Tất cả trạng thái</option>
            <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>Active</option>
            <option value="locked" {{ request('status')==='locked' ? 'selected' : '' }}>Locked</option>
          </select>
        </div>
        <div class="admin-form-field">
          <label class="sr-only" for="userDateFrom">Từ ngày</label>
          <input id="userDateFrom" type="date" name="date_from" value="{{ request('date_from') }}" class="admin-input">
        </div>
        <div class="admin-form-field">
          <label class="sr-only" for="userDateTo">Đến ngày</label>
          <input id="userDateTo" type="date" name="date_to" value="{{ request('date_to') }}" class="admin-input">
        </div>
        <div class="admin-form-field">
          <div class="admin-form-actions">
            <button type="submit" class="cyber-btn admin-btn admin-btn-full bg-blue-600 text-white hover:bg-blue-500 flex items-center justify-center">
              <i class="fa-solid fa-magnifying-glass"></i>
              <span class="admin-action-label">Tìm</span>
            </button>
            <a href="{{ route('admin.users.index') }}"
               class="admin-btn admin-btn-full py-2 border border-white/10 rounded-lg text-sm text-bl/60 text-center hover:bg-white/5">
              Xóa
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  @if(session('success'))
    <div class="noti-cyber p-4 border-l-4 border-green-500">
      <div class="text-green-400 font-bold flex items-center">
        <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
      </div>
    </div>
  @endif
  @if(session('error'))
    <div class="noti-cyber p-4 border-l-4 border-red-500">
      <div class="text-red-400 font-bold flex items-center">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}
      </div>
    </div>
  @endif

  <div class="cyber-panel overflow-hidden">
    <div class="admin-panel-head">
      <div class="font-bold text-bl text-base">Danh sách</div>
      <div class="admin-panel-head__meta text-bl/60 text-sm">
        Tổng: <span class="text-blue-400 font-bold neon">{{ $users->total() ?? $users->count() }}</span>
      </div>
    </div>

    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead class="bg-white/5">
          <tr class="text-left text-bl/50">
            <th class="font-bold uppercase tracking-wider text-xs">#</th>
            <th class="font-bold uppercase tracking-wider text-xs">Tên</th>
            <th class="font-bold uppercase tracking-wider text-xs">Email</th>
            <th class="font-bold uppercase tracking-wider text-xs">Role</th>
            <th class="font-bold uppercase tracking-wider text-xs">Trạng thái</th>
            <th class="font-bold uppercase tracking-wider text-xs">Ngày tạo</th>
            <th class="font-bold text-right uppercase tracking-wider text-xs">Thao tác</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-white/5">
          @forelse($users as $u)
            @php
              $role = $u->role ?? ($u->is_admin ?? false ? 'admin' : 'user');
            @endphp

            <tr class="hover:bg-white/5 transition duration-150">
              <td class="text-bl/60 font-medium">{{ $u->id }}</td>

              <td>
                <div class="font-bold text-bl">{{ $u->name }}</div>
                <div class="text-xs text-bl/40 mt-0.5 font-mono">UID: {{ $u->id }}</div>
              </td>

              <td class="text-bl/80">{{ $u->email }}</td>

              <td>
                @if($role === 'admin')
                  <span class="px-3 py-1 rounded text-xs font-bold bg-purple-500/20 text-purple-400 border border-purple-500/30 shadow-[0_0_10px_rgba(168,85,247,0.2)]">
                    ADMIN
                  </span>
                @else
                  <span class="px-3 py-1 rounded text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                    USER
                  </span>
                @endif
              </td>

              <td>
                @if($u->isLocked())
                  <span class="px-3 py-1 rounded text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.2)]">
                    LOCKED
                  </span>
                @else
                  <span class="px-3 py-1 rounded text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                    ACTIVE
                  </span>
                @endif
              </td>

              <td class="text-bl/60">
                {{ optional($u->created_at)->format('d/m/Y H:i') }}
              </td>

              <td>
                <div class="admin-actions">
                  <a href="{{ route('admin.users.show', $u->id) }}"
                     class="admin-action-btn border border-white/10 bg-white/5 text-bl/80 hover:bg-white/10 hover:text-blue-400 transition">
                    <i class="fa-solid fa-eye"></i>
                    <span class="admin-action-label">Xem</span>
                  </a>

                  @if($u->isLocked())
                    <form method="POST" action="{{ route('admin.users.unlock', $u->id) }}" class="inline">
                      @csrf
                      <button type="submit"
                              class="admin-action-btn border border-emerald-500/20 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 flex items-center justify-center"
                              onclick="return confirm('Bạn có chắc muốn mở khóa người dùng này?')">
                        <i class="fa-solid fa-lock-open"></i>
                        <span class="admin-action-label">Mở khóa</span>
                      </button>
                    </form>
                  @else
                    <form method="POST" action="{{ route('admin.users.lock', $u->id) }}" class="inline">
                      @csrf
                      <button type="submit"
                              class="admin-action-btn border border-white/10 bg-white/5 text-red-400 hover:bg-red-500/10 hover:border-red-500/20 flex items-center justify-center"
                              onclick="return confirm('Bạn có chắc muốn khóa người dùng này?')">
                        <i class="fa-solid fa-lock"></i>
                        <span class="admin-action-label">Khóa</span>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-6 py-10 text-center text-bl/40 italic">
                Chưa có người dùng nào.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="admin-mobile-cards">
      @forelse($users as $u)
        @php
          $role = $u->role ?? ($u->is_admin ?? false ? 'admin' : 'user');
        @endphp

        <div class="admin-mobile-card">
          <div class="admin-mobile-card__head">
            <div class="admin-mobile-card__title text-bl">
              {{ $u->name }}
            </div>
            <div class="admin-mobile-card__meta">
              {{ optional($u->created_at)->format('d/m/Y H:i') }}
            </div>
          </div>

          <div class="admin-mobile-card__body">
            <div class="admin-mobile-field">
              <div class="admin-mobile-field__label">Email</div>
              <div class="admin-mobile-field__value text-bl/80">{{ $u->email }}</div>
            </div>

            <div class="admin-mobile-field">
              <div class="admin-mobile-field__label">Role</div>
              <div class="admin-mobile-field__value">
                @if($role === 'admin')
                  <span class="px-3 py-1 rounded text-xs font-bold bg-purple-500/20 text-purple-400 border border-purple-500/30 shadow-[0_0_10px_rgba(168,85,247,0.2)]">
                    ADMIN
                  </span>
                @else
                  <span class="px-3 py-1 rounded text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                    USER
                  </span>
                @endif
              </div>
            </div>

            <div class="admin-mobile-field">
              <div class="admin-mobile-field__label">Trạng thái</div>
              <div class="admin-mobile-field__value">
                @if($u->isLocked())
                  <span class="px-3 py-1 rounded text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.2)]">
                    LOCKED
                  </span>
                @else
                  <span class="px-3 py-1 rounded text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                    ACTIVE
                  </span>
                @endif
              </div>
            </div>
          </div>

          <div class="admin-mobile-actions">
            <a href="{{ route('admin.users.show', $u->id) }}"
               class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-bl/80 bg-white/5 hover:bg-white/10 hover:text-blue-400 transition-colors flex items-center justify-center gap-2 w-full">
              <i class="fa-solid fa-eye"></i>
              <span class="admin-action-label">Xem</span>
            </a>

            @if($u->isLocked())
              <form method="POST" action="{{ route('admin.users.unlock', $u->id) }}" class="w-full">
                @csrf
                <button type="submit"
                        class="admin-action-btn border border-emerald-500/20 rounded-lg text-sm font-medium text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 transition-colors flex items-center justify-center gap-2 w-full"
                        onclick="return confirm('Bạn có chắc muốn mở khóa người dùng này?')">
                  <i class="fa-solid fa-lock-open"></i>
                  <span class="admin-action-label">Mở khóa</span>
                </button>
              </form>
            @else
              <form method="POST" action="{{ route('admin.users.lock', $u->id) }}" class="w-full">
                @csrf
                <button type="submit"
                        class="admin-action-btn border border-red-500/20 rounded-lg text-sm font-medium text-red-300 bg-red-500/10 hover:bg-red-500/20 transition-colors flex items-center justify-center gap-2 w-full"
                        onclick="return confirm('Bạn có chắc muốn khóa người dùng này?')">
                  <i class="fa-solid fa-lock"></i>
                  <span class="admin-action-label">Khóa</span>
                </button>
              </form>
            @endif
          </div>
        </div>
      @empty
        <div class="admin-mobile-card">
          <div class="text-center text-bl/40 italic">Chưa có người dùng nào.</div>
        </div>
      @endforelse
    </div>

    @if(method_exists($users, 'links'))
      <div class="admin-panel-footer">
        {{ $users->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
