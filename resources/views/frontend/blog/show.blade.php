@extends('frontend.layouts.app')

@section('title', $post->title . ' | DNT Store')



@section('content')
@php
  $content = (string) ($post->content ?? '');
  $allowed = '<p><br><strong><em><ul><ol><li><a><img><iframe><h1><h2><h3><h4><blockquote><hr><table><thead><tbody><tr><th><td><pre><code><div><span>';
  $safe = strip_tags($content, $allowed);
  $safe = preg_replace('/on\\w+\\s*=\\s*"[^"]*"/i', '', $safe);
  $safe = preg_replace("/on\\w+\\s*=\\s*'[^']*'/i", '', $safe);
  $safe = preg_replace('/javascript:/i', '', $safe);
  $safe = preg_replace('#https?://[^/]+/storage/#', '/storage/', $safe);

  $toCarbon = function ($value) {
    try {
      return $value ? \Illuminate\Support\Carbon::parse($value) : null;
    } catch (\Throwable $e) {
      return null;
    }
  };

  $imageRaw = $post->image ?? null;
  $heroImage = $imageRaw ? (str_starts_with($imageRaw, 'http') ? $imageRaw : asset($imageRaw)) : null;

  $words = str_word_count(trim(strip_tags((string) ($post->content ?? ''))));
  $readMinutes = max(1, (int) ceil($words / 200));

  $publishedAt = $toCarbon($post->published_at ?? $post->created_at ?? null);
  $publishedText = $publishedAt ? $publishedAt->format('d/m/Y') : '';

  $excerpt = trim((string) ($post->excerpt ?? ''));
  if ($excerpt === '') {
    $excerpt = \Illuminate\Support\Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags($content))), 220);
  }

  $categoryName = $post->category?->name ?? ($post->category ?? null);
  if (!$categoryName) {
    $categorySlug = $post->category_slug ?? ($post->category?->slug ?? null);
    $categoryName = (string) (($categories ?? collect())->firstWhere('slug', $categorySlug)->name ?? 'Chung');
  }
@endphp

<div class="page-blog page-blog--show">
  <section class="blog-articleHero -mx-4 px-4 pt-6 pb-10 sm:pb-12" data-reveal>
    <div class="blog-heroPanel">
      <div class="blog-articleHeroInner">
        <a href="{{ route('blog.index') }}" class="blog-backLink">← Quay lại Tin tức</a>

        <div class="blog-articleHead">
          <div class="blog-articleMeta">
            <span class="blog-badge">{{ $categoryName }}</span>
            @if($publishedText)
              <span class="blog-metaText">{{ $publishedText }}</span>
              <span class="blog-metaDot">•</span>
            @endif
            <span class="blog-metaText">{{ $readMinutes }} phút đọc</span>
            @if(isset($post->views))
              <span class="blog-metaDot">•</span>
              <span class="blog-metaText">{{ number_format((int) $post->views) }} views</span>
            @endif
          </div>

          <h1 class="blog-articleTitle">{{ $post->title }}</h1>
          <p class="blog-articleLede">{{ $excerpt }}</p>
        </div>

        <div class="blog-articleCover" data-reveal>
          @if($heroImage)
            <img src="{{ $heroImage }}" alt="{{ $post->title }}" class="blog-imgCover" loading="lazy">
          @else
            <div class="blog-coverPlaceholder" aria-hidden="true"></div>
          @endif
        </div>
      </div>
    </div>
  </section>

  <section class="blog-articleShell mt-8 sm:mt-10" data-reveal>
    <div class="blog-layout">
      <div class="blog-main">
        <div class="blog-summaryBox" data-reveal>
          <div class="blog-summaryTitle">Tóm tắt nhanh</div>
          <div class="blog-summaryText">{{ $excerpt }}</div>
        </div>

        <article class="blog-articleCard" data-reveal>
          <div class="blog-prose">{!! $safe !!}</div>
        </article>

        <div class="blog-articleCta mt-10" data-reveal>
          <div class="blog-finalPanel" data-tilt>
            <div>
              <div class="blog-kicker">Cần hỗ trợ ngay?</div>
              <div class="blog-h2">Đặt lịch sửa / hỏi nhanh với AI</div>
              <div class="blog-muted">DNT Store kiểm tra miễn phí, tư vấn rõ ràng và xử lý nhanh.</div>
            </div>
            <div class="blog-finalActions">
              <a href="{{ route('booking.create') }}" class="blog-btn blog-btnPrimary">Đặt lịch sửa</a>
              <button type="button" class="blog-btn blog-btnGhost" data-open-chat>Chat AI</button>
            </div>
          </div>
        </div>
      </div>

      <aside class="blog-aside" data-reveal>
        <div class="blog-asideDesktop">
          <div class="blog-widget">
            <div class="blog-widgetTitle">Danh mục</div>
            <div class="blog-widgetBody">
              <div class="blog-chipList">
                @foreach(($categories ?? collect()) as $c)
                  <a href="{{ route('blog.index', ['category' => $c->slug]) }}" class="blog-chip">{{ $c->name }}</a>
                @endforeach
              </div>
            </div>
          </div>

          <div class="blog-widget">
            <div class="blog-widgetTitle">Tags / Chủ đề</div>
            <div class="blog-widgetBody">
              <div class="blog-chipList">
                @foreach(($tags ?? []) as $t)
                  <a href="{{ route('blog.index', ['q' => $t]) }}" class="blog-chip">{{ $t }}</a>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        <div class="blog-asideMobile">
          <details class="blog-accordion">
            <summary class="blog-accordionSum">Danh mục</summary>
            <div class="blog-accordionBody">
              <div class="blog-chipList">
                @foreach(($categories ?? collect()) as $c)
                  <a href="{{ route('blog.index', ['category' => $c->slug]) }}" class="blog-chip">{{ $c->name }}</a>
                @endforeach
              </div>
            </div>
          </details>

          <details class="blog-accordion">
            <summary class="blog-accordionSum">Tags / Chủ đề</summary>
            <div class="blog-accordionBody">
              <div class="blog-chipList">
                @foreach(($tags ?? []) as $t)
                  <a href="{{ route('blog.index', ['q' => $t]) }}" class="blog-chip">{{ $t }}</a>
                @endforeach
              </div>
            </div>
          </details>
        </div>
      </aside>
    </div>
  </section>

  <section class="blog-related mt-12 sm:mt-14" data-reveal>
    <div class="blog-sectionHead">
      <h2 class="blog-h2">Bài liên quan</h2>
      <p class="blog-muted">Gợi ý 4 bài mới để bạn đọc tiếp.</p>
    </div>

    <div class="blog-relatedGrid">
      @foreach(($related ?? collect())->take(4) as $p)
        @php
          $img = $p->image ?? null;
          $img = $img ? (str_starts_with($img, 'http') ? $img : asset($img)) : null;
          $dt = $toCarbon($p->published_at ?? $p->created_at ?? null);
          $dtText = $dt ? $dt->format('d/m/Y') : '';
          $ex = trim((string) ($p->excerpt ?? ''));
          if ($ex === '') $ex = \Illuminate\Support\Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags((string) ($p->content ?? '')))), 120);
          $w = str_word_count(trim(strip_tags((string) ($p->content ?? ''))));
          $m = max(1, (int) ceil($w / 200));
        @endphp

        <article class="blog-card blog-cardLatest" data-tilt data-reveal>
          <a href="{{ route('blog.show', $p->slug) }}" class="blog-cardMedia">
            @if($img)
              <img src="{{ $img }}" alt="{{ $p->title }}" loading="lazy" class="blog-imgCover">
            @endif
          </a>
          <div class="blog-cardBody">
            <div class="blog-cardMeta">
              @if($dtText)
                <span class="blog-metaText">{{ $dtText }}</span>
                <span class="blog-metaDot">•</span>
              @endif
              <span class="blog-metaText">{{ $m }} phút</span>
            </div>
            <h3 class="blog-cardTitle">
              <a href="{{ route('blog.show', $p->slug) }}">{{ $p->title }}</a>
            </h3>
            <p class="blog-cardExcerpt">{{ $ex }}</p>
            <div class="blog-cardActions">
              <a href="{{ route('blog.show', $p->slug) }}" class="blog-btn blog-btnInline">Đọc tiếp</a>
            </div>
          </div>
        </article>
      @endforeach
    </div>
  </section>
</div>
@endsection
