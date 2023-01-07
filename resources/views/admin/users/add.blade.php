
@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <section clas="content">
        <div class="row">
            <div class="col-lg-1">

            </div>
            <div class="col-lg-10">
                <div class="card">
                     <div class="card-header">
                        <h5 class="card-title">
                            Add User
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{URL::to('/add-user')}}" method="POST">
                            <div class="form-group row">
                                <label for="name" class="col-sm-2 col-form-label">User Name</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" class="form-control" placeholder="Nhập tên người dùng">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="name" class="col-sm-2 col-form-label">Email</label>
                                <div class="col-sm-10">
                                    <input type="email" name="email" class="form-control" placeholder="Nhập email">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="name" class="col-sm-2 col-form-label">Password</label>
                                <div class="col-sm-10">
                                    <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="name" class="col-sm-2 col-form-label">User Role Type</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="role" id="role" required>
                                        <option value="Admin">Admin</option>
                                        <option value="Customer">Customer</option>
                                    </select>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button class="btn btn-primary" type="submit" >
                                    Add User
                                </button>
                            </div>
                            
                            @csrf
                        </form>
                    </div>   
                </div>
            </div>
            <div class="col-lg-1">

            </div>
        </div>
    </section>
</div>
@endsection