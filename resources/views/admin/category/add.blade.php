@extends('admin.main')

@section('head')
    <script src="/ckeditor/ckeditor.js"></script>
@endsection

@section('content')
    <div class="card card-primary">
    <!-- form start -->
        <form action="" method="post">
            <div class="card-body">

                <div class="form-group">
                    <label for="category">Tên danh mục</label>
                    <input type="text" name="name" class="form-control" placeholder="Nhập tên danh mục">
                </div>

                <div class="form-group">
                    <label for="category">Danh mục</label>
                    <select class="form-control" name="parent_id" id="">
                        <option value="0">Danh mục cha</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" class="form-control" id=""></textarea>
                </div>

                <div class="form-group">
                    <label>Mô tả chi tiết</label>
                    <textarea name="content" id="content" class="form-control" id=""></textarea>
                </div>

                <div class="form-group">
                    <label>Kích hoạt</label>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" value="1" type="radio" id="active" name="active" checked="">
                        <label for="active" class="custom-control-label">có</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" value="0" type="radio" id="no_active" name="active">
                        <label for="no_active" class="custom-control-label">không</label>
                    </div>
                </div>

            </div>
        <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" name="submit" class="btn btn-primary">Thêm danh mục</button>
            </div>
            @csrf
        </form>
    </div>
@endsection

@section('footer')
    <script>
        CKEDITOR.replace('content');
    </script>
@endsection