<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportMetricResource;
use App\Models\ReportMetric;
use App\Models\Table;
use App\Models\TableColumn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportMetricController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->type == 'all') {
            $reportMetrics = ReportMetric::orderByDesc('created_at')
                ->get();
        } else {
            $reportMetrics = ReportMetric::orderByDesc('created_at')->paginate(20);
        }

        return ReportMetricResource::collection($reportMetrics);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $reportMetric = new ReportMetric;
        $reportMetric->code = $request->code;
        $reportMetric->name = $request->name;
        $reportMetric->math_type = $request->math_type;
        $reportMetric->formula = $request->formula;
        $reportMetric->table_id = $request->table_id;
        if($request->table_column_id) {
            $reportMetric->table_column_id = $request->table_column_id;
        }

        if($reportMetric->save()) {
            return response()->json([
                'status' => 'success',
                'model' => new ReportMetricResource($reportMetric),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra, vui lòng thử lại.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $reportMetric = ReportMetric::find($id);

        if(!$reportMetric) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy chỉ tiêu báo cáo',
            ]);
        }

        $table = Table::find($reportMetric->table_id);
        $value = 0;

        switch ($reportMetric->math_type) {
            case 'count':
                $value = DB::table($table->table)->count();
                break;
            case 'sum':
                $tableColumn = TableColumn::find($reportMetric->table_column_id);
                $value = DB::table($table->table)->sum($tableColumn->column);
                break;
        }

        return response()->json([
            'metric' => $reportMetric,
            'value' => $value,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reportMetric = ReportMetric::find($id);

        if(!$reportMetric) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy chỉ tiêu báo cáo',
            ]);
        }

        if($reportMetric->delete()) {
            return response()->json([
                'status' => 'success',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra, vui lòng thử lại.',
        ]);
    }
}
