<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use function Laravel\Prompts\password;

class AuthController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function loginSubmit(Request $request)
    {
        // dd($request);
        $request->validate(
            // rules
            [
                'text_username' => 'required|email',
                'text_password' => 'required|min:6|max:16'
            ],
            // error messages
            [
                'text_username.required' => 'O username é obrigatório',
                'text_username.email' => 'Username deve ser um email valido',
                'text_password.required' => 'A password é obrigatória',
                'text_password.min' => 'A password deve ter pelo menos :min caracteres',
                'text_password.max' => 'A password deve ter no maximo :max caracteres'
            ]
        );
        // get user input
        $username = $request->input('text_username');
        $password = $request->input('text_password');

        // Check if user exists
        $user = User::where('username',$username)
                      ->where('deleted_at',null)
                      ->first();

        if(!$user) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('loginError','Username ou password incorretos');
        }

        // check if password is correct
        if(!password_verify($password,$user->password)) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('loginError','Username ou password incorretos');
        }

        // update last login
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        // login user
        session([
            'user' => [
                'id' => $user->id,
                'username' => $user->username
            ]
        ]);


        //redirect to home
        return redirect()->to('/');
    }

    public function logout()
    {
        // logout from the application
        session()->forget('user');
        return redirect()->to('/login');
    }

}
