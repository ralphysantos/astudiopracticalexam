<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Timesheet;
use Carbon\Carbon;
class TimesheetController extends Controller
{
    
    public function get(Request $request){
        try {
            $timesheets = Timesheet::query();

            if($request->has('filters')){
                $filters = [];

                foreach($request->filters as $key => $value){
                    if(!str_contains($key, '_operator')){
                        
                        if(str_contains($key,'date')){
                            $value = Carbon::createFromFormat('m-d-Y',$value)->format('Y-m-d');
                        }

                        $computedValue = isset( $request->filters[$key.'_operator']) ? $request->filters[$key.'_operator'] == 'like' ? '%'.$value.'%' : $value : $value;

                        array_push($filters,[
                            'name' => $key,
                            'value' => $computedValue,
                            'operator' => $request->filters[$key.'_operator'] ?? '='
                        ]);
                    }
                }
            }

            $timesheets->where(function($query) use ($filters){
                foreach ($filters as $filter) {
                    if(!str_contains($filter['name'], 'project') && !str_contains($filter['name'], 'user')){
                        $query->where($filter['name'], $filter['operator'], $filter['value']);    
                    }

                    if(str_contains($filter['name'], 'project')){
                        $query->whereHas('project', function($query) use ($filter){
                            $query->where('projects.name',$filter['operator'],$filter['value']);
                        });
                    }else if(str_contains($filter['name'], 'user_')){
                        $query->whereHas('user', function($query) use ($filter){
                            $query->where('users.first_name',$filter['operator'],$filter['value']);
                        });
                    }
                }
            });

            $timesheets = $timesheets->with('project', 'user')->get();

            return response()->json([
                'timesheets' => $timesheets
            ], 200);

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
            $timesheet = Timesheet::with('project', 'user')->find($id);

            if(!$timesheet){
                return response()->json([
                    'message' => 'Timesheet not found',
                ], 404);
            }

            return response()->json([
                'timesheet' => $timesheet
            ], 200);
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
                'project_id' => 'required',
                'task_name' => 'required|unique:timesheets',
                'date' => 'required|date',
                'hours' => 'required',
            ]);

            if(!auth()->user()->projects()->where('projects.id', $request->project_id)->exists()){
                return response()->json([
                    'message' => 'Project is not assigned to user' 
                ], 400);
            }


            $timesheet = auth()->user()->timesheets()->create([
                'project_id' => $request->project_id,
                'task_name' => $request->task_name,
                'date' => Carbon::createFromFormat('m-d-Y',$request->date)->format('Y-m-d'),
                'hours' => $request->hours
            ]);
            
            if(!$timesheet){
                return response()->json([
                    'message' => 'Failed to create timesheet',
                ], 400);
            }

            $timesheet->load('project','user');

            return response()->json([
                'message' => 'Timesheet created successfully',
                'timesheet' => $timesheet
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
                'project_id' => 'sometimes',
                'task_name' => 'sometimes',
                'date' => 'sometimes|date',
                'hours' => 'sometimes',
            ]); 

            $timesheet = Timesheet::find($id);

            if(!$timesheet){
                return response()->json([
                    'message' => 'Timesheet not found',
                ], 404);
            }

            if($request->has('project_id') && !auth()->user()->projects()->where('projects.id', $request->project_id)->exists()){
                return response()->json([
                    'message' => 'Project is not assigned to user' 
                ], 400);
            }

            if($request->has('date')){
                $request->merge([
                    'date' => Carbon::createFromFormat('m-d-Y',$request->date)->format('Y-m-d')
                ]);
            }

            $timesheet->update($request->all());

            $timesheet->load('project','user');
            return response()->json([
                'message' => 'Timesheet updated successfully',
                'timesheet' => $timesheet
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
            $timesheet = auth()->user()->timesheets()->find($id);

            if(!$timesheet){
                return response()->json([
                    'message' => 'Timesheet not found',
                ], 404);
            }

            $timesheet->delete();

            return response()->json([
                'message' => 'Timesheet deleted successfully',
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
