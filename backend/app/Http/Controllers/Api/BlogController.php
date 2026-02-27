<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with('author')->where('active', true);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('published')) {
            $query->whereNotNull('published_at')
                  ->where('published_at', '<=', now());
        }

        $posts = $query->orderBy('published_at', 'desc')->paginate(12);

        return response()->json($posts);
    }

    public function show($slug)
    {
        $post = Post::with('author')
            ->where('slug', $slug)
            ->where('active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return response()->json($post);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'type' => 'required|in:news,event',
            'image' => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        $validated['author_id'] = $request->user()->id;

        $post = Post::create($validated);

        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'sometimes|string',
            'type' => 'sometimes|in:news,event',
            'image' => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        $post->update($validated);

        return response()->json($post);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
