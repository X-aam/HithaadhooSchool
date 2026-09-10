<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsArticleRequest;
use App\Models\NewsArticle;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/news/Index', [
            'articles' => NewsArticle::query()
                ->orderByDesc('published_at')
                ->paginate(15)
                ->through(fn (NewsArticle $a) => [
                    'id' => $a->id,
                    'slug' => $a->slug,
                    'category' => $a->category,
                    'image' => $a->image,
                    'is_published' => $a->is_published,
                    'published_at' => $a->published_at?->format('Y-m-d'),
                    'title' => $a->title,
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/news/Form', [
            'article' => null,
            'authorName' => auth()->user()->authorName(),
        ]);
    }

    public function store(NewsArticleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['author'] = $request->user()->authorName();

        NewsArticle::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Article created.']);

        return redirect()->route('admin.news.index');
    }

    public function edit(NewsArticle $news): Response
    {
        return Inertia::render('admin/news/Form', [
            'article' => [
                'id' => $news->id,
                'slug' => $news->slug,
                'category' => $news->category,
                'image' => $news->image,
                'is_published' => $news->is_published,
                'published_at' => $news->published_at?->format('Y-m-d'),
                'author' => $news->author,
                'title' => $news->title,
                'excerpt' => $news->excerpt,
                'body' => $news->body,
            ],
            'authorName' => $news->author,
        ]);
    }

    public function update(NewsArticleRequest $request, NewsArticle $news): RedirectResponse
    {
        $news->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Article updated.']);

        return redirect()->route('admin.news.index');
    }

    public function destroy(NewsArticle $news): RedirectResponse
    {
        $news->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Article deleted.']);

        return redirect()->route('admin.news.index');
    }
}
