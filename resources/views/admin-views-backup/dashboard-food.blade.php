@extends('layouts.admin.app')

@section('title',\App\Models\BusinessSetting::where(['key'=>'business_name'])->first()?->value ?? 'Not Set'??translate('messages.dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        @if(auth('admin')->user()->role_id == 1)
        @php($mod = \App\Models\Module::find(Config::get('module.current_module_id')))
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center py-2">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <img class="onerror-image" data-onerror-image="{{asset('assets/admin/img/grocery.svg')}}" src="{{$mod->icon_full_url }}"
                        width="38" alt="img">
                        <div class="w-0 flex-grow pl-2">
                            <h1 class="page-header-title mb-0">{{translate($mod->module_name)}} {{translate('messages.Dashboard')}}.</h1>
                            <p class="page-header-text m-0">{{translate('Hello, Here You Can Manage Your')}} {{translate($mod->module_name)}} {{translate('orders by Zone.')}}</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-auto min--280">
                    <select name="zone_id" class="form-control js-select2-custom fetch_data_zone_wise">
                        <option value="all">{{ translate('messages.All_Zones') }}</option>
                        @foreach(\App\Models\Zone::orderBy('name')->get() as $zone)
                            <option
                                value="{{$zone['id']}}" {{$params['zone_id'] == $zone['id']?'selected':''}}>
                                {{$zone['name']}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <!-- Stats -->
        <div class="card mb-3">
            <div class="card-body pt-0">
                <div class="d-flex flex-wrap align-items-center justify-content-end">
                    <div class="status-filter-wrap">
                        <div class="statistics-btn-grp">
                            <label>
                                <input type="radio" name="statistics" value="this_year" {{$params['statistics_type'] == 'this_year'?'checked':''}} class="order_stats_update" hidden>
                                <span>{{ translate('This_Year') }}</span>
                            </label>
                            <label>
                                <input type="radio" name="statistics" value="this_month" {{$params['statistics_type'] == 'this_month'?'checked':''}} class="order_stats_update" hidden>
                                <span>{{ translate('This_Month') }}</span>
                            </label>
                            <label>
                                <input type="radio" name="statistics" value="this_week" {{$params['statistics_type'] == 'this_week'?'checked':''}} class="order_stats_update" hidden>
                                <span>{{ translate('This_Week') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row g-2" id="order_stats">
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/food/items.svg')}}" alt="dashboard/grocery">
                            <h6 class="name">{{ translate('messages.foods') }}</h6>
                            <h3 class="count">{{ $data['total_items'] }}</h3>
                            <div class="subtxt">{{ $data['new_items'] }} {{ translate('newly added') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/food/orders.svg')}}" alt="dashboard/grocery">
                            <h6 class="name">{{ translate('messages.orders') }}</h6>
                            <h3 class="count">{{ $data['total_orders'] }}</h3>
                            <div class="subtxt">{{ $data['new_orders'] }} {{ translate('newly added') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/food/stores.svg')}}" alt="dashboard/grocery">
                            <h6 class="name">{{ translate('messages.restaurants') }}</h6>
                            <h3 class="count">{{ $data['total_stores'] }}</h3>
                            <div class="subtxt">{{ $data['new_stores'] }} {{ translate('newly added') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/food/customers.svg')}}" alt="dashboard/grocery">
                            <h6 class="name">{{ translate('messages.customers') }}</h6>
                            <h3 class="count">{{ $data['total_customers'] }}</h3>
                            <div class="subtxt">{{ $data['new_customers'] }} {{ translate('newly added') }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row g-2">
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['searching_for_deliverymen'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('messages.unassigned_orders')}}</span>
                                        </h6>
                                        <span class="card-title text-3F8CE8">
                                            {{$data['searching_for_dm']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['accepted'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('Accepted by Delivery Man')}}</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{$data['accepted_by_dm']}}
                                        </span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['processing'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('Cooking')}}</span>
                                        </h6>
                                        <span class="card-title text-FFA800">
                                            {{$data['preparing_in_rs']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['item_on_the_way'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('Out for Delivery')}}</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{$data['picked_up']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['delivered'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('messages.delivered')}}</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{$data['delivered']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['canceled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('messages.canceled')}}</span>
                                        </h6>
                                        <span class="card-title text-danger">
                                            {{$data['canceled']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['refunded'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('messages.refunded')}}</span>
                                        </h6>
                                        <span class="card-title text-danger">
                                            {{$data['refunded']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['failed'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/payment-failed.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('messages.payment_failed')}}</span>
                                        </h6>
                                        <span class="card-title text-danger">
                                            {{$data['refund_requested']}}
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- End Stats -->

        

        <div class="row g-2">
            <div class="col-lg-8 col--xl-8">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center __gap-12px">
                            <div class="__gross-amount" id="gross_sale">
                                <h6>{{\App\CentralLogics\Helpers::format_currency(array_sum($total_sell))}}</h6>
                                <span>{{ translate('messages.Gross Sale') }}</span>
                            </div>
                            <div class="chart--label __chart-label p-0 move-left-100 ml-auto">
                                <span class="indicator chart-bg-2"></span>
                                <span class="info">
                                    {{ translate('sale') }} ({{ date("Y") }})
                                </span>
                            </div>
                            <select class="custom-select border-0 text-center w-auto ml-auto commission_overview_stats_update" name="commission_overview">
                                    <option
                                    value="this_year" {{$params['commission_overview'] == 'this_year'?'selected':''}}>
                                    {{translate('This year')}}
                                </option>
                                <option
                                    value="this_month" {{$params['commission_overview'] == 'this_month'?'selected':''}}>
                                    {{translate('This month')}}
                                </option>
                                <option
                                    value="this_week" {{$params['commission_overview'] == 'this_week'?'selected':''}}>
                                    {{translate('This week')}}
                                </option>
                            </select>
                        </div>
                        <div id="commission-overview-board">

                            <div id="grow-sale-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col--xl-4">
                <!-- Card -->
                <div class="card h-100">
                    <!-- Header -->
                    <div class="card-header border-0">
                        <h5 class="card-header-title">
                            {{translate('User Statistics')}}
                        </h5>
                        <div id="stat_zone">

                            @include('admin-views.partials._zone-change',['data'=>$data])


                        </div>
                        <select class="custom-select border-0 text-center w-auto user_overview_stats_update" name="user_overview">
                                <option
                                value="this_year" {{$params['user_overview'] == 'this_year'?'selected':''}}>
                                {{translate('This year')}}
                            </option>
                            <option
                                value="this_month" {{$params['user_overview'] == 'this_month'?'selected':''}}>
                                {{translate('This month')}}
                            </option>
                            <option
                                value="this_week" {{$params['user_overview'] == 'this_week'?'selected':''}}>
                                {{translate('This week')}}
                            </option>
                            <option
                                value="overall" {{$params['user_overview'] == 'overall'?'selected':''}}>
                                {{translate('messages.Overall')}}
                            </option>
                        </select>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body" id="user-overview-board">
                        <div class="position-relative pie-chart">
                            <div id="dognut-pie"></div>
                            <!-- Total Orders -->
                            <div class="total--orders">
                                <h3 class="text-uppercase mb-xxl-2">{{ $data['customer'] + $data['stores'] + $data['delivery_man'] }}</h3>
                                <span class="text-capitalize">{{translate('messages.total_users')}}</span>
                            </div>
                            <!-- Total Orders -->
                        </div>
                        <div class="d-flex flex-wrap justify-content-center mt-4">
                            <div class="chart--label">
                                <span class="indicator chart-bg-1"></span>
                                <span class="info">
                                    {{translate('messages.customer')}} {{$data['customer']}}
                                </span>
                            </div>
                            <div class="chart--label">
                                <span class="indicator chart-bg-2"></span>
                                <span class="info">
                                    {{translate('messages.store')}} {{$data['stores']}}
                                </span>
                            </div>
                            <div class="chart--label">
                                <span class="indicator chart-bg-3"></span>
                                <span class="info">
                                    {{translate('messages.delivery_man')}} {{$data['delivery_man']}}
                                </span>
                            </div>
                        </div>

                    </div>
                    <!-- End Body -->
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-restaurants-view">
                    @include('admin-views.partials._top-restaurants',['top_restaurants'=>$data['top_restaurants']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="popular-restaurants-view">
                    @include('admin-views.partials._popular-restaurants',['popular'=>$data['popular']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-selling-foods-view">
                    @include('admin-views.partials._top-selling-foods',['top_sell'=>$data['top_sell']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-rated-foods-view">
                    @include('admin-views.partials._top-rated-foods',['top_rated_foods'=>$data['top_rated_foods']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-deliveryman-view">
                    @include('admin-views.partials._top-deliveryman',['top_deliveryman'=>$data['top_deliveryman']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-customer-view">
                    @include('admin-views.partials._top-customer',['top_customers'=>$data['top_customers']])
                </div>
                <!-- End Card -->
            </div>

        </div>
        @else
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{translate('messages.welcome')}}, {{auth('admin')->user()->f_name}}.</h1>
                    <p class="page-header-text">{{translate('messages.employee_welcome_message')}}</p>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        @endif
    </div>
@endsection

@push('script')
    <script src="{{asset('assets/admin')}}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{asset('assets/admin')}}/vendor/chart.js.extensions/chartjs-extensions.js"></script>
    <script src="{{asset('assets/admin')}}/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>

    <!-- Apex Charts -->
    <script src="{{asset('assets/admin/js/apex-charts/apexcharts.js')}}"></script>
    <!-- Apex Charts -->

@endpush


@push('script_2')

    <!-- Dognut Pie Chart -->
    <script>
        "use strict";
        let options;
        let chart;
         options = {
            series: [{{ $data['customer']}}, {{$data['stores']}}, {{$data['delivery_man']}}],
            chart: {
                width: 320,
                type: 'donut',
            },
            labels: ['{{ translate('Customer') }}', '{{ translate('Store') }}', '{{ translate('Delivery man') }}'],
            dataLabels: {
                enabled: false,
                style: {
                    colors: ['#005555', '#00aa96', '#b9e0e0',]
                }
            },
            responsive: [{
                breakpoint: 1650,
                options: {
                    chart: {
                        width: 250
                    },
                }
            }],
            colors: ['#005555','#00aa96', '#111'],
            fill: {
                colors: ['#005555','#00aa96', '#b9e0e0']
            },
            legend: {
                show: false
            },
        };

         chart = new ApexCharts(document.querySelector("#dognut-pie"), options);
        chart.render();

        options = {
            series: [{
                name: '{{ translate('Gross Sale') }}',
                data: [{{ implode(",",$total_sell) }}]
            },{
                name: '{{ translate('Admin Comission') }}',
                data: [{{ implode(",",$commission) }}]
            },{
                name: '{{ translate('Delivery Comission') }}',
                data: [{{ implode(",",$delivery_commission) }}]
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show:false
                },
                colors: ['#76ffcd','#ff6d6d', '#005555'],
            },
            colors: ['#76ffcd','#ff6d6d', '#005555'],
            dataLabels: {
                enabled: false,
                colors: ['#76ffcd','#ff6d6d', '#005555'],
            },
            stroke: {
                curve: 'smooth',
                width: 2,
                colors: ['#76ffcd','#ff6d6d', '#005555'],
            },
            fill: {
                type: 'gradient',
                colors: ['#76ffcd','#ff6d6d', '#005555'],
            },
            xaxis: {
                //   type: 'datetime',
                categories: [{!! implode(",",$label) !!}]
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy HH:mm'
                },
            },
        };

        chart = new ApexCharts(document.querySelector("#grow-sale-chart"), options);
        chart.render();


    <!-- Dognut Pie Chart -->

        // INITIALIZATION OF CHARTJS
        // =======================================================
        Chart.plugins.unregister(ChartDataLabels);

        $('.js-chart').each(function () {
            $.HSCore.components.HSChartJS.init($(this));
        });

         let updatingChart = $.HSCore.components.HSChartJS.init($('#updatingData'));

        $('.order_stats_update').on('change', function (){
            let type = $(this).val();
            order_stats_update(type);
        })
        function order_stats_update(type) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.dashboard-stats.order')}}',
                data: {
                    statistics_type: type
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    insert_param('statistics_type',type);
                    $('#order_stats').html(data.view)
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        }
        $('.fetch_data_zone_wise').on('change', function (){
            let zone_id = $(this).val();
            fetch_data_zone_wise(zone_id);
        })

        function fetch_data_zone_wise(zone_id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.dashboard-stats.zone')}}',
                data: {
                    zone_id: zone_id
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    insert_param('zone_id', zone_id);
                    $('#order_stats').html(data.order_stats);
                    $('#user-overview-boarde').html(data.user_overview);
                    $('#monthly-earning-graph').html(data.monthly_graph);
                    $('#popular-restaurants-view').html(data.popular_restaurants);
                    $('#top-deliveryman-view').html(data.top_deliveryman);
                    $('#top-rated-foods-view').html(data.top_rated_foods);
                    $('#top-restaurants-view').html(data.top_restaurants);
                    $('#top-selling-foods-view').html(data.top_selling_foods);
                    $('#stat_zone').html(data.stat_zone);
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        }
        $('.user_overview_stats_update').on('change', function (){
            let type = $(this).val();
            user_overview_stats_update(type);
        })

        function user_overview_stats_update(type) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.dashboard-stats.user-overview')}}',
                data: {
                    user_overview: type
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    insert_param('user_overview',type);
                    $('#user-overview-board').html(data.view)
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        }
        $('.commission_overview_stats_update').on('change', function (){
            let type = $(this).val();
            commission_overview_stats_update(type);
        })
        function commission_overview_stats_update(type) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.dashboard-stats.commission-overview')}}',
                data: {
                    commission_overview: type
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    insert_param('commission_overview',type);
                    $('#commission-overview-board').html(data.view)
                    $('#gross_sale').html(data.gross_sale)
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        }

        function insert_param(key, value) {
            key = encodeURIComponent(key);
            value = encodeURIComponent(value);
            // kvp looks like ['key1=value1', 'key2=value2', ...]
             let kvp = document.location.search.substr(1).split('&');
            let i = 0;

            for (; i < kvp.length; i++) {
                if (kvp[i].startsWith(key + '=')) {
                    let pair = kvp[i].split('=');
                    pair[1] = value;
                    kvp[i] = pair.join('=');
                    break;
                }
            }
            if (i >= kvp.length) {
                kvp[kvp.length] = [key, value].join('=');
            }
            // can return this or...
            let params = kvp.join('&');
            // change url page with new params
            window.history.pushState('page2', 'Title', '{{url()->current()}}?' + params);
        }
        
        // Bulk Import Interface JavaScript - FIXED VERSION
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔧 Food Dashboard: Initializing bulk import interface...');
            
            // Import type selection
            document.querySelectorAll('.import-type-card').forEach(card => {
                card.addEventListener('click', function() {
                    console.log('📋 Import type card clicked:', this.dataset.type);
                    
                    // Remove active class from all cards
                    document.querySelectorAll('.import-type-card').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show import interface
                    const importInterface = document.getElementById('import-interface');
                    if (importInterface) {
                        importInterface.style.display = 'block';
                        
                        // Wait for interface to be visible before binding events
                        setTimeout(() => {
                            initializeUploadEvents();
                        }, 100);
                    }
                    
                    // Get selected import type and multilang option
                    const importType = this.dataset.type;
                    const isMultilang = this.dataset.multilang === 'true';
                    
                    // Update form values
                    const importTypeInput = document.getElementById('import-type');
                    const importMultilangInput = document.getElementById('import-multilang');
                    if (importTypeInput) importTypeInput.value = importType;
                    if (importMultilangInput) importMultilangInput.value = isMultilang;
                    
                    // Update template download link
                    const templateLink = document.getElementById('template-download-link');
                    if (templateLink) {
                        // Always use original bulk import routes (multilang reverted to original)
                        templateLink.href = importType === 'items' ? 
                            '{{ route("admin.item.bulk-import") }}?download=template' :
                            '{{ route("admin.store.bulk-import") }}?download=template';
                    }
                    
                    // Update form action
                    const form = document.getElementById('dynamic-import-form');
                    if (form) {
                        // Always use original bulk import routes (multilang reverted to original)
                        form.action = importType === 'items' ? 
                            '{{ route("admin.item.bulk-import") }}' :
                            '{{ route("admin.store.bulk-import") }}';
                    }
                    
                    // Update button text
                    const importBtnText = document.getElementById('import-btn-text');
                    if (importBtnText) {
                        importBtnText.textContent = 
                            importType === 'items' ? 
                            '{{ translate("messages.import_items") }}' : 
                            '{{ translate("messages.import_stores") }}';
                    }
                    
                    // Show advanced options for stores
                    const advancedOptions = document.getElementById('advanced-options');
                    if (advancedOptions) {
                        advancedOptions.style.display = 
                            importType === 'stores' ? 'block' : 'none';
                    }
                });
            });
            
            // Initialize upload events - centralized and safe
            function initializeUploadEvents() {
                const uploadArea = document.getElementById('upload-area');
                const fileInput = document.getElementById('file-input');
                
                console.log('🎯 Initializing upload events...', {
                    uploadArea: !!uploadArea,
                    fileInput: !!fileInput
                });
                
                if (!uploadArea || !fileInput) {
                    console.warn('⚠️ Upload elements not found:', {
                        uploadArea: !!uploadArea,
                        fileInput: !!fileInput
                    });
                    return;
                }
                
                // Remove any existing event listeners to prevent duplicates
                const newUploadArea = uploadArea.cloneNode(true);
                uploadArea.parentNode.replaceChild(newUploadArea, uploadArea);
                
                // Re-get the element reference
                const freshUploadArea = document.getElementById('upload-area');
                const freshFileInput = document.getElementById('file-input');
                
                if (!freshUploadArea || !freshFileInput) {
                    console.error('❌ Failed to get fresh element references');
                    return;
                }
                
                // Click handler - removed preventDefault to allow file input click
                freshUploadArea.addEventListener('click', function(e) {
                    console.log('🖱️ Upload area clicked');
                    // Don't prevent default on file input click
                    if (e.target.id !== 'file-input') {
                        e.preventDefault();
                        freshFileInput.click();
                    }
                });
                
                // Drag and drop handlers
                freshUploadArea.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.add('drag-over');
                });
                
                freshUploadArea.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.remove('drag-over');
                });
                
                freshUploadArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.remove('drag-over');
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        console.log('📁 File dropped:', files[0].name);
                        freshFileInput.files = files;
                        updateUploadArea(files[0]);
                    }
                });
                
                // File input change handler
                freshFileInput.addEventListener('change', function(e) {
                    if (this.files.length > 0) {
                        console.log('📁 File selected:', this.files[0].name);
                        updateUploadArea(this.files[0]);
                    }
                });
                
                console.log('✅ Upload events initialized successfully');
            }
            
            function updateUploadArea(file) {
                const uploadArea = document.getElementById('upload-area');
                if (!uploadArea) return;
                
                const uploadText = uploadArea.querySelector('.upload-text');
                const uploadIcon = uploadArea.querySelector('.upload-icon i');
                
                if (uploadText) uploadText.textContent = file.name;
                if (uploadIcon) uploadIcon.className = 'fas fa-file-excel text-success';
                uploadArea.classList.add('file-selected');
                
                console.log('✅ Upload area updated for file:', file.name);
            }
            
            // Initialize first card if available
            const firstCard = document.querySelector('.import-type-card');
            if (firstCard) {
                setTimeout(() => {
                    firstCard.click();
                }, 500);
            }
        });
    </script>
    
    <style>
        /* Import Type Cards */
        .import-type-selection {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            border-left: 5px solid #667eea;
        }
        
        .import-type-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            margin-bottom: 1rem;
        }
        
        .import-type-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
        }
        
        .import-type-card.active {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }
        
        .import-type-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin: 0 auto 1.5rem;
        }
        
        .import-type-card h5 {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.75rem;
        }
        
        .import-type-card p {
            color: #718096;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .format-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #667eea;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .format-badge.multilang {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }
        
        /* Import Steps */
        .import-step {
            background: white;
            border-radius: 16px;
            margin-bottom: 2rem;
            overflow: hidden;
            border-left: 5px solid #667eea;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.1);
        }
        
        .step-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .step-content h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .step-content p {
            margin: 0;
            opacity: 0.9;
        }
        
        .step-body {
            padding: 2rem;
        }
        
        /* Template Card */
        .template-card {
            background: #f8f9fa;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }
        
        .template-card:hover {
            border-color: #667eea;
            background: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
        }
        
        .template-icon {
            font-size: 2.5rem;
        }
        
        .template-info {
            flex: 1;
        }
        
        .template-info h6 {
            margin: 0 0 0.25rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        /* Upload Area - IMPROVED */
        .upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
            position: relative;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        
        .upload-area:hover, .upload-area.drag-over {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        }
        
        .upload-area.file-selected {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.05);
            border-style: solid;
        }
        
        .upload-area:active {
            transform: translateY(0);
        }
        
        /* Make upload area fully clickable - FIXED */
        .upload-area * {
            pointer-events: none;
        }
        
        .upload-area #file-input {
            pointer-events: all !important;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .upload-text {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .upload-hint {
            color: #718096;
            font-size: 0.9rem;
        }
        
        /* Advanced Options */
        .advanced-options {
            background: #f8f9fa;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
        }
        
        .advanced-options h6 {
            margin-bottom: 1rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .form-group label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        /* Import Button */
        #import-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }
    </style>

    <script>
        // ✅ DUPLICATE CODE REMOVED - Single implementation now handles all functionality
        console.log('🧹 Duplicate JavaScript code removed - using centralized implementation above');
        
        // AJAX form submission for embedded import
        function submitEmbeddedImport() {
            const form = document.getElementById('dynamic-import-form');
            const fileInput = document.getElementById('file-input');
            const progressDiv = document.getElementById('import-progress');
            const resultDiv = document.getElementById('import-result');
            const importBtn = document.getElementById('import-btn');

            // Validate file selection
            if (!fileInput || !fileInput.files.length) {
                if (resultDiv) {
                    resultDiv.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{ translate("messages.please_select_file") }}</div>';
                }
                return;
            }

            // Show progress
            if (progressDiv) progressDiv.style.display = 'block';
            if (importBtn) importBtn.disabled = true;
            if (resultDiv) resultDiv.innerHTML = '';

            // Create FormData for file upload
            const formData = new FormData();
            formData.append('import_file', fileInput.files[0]);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            // Add other form fields
            const importType = document.getElementById('import-type');
            const importMultilang = document.getElementById('import-multilang');
            if (importType) formData.append('import_type', importType.value);
            if (importMultilang) formData.append('multilang', importMultilang.value);

            // Submit via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                if (progressDiv) progressDiv.style.display = 'none';
                if (importBtn) importBtn.disabled = false;
                
                // Check if response contains success indicators
                if (html.includes('success') || html.includes('imported successfully')) {
                    if (resultDiv) {
                        resultDiv.innerHTML = '<div class="alert alert-success">' +
                                '<i class="fas fa-check-circle"></i> ' +
                                '<strong>{{ translate("messages.success") }}!</strong> ' +
                                '{{ translate("messages.items_imported_successfully") }}' +
                            '</div>';
                    }
                    
                    // Reset form
                    if (fileInput) fileInput.value = '';
                    const uploadArea = document.getElementById('upload-area');
                    if (uploadArea) {
                        uploadArea.innerHTML = '<div class="upload-icon">' +
                                '<i class="fas fa-cloud-upload-alt"></i>' +
                            '</div>' +
                            '<div class="upload-text">{{ translate('messages.drag_drop_file') }}</div>' +
                            '<div class="upload-hint">{{ translate('messages.or_click_browse') }} (Excel, CSV - max 10MB)</div>';
                    }
                    
                    // Refresh page data after delay
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    if (resultDiv) {
                        resultDiv.innerHTML = '<div class="alert alert-danger">' +
                                '<i class="fas fa-exclamation-circle"></i>' +
                                '<strong>{{ translate("messages.error") }}!</strong>' +
                                '{{ translate("messages.import_failed") }}' +
                            '</div>';
                    }
                }
            })
            .catch(error => {
                console.error('Import error:', error);
                if (progressDiv) progressDiv.style.display = 'none';
                if (importBtn) importBtn.disabled = false;
                if (resultDiv) {
                    resultDiv.innerHTML = '<div class="alert alert-danger">' +
                            '<i class="fas fa-exclamation-circle"></i>' +
                            '<strong>{{ translate("messages.error") }}!</strong>' +
                            '{{ translate("messages.something_went_wrong") }}' +
                        '</div>';
                }
            });
        }

        // Load test CSV data function
        function loadTestData() {
            // Create test CSV content
            const testCSVContent = `name,description,price,category_id,store_id,discount_type,discount,tax,tax_type,status,translations
"Grilled Chicken Kebab","Tender grilled chicken kebab with rice and vegetables",15.99,1,28,percentage,10,5,percentage,1,"[{""locale"":""en"",""key"":""name"",""value"":""Grilled Chicken Kebab""},{""locale"":""ar"",""key"":""name"",""value"":""كباب الدجاج المشوي""},{""locale"":""ckb"",""key"":""name"",""value"":""كباب مریشك""},{""locale"":""en"",""key"":""description"",""value"":""Tender grilled chicken kebab with rice and vegetables""},{""locale"":""ar"",""key"":""description"",""value"":""دجاج مشوي طري مع الأرز والخضار""},{""locale"":""ckb"",""key"":""description"",""value"":""مریشكی شواوی نه‌رم له‌گه‌ڵ برنج و سه‌وزه‌کان""}]"
"Traditional Kebab","Mixed kebab platter with salad",18.50,1,28,amount,2,8,percentage,1,"[{""locale"":""en"",""key"":""name"",""value"":""Traditional Kebab""},{""locale"":""ar"",""key"":""name"",""value"":""كباب تقليدي""},{""locale"":""ckb"",""key"":""name"",""value"":""کباب نه‌ریتی""},{""locale"":""en"",""key"":""description"",""value"":""Mixed kebab platter with salad""},{""locale"":""ar"",""key"":""description"",""value"":""طبق كباب مشكل مع السلطة""},{""locale"":""ckb"",""key"":""description"",""value"":""طەبەقی کبابی تێکەڵ له‌گه‌ڵ زه‌ڵاته‌""}]"
"Lamb Tikka","Spicy lamb tikka with naan bread",22.00,1,28,percentage,15,10,percentage,1,"[{""locale"":""en"",""key"":""name"",""value"":""Lamb Tikka""},{""locale"":""ar"",""key"":""name"",""value"":""تكة الخروف""},{""locale"":""ckb"",""key"":""name"",""value"":""تیکای بەرخ""},{""locale"":""en"",""key"":""description"",""value"":""Spicy lamb tikka with naan bread""},{""locale"":""ar"",""key"":""description"",""value"":""تكة الخروف الحارة مع خبز النان""},{""locale"":""ckb"",""key"":""description"",""value"":""تیکای بەرخی تیژ له‌گه‌ڵ نانی نان""}]"
"Biryani Rice","Fragrant basmati rice with spices",12.99,2,28,amount,1,5,percentage,1,"[{""locale"":""en"",""key"":""name"",""value"":""Biryani Rice""},{""locale"":""ar"",""key"":""name"",""value"":""أرز بريانی""},{""locale"":""ckb"",""key"":""name"",""value"":""برنجی بریانی""},{""locale"":""en"",""key"":""description"",""value"":""Fragrant basmati rice with spices""},{""locale"":""ar"",""key"":""description"",""value"":""أرز بسمتي عطر بالتوابل""},{""locale"":""ckb"",""key"":""description"",""value"":""برنجی بەسمەتی خۆشبۆن له‌گه‌ڵ بۆنەکان""}]"
"Kurdish Tea","Traditional Kurdish tea with sugar",3.50,3,28,percentage,0,2,percentage,1,"[{""locale"":""en"",""key"":""name"",""value"":""Kurdish Tea""},{""locale"":""ar"",""key"":""name"",""value"":""شاي كردي""},{""locale"":""ckb"",""key"":""name"",""value"":""چای کوردی""},{""locale"":""en"",""key"":""description"",""value"":""Traditional Kurdish tea with sugar""},{""locale"":""ar"",""key"":""description"",""value"":""شاي كردي تقليدي مع السكر""},{""locale"":""ckb"",""key"":""description"",""value"":""چای کوردی نه‌ریتی له‌گه‌ڵ شه‌کر""}]"`;

            // Create a blob with the CSV content
            const blob = new Blob([testCSVContent], { type: 'text/csv' });
            const file = new File([blob], 'test_multilingual_menu.csv', { type: 'text/csv' });

            // Get the file input
            const fileInput = document.getElementById('file-input');
            if (fileInput) {
                // Create a FileList-like object
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                
                // Update the upload area
                updateUploadArea(file);
                
                // Show success message
                const resultDiv = document.getElementById('import-result');
                if (resultDiv) {
                    resultDiv.innerHTML = '<div class="alert alert-info">' +
                            '<i class="fas fa-info-circle"></i>' +
                            '<strong>Test CSV Loaded!</strong> ' +
                            'Sample multilingual menu with 5 items ready for import.' +
                        '</div>';
                }
            }
        }
    </script>

    <!-- Inline Bulk Import Container -->
    <div id="inline-bulk-import-container" class="card mt-4" style="display: none;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="tio-cloud-upload"></i> 
                <span id="import-type-title">Bulk Import</span>
            </h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeInlineImport()">
                <i class="tio-clear"></i> Close
            </button>
        </div>
        <div class="card-body">
            <div id="import-content-area">
                <!-- Dynamic import content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- JavaScript for Inline Bulk Import -->
    <script>
        function loadInlineBulkImport(type) {
            const container = document.getElementById('inline-bulk-import-container');
            const titleElement = document.getElementById('import-type-title');
            const contentArea = document.getElementById('import-content-area');
            
            // Show the container
            container.style.display = 'block';
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Update title
            titleElement.textContent = `Bulk Import ${type.charAt(0).toUpperCase() + type.slice(1)}`;
            
            // Show loading
            contentArea.innerHTML = '<div class="text-center p-4"><i class="tio-reload-24 rotating"></i> Loading...</div>';
            
            // Load content based on type
            let url;
            switch(type) {
                case 'items':
                    url = '{{ route("admin.item.bulk-import") }}?professional=1';
                    break;
                case 'stores':
                    url = '{{ route("admin.store.bulk-import") }}';
                    break;
                case 'categories':
                    // Categories use the item bulk import with category parameter
                    contentArea.innerHTML = '<div class="alert alert-info">' +
                        '<i class="tio-info"></i> Category bulk import: Use the Items import and select category data type.' +
                        '<br><br>' +
                        '<div class="text-center">' +
                            '<a href="{{ asset('assets/categories_multilang_template.csv') }}" class="btn btn-primary" download="categories_multilang_template.csv">' +
                                '<i class="tio-download"></i> Download Multilingual Template' +
                            '</a>' +
                        '</div>' +
                    '</div>';
                    return;
                case 'addons':
                    // Addons use the item bulk import with addon parameter
                    contentArea.innerHTML = '<div class="alert alert-info">' +
                        '<i class="tio-info"></i> Addon bulk import: Use the Items import and select addon data type.' +
                        '<br><br>' +
                        '<div class="text-center">' +
                            '<a href="{{ asset('assets/addons_multilang_template.csv') }}" class="btn btn-primary" download="addons_multilang_template.csv">' +
                                '<i class="tio-download"></i> Download Multilingual Template' +
                            '</a>' +
                        '</div>' +
                    '</div>';
                    return;
                default:
                    contentArea.innerHTML = '<div class="alert alert-warning">Import type not supported</div>';
                    return;
            }
            
            // Load the content via AJAX
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    // Extract only the main content (remove navbar, sidebar, etc.)
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Try multiple selectors to find the main content
                    let mainContent = doc.querySelector('main#content .content-wrapper') || 
                                     doc.querySelector('main#content .container-fluid') ||
                                     doc.querySelector('main#content') || 
                                     doc.querySelector('.main-content') || 
                                     doc.querySelector('.card-body');
                    
                    if (mainContent && mainContent.innerHTML.trim()) {
                        contentArea.innerHTML = mainContent.innerHTML;
                        
                        // Add multilingual template download link for items and stores
                        const multilingualTemplates = {
                            'items': '{{ asset('assets/items_multilang_template.csv') }}',
                            'stores': '{{ asset('assets/stores_multilang_template.csv') }}'
                        };
                        
                        if (multilingualTemplates[type]) {
                            // Find a good place to insert the download link
                            const cardBodies = contentArea.querySelectorAll('.card-body');
                            if (cardBodies.length > 0) {
                                const firstCardBody = cardBodies[0];
                                const downloadSection = document.createElement('div');
                                downloadSection.className = 'alert alert-info mb-3';
                                downloadSection.innerHTML = `
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="tio-info"></i> 
                                            <strong>Multilingual Template Available:</strong> 
                                            Download template with Arabic & Kurdish translations
                                        </div>
                                        <a href="${multilingualTemplates[type]}" class="btn btn-sm btn-primary" download="${type}_multilang_template.csv">
                                            <i class="tio-download"></i> Download Multilingual Template
                                        </a>
                                    </div>
                                `;
                                firstCardBody.insertBefore(downloadSection, firstCardBody.firstChild);
                            }
                        }
                        
                        // Re-initialize any JavaScript that might be needed (safely)
                        const scripts = contentArea.querySelectorAll('script');
                        scripts.forEach(script => {
                            try {
                                if (script.innerHTML && script.innerHTML.trim()) {
                                    // Only eval non-empty inline scripts
                                    eval(script.innerHTML);
                                }
                            } catch (e) {
                                console.warn('Script execution failed:', e);
                            }
                        });
                        
                        // Initialize professional uploader if available
                        if (typeof window.initProfessionalUploader === 'function') {
                            setTimeout(() => {
                                window.initProfessionalUploader();
                            }, 200);
                        }
                        
                        // Allow import form to work normally (don't prevent submission for import forms)
                        const forms = contentArea.querySelectorAll('form:not(#import_form)');
                        forms.forEach(form => {
                            form.addEventListener('submit', function(e) {
                                e.preventDefault();
                                // Add AJAX form submission here if needed for other forms
                            });
                        });
                        
                    } else {
                        contentArea.innerHTML = '<div class="alert alert-warning"><i class="tio-warning"></i> Content could not be extracted from the import page.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading import content:', error);
                    contentArea.innerHTML = `<div class="alert alert-danger">
                        <i class="tio-error"></i> Error loading import interface: ${error.message}
                        <br><small>URL: ${url}</small>
                    </div>`;
                });
        }
        
        function closeInlineImport() {
            const container = document.getElementById('inline-bulk-import-container');
            container.style.display = 'none';
        }
    </script>
@endpush
