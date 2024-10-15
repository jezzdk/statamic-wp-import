@extends('statamic::layout')

@section('title', __('WP Import'))

@section('content')

    <header class="mb-6">
        <h1>WP Import</h1>
    </header>

    <div class="card p-4 content">
        <form action="{{ cp_route('wp-import.upload') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            <h2 class="font-bold">Upload</h2>
            <p class="text-grey text-sm my-1">Upload the JSON file you have exported with <a href="https://github.com/jezzdk/wordpress-to-statamic-exporter" target="_blank">this WordPress plugin</a>.</p>
            <div class="flex space-x-3 items-center mt-3">
                <input type="file" class="border rounded px-3 py-2 text-sm w-full" name="file" />
                <div>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </div>
        </form>
    </div>

    @include('statamic::partials.docs-callout', [
        'topic' => __('this addon'),
        'url' => 'https://statamic.com/addons/rad-pack/wp-import'
    ])

@endsection
