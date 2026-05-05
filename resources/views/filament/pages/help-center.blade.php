<div class="ecms-settings-content">
    <section class="ecms-settings-header">
        <p class="ecms-settings-eyebrow">Help</p>
        <h1>帮助中心</h1>
        <p>这里提供后台常用入口，以及可直接在线浏览的开发文档，不需要手动打开 `.md` 原文件。</p>
    </section>

    <section class="ecms-settings-section">
        <div class="ecms-settings-section-head">
            <h2>常用帮助</h2>
        </div>

        <div class="ecms-settings-grid">
            <article class="ecms-settings-card">
                <div class="ecms-settings-card-main">
                    <span class="ecms-settings-card-icon">
                        <x-heroicon-o-book-open class="ecms-settings-card-icon-svg" />
                    </span>
                    <h3>开发文档</h3>
                    <p>左侧目录、右侧正文的在线文档浏览页，适合查看架构、发布清单、二次开发和安全说明。</p>
                </div>
                <a href="{{ route('developer.docs') }}" class="ecms-settings-card-link">在线浏览</a>
            </article>

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

    <section class="ecms-settings-section">
        <div class="ecms-settings-section-head">
            <h2>文档快速入口</h2>
        </div>

        <div class="ecms-settings-grid">
            <article class="ecms-settings-card">
                <div class="ecms-settings-card-main">
                    <h3>1.0 发布清单</h3>
                    <p>查看当前版本上线前的检查项、环境准备和发布步骤。</p>
                </div>
                <a href="{{ route('developer.docs', ['page' => 'release/1.0-release-plan']) }}" class="ecms-settings-card-link">打开文档</a>
            </article>

            <article class="ecms-settings-card">
                <div class="ecms-settings-card-main">
                    <h3>二次开发指南</h3>
                    <p>快速理解主题、首页位置位、路由和后续扩展的落点。</p>
                </div>
                <a href="{{ route('developer.docs', ['page' => 'development/secondary-development']) }}" class="ecms-settings-card-link">打开文档</a>
            </article>
        </div>
    </section>
</div>
