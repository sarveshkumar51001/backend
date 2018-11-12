@extends('admin.app')
@if(\Auth::user()->role == \App\User::ROLE_PAYMENT_ACCEPTOR)
    @section('content')
        <h1> Limited Permission </h1>
        <br>
        <h3>
            <ul>
                <li>User can only view student info by searching on Student Number</li>
                <li>User can pay/view student bill by searching on Bill Number</li>

            </ul>
        </h3>
    @endsection

@else
    @section('content')
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <a data-toggle="collapse" href="#dashboard" aria-expanded="true" aria-controls="collapseOne">
                            <i class="fa fa-dashboard"></i>&nbsp;Dashboard
                        </a>
                    </div>
                    <div class="card-body p-0 collapse" id="dashboard" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="tab-content">
                            <div class="tab-pane active" id="tasks">
                                <table class="table table-hover table-align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="text-center">Programs</th>
                                        <th class="text-center">Payment Plans</th>
                                        <th class="text-center">Scholarships</th>
                                        <th class="text-center">Fee Categories</th>
                                        <th class="text-center">Payment status</th>
                                        <th class="text-center">Total Fine</th>
                                        <th class="text-center">Total Scholarship</th>
                                        <th class="text-center">Total Amount Due</th>
                                        <th class="text-center">Total Amount Received </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($organizations as $key => $organization)
                                        @php if(session('c_org') > 0 && $organization['id'] != session('c_org') ) { continue; }  @endphp
                                    <tr>
                                        <td>{{ $organization['name'] }}</td>
                                        <td class="text-center">{{ $metrics[$organization['id']]->p_count ?? 0 }}</td>
                                        <td class="text-center">{{ $metrics[$organization['id']]->pp_count ?? 0 }}</td>
                                        <td class="text-center">{{ $metrics[$organization['id']]->s_count ?? 0 }}</td>
                                        <td class="text-center">{{ $metrics[$organization['id']]->fc_count ?? 0 }}</td>
                                        <td>
                                            <div>Paid
                                                <span class="font-weight-bold float-right">({{ $metrics[$organization['id']]->bills_stat->total_paid_percentage ?? 0 }}% - {{ $metrics[$organization['id']]->bills_stat->total_bills_count ?? 0 }} Bills)</span>
                                            </div>
                                            <div class="progress progress-sm mt-2 mb-3">
                                                <div class="progress-bar bg-success" style="width: {{ $metrics[$organization['id']]->bills_stat->total_paid_percentage ?? 0  }}%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </td>
                                        <td class="text-center"><i class="fa fa-rupee"></i> {{ $metrics[$organization['id']]->bills_stat->total_fine_amount ?? 0 }}</td>
                                        <td class="text-center"><i class="fa fa-rupee"></i> {{ $metrics[$organization['id']]->bills_stat->total_scholarship_amount ?? 0 }}</td>
                                        <td class="text-center"><i class="fa fa-rupee"></i> {{ $metrics[$organization['id']]->bills_stat->total_due_amount ?? 0 }}</td>
                                        <td class="text-center"><i class="fa fa-rupee"></i> {{ $metrics[$organization['id']]->bills_stat->total_paid_amount ?? 0 }}</td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-5">
                                <h4 class="card-title mb-0">Payment vs Settlement</h4>
                                <div class="small text-muted">{{ date('F Y') }} </div>
                            </div>
                        </div>
                        <div class="chart-wrapper" style="height:500px;margin-top:40px;">
                            <canvas id="payment-vs-settled-chart" class="chart" height="300"></canvas>
                        </div>
                    </div>
                    <div class="card-footer">
                        <ul>
                            <li class="hidden-sm-down">
                                <div class="text-muted">Payment</div>
                                <strong><i class="fa fa-rupee"></i> {{ array_sum($payment_vs_settled[0]) }} </strong>
                            </li>
                            @php $percentSettle = !empty(array_sum($payment_vs_settled[1]) && !empty(array_sum($payment_vs_settled[0]))) ? (array_sum($payment_vs_settled[1]) / array_sum($payment_vs_settled[0]) ) * 100 : 0 @endphp
                            <li>
                                <div class="text-muted">Settled ({{round($percentSettle, 2)}}%)</div>
                                <strong><i class="fa fa-rupee"></i> {{ array_sum($payment_vs_settled[1]) }} </strong>
                                <div class="progress progress-xs mt-2">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{$percentSettle}}%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-money"></i>Payment received via modes
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper">
                            <canvas id="pay-via-modes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-money"></i>Settled vs Unsettled
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper">
                            <canvas id="settled-metrics"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-money"></i>Head wise amount
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper">
                            <canvas id="head-wise-metrics"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endsection

    @section('footer-js')
        <script language="Javascript" type="text/javascript">
            var pieData = {
                labels: {!! json_encode(array_keys($mode_metrics)) !!},
                datasets: [{
                    data: {{ json_encode(array_values($mode_metrics)) }},
                    backgroundColor: ['#4BC0C0', '#FFCE56', '#E7E9ED', '#36A2EB']
                }]
            };
            var ctx = document.getElementById('pay-via-modes');
            new Chart(ctx, {
                type: 'pie',
                data: pieData,
                options: {
                    responsive: true,
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return get_chart_percentages(tooltipItem, data);
                            }
                        }
                    }
                }
            });

            var pieDataSettled = {
                labels: {!! json_encode(array_keys($settled_metrics)) !!},
                datasets: [{
                    data: {{ json_encode(array_values($settled_metrics)) }},
                    backgroundColor: ['#4BC0C0', '#FFCE56']
                }]
            };
            var ctxSettled = document.getElementById('settled-metrics');
            new Chart(ctxSettled, {
                type: 'pie',
                data: pieDataSettled,
                options: {
                    responsive: true,
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return get_chart_percentages(tooltipItem, data);
                            }
                        }
                    }
                }
            });

            var pieDataHeadWise = {
                labels: {!! json_encode(array_keys($head_wise_metrics)) !!},
                datasets: [{
                    data: {{ json_encode(array_values($head_wise_metrics)) }},
                    backgroundColor: {!! json_encode(get_random_color_codes(count($head_wise_metrics))) !!}
                }]
            };
            var ctxHeadWise = document.getElementById('head-wise-metrics');
            new Chart(ctxHeadWise, {
                type: 'pie',
                data: pieDataHeadWise,
                options: {
                    responsive: true,
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return get_chart_percentages(tooltipItem, data);
                            }
                        }
                    }
                }
            });

            // Main Chart
            var data1 = {!! json_encode(array_values($payment_vs_settled[0])) !!};
            var data2 = {!! json_encode(array_values($payment_vs_settled[1])) !!};

            var data = {
                labels: {!! json_encode(range(1, date('t'))) !!},
                datasets: [
                    {
                        label: 'Payment Received',
                        backgroundColor: convertHex($.brandInfo,10),
                        borderColor: '#FFA07A',
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 2,
                        data: data1
                    },
                    {
                        label: 'Payment Settled',
                        backgroundColor: convertHex($.brandInfo,80),
                        borderColor: $.brandSuccess,
                        pointHoverBackgroundColor: '#006400',
                        borderWidth: 2,
                        data: data2,
                    }
                ]
            };

            var options = {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            drawOnChartArea: false,
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            maxTicksLimit: 5,
                            stepSize: Math.ceil(5000000 / 10),
                            max: 5000000
                        }
                    }]
                },
                elements: {
                    point: {
                        radius: 0,
                        hitRadius: 10,
                        hoverRadius: 4,
                        hoverBorderWidth: 3,
                    }
                },
            };
            var ctx = $('#payment-vs-settled-chart');
            var mainChart = new Chart(ctx, {
                type: 'line',
                data: data,
                options: options
            });

            //convert Hex to RGBA
            function convertHex(hex,opacity){
                hex = hex.replace('#','');
                var r = parseInt(hex.substring(0,2), 16);
                var g = parseInt(hex.substring(2,4), 16);
                var b = parseInt(hex.substring(4,6), 16);

                var result = 'rgba('+r+','+g+','+b+','+opacity/100+')';
                return result;
            }

        </script>
    @endsection
@endif