<?php

namespace Tests\Feature;

use App\Models\ContentModel;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostModelTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_detects_flash_model_by_table_name(): void
    {
        $flashModel = ContentModel::create([
            'name' => '快讯',
            'table_name' => 'posts_flash',
        ]);

        $legacyFlashModel = ContentModel::create([
            'name' => '旧快讯',
            'table_name' => 'posts_kx',
        ]);

        $newsModel = ContentModel::create([
            'name' => '新闻文章',
            'table_name' => 'posts_news',
        ]);

        $flashPost = new Post(['model_id' => $flashModel->id]);
        $flashPost->setRelation('contentModel', $flashModel);

        $legacyFlashPost = new Post(['model_id' => $legacyFlashModel->id]);
        $legacyFlashPost->setRelation('contentModel', $legacyFlashModel);

        $newsPost = new Post(['model_id' => $newsModel->id]);
        $newsPost->setRelation('contentModel', $newsModel);

        $this->assertTrue($flashPost->isFlashModel());
        $this->assertTrue($legacyFlashPost->isFlashModel());
        $this->assertFalse($newsPost->isFlashModel());
    }
}
