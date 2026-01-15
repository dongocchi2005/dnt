<div class="cy-dd cy-dd--noti" data-dd-menu>
  <div class="cy-dd-head cy-dd-head--row">
    <div class="cy-dd-title">Thông báo</div>
  </div>

  <div class="cy-dd-list">
    @forelse($notis as $noti)
      @php $url = $noti->data['url'] ?? '#'; @endphp

      <a href="{{ $url }}"
         @click.prevent="
           window.markRead && window.markRead('{{ $noti->id }}', {{ $noti->read_at ? 'true' : 'false' }});
           window.location.href='{{ $url }}';
         "
         class="cy-noti {{ $noti->read_at ? 'is-read' : 'is-unread' }}">
        <div class="cy-noti-title">{{ $noti->data['title'] ?? 'Thông báo' }}</div>
        <div class="cy-noti-msg">{{ $noti->data['message'] ?? '' }}</div>
        <div class="cy-noti-time">{{ $noti->created_at->diffForHumans() }}</div>
      </a>
    @empty
      <div class="cy-dd-empty">Chưa có thông báo</div>
    @endforelse
  </div>
</div>
