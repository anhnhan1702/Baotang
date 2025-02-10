<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TableResource;
use App\Http\Resources\TableSingleResource;
use App\Models\Form;
use App\Models\Table;
use App\Models\TableCategory;
use App\Models\TableColumn;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->type == 'all') {
            $tables = Table::orderByDesc('created_at')
                ->get();
        } else {
            $tables = Table::orderByDesc('created_at')->paginate(100);
        }
        return TableResource::collection($tables);
    }

    /**
     * Validator
     */
    public function validator(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:posts|max:255',
            'columns' => 'required',
        ]);

        return response()->json([
            'error' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO: xử lý giới hạn độ dài tên bảng & cột (64 ký tự)

        $tableName = $this->convertTableName($request->name);
        $tableColumns = json_decode($request->input('columns'));

        $findTable = Table::where('table', $tableName)->first();
        if($findTable) {
            return response()->json([
                'status' => 'duplicate',
                'message' => 'Table này đã có sẵn',
                'model' => new TableResource($findTable),
            ]);
        }
 
        // Tạo bảng mysql
        Schema::create($tableName, function (Blueprint $table) use ($tableColumns, $request) {
            $table->id();
            $table->comment($request->name);
            $table->string('name');
            $table->foreignIdFor(User::class)->constrained('users')->onDelete('cascade');
            foreach($tableColumns as $tableColumn) {
                switch ($tableColumn->type->id) {
                    case 'slug':
                        $table->string($this->convertColumnName($tableColumn->name))->unique();
                        break;
                    case 'string':
                        $table->string($this->convertColumnName($tableColumn->name))->nullable();
                        break;
                    case 'textarea':
                        $table->text($this->convertColumnName($tableColumn->name))->nullable();
                        break;
                    case 'text':
                        $table->text($this->convertColumnName($tableColumn->name))->nullable();
                        break;
                    case 'boolean':
                        $table->boolean($this->convertColumnName($tableColumn->name))->nullable();
                        break;
                    case 'integer':
                        // TODO: hoàn thiện
                        $table->string($this->convertColumnName($tableColumn->name))->nullable();
                        break;
                    case 'parent':
                        // Belongs to relationship
                        // TODO: hoàn thiện
                        $table->foreignId($this->convertColumnName($tableColumn->name))->nullable();
                        break;
                    case 'children':
                        // Has many relationship
                        // TODO: hoàn thiện
                        $table->json($this->convertColumnName($tableColumn->name))->nullable();
                        break;
                    case 'file':
                        $table->json($this->convertColumnName($tableColumn->name), 1000)->nullable();
                        break;
                    case 'url':
                        $table->string($this->convertColumnName($tableColumn->name), 1000)->nullable();
                        break;
                    case 'location':
                        $table->json($this->convertColumnName($tableColumn->name))->nullable();
                        break;
                    case 'location_array':
                        $table->json($this->convertColumnName($tableColumn->name))->nullable();
                        break;
                    case 'gender':
                        $table->unsignedTinyInteger($this->convertColumnName($tableColumn->name))->nullable();
                        break;
                    default:
                        $table->string($this->convertColumnName($tableColumn->name))->nullable();
                        break;
                }       
            }
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // Lưu dữ liệu bảng
        $newTable = Table::create([
            'name' => $request->name,
            'table' => $tableName,
        ]);

        // Danh muc
        if($request->categories) {
            $categories = json_decode($request->categories);
            $categoryIds = [];
            foreach($categories as $category) {
                $categoryIds[] = $category->id;
            }
            $newTable->categories()->sync($categoryIds);
        }

        $form = new Form;
        $form->name = $request->name;
        $form->table_id = $newTable->id;
        $form->description = 'Biểu mặc định';
        $form->save();

        // Lưu dữ liệu cột
        foreach($tableColumns as $tableColumn) {
            $relationshipTableId = null;
            if($tableColumn->type->id == 'parent' || $tableColumn->type->id == 'children') {
                $relationshipTableId = $tableColumn->relationship_table->id;
            }

            TableColumn::create([
                'table_id' => $newTable->id,
                'name' => $tableColumn->name,
                'description' => optional($tableColumn)->description,
                'column' => $this->convertColumnName($tableColumn->name),
                'type' => $tableColumn->type->id,
                'relationship_table_id' => $relationshipTableId,
                'is_showonindex' => $tableColumn->is_showonindex,
                'is_required' => $tableColumn->is_required,
                'is_searchable' => $tableColumn->is_searchable,
                'is_sortable' => $tableColumn->is_sortable,
                'is_unique' => $tableColumn->is_unique,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'model' => new TableResource($newTable),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $table = Table::where('id', $id)->with(['columns', 'categories'])->first();

        if(!$table) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy table',
            ]);
        }

        return new TableSingleResource($table);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $table = Table::where('id', $id)->with(['columns', 'categories'])->first();

        if(!$table) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy table',
            ]);
        }
        
        $table->name = $request->name;
        
        if($table->save()) {
            // Danh muc
            if($request->categories) {
                $categories = json_decode($request->categories);
                $categoryIds = [];
                foreach($categories as $category) {
                    $categoryIds[] = $category->id;
                }
                $table->categories()->sync($categoryIds);
            } else {
                $table->categories()->sync([]);
            }
            
            // Update column
            $tableColumns = json_decode($request->input('columns'));

            $newColumnIds = [];
            foreach($tableColumns as $tableColumn) {
                if(isset($tableColumn->id)) {
                    $newColumnIds[] = $tableColumn->id;
                    TableColumn::where('id', $tableColumn->id)->update([
                        'name' => $tableColumn->name,
                        'description' => $tableColumn->description,
                        'is_showonindex' => $tableColumn->is_showonindex,
                        'is_required' => $tableColumn->is_required,
                        'is_searchable' => $tableColumn->is_searchable,
                        'is_sortable' => $tableColumn->is_sortable,
                        'is_unique' => $tableColumn->is_unique,
                    ]);
                } else {
                    // Create new column
                    $relationshipTableId = null;
                    if($tableColumn->type->id == 'parent' || $tableColumn->type->id == 'children') {
                        $relationshipTableId = $tableColumn->relationship_table->id;
                    }

                    TableColumn::create([
                        'table_id' => $table->id,
                        'name' => $tableColumn->name,
                        'description' => optional($tableColumn)->description,
                        'column' => $this->convertColumnName($tableColumn->name),
                        'type' => $tableColumn->type->id,
                        'relationship_table_id' => $relationshipTableId,
                        'is_showonindex' => $tableColumn->is_showonindex,
                        'is_required' => $tableColumn->is_required,
                        'is_searchable' => $tableColumn->is_searchable,
                        'is_sortable' => $tableColumn->is_sortable,
                        'is_unique' => $tableColumn->is_unique,
                    ]);

                    Schema::table($table->table, function (Blueprint $table) use ($tableColumn) {
                        switch ($tableColumn->type->id) {
                            case 'slug':
                                $table->string($this->convertColumnName($tableColumn->name))->unique();
                                break;
                            case 'string':
                                $table->string($this->convertColumnName($tableColumn->name))->nullable();
                                break;
                            case 'textarea':
                                $table->text($this->convertColumnName($tableColumn->name))->nullable();
                                break;
                            case 'text':
                                $table->text($this->convertColumnName($tableColumn->name))->nullable();
                                break;
                            case 'boolean':
                                $table->boolean($this->convertColumnName($tableColumn->name))->nullable();
                                break;
                            case 'integer':
                                // TODO: hoàn thiện
                                $table->string($this->convertColumnName($tableColumn->name))->nullable();
                                break;
                            case 'parent':
                                // Belongs to relationship
                                // TODO: hoàn thiện
                                $table->foreignId($this->convertColumnName($tableColumn->name))->nullable();
                                break;
                            case 'children':
                                // Has many relationship
                                // TODO: hoàn thiện
                                $table->json($this->convertColumnName($tableColumn->name))->nullable();
                                break;
                            case 'file':
                                $table->string($this->convertColumnName($tableColumn->name), 1000)->nullable();
                                break;
                            case 'url':
                                $table->string($this->convertColumnName($tableColumn->name), 1000)->nullable();
                                break;
                            case 'location':
                                $table->json($this->convertColumnName($tableColumn->name))->nullable();
                                break;
                            case 'location_array':
                                $table->json($this->convertColumnName($tableColumn->name))->nullable();
                                break;
                            case 'gender':
                                $table->unsignedTinyInteger($this->convertColumnName($tableColumn->name))->nullable();
                                break;
                            default:    
                                $table->string($tableColumn->name)->nullable();
                                break;
                        } 
                    });
                }
            }

            // Delete columns
            foreach($table->columns as $column) {
                if(!in_array($column->id, $newColumnIds)) {
                    Schema::table($table->table, function (Blueprint $table) use ($column) {
                        $table->dropColumn($column->column);
                    });
                    $column->delete();
                }
            }

            return response()->json([
                'status' => 'success',
                'model' => new TableResource($table),
            ]);
        }
    }

    protected function filterDataTables($tables)
    {
        $filteredTables = [];
        foreach ($tables as $table) {
            if(substr($table->Tables_in_mvcms, 0, 3) === 'db_') {
                //$filteredTables[] = $table->Tables_in_mvcms;
                $filteredTables[] = $table;
            }
        }
        return $filteredTables;
    }

    protected function convertTableName($name)
    {
        $name = Str::plural($name);
        $name = Str::of($name)->slug('_');
        return 'db_'.$name;
    }

    protected function convertColumnName($name)
    {
        $name = Str::of($name)->slug('_');
        return $name;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $table = Table::find($id);

        if(!$table) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy bảng',
            ]);
        }

        $tableName = $table->table;

        if($table->delete()) {
            Schema::dropIfExists($tableName);
            
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
