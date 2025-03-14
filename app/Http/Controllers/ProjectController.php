<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attribute;
use App\Models\Project;
class ProjectController extends Controller
{
    

    public function create(Request $request){
        
        try {
            $request->validate([
                'name' => 'required|unique:projects',
                'status'=> 'required'
            ]);
            $project = new Project();
            $project->name = $request->name;
            $project->status = $request->status;

            if(!$project->save()){
                return response()->json([
                    'message' => 'Failed to create project'
                ], 422);
            }

            if($request->has('attributes')){
                $syncData = [];
                foreach($request['attributes'] as $attribute){
                    $syncData[$attribute['id']] = [
                        'entity_id' => $project->id,
                        'value' => $attribute['value'],
                    ];
                }
                
                $project->attributes()->sync($syncData);
            }
            
            if($request->has('users')){
                $project->users()->sync($request->users);
            }
    

            return response()->json([
                'message' => 'Project created successfully',
                'project' => $project->load('attributes','users')
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
                'name' => 'sometimes|unique:projects',
                'status'=> 'sometimes'
            ]);
            $project = Project::find($id);

            if(!$project){
                return response()->json([
                    'message' => 'Project not found'
                ], 404);
            }
            
            $project->update($request->except('attributes','attributes_detach','users','users_detach'));

            if($request->has('attributes')){
                $syncData = [];
                foreach($request['attributes'] as $attribute){
                    $syncData[$attribute['id']] = [
                        'entity_id' => $project->id,
                        'value' => $attribute['value'],
                    ];
                }
                
                $project->attributes()->syncWithoutDetaching($syncData);
            }

            if($request->has('attributes_detach')){
                $project->attributes()->detach($request->attributes_detach);
            }

            if($request->has('users')){
                $project->users()->sync($request->users);
            }

            if($request->has('users_detach')){
                $project->users()->detach($request->users_detach);
            }
    
            return response()->json([
                'message' => 'Project updated successfully',
                'project' => $project->load('attributes','users')
            ], 201);
        
        } catch (\Throwable $th) {
            $status = $th->status ?? 500;
            $message = $status == 500 ? 'Something went wrong' : $th->getMessage();
            dd($th->getMessage());
            return response()->json([            
                'message' => $message,
            ], $status);
        }
    }

    public function get(Request $request){

        try {
            $project = Project::query();
            if($request->has('filters')){
                $customFilters = [];
                $basicFilters = [];
                foreach ($request->filters as $key => $value) {
                    $attr = Attribute::where('name', $key)->first();
    
                    $computedValue = isset( $request->filters[$key.'_operator']) ? $request->filters[$key.'_operator'] == 'like' ? '%'.$value.'%' : $value : $value;
                    if($attr){
                        array_push($customFilters,[
                            'name' => $key,
                            'value' => $computedValue,
                            'operator' => $request->filters[$key.'_operator'] ?? '='
                        ]);
                    }else{
                        if(!str_contains($key, '_operator')){
                            array_push($basicFilters,[
                                'name' => $key,
                                'value' => $computedValue,
                                'operator' => $request->filters[$key.'_operator'] ?? '='
                            ]);
                        }
                    }
                }
    
                $project->where(function($query) use ($customFilters){
                    foreach ($customFilters as $filter) {
                        $query->whereHas('attributes', function($query) use ($filter){
                            $query->where('attributes.name',$filter['operator'],$filter['name'])
                            ->where('attributes_values.value',$filter['operator'],$filter['value']);
                        });
                    }
                });
    
                $project->where(function($query) use ($basicFilters){
                    foreach ($basicFilters as $filter) {
                        $query->where($filter['name'], $filter['operator'], $filter['value']);
                    }
                });
            }
    
            $projects = $project->with('attributes')->get();
            
            return response()->json([
                'projects' => $projects
            ], 200);
        } catch (\Throwable $th) {
            $status = $th->status ?? 500;
            $message = $status == 500 ? 'Something went wrong' : $th->getMessage();

            return response()->json([            
                'message' => $message,
            ], $status);
        }
    }

    public function getById($id){
        try {
            $project = Project::with(['attributes','users'])->find($id);

            if(!$project){
                return response()->json([
                    'message' => 'Project not found'
                ], 404);
            }

            return response()->json([
                'project' => $project
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
            $project = Project::find($id);

            if(!$project){
                return response()->json([
                    'message' => 'Project not found'
                ], 404);
            }

            if($project->attributes->count() > 0){
                $project->attributes()->detach();
            }

            if(!$project->delete()){
                return response()->json([
                    'message' => 'Failed to delete project'
                ], 422);
            }

            return response()->json([
                'message' => 'Project deleted successfully'
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
