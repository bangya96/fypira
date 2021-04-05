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
use Illuminate\Support\Facades\App;
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
        if (Auth::user()->roles == 'user'){
            $getappointment = Appointment::leftJoin('users', 'appointments.user_id', 'users.id')
                ->where('appointments.user_id', Auth::user()->id)
                ->select('appointments.id as id', 'date', 'time', 'service', 'status', 'name', 'tel_mobile', 'completed')
                ->orderBy('id', 'desc')->get();
            return response()->json(['data' => $getappointment], $this->successStatus);
        } elseif (Auth::user()->roles == 'admin'){
            $getappointment = Appointment::leftJoin('users', 'appointments.user_id', 'users.id')
                ->select('appointments.id as id', 'date', 'time', 'service', 'status', 'name', 'tel_mobile', 'completed')
                ->orderBy('appointments.id', 'desc')->get();
            return response()->json(['data' => $getappointment], $this->successStatus);
        }
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

    public function changeStatus(Request $request){
        $data = Appointment::find($request->id);
        if ($request->status == 'completed'){
            $data->completed = $request->status;
        } else {
            $data->status = $request->status;
        }
        $data->save();
    }

    public function idUser(Request $request){
        $users = User::find($request->id);

        return response()->json(['data' => $users], $this->successStatus);
    }

    public function saveUser(Request $request){
log::alert($request);
        $user = User::find($request->id);
        $user->name = $request->form['name'];
        $user->email = $request->form['email'];
        $user->tel_mobile = $request->form['tel_mobile'];
        $user->address1 = $request->form['address1'];
        $user->address2 = $request->form['address2'];
        $user->poscode = $request->form['poscode'];
        $user->city = $request->form['city'];
        $user->state = $request->form['state'];
        $user->gender = $request->form['gender'];
        $user->roles = $request->form['roles'] ?? $user->roles;
        if ($request->form['password'] != null){
            $user->password = bcrypt($request->form['password']);
        }
        $user->save();

        return response()->json(['data' => $user], $this->successStatus);
    }

    public function allUser(){
        $users = User::all();

        return response()->json(['data' => $users], $this->successStatus);
    }
}
