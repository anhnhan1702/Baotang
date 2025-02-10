<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiaBanResource;
use App\Models\DiaBan;
use Illuminate\Http\Request;

class DiaBanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->type == 'all') {
            $diaBans = DiaBan::orderBy('created_at')
                ->get();
        } else {
            $diaBans = DiaBan::orderBy('created_at')->paginate(30);
        }

        return DiaBanResource::collection($diaBans);
    }

    /**
     * Display a tree of the resource.
     */
    public function tree(Request $request)
    {
        $diaBans = DiaBan::orderBy('created_at')
            ->whereNull('parent_id')
            ->with(['children'])
            ->get();
        return $diaBans;
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
