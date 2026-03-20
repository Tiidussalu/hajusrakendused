<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    // ── Anyone: view posts ────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $posts = Post::with('user')
            ->withCount('approvedComments')
            ->where('published', true)
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('Blog/Index', [
            'posts'  => $posts,
            'search' => $request->search,
        ]);
    }

    public function show(Post $post): Response
    {
        abort_if(!$post->published, 404);
        $post->load(['user', 'approvedComments']);

        return Inertia::render('Blog/Show', ['post' => $post]);
    }

    // ── Anyone: add comments (guests too) ────────────────────────────────────

    public function storeComment(Request $request, Post $post)
    {
        $comment = $post->comments()->create([
            ...$request->validate([
                'author_name'  => 'required|string|max:100',
                'author_email' => 'required|email|max:255',
                'body'         => 'required|string|min:2|max:2000',
            ]),
            'user_id'  => auth()->id(),
            'approved' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'comment' => $comment]);
        }

        return back()->with('success', 'Kommentaar lisatud!');
    }

    // ── Logged-in users: create posts, edit own posts ─────────────────────────

    public function create(): Response
    {
        return Inertia::render('Blog/Create');
    }

    public function store(Request $request)
    {
        $post = Auth::user()->posts()->create($request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'image'       => 'nullable|string|max:500',
            'published'   => 'boolean',
        ]));

        return redirect()->route('blog.show', $post->slug)
            ->with('success', 'Postitus loodud!');
    }

    public function edit(Post $post): Response
    {
        // Only the post author can edit
        abort_unless(Auth::id() === $post->user_id, 403);

        return Inertia::render('Blog/Edit', ['post' => $post]);
    }

    public function update(Request $request, Post $post)
    {
        // Only the post author can update
        abort_unless(Auth::id() === $post->user_id, 403);

        $post->update($request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'image'       => 'nullable|string|max:500',
            'published'   => 'boolean',
        ]));

        return redirect()->route('blog.show', $post->slug)
            ->with('success', 'Postitus uuendatud!');
    }

    // ── Admin only: delete comments ───────────────────────────────────────────

    public function destroyComment(Comment $comment)
    {
        abort_unless(Auth::user()?->is_admin, 403);

        $comment->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Kommentaar kustutatud!');
    }
}