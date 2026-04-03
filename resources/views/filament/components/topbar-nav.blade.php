@php
    $items = [
        ['label' => '内容', 'pattern' => 'filament.admin.resources.posts.*', 'url' => url('/admin/posts')],
        ['label' => '栏目', 'pattern' => 'filament.admin.resources.categories.*', 'url' => url('/admin/categories')],
        ['label' => '会员', 'pattern' => 'filament.admin.resources.users.*', 'url' => url('/admin/users')],
        ['label' => '评论', 'pattern' => 'filament.admin.resources.comments.*', 'url' => url('/admin/comments')],
        ['label' => '媒体', 'pattern' => 'filament.admin.pages.media-manager', 'url' => url('/admin/media-manager')],
    ];
@endphp

<div class="ecms-topbar-nav hidden xl:flex xl:items-center xl:gap-6">
    @foreach ($items as $item)
        <a
            href="{{ $item['url'] }}"
            @class([
                'ecms-topbar-link',
                'is-active' => request()->routeIs($item['pattern']),
            ])
        >
            {{ $item['label'] }}
        </a>
    @endforeach
</div>
