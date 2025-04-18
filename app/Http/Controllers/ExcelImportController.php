<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportService;
use App\Models\Row;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Requests\ImportRequest;

class ExcelImportController extends Controller
{
    public function showUploadForm()
    {
        return view('excel.upload');
    }

    public function upload(ImportRequest $request, ExcelImportService $service)
    {
        try {
            $service->import($request->file('file'));
            return $this->respondWithSuccess('Import started');
        } catch (\Throwable $e) {
            return $this->respondWithError('Error happened.');
        }
    }

    public function progress()
    {
        $processed = Redis::get('import_progress') ?: 0;

        return response()->json([
            'processed' => (int) $processed,
        ]);
    }

    public function showData(Request $request)
    {
        $rows = Row::orderBy('date')->paginate(100);

        $grouped = $rows->getCollection()
            ->groupBy(fn($r) => $r->date->format('d.m.Y'))
            ->map(fn($group) => $group->values());

        return response()->json([
            'current_page' => $rows->currentPage(),
            'last_page'    => $rows->lastPage(),
            'data'         => $grouped,
        ]);
    }


}
