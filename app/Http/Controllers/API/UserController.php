<?php
/**
 * Created by PhpStorm.
 * User: BangYa
 * Date: 10/2/2020
 * Time: 10:40 AM
 */

namespace App\Http\Controllers\API;
use App\Appointment;
use App\Brand;
use App\Document;
use App\Product;
use App\Slider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public $successStatus = 200;
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(){
        $username = request('username');
        $password = request('password');

        if(Auth::attempt(['username' => request('username'), 'password' => request('password')])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')-> accessToken;
            $user->api_token = $success['token'];
            $user->save();
            return response()->json(['success' => $success], $this-> successStatus);
        }
        else {
            return response()->json(['error' => 'error'], $this->successStatus);
        }
    }
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')-> accessToken;
        $user->profile_pic = 'https://www.kindpng.com/picc/m/22-223941_transparent-avatar-png-male-avatar-icon-transparent-png.png';
        $user->api_token = $success['token'];
        $user->save();
        return response()->json(['success'=>$success], $this-> successStatus);
    }
    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    public function slider()
    {
        $slider = Slider::all();
        return response()->json($slider, $this->successStatus);
    }

    public function getappointment()
    {
        $getappointment = Appointment::where('user_id', Auth::user()->id)->orderBy('id', 'desc')->get();
        return response()->json(['data' => $getappointment], $this->successStatus);
    }

    public function logout()
    {
        $user = Auth::user();
        $user->api_token = null;
        $user->save();
        return response()->json('Successfully logged out');
    }

    public function book(Request $request){
        $book = new Appointment();
        $book->date = date("Y-m-d", strtotime($request->date));
        $book->time = date("H:i", strtotime($request->time));
        $book->service = $request->service;
        $book->user_id = Auth::user()->id;
        $book->status = 'pending';
        $book->save();

        return response()->json($book, $this->successStatus);
    }
}
