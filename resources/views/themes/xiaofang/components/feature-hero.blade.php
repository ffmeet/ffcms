@php
    $align = $align ?? 'center';
@endphp

<section class="border-b border-[#ece7e0] pb-10 pt-8 lg:pb-12 lg:pt-12">
    <div class="{{ $align === 'center' ? 'mx-auto max-w-4xl text-center' : 'max-w-4xl' }}">
        <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-[#8b8175]">{{ $eyebrow }}</p>
        <h1 class="mt-5 font-serif text-4xl font-semibold leading-tight text-[#151515] sm:text-5xl lg:text-[3.6rem]">{{ $title }}</h1>
        <p class="mt-5 text-base leading-8 text-[#5f574f] {{ $align === 'center' ? 'mx-auto max-w-3xl' : 'max-w-3xl' }}">{{ $description }}</p>

        @if (! empty($actions ?? []))
            <div class="mt-7 flex flex-wrap gap-3 {{ $align === 'center' ? 'justify-center' : '' }}">
                @foreach ($actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="{{ ($action['variant'] ?? 'secondary') === 'primary'
                            ? 'bg-[#151515] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]'
                            : 'border border-[#d8d1c8] bg-white px-5 py-3 text-sm font-semibold text-[#151515] transition hover:border-[#151515]' }}"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        @endif

        @if (! empty($metrics ?? []))
            <div class="mt-8 flex flex-wrap gap-x-8 gap-y-3 text-sm {{ $align === 'center' ? 'justify-center' : '' }}">
                @foreach ($metrics as $metric)
                    <div class="flex items-center gap-3 text-[#8b8175]">
                        <span class="font-semibold uppercase tracking-[0.18em]">{{ $metric['label'] }}</span>
                        <span class="text-[#151515]">{{ $metric['value'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
