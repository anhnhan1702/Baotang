<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Models\Table;
use App\Models\TableColumn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->type == 'all') {
            $reports = Report::orderByDesc('created_at')
                ->get();
        } else {
            $reports = Report::orderByDesc('created_at')->paginate(20);
        }

        return ReportResource::collection($reports);
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
        $report = new Report;
        $report->name = $request->name;

        if($report->save()) {
            $reportMetrics = json_decode($request->report_metrics);
            $reportMetricIds = [];
            foreach($reportMetrics as $reportMetric) {
                $reportMetricIds[] = $reportMetric->id;
            }
            $report->report_metrics()->sync($reportMetricIds);

            return response()->json([
                'status' => 'success',
                'model' => new ReportResource($report),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $report = Report::find($id);

        if(!$report) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy báo cáo',
            ]);
        }

        $metrics = [];
        foreach($report->report_metrics as $reportMetric) {
            $table = Table::find($reportMetric->table_id);
            $value = DB::table($table->table);

            // Filters
            if($request->date_range) {
                $dateRange = explode(',', $request->date_range);
                $startDate = Carbon::parse($dateRange[0])->startOfDay();
                $endDate = Carbon::parse($dateRange[1])->endOfDay();
                $value = $value->where('created_at', '>=', $startDate);
                $value = $value->where('created_at', '<=', $endDate);
            }

            if($request->user_id) {
                $value = $value->where('user_id', $request->user_id);
            }
            // /Filters

            switch ($reportMetric->math_type) {
                case 'count':
                    $value = $value->count();
                    break;
                case 'sum':
                    $tableColumn = TableColumn::find($reportMetric->table_column_id);
                    $value = $value->sum($tableColumn->column);
                    break;
            }

            $metrics[] = [
                'id' => $reportMetric->id,
                'code' => $reportMetric->code,
                'name' => $reportMetric->name,
                'value' => $value,
            ];
        }

        return response()->json([
            'report' => new ReportResource($report),
            'metrics' => $metrics,
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
        $report = Report::find($id);

        if(!$report) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy báo cáo',
            ]);
        }

        if($report->delete()) {
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
