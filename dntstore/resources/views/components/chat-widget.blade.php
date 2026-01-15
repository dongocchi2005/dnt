{{-- DNT Assistant Widget --}}
<div id="dntChat" class="fixed bottom-6 right-6 z-[9999]">
    {{-- Toggle button --}}
    <button id="dntChatToggle"
            type="button"
            class="w-14 h-14 rounded-full shadow-lg bg-sky-500 text-bl flex items-center justify-center"
            aria-expanded="false"
            aria-controls="dntChatPanel">
        {{-- icon --}}
        <span class="text-xl">💬</span>
    </button>

    {{-- Panel --}}
    <div id="dntChatPanel"
         class="hidden mt-4 w-[360px] max-w-[90vw] bg-white rounded-2xl shadow-2xl overflow-hidden chat-panel">
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-sky-600 text-bl">
            <div>
                <div class="font-bold">Trợ Lý AI</div>
                <div id="dntChatStatus" class="text-xs opacity-90">Sẵn sàng</div>
            </div>

            <button id="dntChatClose" type="button" class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30">
                ✕
            </button>
        </div>

        {{-- Quick actions --}}
       <div class="px-4 py-2 bg-sky-50 flex gap-2 flex-wrap">
    <button type="button"
        class="dnt-chat-quick bg-bl border rounded-full px-3 py-1 text-xs text-black hover:bg-gray-200"
        data-chat-preset="Mình muốn đặt lịch sửa chữa">
        Đặt lịch ngay
    </button>

    <button type="button"
        class="dnt-chat-quick bg-bl border rounded-full px-3 py-1 text-xs text-black hover:bg-gray-200"
        data-chat-preset="Tra cứu đơn hàng">
        Tra cứu đơn
    </button>

    <button type="button"
        class="dnt-chat-quick bg-bl border rounded-full px-3 py-1 text-xs text-black hover:bg-gray-200"
        data-chat-preset="Mình muốn gặp nhân viên hỗ trợ">
        Gọi hỗ trợ
    </button>
</div>


        {{-- Messages --}}
        <div id="dntChatCountdown" class="hidden px-4 py-2 text-sm bg-amber-50 text-amber-900 border-b"></div>
        <div id="dntChatMessages" class="p-4 space-y-3 h-[320px] overflow-auto bg-white"></div>

        {{-- Input --}}
        <div class="p-3 border-t bg-bl flex gap-2">
            <input id="dntChatInput"
                   type="text"
                   class="flex-1 border rounded-xl px-3 py-2 outline-none text-gray-900 placeholder-gray-500"
                   placeholder="Nhập tin nhắn của bạn..." />
            <button id="dntChatSend"
                    type="button"
                    class="px-4 py-2 rounded-xl bg-black text-white !text-white disabled:opacity-60"
                    style="color:#fff">
                Gửi
            </button>
        </div>
    </div>
 </div>

