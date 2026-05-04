<?php

namespace App\Support\Homepage;

use App\Models\Post;
use Illuminate\Support\Collection;

class XiaofangHomepageBuilder
{
    public function build(?Post $leadPost, Collection $featuredPosts, array $themeHomepageContent = []): array
    {
        $topLeadStories = collect($themeHomepageContent['top_lead_stories'] ?? []);
        $topSecondaryStories = collect($themeHomepageContent['top_secondary_stories'] ?? []);
        $latestRailStories = collect($themeHomepageContent['latest_rail_stories'] ?? []);
        $featureGroupStories = collect($themeHomepageContent['feature_group_stories'] ?? []);
        $designGroupStories = collect($themeHomepageContent['design_group_stories'] ?? []);
        $inspirationSlotStories = collect($themeHomepageContent['inspiration_stories'] ?? []);
        $readMoreSlotStories = collect($themeHomepageContent['read_more_stories'] ?? []);
        $slotLimits = $themeHomepageContent['slot_limits'] ?? [];
        $latestRailLimit = max(1, (int) ($slotLimits['slot_03'] ?? 7));

        $nonFlashStories = $featuredPosts->filter(fn (Post $post): bool => ! $post->isFlashModel())->values();
        $coverStories = $nonFlashStories->filter(fn (Post $post): bool => filled($post->cover_image_url))->values();
        $featureCandidates = $nonFlashStories
            ->filter(fn (Post $post): bool => filled($post->cover_image_url) && filled($post->summary) && mb_strlen((string) $post->title) >= 12)
            ->values();

        if ($featureCandidates->count() < 8) {
            $featureCandidates = $coverStories;
        }

        $editorialStories = $featureCandidates->filter(fn (Post $post): bool => $post->is_featured)->take(2)->values();

        if ($editorialStories->count() < 2) {
            $editorialStories = $editorialStories
                ->concat(
                    $featureCandidates
                        ->reject(fn (Post $post): bool => $editorialStories->pluck('id')->contains($post->id))
                        ->take(2 - $editorialStories->count())
                )
                ->take(2)
                ->values();
        }

        $stripStory = $featureCandidates->first(
            fn (Post $post): bool => $post->is_recommended && ! $editorialStories->pluck('id')->contains($post->id)
        )
            ?? $featureCandidates->first(fn (Post $post): bool => ! $editorialStories->pluck('id')->contains($post->id))
            ?? $coverStories->first(fn (Post $post): bool => ! $editorialStories->pluck('id')->contains($post->id))
            ?? $nonFlashStories->first(fn (Post $post): bool => ! $editorialStories->pluck('id')->contains($post->id));

        $groupStories = $featureCandidates->slice(3, 4)->values();

        if ($groupStories->count() < 4) {
            $usedForGroup = collect([$leadPost?->id, ...$editorialStories->pluck('id')->all(), $stripStory?->id])->filter()->all();
            $groupStories = $nonFlashStories
                ->reject(fn (Post $post): bool => in_array($post->id, $usedForGroup, true))
                ->take(4)
                ->values();
        }

        $usedStoryIds = collect([
            $leadPost?->id,
            ...$editorialStories->pluck('id')->all(),
            $stripStory?->id,
            ...$groupStories->pluck('id')->all(),
        ])->filter();

        $latestList = $nonFlashStories
            ->reject(fn (Post $post): bool => $usedStoryIds->contains($post->id))
            ->take($latestRailLimit)
            ->values();

        if ($latestList->isEmpty()) {
            $latestList = $nonFlashStories->take($latestRailLimit)->values();
        }

        $readMoreStories = $featureCandidates
            ->reject(fn (Post $post): bool => $usedStoryIds->contains($post->id))
            ->take(4)
            ->values();

        if ($readMoreStories->count() < 4) {
            $readMoreStories = $coverStories->take(4)->values();
        }

        $inspirationStories = $featureCandidates
            ->reject(fn (Post $post): bool => $usedStoryIds->contains($post->id))
            ->take(4)
            ->values();

        if ($inspirationStories->count() < 4) {
            $inspirationStories = $coverStories->take(4)->values();
        }

        if ($topLeadStories->isNotEmpty()) {
            $leadPost = $topLeadStories->first();
            $stripStory = $topLeadStories->skip(1)->first() ?? $stripStory;
        }

        if ($topSecondaryStories->isNotEmpty()) {
            $editorialStories = $topSecondaryStories->take(2)->values();
        }

        if ($latestRailStories->isNotEmpty()) {
            $latestList = $latestRailStories->take($latestRailLimit)->values();
        }

        if ($featureGroupStories->isNotEmpty()) {
            $groupStories = $featureGroupStories->take(4)->values();
        }

        if ($inspirationSlotStories->isNotEmpty()) {
            $inspirationStories = $inspirationSlotStories->take(4)->values();
        }

        if ($readMoreSlotStories->isNotEmpty()) {
            $readMoreStories = $readMoreSlotStories->take(4)->values();
        }

        return [
            'lead_post' => $leadPost,
            'strip_story' => $stripStory,
            'editorial_stories' => $editorialStories,
            'latest_stories' => $latestList,
            'feature_group_stories' => $groupStories,
            'feature_group_lead' => $groupStories->first(),
            'feature_group_items' => $groupStories->slice(1, 3)->values(),
            'feature_group_label' => ($groupStories->first()?->category?->name ?? '专题').'组',
            'design_group_stories' => $designGroupStories,
            'design_group_lead' => $designGroupStories->first(),
            'design_group_items' => $designGroupStories->slice(1, 3)->values(),
            'design_group_label' => ($designGroupStories->first()?->category?->name ?? '设计').'组',
            'inspiration_stories' => $inspirationStories,
            'read_more_stories' => $readMoreStories,
        ];
    }
}
