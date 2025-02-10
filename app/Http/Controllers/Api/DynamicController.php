<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DynamicResource;
use App\Models\Table;
use App\Models\TableColumn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DynamicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $table = Table::find($request->table_id);
        $childUserIds = $user->childUserIds();

        $items = DB::table($table->table)
            ->orderByDesc('created_at');
            
        // Not Admin
        if($user->id != 1) {
            $items = $items->whereIn('user_id', $childUserIds);
        }
        
        // Filters
        if($request->date_range) {
            $dateRange = explode(',', $request->date_range);
            $startDate = Carbon::parse($dateRange[0])->startOfDay();
            $endDate = Carbon::parse($dateRange[1])->endOfDay();
            $items = $items->where('created_at', '>=', $startDate);
            $items = $items->where('created_at', '<=', $endDate);
        }

        if($request->user_id) {
            $items = $items->where('user_id', $request->user_id);
        }
        // /Filters
        
        $items = $items->paginate(15);
        foreach($items as $tableItem) {
            $tableColumns = TableColumn::where('type', 'location')->where('table_id', $table->id)->get();
            foreach($tableColumns as $tableColumn) {
                $tableItem->location = json_decode($tableItem->{$tableColumn->column}); 
                $tableItem->lat = $tableItem->location->lat; 
                $tableItem->lng = $tableItem->location->lng; 
            }

            $tableColumns = TableColumn::where('type', 'location_array')->where('table_id', $table->id)->get();
            foreach($tableColumns as $tableColumn) {
                $tableItem->location_array = json_decode($tableItem->{$tableColumn->column}); 
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
        }
        
        return $items;

        //return DynamicResource::collection($items);

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
        $table = Table::find($request->table_id);
        $values = json_decode($request->values, JSON_OBJECT_AS_ARRAY );

        $convertedValues = [];
        foreach($table->columns as $column) {
            switch ($column->type) {
                case 'boolean':
                    if(isset($values[$column->column])) {
                        $convertedValues[$column->column] = ($values[$column->column] == true) ? 1 : 0;
                    } else {
                        $convertedValues[$column->column] = 0;
                    }
                    break;
                case 'location':
                    if(isset($values[$column->column])) {
                        $convertedValues[$column->column] = json_encode($values[$column->column]);
                    }
                    break;
                case 'location_array':
                    if(isset($values[$column->column])) {
                        $convertedValues[$column->column] = json_encode($values[$column->column]);
                    }
                    break;
                case 'file':
                    // if($request->attachments) {
                    //     // $year = Carbon::now()->format('Y');
                    //     // $month = Carbon::now()->format('m');
                    //     // $path = "tickets_attachments/{$year}/{$month}/";
                    //     // $attachmentArray = [];
                    //     // foreach($request->attachments as $key => $attachment) {
                    //     //     $uploadedFile = $request->attachments[$key];
                    //     //     $filePath = $path.$this->generateRandomName($path, $uploadedFile->extension());
                    //     //     $file = Storage::disk(config('filesysystems.default'))->put( $filePath, $uploadedFile);
                    //     //     $attachmentArray[] = [
                    //     //         'path' => $file,
                    //     //         'file_name' => $uploadedFile->getClientOriginalName(),
                    //     //     ];
                    //     // }
                    //     // $ticket->attachments = $attachmentArray;
                    // }
                    // $convertedValues[$column->column] = ($values[$column->column] == true) ? 1 : 0;
                    break;
                case 'parent':
                    if(isset($values[$column->column]['id'])) {
                        $convertedValues[$column->column] = $values[$column->column]['id'];
                    }
                    break;
                case 'children':
                    if(isset($values[$column->column])) {
                        $convertedValues[$column->column] = json_encode($values[$column->column]);
                    }
                    break;
                default:
                    if(isset($values[$column->column])) {
                        $convertedValues[$column->column] = $values[$column->column];
                    }
                    break;
            }
        }

        if(isset($values['user']['id'])) {
            $convertedValues['user_id'] = $values['user']['id'];
        } else {
            $convertedValues['user_id'] = auth()->user()->id;
        }
        $convertedValues['name'] = $values['name'];
        $convertedValues['created_at'] = now()->toDateTimeString();
        $convertedValues['updated_at'] = $convertedValues['created_at'];

        DB::table($table->table)->insert($convertedValues);

        return response()->json([
            'status' => 'success',
        ]);
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
    public function destroy(Request $request, string $id)
    {
        $table = Table::where('id', $request->table_id)->first();
        $deleted = DB::table($table->table)->where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }
}
