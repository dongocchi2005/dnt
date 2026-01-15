<div class="breadcrumb-bar">
  <div class="max-w-7xl mx-auto px-4">
    <div class="breadcrumb-wrap">
      <div class="breadcrumb">
        @foreach($crumbs as $i => $crumb)
          @if($i > 0)
            <span class="crumb-sep">›</span>
          @endif
          @if(!empty($crumb['url']) && $i < count($crumbs) - 1)
            <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
          @else
            <span class="crumb-current">{{ $crumb['label'] }}</span>
          @endif
        @endforeach
      </div>
    </div>
  </div>
</div>

