@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-user"></i>Customers
        </div>
        <div class="card-body">
            <table class="table table-responsive-sm table-hover table-outline mb-0">
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
                        <td><div>{{ $user->student_id }}</div></td>
                        <td><div>{{ $user->student_name }}</div></td>
                        <td><div>{{ json_encode($user->academic_details) }}</div></td>
                        <td><div>{{ json_encode($user->contact_details) }}</div></td>
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