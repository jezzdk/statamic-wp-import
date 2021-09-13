@extends('statamic::layout')

@section('title', __('WP Import'))

@section('content')

    <header class="mb-3"><h1>WP Import</h1></header>

    <div class="card p-2 content">
        <form action="{{ cp_route('wp-import.upload') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            <h2 class="font-bold">JSON file</h2>
            <p class="text-grey text-sm my-1">Upload the JSON file you have exported with the official <a href="https://github.com/statamic/wordpress-to-statamic-exporter" target="_blank">WordPress plugin</a>.</p>
            <div class="flex justify-between items-center">
                <div class="pr-4">
                    <input type="file" class="form-control" name="file" />
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </div>
        </form>
    </div>

@endsection