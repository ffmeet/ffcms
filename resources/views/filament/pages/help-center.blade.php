<div class="ecms-settings-content">
    <section class="ecms-settings-header">
        <p class="ecms-settings-eyebrow">Help</p>
        <h1>帮助中心</h1>
        <p>这里先放后台常用入口和说明，后面可以继续补成更完整的运营帮助、发布规范和快捷入口。</p>
    </section>

    <section class="ecms-settings-section">
        <div class="ecms-settings-section-head">
            <h2>常用帮助</h2>
        </div>

        <div class="ecms-settings-grid">
            <article class="ecms-settings-card">
                <div class="ecms-settings-card-main">
                    <span class="ecms-settings-card-icon">
                        <x-heroicon-o-pencil-square class="ecms-settings-card-icon-svg" />
                    </span>
                    <h3>发布内容</h3>
                    <p>从右上角的发布入口，分别进入发布文章或发布快讯的独立表单。</p>
                </div>
                <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl() }}" class="ecms-settings-card-link">打开内容</a>
            </article>

            <article class="ecms-settings-card">
                <div class="ecms-settings-card-main">
                    <span class="ecms-settings-card-icon">
                        <x-heroicon-o-cog-6-tooth class="ecms-settings-card-icon-svg" />
                    </span>
                    <h3>低频设置</h3>
                    <p>内容模型、会员组等低频配置现在会统一保持在设置壳层内操作。</p>
                </div>
                <a href="{{ \App\Filament\Pages\SettingsCenter::getUrl() }}" class="ecms-settings-card-link">打开设置</a>
            </article>
        </div>
    </section>
</div>
