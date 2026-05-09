<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KnowledgeBaseController extends Controller
{
    public function index()
    {
        $articles = KnowledgeArticle::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('knowledge-base.index', compact('articles'));
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

        $adminId = User::query()
            ->whereHas('role', function ($query) {
                $query->where('name', 'admin');
            })
            ->value('id');

        KnowledgeArticle::query()->create([
            'user_id' => $adminId,
            'title' => $data['title'],
            'slug' => Str::slug($data['title']) . '-' . time(),
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
            'slug' => Str::slug($data['title']) . '-' . $article->id,
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
