<?php

use function Livewire\Volt\{state, usesFileUploads, computed, rules};
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

usesFileUploads();

state([
    'title' => '',
    'description' => '',
    'image' => null,
    'editingPostId' => null, // ← used to track the post being edited
    'alertMessage' => null,
    'alertType' => 'success',
]);

rules([
    'title' => 'required|min:3',
    'description' => 'required|min:3',
    'image' => 'nullable|image|max:1024', // optional on update
]);

$posts = computed(fn() => Post::all());

$addPost = function () {
    $this->validate();

    Post::create([
        'title' => $this->title,
        'description' => $this->description,
        'image' => $this->image ? $this->image->store('posts') : null,
    ]);

    $this->reset(['title', 'description', 'image']);
    $this->alertMessage = 'Post created successfully';
    $this->alertType = 'success';
};

$editPost = function (Post $post) {
    $this->editingPostId = $post->id;
    $this->title = $post->title;
    $this->description = $post->description;
    $this->image = null; // don't prefill image for edit
};

$updatePost = function () {
    $this->validate();

    $post = Post::find($this->editingPostId);

    $data = [
        'title' => $this->title,
        'description' => $this->description,
    ];

    if ($this->image) {
        Storage::delete($post->image);
        $data['image'] = $this->image->store('posts');
    }

    $post->update($data);

    $this->reset(['title', 'description', 'image', 'editingPostId']);
    $this->alertMessage = 'Post edited successfully';
    $this->alertType = 'success';
};

$deletePost = function (Post $post) {
    Storage::delete($post->image);
    $post->delete();
    $this->alertMessage = 'Post deleted successfully';
    $this->alertType = 'success';
};
?>

<div class="p-4">
    <h2 class="text-white text-2xl font-bold mb-4">Posts</h2>

    {{-- Form --}}
    <form wire:submit="{{ $editingPostId ? 'updatePost' : 'addPost' }}" enctype="multipart/form-data" class="space-y-4 bg-white p-4 rounded shadow">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" wire:model="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <input type="text" wire:model="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
            <input type="file" wire:model="image" class="mt-1 block w-full">
            @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex space-x-2">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                {{ $editingPostId ? 'Update' : 'Save' }}
            </button>
            @if ($editingPostId)
                <button type="button" wire:click="$reset(['title', 'description', 'image', 'editingPostId'])" class="bg-gray-400 text-white px-4 py-2 rounded">
                    Cancel
                </button>
            @endif
        </div>
    </form>

    {{-- Posts List --}}
    <h3 class="text-white mt-8 mb-4 text-xl">List of Posts</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($this->posts as $post)
            <div class="bg-white rounded shadow overflow-hidden">
                <img src="{{ asset('/storage/' . $post->image) }}" alt="Post Image" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h4 class="text-lg font-bold">{{ $post->title }}</h4>
                    <p class="text-gray-600">{{ $post->description }}</p>
                    <div class="flex space-x-2 mt-3">
                        <button wire:click="editPost({{ $post->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">
                            Edit
                        </button>
                        <button wire:click="deletePost({{ $post->id }})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div
    x-data="{ show: true }"
    x-show="show"
    x-transition
    x-init="setTimeout(() => show = false, 3000)"
    wire:poll
>
    @if ($alertMessage)
        <div class="mb-4 px-4 py-2 rounded text-white {{ $alertType === 'success' ? 'bg-green-500' : 'bg-red-500' }}">
            {{ $alertMessage }}
        </div>
    @endif
</div>

</div>
