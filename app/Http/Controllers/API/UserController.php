<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function login(Request $request){
        
        try{
            //validate request
            $request->validate([
                'email'=>'required|email',
                'password'=>'required',
            ]);

            //find user by email
            $credentials = request(['email','password']);
            
            if(!Auth::attempt($credentials)){
                return ResponseFormatter::error('Unauthorized', 401);
            }

            $user=User::where('email', $request->email)->first();
            
            if(!Hash::check($request->password,$user->password)){
                throw new Exception('Invalid Password');
            }

            //generate token
            $tokenResult=$user->createToken('authToken')->plainTextToken;
            
            return ResponseFormatter::success([
                'access_token'=>$tokenResult,
                'token_type'=>'Bearer',
                'user'=>$user
            ], 'Login Success');

        } catch (Exception $e){
            return ResponseFormatter::error('Authentication Failed');
        }
    }

    public function register(Request $request){
        try{
            //validate request
            $request->validate([
                'name'=>['required','string','max:255'],
                'email'=>['required','string','email','max:255','unique:users'],
                'password'=>['required','string','confirmed',new Password],
            ]);

            //create user
            $user=User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password)
            ]);

            //generate token
            $tokenResult= $user->createToken('authToken')->plainTextToken;

            //return response
            return ResponseFormatter::success([
                'access_token'=>$tokenResult,
                'token_type'=>'Bearer',
                'user'=>$user
            ], 'Register Success');

        }catch (Exception $error) {
            return ResponseFormatter::error('Authentication Failed');
        }
    }

    public function logout(Request $request){
        //revoke token
        $token=$request->user()->currentAccessToken()->delete();

        //return response
        return ResponseFormatter::success('Logout Success');
    }

    public function fetch(Request $request){
        
        $user=$request->user();

        return ResponseFormatter::success($user,'Fetch Success');
    }
}
