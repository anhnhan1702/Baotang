<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ForceAppResource;
use App\Models\ForceApp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ForceAppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $forceApps = ForceApp::orderByDesc('created_at')->paginate(20);
        return ForceAppResource::collection($forceApps);
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
        $forceApp = new ForceApp;
        $forceApp->name = $request->name;
        $forceApp->description = $request->description;
        $forceApp->slug = Str::slug($request->name);
        $forceApp->icon = 'CircleStackIcon';

        $forceAppFind = ForceApp::where('slug', $forceApp->slug)->first();
        if($forceAppFind) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng chọn tên khác',
            ]);
        }

        if($forceApp->save()) {
            $forms = json_decode($request->forms);
            $formIds = [];
            foreach($forms as $form) {
                $formIds[] = $form->id;
            }
            $forceApp->forms()->sync($formIds);

            $reports = json_decode($request->reports);
            $reportIds = [];
            foreach($reports as $report) {
                $reportIds[] = $report->id;
            }
            $forceApp->reports()->sync($reportIds);

            return response()->json([
                'status' => 'success',
                'model' => new ForceAppResource($forceApp),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $forceApp = ForceApp::where('slug', $id)->orWhere('id', $id)->with(['forms', 'reports'])->first();

        if(!$forceApp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy app',
            ]);
        }

        return new ForceAppResource($forceApp);
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
        $forceApp = ForceApp::where('id', $id)->with(['forms'])->first();

        if(!$forceApp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy app',
            ]);
        }

        $forceApp->name = $request->name;
        $forceApp->description = $request->description;
        $forceApp->slug = Str::slug($request->name);
        $forceApp->icon = 'CircleStackIcon';

        $forceAppFind = ForceApp::where('id', '!=', $id)->where('slug', $forceApp->slug)->first();
        if($forceAppFind) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng chọn tên khác',
            ]);
        }

        if($forceApp->save()) {
            if($request->forms) {
                $forms = json_decode($request->forms);
                $formIds = [];
                foreach($forms as $form) {
                    $formIds[] = $form->id;
                }
                $forceApp->forms()->sync($formIds);
            } else {
                $forceApp->forms()->sync([]);
            }

            if($request->reports) {
                $reports = json_decode($request->reports);
                $reportIds = [];
                foreach($reports as $report) {
                    $reportIds[] = $report->id;
                }
                $forceApp->reports()->sync($reportIds);
            } else {
                $forceApp->reports()->sync([]);
            }

            return response()->json([
                'status' => 'success',
                'model' => new ForceAppResource($forceApp),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
