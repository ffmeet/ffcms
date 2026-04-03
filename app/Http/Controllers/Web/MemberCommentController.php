<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberCommentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:approved,pending'],
            'q' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:latest,oldest,status'],
        ]);

        $comments = Comment::query()
            ->with(['post', 'parent.user'])
            ->where('user_id', $request->user()->id)
            ->when(
                filled($filters['status'] ?? null),
                fn ($query) => $query->where('status', $filters['status']),
            )
            ->when(
                filled($filters['q'] ?? null),
                fn ($query) => $query->where(function ($subQuery) use ($filters): void {
                    $subQuery
                        ->where('content', 'like', '%'.$filters['q'].'%')
                        ->orWhereHas('post', fn ($postQuery) => $postQuery->where('title', 'like', '%'.$filters['q'].'%'));
                }),
            )
            ->when(
                ($filters['sort'] ?? 'latest') === 'oldest',
                fn ($query) => $query->oldest(),
                fn ($query) => $query->latest(),
            )
            ->when(
                ($filters['sort'] ?? null) === 'status',
                fn ($query) => $query->reorder('status')->latest('created_at'),
            )
            ->paginate(12)
            ->withQueryString();

        return view('site.member.comments.index', [
            'user' => $request->user(),
            'comments' => $comments,
            'filters' => $filters,
        ]);
    }
}
