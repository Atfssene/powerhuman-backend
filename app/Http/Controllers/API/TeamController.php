<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;

class TeamController extends Controller
{

    public function create(CreateTeamRequest $request){
        
        try{
            
            //upload icon
            if($request->hasFile('icon')){
                $path=$request->file('icon')->store('public/icons');
            }
            
            //create team
            $team=Team::create([
                'name'=>$request->name,
                'icon'=>$path,
                'company_id'=>$request->company_id
            ]);

            if($team){
                throw new Exception('Team not created');
            }
            
            return ResponseFormatter::success($team,'Team Created');
        
        }catch(Exception $e){
            return ResponseFormatter::error($e->getMessage(),500);
        }

    }

    public function update(UpdateTeamRequest $request,$id){
        try{
            //get team
            $team=Team::find($id);

            //check if team exists
            if(!$team){
                throw new Exception('Team not Found');
            }

            //upload logo
            if($request->hasFile('icon')){
                $path=$request->file('icon')->store('public/icons');
            }

            //update team
            $team->update([
                'name'=>$request->name,
                'icon'=>isset($path) ?$path:$team->icon,
                'company_id'=>$request->company_id
            ]);

        }catch(Exception $e){
            return ResponseFormatter::error($e->getMessage(),500);
        }
    }

    public function fetch(Request $request){
        
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit',10);

        $teamQuery = team::query();
        
        if($id){

            $team=$teamQuery->find($id);
            
            if($team){
                return ResponseFormatter::success($team, 'Team Found');
            }
            return ResponseFormatter::error('Team not found', 404);
        }
        
        $teams = $teamQuery->where('company_id', $request->company_id);

        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Teams found'
        );
    
    }

    public function destroy($id){
        try{

            $team=Team::find($id);

            if(!$team){
                throw new Exception('Team not Found');
            }

            $team->delete();

            return ResponseFormatter::success('Team Deleted');
        } catch(Exception $e){
            return ResponseFormatter::error($e->getMessage(),500);
        }
    }

}
