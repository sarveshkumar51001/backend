@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-user"></i>Customers
        </div>
        <div class="card-body">
            <table class="table table-responsive-sm table-hover table-outline mb-0 datatable">
                <thead class="thead-light">
                <tr>
                    <th>student_id</th>
                    <th>student_name</th>
                    <th>academic_details</th>
                    <th>contact_details</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td><a href="{{ url('customers/'.$user->id) }}">{{ $user->student_id }}</a></td>
                        <td><a href="{{ url('customers/'.$user->id) }}">{{ $user->student_name }}</a></td>
                        <td>{{ json_encode($user->academic_details) }}</td>
                        <td>{{ json_encode($user->contact_details) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="row pull-right mr-4">
                {!! $users->render() !!}
            </div>
        </div>
    </div>
@endsection