<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function fetch(Request $request){
        
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit',10);

        $companyQuery = company::with(['users'])->whereHas('users', function($query) {
            $query->where('user_id',Auth::id());
        });
        
        if($id){

            $company=$companyQuery->find($id);
            
            if($company){
                return ResponseFormatter::success($company, 'Company Found');
            }
            return ResponseFormatter::error('Company not found', 404);
        }
        
        $companies=$companyQuery;
        
        if($name){
            $companies->where('name', 'like', '%'. $name . '%');
        }
        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies found'
        );
    
    }


    public function create(CreateCompanyRequest $request){
        
        try{
            
            //upload logo
            if($request->hasFile('logo')){
                $path=$request->file('logo')->store('public/logos');
            }
            
            //create company
            $company=company::create([
                'name'=>$request->name,
                'logo'=>$path,
                
            ]);

            if($company){
                throw new Exception('Company not created');
            }

            //attach company to user
            $user= User::find(Auth::id());
            $user->companies()->attach($company->id);

            //load user at company
            $company->load('users');
            
            return ResponseFormatter::success($company,'Company Created');
        
        }catch(Exception $e){
            return ResponseFormatter::error($e->getMessage(),500);
        }

    }

    public function update(UpdateCompanyRequest $request,$id){
        try{
            //get company
            $company=company::find($id);

            //check if company exists
            if(!$company){
                throw new Exception('Company not Found');
            }

            //upload logo
            if($request->hasFile('logo')){
                $path=$request->file('logo')->store('public/logos');
            }

            //update company
            $company->update([
                'name'=>$request->name,
                'logo'=>isset($path) ?$path:$company->logo
            ]);

        }catch(Exception $e){
            return ResponseFormatter::error($e->getMessage(),500);
        }
    }
}