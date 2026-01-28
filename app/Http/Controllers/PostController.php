<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\AuditLog;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user()?->isAdmin()) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Post::with('client');
        $search = trim((string) $request->input('q', ''));
        $clientId = $request->integer('client_id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%');
            });
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $posts = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $clients = Client::orderBy('name')->get();

        return view('posts.index', compact('posts', 'clients', 'search', 'clientId'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();

        return view('posts.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $post = Post::create($data);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'post_created',
            'auditable_type' => Post::class,
            'auditable_id' => $post->id,
            'before' => null,
            'after' => $post->only(['client_id', 'name', 'address']),
        ]);

        return redirect()->route('posts.index')->with('status', 'Puesto creado.');
    }

    public function edit(Post $post)
    {
        $clients = Client::orderBy('name')->get();

        return view('posts.edit', compact('post', 'clients'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $before = $post->only(['client_id', 'name', 'address', 'notes']);
        $post->update($data);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'post_updated',
            'auditable_type' => Post::class,
            'auditable_id' => $post->id,
            'before' => $before,
            'after' => $post->only(['client_id', 'name', 'address', 'notes']),
        ]);

        return redirect()->route('posts.index')->with('status', 'Puesto actualizado.');
    }

    public function destroy(Post $post)
    {
        AuditLog::create([
            'user_id' => request()->user()?->id,
            'action' => 'post_deleted',
            'auditable_type' => Post::class,
            'auditable_id' => $post->id,
            'before' => $post->only(['client_id', 'name']),
            'after' => null,
        ]);

        $post->delete();

        return redirect()->route('posts.index')->with('status', 'Puesto eliminado.');
    }
}
