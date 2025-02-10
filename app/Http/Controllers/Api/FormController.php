<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FormResource;
use App\Models\Form;
use App\Models\Table;
use Illuminate\Http\Request;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->type == 'all') {
            $forms = Form::orderByDesc('created_at')
                ->get();
        } else {
            $forms = Form::orderByDesc('created_at')->paginate(100);
        }

        return FormResource::collection($forms);
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
        $tableJson = json_decode($request->table);
        $table = Table::find($tableJson->id);

        $fields = json_decode($request->fields);
        $fieldIds = [];
        if($fields) {
            foreach($fields as $field) {
                $fieldIds[] = $field->id;
            }
        }
       

        $form = new Form;
        $form->name = $request->name;
        $form->table_id = $table->id;
        $form->description = $request->description;
        $form->template = $request->template;
        $form->is_mapview_enabled = $request->is_mapview_enabled === 'true' ? true : false;
        if($fields) {
            $form->enabled_columns = $fieldIds;
        }

        if($form->save()) {
            return response()->json([
                'status' => 'success',
                'model' => new FormResource($form),
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
    public function show(string $id)
    {
        $form = Form::find($id);

        if(!$form) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy form',
            ]);
        }

        return new FormResource($form);
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
        $form = Form::find($id);

        if(!$form) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy form',
            ]);
        }

        if($form->description !== 'Biểu mặc định') {
            $form->name = $request->name;
            $form->description = $request->description;
            if($request->enabled_columns) {
                $form->enabled_columns = json_decode($request->enabled_columns);
            } else {
                $form->enabled_columns = null;
            }
        }
        $form->template = $request->template;

        if($form->save()) {
            return response()->json([
                'status' => 'success',
                'form' => new FormResource($form),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra, vui lòng thử lại.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $form = Form::find($id);

        if(!$form) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy form',
            ]);
        }

        if($form->delete()) {
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
