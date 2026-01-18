@extends('layouts.admin')

@section('page-title','Tạo bài viết')

@section('content')
<div class="max-w-3xl text-bl">
    @if($errors->any())
        <div class="mb-4 rounded border border-red-500/40 bg-red-500/10 p-3 text-sm text-red-300">
            @foreach($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.posts.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-4">
        @csrf

        {{-- Tiêu đề --}}
        <div>
            <label class="block text-sm font-medium text-bl/80">Tiêu đề</label>
            <input
                type="text"
                name="title"
                required
                value="{{ old('title') }}"
                class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded
                       text-bl placeholder-white/50
                       focus:ring-blue-500 focus:border-blue-500"
            >
        </div>

        {{-- Slug --}}
        <div>
            <label class="block text-sm font-medium text-bl/80">Slug (tùy chọn)</label>
            <input
                type="text"
                name="slug"
                value="{{ old('slug') }}"
                class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded
                       text-bl placeholder-white/50
                       focus:ring-blue-500 focus:border-blue-500"
            >
        </div>

        {{-- Tóm tắt --}}
        <div>
            <label class="block text-sm font-medium text-bl/80">Tóm tắt</label>
            <textarea
                name="excerpt"
                rows="3"
                class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded
                       text-bl placeholder-white/50
                       focus:ring-blue-500 focus:border-blue-500"
            >{{ old('excerpt') }}</textarea>
        </div>

        {{-- Nội dung --}}
        <div>
            <label class="block text-sm font-medium text-bl/80">Nội dung</label>

            {{-- TinyMCE sẽ biến textarea này thành editor --}}
            <textarea
                id="postContent"
                name="content"
                rows="10"
                class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded
                       text-bl placeholder-white/50
                       focus:ring-blue-500 focus:border-blue-500"
            >{!! old('content') !!}</textarea>

            {{-- Tools --}}
            <div class="mt-3 space-y-3">
                {{-- Upload ảnh & chèn --}}
                <div class="flex flex-wrap items-center gap-3">
                    <input
                        type="file"
                        id="contentImageInput"
                        accept="image/jpeg,image/png,image/webp"
                        class="block text-sm text-bl/80
                               file:mr-4 file:py-2 file:px-4
                               file:rounded file:border-0
                               file:bg-blue-600 file:text-bl
                               hover:file:bg-blue-700"
                    >
                    <button
                        type="button"
                        id="contentImageUpload"
                        class="bg-blue-600 hover:bg-blue-700 text-bl px-4 py-2 rounded-md"
                    >
                        Upload ảnh & chèn vào nội dung
                    </button>
                    <span id="contentImageStatus" class="text-xs text-bl/60"></span>
                </div>

                {{-- Chèn video từ link --}}
                <div class="flex flex-wrap items-center gap-3">
                    <input
                        type="url"
                        id="contentVideoUrl"
                        placeholder="Dán link video YouTube / TikTok / Facebook / Vimeo"
                        class="flex-1 min-w-[240px] bg-transparent border border-white/30 px-3 py-2 rounded
                               text-bl placeholder-white/50
                               focus:ring-blue-500 focus:border-blue-500"
                    >
                    <button
                        type="button"
                        id="contentVideoInsert"
                        class="bg-blue-600 hover:bg-blue-700 text-bl px-4 py-2 rounded-md"
                    >
                        Chèn video
                    </button>
                </div>

                <div class="text-xs text-bl/60">
                    Gợi ý: chỉ cần dán link, hệ thống sẽ tự nhúng video. Bạn không cần biết code.
                </div>
            </div>
        </div>

        {{-- Ảnh đại diện --}}
        <div>
            <label class="block text-sm font-medium text-bl/80">Ảnh</label>
            <input
                type="file"
                name="image"
                accept="image/*"
                class="mt-1 block w-full text-sm text-bl/80
                       file:mr-4 file:py-2 file:px-4
                       file:rounded file:border-0
                       file:bg-blue-600 file:text-bl
                       hover:file:bg-blue-700"
            >
        </div>

        {{-- Kích hoạt --}}
        <div>
            <label class="inline-flex items-center text-bl/80">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active') ? 'checked' : '' }}
                    class="rounded border-white/40 bg-transparent text-blue-600 focus:ring-blue-500"
                >
                <span class="ml-2">Kích hoạt</span>
            </label>
        </div>

        {{-- Submit --}}
        <div>
            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-bl px-5 py-2 rounded-md"
            >
                Lưu
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
{{-- TinyMCE CDN (no-api-key) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>

<script>
(() => {
    const imageInput  = document.getElementById('contentImageInput');
    const imageBtn    = document.getElementById('contentImageUpload');
    const imageStatus = document.getElementById('contentImageStatus');

    const videoInput  = document.getElementById('contentVideoUrl');
    const videoBtn    = document.getElementById('contentVideoInsert');

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const setStatus = (message, isError = false) => {
        if (!imageStatus) return;
        imageStatus.textContent = message;
        imageStatus.className = isError ? 'text-xs text-red-400' : 'text-xs text-bl/60';
    };

    const buildVideoEmbed = (rawUrl) => {
        let url;
        try { url = new URL(rawUrl); } catch (e) { return null; }

        const host = url.hostname.replace('www.', '');
        let src = null;

        // YouTube
        if (host.includes('youtube.com') || host.includes('youtu.be')) {
            let id = '';
            if (host.includes('youtu.be')) {
                id = url.pathname.replace('/', '');
            } else if (url.pathname.startsWith('/watch')) {
                id = url.searchParams.get('v') || '';
            } else if (url.pathname.startsWith('/embed/')) {
                id = url.pathname.split('/embed/')[1] || '';
            } else if (url.pathname.startsWith('/shorts/')) {
                id = url.pathname.split('/shorts/')[1] || '';
            }
            if (id) src = `https://www.youtube.com/embed/${id}`;
        }

        // Vimeo
        if (!src && host.includes('vimeo.com')) {
            const match = url.pathname.match(/\/(\d+)/);
            if (match && match[1]) src = `https://player.vimeo.com/video/${match[1]}`;
        }

        // TikTok
        if (!src && host.includes('tiktok.com')) {
            const match = url.pathname.match(/video\/(\d+)/);
            if (match && match[1]) src = `https://www.tiktok.com/embed/v2/${match[1]}`;
        }

        // Facebook video plugin
        if (!src && host.includes('facebook.com')) {
            const encoded = encodeURIComponent(rawUrl);
            src = `https://www.facebook.com/plugins/video.php?href=${encoded}&show_text=0&width=560`;
        }

        if (!src) return null;

        return `
            <div class="embed-16x9">
                <iframe src="${src}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen></iframe>
            </div>
        `;
    };

    // Init TinyMCE
    tinymce.init({
        selector: '#postContent',
        height: 560,
        menubar: false,
        branding: false,

        plugins: 'link lists image media code table autoresize',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link media image | table | code',

        // allow iframe
        extended_valid_elements: 'iframe[src|allow|allowfullscreen|frameborder|scrolling|class|width|height]',
        valid_children: '+body[iframe],+div[iframe]',

        // Khi dán link TikTok/YouTube: tự nhúng
        paste_preprocess: (plugin, args) => {
            const txt = (args.content || '').trim();
            const embed = buildVideoEmbed(txt);
            if (embed) args.content = embed;
        },

        setup: (editor) => {
            // helper để chèn HTML tại vị trí con trỏ
            window.__dntInsertContent = (html) => {
                editor.focus();
                editor.insertContent(html);
            };
        }
    });

    // Upload ảnh -> insert vào TinyMCE
    imageBtn?.addEventListener('click', async () => {
        const file = imageInput?.files?.[0];
        if (!file) {
            setStatus('Vui lòng chọn ảnh.', true);
            return;
        }

        const formData = new FormData();
        formData.append('image', file);

        setStatus('Đang tải ảnh...');
        imageBtn.disabled = true;

        try {
            const response = await fetch('{{ route('admin.posts.content-image') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: formData,
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.url) {
                setStatus(data.message || 'Upload thất bại.', true);
                return;
            }

            // Chèn ảnh vào editor
            window.__dntInsertContent?.(`<p><img src="${data.url}" alt="Ảnh minh họa" /></p>`);
            setStatus('Đã chèn ảnh.');
            if (imageInput) imageInput.value = '';
        } catch (error) {
            setStatus('Không thể upload ảnh.', true);
        } finally {
            imageBtn.disabled = false;
        }
    });

    // Chèn video từ link -> insert embed vào TinyMCE
    videoBtn?.addEventListener('click', () => {
        const rawUrl = (videoInput?.value || '').trim();
        if (!rawUrl) return;

        const embed = buildVideoEmbed(rawUrl);
        if (!embed) {
            alert('Link video không hợp lệ hoặc chưa hỗ trợ.');
            return;
        }

        window.__dntInsertContent?.(embed);
        videoInput.value = '';
    });
})();
</script>

{{-- CSS embed responsive (đặt ở file CSS chung cũng được, nhưng để đây chạy ngay) --}}
<style>
.embed-16x9{
  position: relative;
  width: 100%;
  padding-top: 56.25%;
  margin: 14px 0;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid rgba(255,255,255,.12);
  background: rgba(0,0,0,.2);
}
.embed-16x9 iframe{
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  border: 0;
}
</style>
@endpush
