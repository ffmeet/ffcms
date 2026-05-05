<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="{{ $ariaLabel ?? '概览' }}">
    @foreach (($cards ?? []) as $card)
        <article class="rounded-[1.6rem] border border-[#e5e7eb] bg-white p-5 shadow-[0_18px_46px_rgba(15,23,42,0.05)]">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#6b7280]">{{ $card['label'] }}</p>
            <strong class="mt-3 block text-3xl font-black tracking-tight text-[#181512]">{{ $card['value'] }}</strong>
            <p class="mt-3 text-sm leading-7 text-[#5f574f]">{{ $card['description'] }}</p>
        </article>
    @endforeach
</section>
