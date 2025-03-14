<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attribute;
class AttributeController extends Controller
{
    public function create(Request $request){
        try {
            $request->validate([
                'name' => 'required',
                'type' => 'required',
            ]);
    
            $attribute = new Attribute();
            $attribute->name = $request->name;
            $attribute->type = $request->type;
            $attribute->save();
    
            return response()->json([
                'message' => 'Attribute created successfully',
                'data' => $attribute
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to create attribute',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id){
        
        try {
            $request->validate([
                'name' => 'required',
                'type' => 'required',
            ]);
    
            $attribute = Attribute::find($id);
            $attribute->name = $request->name;
            $attribute->type = $request->type;
            $attribute->save();
    
            return response()->json([
                'message' => 'Attribute updated successfully',
                'data' => $attribute
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to update attribute',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
