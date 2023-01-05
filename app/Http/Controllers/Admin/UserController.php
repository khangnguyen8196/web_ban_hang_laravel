<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function AllUser()
    {   
        $all = DB:: table('users')->get();
        return view('admin.users.allUser',compact('all'),
        [
            'title'=>'Danh sách người dùng'
        ]);  
    }
}
