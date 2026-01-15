@extends('layouts.admin')

@section('page-title','Tạo sản phẩm')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="cyber-panel p-6">
        <h1 class="text-2xl font-bold text-bl mb-6 neon font-display">Tạo sản phẩm</h1>

        <form action="{{ route('admin.products.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-6">
        @csrf

        {{-- Tên --}}
        <div>
            <label class="block text-sm font-medium text-bl/80 mb-1">Tên sản phẩm</label>
            <input
                type="text"
                name="name"
                required
                class="block w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5
                       text-bl placeholder-white/30
                       focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none shadow-inner"
                placeholder="Nhập tên sản phẩm..."
            >
        </div>

        {{-- Slug --}}
        <div>
            <label class="block text-sm font-medium text-bl/80 mb-1">
                Slug (tùy chọn)
            </label>
            <input
                type="text"
                name="slug"
                class="block w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5
                       text-bl placeholder-white/30
                       focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none shadow-inner"
                placeholder="tu-dong-tao-neu-trong"
            >
        </div>

        {{-- Mô tả --}}
        <div>
            <label class="block text-sm font-medium text-bl/80 mb-1">Mô tả</label>
            <textarea
                name="description"
                rows="4"
                class="block w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5
                       text-bl placeholder-white/30
                       focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none shadow-inner custom-scrollbar"
                placeholder="Mô tả chi tiết sản phẩm..."
            ></textarea>
        </div>

        {{-- Danh mục --}}
        <div>
            <label class="block text-sm font-medium text-bl/80 mb-1">Danh mục</label>
            <select name="category_id" id="category_id"
                    class="block w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5
                           text-bl
                           focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none appearance-none [&>option]:bg-gray-900">
                <option value="" class="text-gray-400">Chọn danh mục</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Giá --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-bl/80 mb-1">Giá gốc</label>
                <div class="relative">
                    <input
                        type="number"
                        name="original_price"
                        required
                        class="block w-full bg-white/5 border border-white/10 rounded-lg pl-4 pr-12 py-2.5
                               text-bl placeholder-white/30
                               focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none shadow-inner"
                        placeholder="0"
                    >
                    <span class="absolute right-4 top-2.5 text-bl/40 text-sm">VND</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-bl/80 mb-1">Giá bán</label>
                <div class="relative">
                    <input
                        type="number"
                        name="sale_price"
                        required
                        class="block w-full bg-white/5 border border-white/10 rounded-lg pl-4 pr-12 py-2.5
                               text-bl placeholder-white/30
                               focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none shadow-inner"
                        placeholder="0"
                    >
                    <span class="absolute right-4 top-2.5 text-bl/40 text-sm">VND</span>
                </div>
            </div>
        </div>

        {{-- Ảnh --}}
        <div>
            <label class="block text-sm font-medium text-bl/80 mb-1">Ảnh chính</label>
            <div class="flex items-center justify-center w-full">
                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-white/10 border-dashed rounded-lg cursor-pointer bg-white/5 hover:bg-white/10 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <i class="fa-solid fa-cloud-arrow-up text-2xl text-bl/40 mb-2"></i>
                        <p class="text-sm text-bl/60"><span class="font-semibold">Click để tải lên</span> hoặc kéo thả</p>
                        <p class="text-xs text-bl/40">PNG, JPG, GIF (Max 2MB)</p>
                    </div>
                    <input id="dropzone-file" type="file" name="image" accept="image/*" class="hidden" />
                </label>
            </div>
        </div>

        {{-- Gallery --}}
        <div>
            <label class="block text-sm font-medium text-bl/80 mb-1">Ảnh gallery (nhiều ảnh)</label>
            <input
                type="file"
                name="gallery[]"
                accept="image/*"
                multiple
                class="block w-full text-sm text-bl/60
                       file:mr-4 file:py-2 file:px-4
                       file:rounded-full file:border-0
                       file:text-xs file:font-semibold
                       file:bg-blue-500/10 file:text-blue-400
                       hover:file:bg-blue-500/20"
            >
        </div>

        {{-- Số lượng --}}
        <div>
            <label class="block text-sm font-medium text-bl/80 mb-1">Số lượng kho</label>
            <input
                type="number"
                name="stock"
                value="0"
                class="block w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5
                       text-bl placeholder-white/30
                       focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none shadow-inner"
            >
        </div>

        {{-- Checkbox --}}
        <div class="flex items-center gap-6 p-4 bg-white/5 rounded-lg border border-white/5">
            <label class="inline-flex items-center cursor-pointer group">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="rounded border-white/30 bg-white/10 text-blue-500
                           focus:ring-blue-500 focus:ring-offset-0 w-5 h-5 transition-all"
                    checked
                >
                <span class="ml-2 text-bl group-hover:text-blue-400 transition-colors">Kích hoạt</span>
            </label>

            <label class="inline-flex items-center cursor-pointer group">
                <input
                    type="checkbox"
                    name="is_clearance"
                    value="1"
                    class="rounded border-white/30 bg-white/10 text-red-500
                           focus:ring-red-500 focus:ring-offset-0 w-5 h-5 transition-all"
                >
                <span class="ml-2 text-bl group-hover:text-red-400 transition-colors">Hàng thanh lý</span>
            </label>
        </div>

        {{-- Variants Section --}}
        <div class="border border-white/10 rounded-lg p-4 bg-white/5">
            <div class="font-bold text-bl mb-3 flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-blue-400"></i> Phân loại hàng (Size và Màu)
            </div>
            
            <div id="optionsBox" class="space-y-3 mb-4">
                <!-- Size input -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="relative">
                        <input type="text" name="options[0][name]" value="Size" readonly 
                               class="w-full bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-bl font-medium text-center">
                    </div>
                    <div class="md:col-span-2">
                        <input type="text" name="options[0][values]" placeholder="Nhập các size, cách nhau dấu phẩy (VD: S, M, L)" 
                               class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-bl placeholder-white/20 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <!-- Color input -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="relative">
                        <input type="text" name="options[1][name]" value="Màu" readonly 
                               class="w-full bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-bl font-medium text-center">
                    </div>
                    <div class="md:col-span-2">
                        <input type="text" name="options[1][values]" placeholder="Nhập các màu (VD: Đen, Trắng, Đỏ)" 
                               class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-bl placeholder-white/20 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10 pt-4 mt-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="font-bold text-bl">Bảng tổ hợp biến thể</div>
                    <button type="button" id="generateBtn" 
                            class="px-4 py-2 rounded-lg bg-purple-600/20 text-purple-400 border border-purple-500/30 hover:bg-purple-600/30 transition-all text-sm font-bold shadow-[0_0_10px_rgba(168,85,247,0.2)]">
                        <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Tạo biến thể
                    </button>
                </div>

                <div class="flex items-center gap-2 mb-4 p-3 bg-white/5 rounded-lg border border-white/5">
                    <span class="text-sm text-bl/60 whitespace-nowrap">Thiết lập giá hàng loạt:</span>
                    <input type="number" id="bulkPrice" placeholder="Nhập giá..." class="bg-white/10 border border-white/10 rounded px-3 py-1.5 text-bl text-sm w-32 focus:border-blue-500 outline-none">
                    <button type="button" id="applyBulkBtn" class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm hover:bg-blue-500 transition-colors">Áp dụng</button>
                </div>

                <div id="variantTableWrap" class="overflow-x-auto rounded-lg border border-white/10">
                    <table class="min-w-full text-sm">
                        <thead class="bg-white/10 text-bl/80">
                            <tr id="variantHead" class="text-left">
                                <th class="px-4 py-2 font-semibold">Tổ hợp</th>
                                <th class="px-4 py-2 font-semibold">SKU</th>
                                <th class="px-4 py-2 font-semibold">Giá bán</th>
                                <th class="px-4 py-2 font-semibold text-center">Mặc định</th>
                                <th class="px-4 py-2 font-semibold text-center">Xóa</th>
                            </tr>
                        </thead>
                        <tbody id="variantRows" class="divide-y divide-white/5 bg-white/5"></tbody>
                    </table>
                </div>
                <div id="variantInfo" class="text-xs text-bl/40 mt-2 flex items-center gap-1">
                    <i class="fa-solid fa-circle-info"></i> Giới hạn tối đa 100 biến thể.
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="pt-4 border-t border-white/10">
            <button
                type="submit"
                class="cyber-btn w-full sm:w-auto bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded-lg text-lg shadow-[0_0_20px_rgba(37,99,235,0.3)]">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu sản phẩm
            </button>
        </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const optionsBox = document.getElementById('optionsBox');
  const generateBtn = document.getElementById('generateBtn');
  const variantRows = document.getElementById('variantRows');
  const applyBulkBtn = document.getElementById('applyBulkBtn');
  const bulkPrice = document.getElementById('bulkPrice');
  const form = document.querySelector('form');
  const salePriceInput = document.querySelector('input[name="sale_price"]');
  const originalPriceInput = document.querySelector('input[name="original_price"]');

  function parseOptions(){
    const groups = [];
    optionsBox.querySelectorAll('.grid').forEach(row => {
      const name = row.querySelector('input[name^="options"][name$="[name]"]').value.trim();
      const valuesStr = row.querySelector('input[name^="options"][name$="[values]"]').value.trim();
      if (!name || !valuesStr) return;
      const values = Array.from(new Set(valuesStr.split(',').map(s => s.trim()).filter(Boolean)));
      if (values.length) groups.push({ name, values });
    });
    return groups;
  }

  function cartesian(arrays) {
    return arrays.reduce((acc, curr) => {
      const out = [];
      acc.forEach(a => {
        curr.forEach(b => {
          out.push(a.concat([b]));
        });
      });
      return out;
    }, [[]]);
  }

  function resolveDefaultPrice(){
    const sale = (salePriceInput?.value || '').trim();
    const original = (originalPriceInput?.value || '').trim();
    return sale || original || '';
  }

  function setDefaultVariant(idx){
    variantRows.querySelectorAll('input[name^="variants"][name$="[is_default]"]').forEach(h => h.value = '0');
    const hidden = variantRows.querySelector(`input[name="variants[${idx}][is_default]"]`);
    if (hidden) hidden.value = '1';
  }

  function renderVariants(groups, defaultPrice){
    if (!groups.length) return false;
    const arrays = groups.map(g => g.values.map(v => ({ option: g.name, value: v })));
    const combos = cartesian(arrays);
    if (combos.length > 100) {
      alert('Số lượng biến thể quá lớn (>100). Vui lòng giảm số giá trị.');
      return false;
    }
    variantRows.innerHTML = '';
    combos.forEach((items, idx) => {
      const comboData = items.map(it => `${it.option}:${it.value}`).join('|');
      const comboLabel = items.map(it => `<span class="px-2 py-0.5 rounded bg-white/10 text-xs mr-1">${it.value}</span>`).join('');
      const tr = document.createElement('tr');
      tr.className = 'hover:bg-white/5 transition-colors';
      tr.innerHTML = `
        <td class="px-4 py-3 text-bl/80 text-sm font-medium">
          ${comboLabel}
          <input type="hidden" name="variants[${idx}][combo]" value="${comboData}">
        </td>
        <td class="px-4 py-3">
          <input type="text" name="variants[${idx}][sku]" class="bg-white/5 border border-white/10 rounded px-2 py-1.5 text-bl w-full text-xs focus:border-blue-500 outline-none" placeholder="Tự sinh">
        </td>
        <td class="px-4 py-3">
          <input type="number" step="0.01" name="variants[${idx}][price]" value="${defaultPrice}" class="bg-white/5 border border-white/10 rounded px-2 py-1.5 text-bl w-full text-xs font-bold focus:border-blue-500 outline-none" required>
        </td>
        <td class="px-4 py-3 text-center">
          <input type="radio" name="default_variant" value="${idx}" ${idx === 0 ? 'checked' : ''} class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 focus:ring-blue-500">
          <input type="hidden" name="variants[${idx}][is_default]" value="${idx === 0 ? 1 : 0}">
        </td>
        <td class="px-4 py-3 text-center">
          <button type="button" class="p-1.5 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors remove-variant">
            <i class="fa-solid fa-trash"></i>
          </button>
        </td>
      `;
      variantRows.appendChild(tr);
    });
    return combos.length > 0;
  }

  generateBtn.addEventListener('click', () => {
    const groups = parseOptions();
    renderVariants(groups, resolveDefaultPrice());
  });

  variantRows.addEventListener('click', (e) => {
    if (e.target.closest('.remove-variant')) {
      e.target.closest('tr')?.remove();
    }
  });

  variantRows.addEventListener('change', (e) => {
    if (e.target.matches('input[type="radio"][name="default_variant"]')) {
      setDefaultVariant(e.target.value);
    }
  });

  applyBulkBtn.addEventListener('click', () => {
    const price = bulkPrice.value.trim();
    if (!price) return;
    variantRows.querySelectorAll('tr').forEach((tr) => {
      tr.querySelector('input[name$="[price]"]').value = price;
    });
  });

  form.addEventListener('submit', (e) => {
    const groups = parseOptions();
    const hasOptions = groups.length > 0;
    const hasVariants = variantRows.querySelectorAll('tr').length > 0;
    if (hasOptions && !hasVariants) {
      const ok = renderVariants(groups, resolveDefaultPrice());
      if (!ok) {
        e.preventDefault();
        return;
      }
    }
    const rows = variantRows.querySelectorAll('tr');
    if (rows.length > 0) {
      let ok = true;
      rows.forEach(tr => {
        const price = tr.querySelector('input[name$="[price]"]').value;
        if (!price) ok = false;
      });
      if (!ok) {
        e.preventDefault();
        alert('Vui lòng nhập đầy đủ giá cho các biến thể.');
        return;
      }
      const radios = variantRows.querySelectorAll('input[type="radio"][name="default_variant"]');
      if (radios.length && !Array.from(radios).some(r => r.checked)) {
        radios[0].checked = true;
        setDefaultVariant(radios[0].value);
      }
    }
  });
});
</script>
@endpush

@endsection
