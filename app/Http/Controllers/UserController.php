<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function store(Request $request)
    {

        $validated = $request->validate([
            'email'         => 'required|unique:users|email',
            'name'          => 'required',
            'surname'       => 'required',
            'password'      => 'required',
            'description'   => 'required',
        ]);


        User::create([
            'name'          =>  $request->name,
            'surname'       =>  $request->surname,
            'email'         =>  $request->email,
            'password'      =>  bcrypt($request->password),
            'description'   =>  $request->description,
        ]);

        return response()->json([
            'message' => 'User Created Successful',
            'success' => true,
        ]);
    }


    public function update(Request $request)
    {

        $validated = $request->validate([
            'name'          => 'required',
            'surname'       => 'required',
            'description'   => 'required',
        ]);

        if (!$validated) {
            return response()->json([
                'success'   =>  false,
                'message'   =>  $request->validate->fails()
            ]);
        }

        $update = User::where('email', $request->email)
            ->update([
                'name'          =>      $request->name,
                'surname'       =>      $request->surname,
                'description'   =>      $request->description
            ]);

        $user = User::where('email', $request->email)->first();

        return response()->json([
            'message' => 'User Updated Successful',
            'success' =>  true,
            'data'    =>  $user,
        ]);
    }


    public function destroy(Request $request)
    {

        $delete = User::where('email', $request->email)->delete();

        return response()->json([
            'message' => 'User Deleted Successful',
            'success' =>  true,
        ]);
    }


    public function login(Request $request)
    {

        $rules = [
            'email'    => 'required',
            'password' => 'required',
        ];

        $input     = $request->only('email', 'password');
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()]);
        }

        if (!Auth::attempt($input)) {
            return response()->json(['success' => false, 'error' => "Invalid email or password!"]);
        }

        $device_token = Str::random(40);

        User::where('email', $request->email)
            ->update([
                'device_token'      =>      $device_token
            ]);

        $user = Auth::user();
        $user->save();

        return response()->json([
            'success'           => true,
            "email"             => $user->email,
            "name"              => $user->name,
            "description"       => $user->description,
            "surname"           => $user->surname,
            "device_token"      => $device_token
        ]);
    }

    public function getInfo(Request $request)
    {

        $user = User::where('device_token', $request->device_token)->first();
        
        if ($user) {
            return response()->json([
                "name"              => $user->name,
                "description"       => $user->description,
                "surname"           => $user->surname,
            ]);
        }
    }
}
