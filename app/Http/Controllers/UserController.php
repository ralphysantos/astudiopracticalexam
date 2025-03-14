<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

class UserController extends Controller
{

    public function get(Request $request){
        try {
            $users = User::query();

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

                $users->where(function($query) use ($filters){
                    foreach ($filters as $filter) {
                        if(!str_contains($filter['name'], 'project')){
                            $query->where($filter['name'], $filter['operator'], $filter['value']);
                        }
                        if(str_contains($filter['name'], 'project')){
                            $query->whereHas('projects', function($query) use ($filter){
                                $query->where('projects.name',$filter['operator'],$filter['value']);
                            });
                        }
                    }

                });

            }


            $users = $users->with('projects')->get();
            return response()->json([
                'users' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to get users',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function create(Request $request){
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            if($user && $request->has('projects')){
                $user->projects()->sync($request->projects);
                $user->load('projects');
            }

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getById($id){
        try {
            $user = User::with(['projects'])->find($id);

            if(!$user){
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
            
            return response()->json([
                'user' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to get user',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id){
        try {
            $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:users|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/',
                'password' => 'sometimes|string|min:8|confirmed',
            ]);

            $user = User::find($id);           

            if(!$user){
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            $user->update($request->all());

            if($user && $request->has('projects')){
                $user->projects()->sync($request->projects);
                $user->load('projects');
            }

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function delete($id){
        try {
            $user = User::find($id);
            
            if(!$user){
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }            

            if($user->projects()->count() > 0){
                $user->projects()->detach();
            }

            if(!$user->delete()){             
                return response()->json([
                    'message' => 'Failed to delete user'
                ], 500);
            }

            return response()->json([
                'message' => 'User deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to delete user',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
