<?php

namespace RadPack\StatamicWpImport\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use RadPack\StatamicWpImport\Helpers\WpImporter;

class ImportController
{
    public function index()
    {
        return view('wp-import::upload');
    }

    public function upload(Request $request)
    {
        $stream = fopen($request->file('file'), 'r+');
        $contents = stream_get_contents($stream);
        fclose($stream);

        try {
            $prepared = $this->importer()->prepare($contents);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }

        Cache::put('wp-import.statamic.prepared', $prepared);

        return redirect()->to(cp_route('wp-import.summary'));
    }

    public function summary()
    {
        if (!$data = Cache::get('wp-import.statamic.prepared')) {
            return redirect()->to(cp_route('wp-import.index'));
        }

        return view('wp-import::summary', [
            'summary' => $this->importer()->summary($data),
        ]);
    }

    public function import(Request $request)
    {
        set_time_limit(0);

        $prepared = Cache::get('wp-import.statamic.prepared');

        $summary = $request->input('summary');

        $this->importer()->import($prepared, $summary);

        return ['success' => true];
    }

    private function importer()
    {
        return new WpImporter;
    }
}
