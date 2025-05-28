<?php

use function Livewire\Volt\{state, usesFileUploads, computed, rules};
use App\Models\Post;
use Illuminate\Support\Facades\Storage;


usesFileUploads();
state([
    'title' => '',
    'description' => '',
    'image' => null
]);

rules([
    'title' => 'required|min:3',
    'description' => 'required|min:3',
    'image' => 'image|max:1024'
]);

$posts = computed(fn() => Post::all());
$addPost = function() {
    $this->validate();
    Post::create([
        'title' => $this->title,
        'description' => $this->description,
        'image' => $this->image->store('posts'),
    ]);
    $this->title = '';
    $this->description = '';
};

$deletePost = function(Post $post) {
Storage::delete($post->image);
$post->delete();
};

?>

<div>
    <h2 class="text-white">Post </h2>
    <div class="my-4">
    <form wire:submit="addPost" class="p-4 space-y-2" enctype="multipart/form-data">
    <div>
        <label for="title" class="text-white">Title</label>
        <input type="text" wire:model="title" class="block w-full px-4 py-2 mt-2 text-white-700 bg-white"/>
        @error('title')
        <span class="text-red-400">{{ $message}}</span>
        @enderror
    </div>

    <div>
        <label for="description" class="text-white">Description</label>
        <input type="text" wire:model="description" class="block w-full px-4 py-2 mt-2 text-gray-700 bg-white" />
        @error('description')
        <span class="text-red-400">{{ $message}}</span>
        @enderror
    </div>

    <div>
        <label for="image" class="text-white">Image</label>
        <input type="file" wire:model="image" class="block w-full px-4 py-2 mt-2 text-gray-700 bg-white" />
        @error('image')
        <span class="text-red-400">{{ $message}}</span>
        @enderror
    </div>

    <button type="submit" class="px-3 py-2 bg-indigo-500 rounded">Save</button>
    </form>
    </div>


        <h3 class="text-white">List of posts</h3>



    @foreach ($this->posts as $post)
<div class="max-w-sm bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <a href="#">
        <img class="rounded-t-lg" src="{{asset('/storage/' .$post->image)}}" alt="" />
    </a>
    <div class="p-5">
        <a href="#">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{$post->title}}</h5>
        </a>
        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"> {{$post->description}}</p>
        <button wire:click="deletePost({{ $post->id}})" class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">DELETE</button>

    </div>
</div>
@endforeach

</div>
