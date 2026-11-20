<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search', strict: true)->trim()->toString();

        $articles = KnowledgeArticle::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(fn ($query) => $query
                    ->whereLike('title', "%{$search}%")
                    ->orWhereLike('content', "%{$search}%")
                    ->orWhereLike('status', "%{$search}%")
                    ->orWhereHas('user', fn ($author) => $author->whereLike('name', "%{$search}%")));
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('knowledge-base.index', compact('articles', 'search'));
    }

    public function create()
    {
        return view('knowledge-base.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
        ]);

        KnowledgeArticle::query()->create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'slug' => (Str::slug($data['title']) ?: 'article').'-'.Str::lower(Str::ulid()->toBase32()),
            'content' => $data['content'],
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('knowledge.index')
            ->with('success', 'Article created successfully.');
    }

    public function edit(KnowledgeArticle $article)
    {
        return view('knowledge-base.edit', compact('article'));
    }

    public function update(Request $request, KnowledgeArticle $article)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $article->update([
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('knowledge.index')
            ->with('success', 'Article updated successfully.');
    }

    public function destroy(KnowledgeArticle $article)
    {
        KnowledgeArticle::query()
            ->whereKey($article->id)
            ->delete();

        return redirect()
            ->route('knowledge.index')
            ->with('success', 'Article deleted successfully.');
    }
}
