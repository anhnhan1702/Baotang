<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TableResource;
use App\Models\Table;
use App\Models\TableColumn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->table_ids) {
            $tableIds = explode(',', $request->table_ids);
        } else {
            $tableIds = TableColumn::where('type', 'location')
                ->orWhere('type', 'location_array')
                ->pluck('table_id')
                ->toArray();
        }

        $items = [];

        foreach($tableIds as $tableId) {
            $table = Table::find($tableId);
            $tableItems = DB::table($table->table)->get()->toArray();
            foreach($tableItems as $tableItem) {
                $tableColumns = TableColumn::where('type', 'location')->where('table_id', $table->id)->get();
                foreach($tableColumns as $tableColumn) {
                    $location = json_decode($tableItem->{$tableColumn->column});
                    $tableItem->{$tableColumn->column} = $location;
                    $tableItem->lat = $location->lat; 
                    $tableItem->lng = $location->lng; 
                }
                $items[] = $tableItem;
                
                $tableColumns = TableColumn::where('type', 'location_array')->where('table_id', $table->id)->get();
                foreach($tableColumns as $tableColumn) {
                    $locationArray = json_decode($tableItem->{$tableColumn->column});
                    $tableItem->{$tableColumn->column} = $locationArray;
                    $tableItem->location_array = $locationArray; 
                }

                $columns = [];
                $tableColumns = TableColumn::whereNotIn('type', ['location', 'location_array'])->where('table_id', $table->id)->get();
                foreach($tableColumns as $column) {
                    $columns[] = [
                        'id' => $column['id'],
                        'column' => $column['column'],
                        'name' => $column['name'],
                        'type' => $column['type'],
                    ];
                }
                $tableItem->columns = $columns;
                $items[] = $tableItem;
            }
        }
        
        return response()->json([
            'data' => $items,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function tables()
    {
        $tableIds = TableColumn::where('type', 'location')
            ->orWhere('type', 'location_array')
            ->pluck('table_id')->toArray();

        $tables = Table::whereIn('id', $tableIds)
            // ->with('columns:id,table_id,column,name,type')
            ->get();
        
        return TableResource::collection($tables);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }
}
