@extends('admin.layout.main_app')
@section('title', 'Stock Dispatch List')
@section('content')
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0">Stock Dispatch List</h1>
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">Stock Dispatch List</li>
                                </ol>
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.container-fluid -->
                </section>
                <!-- /.content-header -->
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            @if ($message = Session::get('success'))
                                <div class="alert alert-success">
                                    <p>{{ $message }}</p>
                                </div>
                            @endif
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h3 class="card-title">Stock Dispatch List</h3>
                                            </div>
                                            <div class="col-md-5">
                                            </div>
                                            <div class="col-md-1">
                                                <a href="{{ route('admin.stock.dispatch.add') }}" class="btn btn-block btn-primary"><i class="fas fa-plus"></i> Add</a>
                                            </div>
                                        </div>
                                    </div>
                                    <form action="{{ route('admin.stock.dispatch.serach') }}" method="get">
                                        <div class="row ml-3">
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label>Start Date:</label>
                                                    <div class="input-group">
                                                        <input type="date" name="startDate" value="{{$startDate}}" class="form-control float-right"  required/>
                                                    </div>
                                                    <!-- /.input group -->
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label>End Date:</label>
                                                    <div class="input-group">
                                                        <input type="date" name="endDate" value="{{$endDate}}" class="form-control float-right" required />
                                                    </div>
                                                    <!-- /.input group -->
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <button type="submit" class="btn btn-primary mt-4">Search</button>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <table id="customer_list" class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>S.no.</th>
                                                    <th>Date</th>
                                                    <th>From</th>
                                                    <th>Branch/Customer</th>
                                                    <th>To</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            
                                                 @php
                                                    $i = 0;
                                                @endphp
                                                @foreach ($stockDispatchList as $stock)
                                                @php
                                                    $branchfrom = DB::table('branch')->where('id', '=', $stock->from_id)->first();
                                                @endphp
                                                
                                                <tr>
                                                    <td>STKDISP-{{ $stock->id }}</td>
                                                    <td>{{ $stock->stock_date }}</td>
                                                    <td>{{ $branchfrom->name }}</td>
                                                    <td> 
                                                        @if($stock->type==1)
                                                            <p>Branch</p>
                                                        @else
                                                            <p>Customer</p>
                                                        @endif
                                                    </td>
                                                    <td> 
                                                        @if($stock->type =='1')
                                                             @php
                                                                $branchto = DB::table('branch')->where('id', '=', $stock->branch_to_id)->first();
                                                                $sendTo = $branchto->name;
                                                            @endphp
                                                        @else
                                                            @php
                                                                $coustomer = DB::table('users')->where('id', '=', $stock->vendor_to_id)->first();
                                                                $sendTo = $coustomer->name;
                                                            @endphp
                                                        @endif
                                                        {{$sendTo}}
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.stock.dispatch.edit',$stock->id) }}" class="btn btn-warning">Edit</a>
                                                        <a href="javascript:void(0)" id="delete-user" data-id="{{ $stock->id }}" data-url="{{ route('admin.stock.dispatch.delete',$stock->id) }}"  class="btn btn-danger delete">Delete</a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <div class="d-flex justify-content-center">
                                            {!! $stockDispatchList->links() !!}
                                        </div>
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
                <!-- /.content -->
            </div>
@endsection
