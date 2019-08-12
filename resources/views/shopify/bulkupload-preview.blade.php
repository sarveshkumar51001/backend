@extends('admin.app')
@section('content')
    <div class ="body">
        <div class = "card">
            @if(!empty($errored_data))
               <div class="alert-danger p-2">
               <p style = "font-weight:bold">Err !!! There are few errors. Please correct before submitting again.</p>
               @if(!empty($errored_data['sheet']))
               		<h6>Sheet Errors</h6>
                    <ul>
                    @foreach($errored_data['sheet'] as $sheet_errors)
                    	<li>{{ $sheet_errors }}</li>
                    @endforeach
                    </ul>
                @endif

                @if(!empty($errored_data['rows']))
                    <div class="pull-right">
                    	<a href="#" onclick="$('.collapse').collapse('hide');" class="mr-2">Collapse All <i class="fa fa-angle-double-up" aria-hidden="true"></i></a>
                    	<a href="#" onclick="$('.collapse').collapse('show');">Expand All <i class="fa fa-angle-double-down" aria-hidden="true"></i></a>
                    </div>
                	<h6>Rows Error</h6>
                	<ul>
                	@foreach($errored_data['rows'] as $row_no => $errors)
                		@if(count($errors) > 1)
                			<li><a href="#row-{{ $row_no }}" data-toggle="collapse"><b>Row {{ $row_no }} ({{ count($errors)}} Errors) <i class="fa fa-angle-double-down" aria-hidden="true"></i></b></a>
                				<div id="row-{{ $row_no }}" class="collapse">
                    				<ul>
                    				@foreach($errors as $error)
                                        @php $error_slug = str_replace('+','-',urlencode('bkmrk-' . substr(strtolower(preg_replace('/s+/', '-', trim($error))), 0, 20))) @endphp
                    					<li>{{ $error }} <a target="_blank" href="https://wiki.valedra.com/link/33#{{$error_slug}}"> Help <i class="fa fa-external-link"></i></a></li>
                    				@endforeach
                    				</ul>
								</div>    				
                			</li>
                		@else
                			<li><b>Row {{ $row_no }}</b> - {{ $errors[0] }}
            			@endif
                	@endforeach
                	</ul>
            	@endif
            	</div>
            @else
                <div class="alert alert-success">Thank You! Your file was successfully uploaded. Your orders will be processed in few hours.
                </div>
            @endif
			
			@if(!array_key_exists('incorrect_headers', $errored_data))
                <table class="table table-striped table-bordered table-responsive table-fixed">
                    <thead>
                        <tr>
                            @foreach(\App\Library\Shopify\Excel::$headerMap as $header)
                                <th><strong>{{ $header }}</strong></th>
                            @endforeach
                        </tr>
                        </thead>
    
                        @foreach($excel_response as $index => $row)
                            <tr @if(!empty($errored_data['rows'][$index+1])) style="background: yellow;" @endif>
                                @foreach(\App\Library\Shopify\Excel::$headerMap as $key => $header)
                                    @if(isset($row[$key]))
                                        @if(is_array($row[$key]))
                                            <td>
                                                <table>
                                                    <thead>
                                                    <td>No.</td>
                                                    @php $head = reset($row[$key]) @endphp
                                                    @if(!is_bool($head))
                                                    @foreach(array_keys($head) as $instKey)
                                                        @if(isset(\App\Library\Shopify\Excel::$headerMap[$instKey]))
                                                            <td>{{ $instKey }}</td>
                                                        @endif
                                                    @endforeach
                                                    @endif
                                                    </thead>
    
                                                    @foreach($row[$key] as $index => $installment)
                                                        <tr>
                                                            <td>{{$index}}</td>
                                                            @foreach($installment as $key => $value)
                                                                @if(isset(\App\Library\Shopify\Excel::$headerMap[$key]))
                                                                    <td>{{ $value }}</td>
                                                                @endif
                                                            @endforeach
                                                        </tr>
    
                                                    @endforeach
                                                </table>
                                            </td>
                                        @else
                                            <td class="@if(!empty($errored_data['rows'][$index+1][$key])) alert-danger @endif ">{{ $row[$key] }}</td>
                                        @endif
                                    @else
                                        <td class="@if(!empty($errored_data['rows'][$index+1][$key])) alert-danger @endif "></td>
                                    @endif
                                @endforeach
                            </tr>                  
                        @endforeach
                    </tbody>
                </table>
            @else
            	<div class="alert-danger p-2" style="text-align: center;">
            		<h3>{{ $errored_data['incorrect_headers'] }}</h3>
            	</div>
            @endif
            <div class="pull-left mt-2">
                <a href="{{ route('bulkupload.upload') }}">
                    <button class="btn btn-lg btn-success">Go Back</button>
                </a>
            </div>
        </div>
    </div>
@endsection
