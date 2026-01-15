@extends('layouts.admin')

@section('page-title','Sửa sản phẩm')

@section('content')
@php
  use Illuminate\Support\Facades\Storage;

  $imgUrl = function ($p) {
      if (!$p) return null;
      if (str_starts_with($p,'http://') || str_starts_with($p,'https://') || str_starts_with($p,'/')) return $p;

      // ưu tiên public disk (/storage/...)
      if (Storage::disk('public')->exists($p)) return Storage::url($p);

      // fallback public/...
      return asset($p);
  };

  $mainImageUrl = $imgUrl($product->image);
@endphp

<div class="max-w-4xl mx-auto text-bl p-6">
  <div class="bg-gray-900 rounded-lg shadow-lg p-6">
    <h1 class="text-2xl font-bold text-white mb-6">Sửa sản phẩm</h1>

    <form action="{{ route('admin.products.update', $product) }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6"
          id="productForm">
      @csrf
      @method('PUT')

      {{-- Tên --}}
      <div>
        <label class="block text-sm font-medium text-bl/80">Tên</label>
        <input type="text" name="name"
               value="{{ old('name', $product->name) }}"
               required
               class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded text-bl placeholder-white/50
                      focus:ring-blue-500 focus:border-blue-500">
      </div>

      {{-- Slug --}}
      <div>
        <label class="block text-sm font-medium text-bl/80">Slug (tùy chọn)</label>
        <input type="text" name="slug"
               value="{{ old('slug', $product->slug) }}"
               class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded text-bl placeholder-white/50
                      focus:ring-blue-500 focus:border-blue-500">
      </div>

      {{-- Mô tả --}}
      <div>
        <label class="block text-sm font-medium text-bl/80">Mô tả</label>
        <textarea name="description" rows="3"
                  class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded text-bl placeholder-white/50
                         focus:ring-blue-500 focus:border-blue-500">{{ old('description', $product->description) }}</textarea>
      </div>

      {{-- Danh mục --}}
      <div>
        <label class="block text-sm font-medium text-bl/80">Danh mục</label>
        <select name="category_id" id="category_id"
                class="mt-1 block w-full bg-gray-800 border border-white/30 rounded-md text-bl
                       focus:ring-blue-500 focus:border-blue-500 appearance-none">
          <option value="" class="bg-gray-800 text-bl">Chọn danh mục</option>
          @foreach($categories as $category)
            <option value="{{ $category->id }}"
              {{ (string)old('category_id', $product->category_id) === (string)$category->id ? 'selected' : '' }}
              class="bg-gray-800 text-bl">
              {{ $category->name }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Giá --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-bl/80">Giá gốc</label>
          <input type="number" name="original_price"
                 value="{{ old('original_price', $product->original_price) }}"
                 required
                 class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded text-bl
                        focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
          <label class="block text-sm font-medium text-bl/80">Giá bán</label>
          <input type="number" name="sale_price"
                 value="{{ old('sale_price', $product->sale_price) }}"
                 required
                 class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded text-bl
                        focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>

      {{-- Ảnh chính --}}
      <div>
        <label class="block text-sm font-medium text-bl/80">Ảnh chính</label>

        @if($mainImageUrl)
          <div class="mt-2 mb-3">
            <img src="{{ $mainImageUrl }}"
                 alt="{{ $product->name }}"
                 class="w-40 h-24 object-cover rounded border border-white/20">
            <div class="text-xs text-bl/60 mt-1">Ảnh hiện tại</div>
          </div>
        @endif

        <input type="file" name="image" accept="image/*"
               class="mt-1 block w-full text-sm text-bl/80
                      file:mr-4 file:py-2 file:px-4
                      file:rounded file:border-0
                      file:bg-blue-600 file:text-bl
                      hover:file:bg-blue-700">
      </div>

      {{-- Gallery --}}
      <div>
        <label class="block text-sm font-medium text-bl/80">Ảnh gallery (nhiều ảnh)</label>

        @if($product->productImages && $product->productImages->count() > 0)
          <div class="mt-2 mb-3 flex flex-wrap gap-2" id="galleryWrap">
            @foreach($product->productImages as $img)
              @php $gUrl = $imgUrl($img->image); @endphp
              <div class="relative">
                <img src="{{ $gUrl }}"
                     alt="Gallery"
                     class="w-20 h-20 object-cover rounded border border-white/20">
                {{-- Nếu bạn có route xóa ảnh gallery riêng, gắn vào đây --}}
                <button type="button"
                        class="absolute top-0 right-0 bg-red-600 text-white text-xs px-1 rounded remove-gallery"
                        data-id="{{ $img->id }}"
                        data-url="{{ route('admin.products.gallery.destroy', [$product, $img]) }}">
                  X
                </button>
              </div>
            @endforeach
          </div>
        @endif

        <input type="file" name="gallery[]" accept="image/*" multiple
               class="mt-1 block w-full text-sm text-bl/80
                      file:mr-4 file:py-2 file:px-4
                      file:rounded file:border-0
                      file:bg-blue-600 file:text-bl
                      hover:file:bg-blue-700">
        <div class="text-xs text-white/50 mt-1">
          Bấm X trên ảnh để xóa ảnh phụ ngay.
        </div>
      </div>

      {{-- Số lượng --}}
      <div>
        <label class="block text-sm font-medium text-bl/80">Số lượng</label>
        <input type="number" name="stock"
               value="{{ old('stock', $product->stock) }}"
               class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded text-bl
                      focus:ring-blue-500 focus:border-blue-500">
      </div>

      {{-- Checkbox --}}
      <div class="flex items-center gap-6">
        <label class="inline-flex items-center text-bl/80">
          <input type="checkbox" name="is_active" value="1"
                 class="rounded border-white/40 bg-transparent text-blue-600 focus:ring-blue-500"
                 {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
          <span class="ml-2">Kích hoạt</span>
        </label>

        <label class="inline-flex items-center text-bl/80">
          <input type="checkbox" name="is_clearance" value="1"
                 class="rounded border-white/40 bg-transparent text-blue-600 focus:ring-blue-500"
                 {{ old('is_clearance', $product->is_clearance) ? 'checked' : '' }}>
          <span class="ml-2">Thanh lý</span>
        </label>
      </div>

      {{-- ============ PHÂN LOẠI / BIẾN THỂ ============ --}}
      <div class="border border-white/20 rounded p-3">
        <div class="font-semibold mb-3 text-white">Phân loại hàng</div>

        <div id="optionsBox" class="space-y-3">
          @php
            // hiển thị lại option groups từ DB nếu bạn có quan hệ options
            $optGroups = ($product->options ?? collect())->map(function($o){
              return [
                'name' => $o->name,
                'values' => $o->values->pluck('value')->implode(', ')
              ];
            })->values();
          @endphp

          @foreach($optGroups as $i => $og)
            <div class="opt-row grid grid-cols-1 md:grid-cols-3 gap-2 border border-white/15 rounded p-2">
              <input type="text" name="options[{{ $i }}][name]"
                     value="{{ $og['name'] }}"
                     placeholder="Tên phân loại (VD: Màu)"
                     class="opt-name bg-transparent border border-white/30 rounded px-2 py-1 text-bl">
              <input type="text" name="options[{{ $i }}][values]"
                     value="{{ $og['values'] }}"
                     placeholder="Giá trị (VD: Đen, Trắng, Đỏ)"
                     class="opt-values bg-transparent border border-white/30 rounded px-2 py-1 text-bl md:col-span-2">
              <div class="md:col-span-3 flex justify-end">
                <button type="button" class="px-2 py-1 rounded bg-red-600 text-dark remove-option">Xóa nhóm</button>
              </div>
            </div>
          @endforeach
        </div>

        <div class="flex gap-2 mt-3">
          <button type="button" id="addOptionBtn" class="px-3 py-1 rounded bg-cyan-600 text-dark">+ Thêm phân loại</button>
          <button type="button" id="generateBtn" class="px-3 py-1 rounded bg-purple-600 text-dark">Tạo tổ hợp biến thể</button>
        </div>

        <div class="mt-4">
          <div class="flex items-center justify-between mb-2">
            <div class="font-semibold text-white">Bảng tổ hợp biến thể</div>
            <div class="flex items-center gap-2">
              <input type="number" id="bulkPrice" placeholder="Giá áp dụng"
                     class="bg-transparent border border-white/30 rounded px-2 py-1 text-bl">
              <button type="button" id="applyBulkBtn" class="px-3 py-1 rounded bg-blue-600 text-dark">Áp dụng giá hàng loạt</button>
            </div>
          </div>

          <div id="variantTableWrap" class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left text-white/80">
                  <th class="px-2 py-1">Tổ hợp</th>
                  <th class="px-2 py-1">SKU</th>
                  <th class="px-2 py-1">Giá</th>
                  <th class="px-2 py-1">Mặc định</th>
                  <th class="px-2 py-1">Xóa</th>
                </tr>
              </thead>

              <tbody id="variantRows">
                @php
                  $vv = old('variants', $product->variants_json ?? []);
                @endphp

                @foreach($vv as $i => $v)
                  @php
                    $pairs = $v['pairs'] ?? null;
                    // Nếu DB cũ chỉ có combo, vẫn hiển thị được
                    if (!$pairs && !empty($v['combo'])) {
                      $pairs = collect(explode('|', $v['combo']))->map(function($p){
                        [$n,$val] = array_pad(explode(':', $p, 2), 2, '');
                        return ['name'=>$n,'value'=>$val];
                      })->toArray();
                    }
                    $comboLabel = collect($pairs ?? [])->map(fn($p) => ($p['name'] ?? '').': '.($p['value'] ?? ''))->implode(' | ');
                    $comboData  = collect($pairs ?? [])->map(fn($p) => ($p['name'] ?? '').':'.($p['value'] ?? ''))->implode('|');
                    $isDefault = (bool)($v['is_default'] ?? false);
                  @endphp

                  <tr class="variant-row">
                    <td class="px-2 py-1 text-white/80">
                      <span class="combo-label">{{ $comboLabel }}</span>
                      <input type="hidden" name="variants[{{ $i }}][combo]" value="{{ $comboData }}" class="combo-input">
                    </td>

                    <td class="px-2 py-1">
                      <input type="text" name="variants[{{ $i }}][sku]"
                             value="{{ $v['sku'] ?? '' }}"
                             class="bg-transparent border border-white/30 rounded px-2 py-1 text-bl w-40">
                    </td>

                    <td class="px-2 py-1">
                      <input type="number" step="0.01" name="variants[{{ $i }}][price]"
                             value="{{ $v['price'] ?? '' }}"
                             class="bg-transparent border border-white/30 rounded px-2 py-1 text-bl w-28" required>
                    </td>

                    <td class="px-2 py-1">
                      <input type="radio" name="default_variant" value="{{ $i }}" {{ $isDefault ? 'checked' : '' }}>
                      <input type="hidden" name="variants[{{ $i }}][is_default]" value="{{ $isDefault ? 1 : 0 }}" class="is-default">
                    </td>

                    <td class="px-2 py-1">
                      <button type="button" class="px-2 py-1 rounded bg-red-600 text-dark remove-variant">Xóa</button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div id="variantInfo" class="text-xs text-white/60 mt-2">Giới hạn tối đa 100 biến thể.</div>
        </div>
      </div>

      {{-- Submit --}}
      <div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-bl px-5 py-2 rounded-md">
          Cập nhật
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const optionsBox   = document.getElementById('optionsBox');
  const addOptionBtn = document.getElementById('addOptionBtn');
  const generateBtn  = document.getElementById('generateBtn');
  const variantRows  = document.getElementById('variantRows');
  const applyBulkBtn = document.getElementById('applyBulkBtn');
  const bulkPrice    = document.getElementById('bulkPrice');
  const form         = document.getElementById('productForm');
  const galleryWrap  = document.getElementById('galleryWrap');

  // ===== Options UI =====
  function optionRowHtml(idx){
    return `
      <div class="opt-row grid grid-cols-1 md:grid-cols-3 gap-2 border border-white/15 rounded p-2">
        <input type="text" name="options[${idx}][name]" placeholder="Tên phân loại (VD: Màu)"
               class="opt-name bg-transparent border border-white/30 rounded px-2 py-1 text-bl">
        <input type="text" name="options[${idx}][values]" placeholder="Giá trị (VD: Đen, Trắng, Đỏ)"
               class="opt-values bg-transparent border border-white/30 rounded px-2 py-1 text-bl md:col-span-2">
        <div class="md:col-span-3 flex justify-end">
          <button type="button" class="px-2 py-1 rounded bg-red-600 text-dark remove-option">Xóa nhóm</button>
        </div>
      </div>`;
  }

  function nextOptionIndex(){
    return optionsBox.querySelectorAll('.opt-row').length;
  }

  addOptionBtn.addEventListener('click', function(){
    const count = optionsBox.querySelectorAll('.opt-row').length;
    if (count >= 3) return alert('Tối đa 3 nhóm phân loại.');
    optionsBox.insertAdjacentHTML('beforeend', optionRowHtml(nextOptionIndex()));
  });

  optionsBox.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove-option')) {
      e.target.closest('.opt-row')?.remove();
      reindexOptions();
    }
  });

  function reindexOptions(){
    const rows = optionsBox.querySelectorAll('.opt-row');
    rows.forEach((row, idx) => {
      const name = row.querySelector('.opt-name');
      const vals = row.querySelector('.opt-values');
      if (name) name.name = `options[${idx}][name]`;
      if (vals) vals.name = `options[${idx}][values]`;
    });
  }

  function parseOptions(){
    const groups = [];
    optionsBox.querySelectorAll('.opt-row').forEach(row => {
      const name = (row.querySelector('.opt-name')?.value || '').trim();
      const valuesStr = (row.querySelector('.opt-values')?.value || '').trim();
      if (!name || !valuesStr) return;

      const values = Array.from(new Set(
        valuesStr.split(',').map(s => s.trim()).filter(Boolean)
      ));

      if (values.length) groups.push({ name, values });
    });
    return groups;
  }

  function cartesian(arrays) {
    return arrays.reduce((acc, curr) => {
      const out = [];
      acc.forEach(a => curr.forEach(b => out.push(a.concat([b]))));
      return out;
    }, [[]]);
  }

  function getExistingComboSet(){
    const set = new Set();
    variantRows.querySelectorAll('.combo-input').forEach(h => set.add(h.value));
    return set;
  }

  // ===== Variants UI =====
  function addVariantRow(idx, comboLabel, comboData){
    const tr = document.createElement('tr');
    tr.className = 'variant-row';
    tr.innerHTML = `
      <td class="px-2 py-1 text-white/80">
        <span class="combo-label">${comboLabel}</span>
        <input type="hidden" name="variants[${idx}][combo]" value="${comboData}" class="combo-input">
      </td>
      <td class="px-2 py-1">
        <input type="text" name="variants[${idx}][sku]"
               class="bg-transparent border border-white/30 rounded px-2 py-1 text-bl w-40"
               placeholder="Tự sinh nếu trống">
      </td>
      <td class="px-2 py-1">
        <input type="number" step="0.01" name="variants[${idx}][price]"
               class="bg-transparent border border-white/30 rounded px-2 py-1 text-bl w-28" required>
      </td>
      <td class="px-2 py-1">
        <input type="radio" name="default_variant" value="${idx}">
        <input type="hidden" name="variants[${idx}][is_default]" value="0" class="is-default">
      </td>
      <td class="px-2 py-1">
        <button type="button" class="px-2 py-1 rounded bg-red-600 text-dark remove-variant">Xóa</button>
      </td>
    `;
    variantRows.appendChild(tr);
  }

  function reindexVariants(){
    const rows = variantRows.querySelectorAll('.variant-row');
    rows.forEach((row, idx) => {
      // combo
      const combo = row.querySelector('.combo-input');
      if (combo) combo.name = `variants[${idx}][combo]`;

      // sku/price
      const sku = row.querySelector('input[name$="[sku]"]');
      const price = row.querySelector('input[name$="[price]"]');
      if (sku) sku.name = `variants[${idx}][sku]`;
      if (price) price.name = `variants[${idx}][price]`;

      // default
      const radio = row.querySelector('input[type="radio"][name="default_variant"]');
      const hidden = row.querySelector('.is-default');
      if (radio) radio.value = idx;
      if (hidden) hidden.name = `variants[${idx}][is_default]`;
    });

    // sync hidden is_default after reindex
    syncDefaultHidden();
  }

  function syncDefaultHidden(){
    const checked = variantRows.querySelector('input[type="radio"][name="default_variant"]:checked');
    variantRows.querySelectorAll('.is-default').forEach(h => h.value = '0');
    if (checked){
      const idx = checked.value;
      const hidden = variantRows.querySelector(`input[name="variants[${idx}][is_default]"]`);
      if (hidden) hidden.value = '1';
    }
  }

  generateBtn.addEventListener('click', () => {
    const groups = parseOptions();
    if (!groups.length) return alert('Vui lòng nhập ít nhất 1 nhóm phân loại + giá trị.');

    const arrays = groups.map(g => g.values.map(v => ({ option: g.name, value: v })));
    const combos = cartesian(arrays);

    if (combos.length > 100) {
      alert('Số lượng biến thể quá lớn (>100). Vui lòng giảm số giá trị.');
      return;
    }

    const existing = getExistingComboSet();
    let idx = variantRows.querySelectorAll('.variant-row').length;

    combos.forEach(items => {
      const comboData  = items.map(it => `${it.option}:${it.value}`).join('|');
      if (existing.has(comboData)) return;

      const comboLabel = items.map(it => `${it.option}: ${it.value}`).join(' | ');
      addVariantRow(idx++, comboLabel, comboData);
      existing.add(comboData);
    });

    reindexVariants();
  });

  variantRows.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove-variant')) {
      e.target.closest('.variant-row')?.remove();
      reindexVariants();
    }
  });

  variantRows.addEventListener('change', (e) => {
    if (e.target.matches('input[type="radio"][name="default_variant"]')) {
      syncDefaultHidden();
    }
  });

  applyBulkBtn.addEventListener('click', () => {
    const price = (bulkPrice.value || '').trim();
    if (!price) return;
    variantRows.querySelectorAll('input[name$="[price]"]').forEach(inp => inp.value = price);
  });

  if (galleryWrap) {
    galleryWrap.addEventListener('click', async (e) => {
      const btn = e.target.closest('.remove-gallery');
      if (!btn) return;
      const url = btn.dataset.url;
      if (!url) return;
      const ok = await confirmGalleryDelete();
      if (!ok) return;
      btn.disabled = true;
      try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(url, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.ok) throw new Error(data.message || 'Xóa thất bại');
        btn.closest('.relative')?.remove();
      } catch (err) {
        alert(err.message || 'Có lỗi xảy ra');
      } finally {
        btn.disabled = false;
      }
    });
  }

  function confirmGalleryDelete() {
    const modal = document.getElementById('galleryDeleteModal');
    const okBtn = document.getElementById('galleryDeleteOk');
    const cancelBtn = document.getElementById('galleryDeleteCancel');
    if (!modal || !okBtn || !cancelBtn) return Promise.resolve(true);

    modal.classList.remove('hidden');

    return new Promise((resolve) => {
      const cleanup = () => {
        okBtn.removeEventListener('click', onOk);
        cancelBtn.removeEventListener('click', onCancel);
        modal.classList.add('hidden');
      };
      const onOk = () => {
        cleanup();
        resolve(true);
      };
      const onCancel = () => {
        cleanup();
        resolve(false);
      };
      okBtn.addEventListener('click', onOk, { once: true });
      cancelBtn.addEventListener('click', onCancel, { once: true });
    });
  }

  form.addEventListener('submit', (e) => {
    const groups = parseOptions();
    const hasVariants = variantRows.querySelectorAll('.variant-row').length > 0;

    if (groups.length && !hasVariants) {
      e.preventDefault();
      alert('Bạn đã nhập phân loại nhưng chưa tạo tổ hợp biến thể.');
      return;
    }

    if (hasVariants) {
      let ok = true;
      variantRows.querySelectorAll('input[name$="[price]"]').forEach(inp => {
        if (!inp.value) ok = false;
      });
      if (!ok) {
        e.preventDefault();
        alert('Vui lòng nhập đầy đủ giá cho các biến thể.');
      }
    }
  });

  // initial sync
  reindexOptions();
  reindexVariants();
});
</script>
@endpush

<div id="galleryDeleteModal" class="hidden fixed inset-0 z-50">
  <div class="absolute inset-0 bg-black/60"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-sm rounded-xl border border-white/10 bg-gray-900 p-5 text-bl shadow-2xl">
      <div class="text-lg font-semibold">Xác nhận xóa ảnh</div>
      <div class="mt-2 text-sm text-bl/70">Bạn có chắc muốn xóa ảnh phụ này?</div>
      <div class="mt-4 flex justify-end gap-2">
        <button id="galleryDeleteCancel" type="button" class="px-4 py-2 rounded-md border border-white/20 text-bl/80 hover:bg-white/10">
          Hủy
        </button>
        <button id="galleryDeleteOk" type="button" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">
          Xóa
        </button>
      </div>
    </div>
  </div>
</div>
