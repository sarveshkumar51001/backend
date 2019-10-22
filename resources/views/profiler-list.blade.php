@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-user"></i>Profiling result
        </div>
        <div class="card-body">
            <table class="table table-responsive-sm table-hover table-outline mb-0">
                <thead class="thead-light">
                <tr>
                    <th>Name</th>
                    <th>Summary</th>
                    <th>Results</th>
                </tr>
                </thead>
                <tbody>
                @foreach($profiles as $profile)
                    @php
                        $profile_data = json_decode($profile['parent_profiler']['parent_profile']);
                        $profile_categories = ($profile['parent_profiler']['category_score']);
                    @endphp
                    <tr>
                        <td><div>
                                <b>Name: </b> {{ $profile_data->name }}
                                <br/><b>Email: </b> {{ $profile_data->email_id }}
                                <br/><b>Contact: </b> {{ $profile_data->contact }}
                                <br/><b>Address: </b> {{ $profile_data->address }}
                                <br/><b>Income: </b> {{ $profile_data->income_org  }}
                                <br/><b><a href="{{ url('customers/profiler/'.((array) $profile['_id'])['oid']) }}">View response</a></b>
                            </div></td>
                        <td><div>{{ $profile['parent_profiler']['text'] }}</div></td>
                        <td width="300">
                            <ul>

                                @foreach($profile['parent_profiler']['category_score'] as $category)
                                    <li>{{ $category['Category ' . ($loop->index +1)] }} - {{ round($category['Score'], 2) }}%</li>
                                    @if(!empty($category['Message']))<b>Desc: </b><code>{{ $category['Message'] }}</code>@endif
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="row pull-right mr-4">
{{--                {!! $profiles->render() !!}--}}
            </div>
        </div>
    </div>
@endsection