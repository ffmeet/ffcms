<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Models\Category;
use App\Models\ContentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostFormCategoryOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_options_include_categories_from_multiple_models(): void
    {
        $newsModel = ContentModel::create([
            'name' => '新闻模型',
            'table_name' => 'posts_news',
        ]);

        $flashModel = ContentModel::create([
            'name' => '快讯模型',
            'table_name' => 'posts_flash',
        ]);

        $newsCategory = Category::create([
            'model_id' => $newsModel->id,
            'name' => '公司新闻',
            'slug' => 'company-news',
            'sort_order' => 1,
            'level' => 0,
        ]);

        $flashCategory = Category::create([
            'model_id' => $flashModel->id,
            'name' => '最新快讯',
            'slug' => 'latest-flash',
            'sort_order' => 2,
            'level' => 0,
        ]);

        $options = PostForm::categoryOptions();

        $this->assertSame('公司新闻 [新闻模型]', $options[$newsCategory->id]);
        $this->assertSame('最新快讯 [快讯模型]', $options[$flashCategory->id]);
    }
}
