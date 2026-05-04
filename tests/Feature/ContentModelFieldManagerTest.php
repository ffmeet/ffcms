<?php

namespace Tests\Feature;

use App\Models\ContentModel;
use App\Support\ContentModelFieldManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentModelFieldManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_normalizes_legacy_key_value_field_config(): void
    {
        $normalized = ContentModelFieldManager::normalizeFieldConfig([
            'source' => '来源',
            'seo_title' => '不应保留',
            'priority_level' => '',
        ]);

        $this->assertSame([
            [
                'name' => 'source',
                'label' => '来源',
                'type' => 'text',
                'required' => false,
                'options' => [],
                'placeholder' => '',
                'helper_text' => '',
                'default' => null,
            ],
            [
                'name' => 'priority_level',
                'label' => 'Priority Level',
                'type' => 'text',
                'required' => false,
                'options' => [],
                'placeholder' => '',
                'helper_text' => '',
                'default' => null,
            ],
        ], $normalized);
    }

    public function test_it_normalizes_structured_field_config_and_filters_invalid_rows(): void
    {
        $normalized = ContentModelFieldManager::normalizeFieldConfig([
            [
                'name' => 'source_name',
                'label' => '来源名称',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'seo_title',
                'label' => '保留字段',
                'type' => 'text',
            ],
            [
                'name' => 'topic',
                'label' => '专题',
                'type' => 'select',
                'required' => false,
                'options' => ['头条', '', '专题'],
            ],
        ]);

        $this->assertSame([
            [
                'name' => 'source_name',
                'label' => '来源名称',
                'type' => 'text',
                'required' => true,
                'options' => [],
                'placeholder' => '',
                'helper_text' => '',
                'default' => null,
            ],
            [
                'name' => 'topic',
                'label' => '专题',
                'type' => 'select',
                'required' => false,
                'options' => ['头条', '专题'],
                'placeholder' => '',
                'helper_text' => '',
                'default' => null,
            ],
        ], $normalized);
    }

    public function test_it_normalizes_field_metadata_and_coerces_default_values(): void
    {
        $normalized = ContentModelFieldManager::normalizeFieldConfig([
            [
                'name' => 'author_note',
                'label' => '作者备注',
                'type' => 'textarea',
                'placeholder' => '填写备注',
                'helper_text' => '这段只用于后台录入。',
                'default' => '默认备注',
            ],
            [
                'name' => 'weight',
                'label' => '权重',
                'type' => 'number',
                'default' => '12',
            ],
            [
                'name' => 'is_featured',
                'label' => '推荐',
                'type' => 'toggle',
                'default' => 'true',
            ],
            [
                'name' => 'topic',
                'label' => '专题',
                'type' => 'select',
                'options' => ['头条', '专题'],
                'default' => '专题',
            ],
        ]);

        $this->assertSame('填写备注', $normalized[0]['placeholder']);
        $this->assertSame('这段只用于后台录入。', $normalized[0]['helper_text']);
        $this->assertSame('默认备注', $normalized[0]['default']);
        $this->assertSame(12, $normalized[1]['default']);
        $this->assertTrue($normalized[2]['default']);
        $this->assertSame('专题', $normalized[3]['default']);
    }

    public function test_it_filters_custom_fields_by_selected_model(): void
    {
        $model = ContentModel::create([
            'name' => '新闻模型',
            'table_name' => 'posts_news',
            'field_config' => [
                [
                    'name' => 'source',
                    'label' => '来源',
                    'type' => 'text',
                    'required' => false,
                ],
                [
                    'name' => 'is_featured',
                    'label' => '头条推荐',
                    'type' => 'toggle',
                    'required' => false,
                ],
            ],
        ]);

        $filtered = ContentModelFieldManager::filterCustomFieldsForModelId([
            'source' => '新华社',
            'is_featured' => true,
            'legacy_key' => 'should be dropped',
        ], $model->id);

        $this->assertSame([
            'source' => '新华社',
            'is_featured' => true,
        ], $filtered);
    }
}
