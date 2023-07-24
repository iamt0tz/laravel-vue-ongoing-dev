<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    //
    public function index()
    {
        // $users = User::latest()->get();
        $users = User::latest()->get()->map(function ($user){
            return[
                'id' => $user->id,
                'name'=> $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at ->format('m-d-Y'),
                // 'created_at' => $user->created_at ->format(config('app.date_format')),
            ];
        });
        return $users;
    }

    public function store()
    { 
        request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:8',

        ]);


        return User:: create([
            'name'=> request('name'),
            'email'=> request('email'),
            'password'=> bcrypt(request('password')),
        ]);
 
    }

    public function update(User $user)
    { 
        request()->validate([
            
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $user->id,            
            'password' => 'sometimes|min:8',

        ]);
        $user-> update([
        'name'=> request('name'), 
        'email'=> request('email'),
        'password'=> request('password') ? bcrypt(request('password')) : $user->password,
    ]);
    return $user;
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->noContent();

    }
}
