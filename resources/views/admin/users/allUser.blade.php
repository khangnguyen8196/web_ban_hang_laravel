@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <section class="content">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-12">
                          <div class="card">
                            <div class="card-header">
                              <h3 class="card-title">All User</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                              <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                  <th>STT</th>
                                  <th>Name</th>
                                  <th>Email(s)</th>
                                  <th>Role</th>
                                  <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($all as $key => $row)
                                    <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{$row->name}} </td>
                                    <td>{{$row->email}}</td>
                                    <td>{{$row->role}}</td>
                                    <td>
                                        <a href="{{URL::to('editUser'.$row->id)}}">Edit</a>
                                        <a href="{{URL::to('deleteUser'.$row->id)}}">Delete</a>
                                    </td>
                                    </tr>
                                @endforeach
                                </tbody>
                              </table>
                            </div>
                            <!-- /.card-body -->
                          </div>
                          <!-- /.card -->
                        </div>
                        <!-- /.col -->
                      </div>
                      <!-- /.row -->
                    </div>
                    <!-- /.container-fluid -->
                  </section>
            </div>
    
        </div>

    </section>
</div>
@endsection