<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attribute;
class AttributeController extends Controller
{
    public function get(Request $request){
        try {
            $attributes = Attribute::query();

            if($request->has('filters')){
                $filters = [];
                foreach($request->filters as $key => $value){
                    $computedValue = isset( $request->filters[$key.'_operator']) ? $request->filters[$key.'_operator'] == 'like' ? '%'.$value.'%' : $value : $value;
                    if(!str_contains($key, '_operator')){
                        array_push($filters,[
                            'name' => $key,
                            'value' => $computedValue,
                            'operator' => $request->filters[$key.'_operator'] ?? '='
                        ]);
                    }
                }
    
                $attributes->where(function($query) use ($filters){
                    foreach ($filters as $filter) {
                        $query->where($filter['name'], $filter['operator'], $filter['value']);
                    }
                });
            }

            $attributes = $attributes->get();
            return response()->json([
                'attributes' => $attributes
            ],200);
        }catch (\Throwable $th) {
            $status = $th->status ?? 500;
            $message = $status == 500 ? 'Something went wrong' : $th->getMessage();

            return response()->json([
                'message' => $message,
            ], $status);
        }
        
    }

    public function getById($id){
        try {
            $attribute = Attribute::find($id);

            if (!$attribute) {
                return response()->json([
                    'message' => 'Attribute not found',
                ], 404);
            }

            return response()->json([
                'attribute' => $attribute
            ],200);
        } catch (\Throwable $th) {
            $status = $th->status ?? 500;
            $message = $status == 500 ? 'Something went wrong' : $th->getMessage();

            return response()->json([
                'message' => $message,
            ], $status);
        }
    }

    public function create(Request $request){
        try {
            $request->validate([
                'name' => 'required|unique:attributes',
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
            $status = $th->status ?? 500;
            $message = $status == 500 ? 'Something went wrong' : $th->getMessage();

            return response()->json([
                'message' => $message,
            ], $status);
        }
    }

    public function update(Request $request, $id){
        
        try {
            $request->validate([
                'name' => 'required|unique:attributes',
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
            $status = $th->status ?? 500;
            $message = $status == 500 ? 'Something went wrong' : $th->getMessage();

            return response()->json([
                'message' => $message,
            ], $status);
        }
    }

    public function delete($id){
        try {
            $attribute = Attribute::find($id);

            if (!$attribute) {
                return response()->json([
                    'message' => 'Attribute not found',
                ], 404);
            }

            if ($attribute->values()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete attribute with values',
                ], 422);
            }

            $attribute->delete();
            return response()->json([
                'message' => 'Attribute deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            $status = $th->status ?? 500;
            $message = $status == 500 ? 'Something went wrong' : $th->getMessage();

            return response()->json([            
                'message' => $message,
            ], $status);
        }
    }
}
