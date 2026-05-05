<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name')->default('年度科技先生');
            $table->string('site_tagline')->default('内容杂志与会员经济实验场');
            $table->string('site_description')->nullable();
            $table->string('logo_text', 24)->default('帝');
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('hero_eyebrow')->default('CONTENT PORTAL');
            $table->string('hero_title')->default('用内容、专题、活动和会员体系，把站点升级成真正可运营的内容门户。');
            $table->text('hero_body')->nullable();
            $table->string('hero_primary_label')->default('浏览最新内容');
            $table->string('hero_primary_url')->default('/');
            $table->string('hero_secondary_label')->default('进入会员体系');
            $table->string('hero_secondary_url')->default('/pricing');
            $table->unsignedSmallInteger('featured_posts_limit')->default(8);
            $table->unsignedSmallInteger('featured_categories_limit')->default(6);
            $table->unsignedSmallInteger('featured_tags_limit')->default(12);
            $table->boolean('show_shop_section')->default(true);
            $table->boolean('show_events_section')->default(true);
            $table->boolean('show_membership_section')->default(true);
            $table->json('primary_navigation')->nullable();
            $table->json('footer_navigation')->nullable();
            $table->json('social_links')->nullable();
            $table->json('business_settings')->nullable();
            $table->json('member_settings')->nullable();
            $table->text('footer_copyright')->nullable();
            $table->timestamps();
        });

        Schema::create('membership_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('billing_period')->default('monthly');
            $table->unsignedInteger('duration_days')->default(30);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('features')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status')->default('draft')->index();
            $table->string('cover_image_url')->nullable();
            $table->string('delivery_type')->default('download');
            $table->string('currency', 8)->default('CNY');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_no')->unique();
            $table->string('order_type')->default('product');
            $table->nullableMorphs('purchasable');
            $table->string('title');
            $table->string('currency', 8)->default('CNY');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'order_type', 'status']);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('provider')->default('wechat');
            $table->string('provider_payment_no')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->decimal('amount', 10, 2)->default(0);
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('membership_plans')->cascadeOnDelete();
            $table->foreignId('last_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('status')->default('inactive')->index();
            $table->boolean('auto_renew')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status')->default('draft')->index();
            $table->string('cover_image_url')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->decimal('price', 10, 2)->default(0);
            $table->foreignId('required_member_group_id')->nullable()->constrained('member_groups')->nullOnDelete();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('registration_opens_at')->nullable();
            $table->timestamp('registration_closes_at')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('status')->default('registered')->index();
            $table->string('payment_status')->default('unpaid')->index();
            $table->text('notes')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('membership_plans');
        Schema::dropIfExists('site_settings');
    }
};
