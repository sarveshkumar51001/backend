@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-list"></i>Profiler response
        </div>
        <div class="card-body">
            <table class="table table-responsive-sm table-hover table-outline mb-0">

                @php
                    $profiler_response_basic = json_decode($response['parent_profiler']['parent_profile'], true);
                    $profiler_response_children = json_decode($response['parent_profiler']['children_profile'], true);
                    $profiler_response_lifestyle = json_decode($response['parent_profiler']['lifestyle_profile'], true);
                @endphp

                <tbody>
                    @foreach(array_merge($profiler_response_basic, $profiler_response_children, $profiler_response_lifestyle) as $key => $value)
                        <tr>
                            <td>
                                @if(isset($questions[$key]->title))
                                    {{ $questions[$key]->title }}</td>
                                @else
                                    @php
                                        if ($key == 'address' || $key == 'relationship') continue;
                                        $newKey = last(explode('_', $key, 1));
                                        if (isset($questions[$newKey])) {
                                         dd($questions[$newKey]);
                                        } else {
                                            echo "Not FOUND";
                                        }

                                    @endphp

                                @endif
                            <td>
                                @if(isset($questions[$key]->type) && $questions[$key]->type == 'options')
                                    @php
                                        $ans = ((array) ($questions[$key]->options))[$value] ?? 'Invalid Data';
                                        if (is_scalar($ans))
                                            echo $ans;
                                        else
                                            echo "";
                                    @endphp
                                @elseif(isset($questions[$key]->type) && $questions[$key]->type == 'locale')
                                    @php
                                        echo ((array) ($questions[$key]->options_locale))[$value] ?? 'Invalid Data';
                                    @endphp
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
@endsection