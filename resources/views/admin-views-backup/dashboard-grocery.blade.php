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
                    <select name="zone_id" class="form-control js-select2-custom fetch_data_zone_wise" >
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
                            <img src="{{asset('assets/admin/img/dashboard/grocery/items.svg')}}" alt="dashboard/grocery">
                            <h6 class="name">{{ translate('messages.items') }}</h6>
                            <h3 class="count">{{ $data['total_items'] }}</h3>
                            <div class="subtxt">{{ $data['new_items'] }} {{ translate('newly added') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/grocery/orders.svg')}}" alt="dashboard/grocery">
                            <h6 class="name">{{ translate('messages.orders') }}</h6>
                            <h3 class="count">{{ $data['total_orders'] }}</h3>
                            <div class="subtxt">{{ $data['new_orders'] }} {{ translate('newly added') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/grocery/stores.svg')}}" alt="dashboard/grocery">
                            <h6 class="name">{{ translate('Grocery Stores') }}</h6>
                            <h3 class="count">{{ $data['total_stores'] }}</h3>
                            <div class="subtxt">{{ $data['new_stores'] }} {{ translate('newly added') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/grocery/customers.svg')}}" alt="dashboard/grocery">
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
                                            <span>{{translate('Packaging')}}</span>
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
    <style>
        .import-type-selection {
            display: flex;
            flex-direction: column;
        }

        .import-type-card {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .import-type-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.15);
            border-color: #667eea;
        }

        .import-type-card.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: #667eea;
            color: white;
        }

        .import-type-card.active .import-type-icon i {
            color: white;
        }

        .import-type-card.active .format-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .import-type-icon {
            text-align: center;
            margin-bottom: 1rem;
        }

        .import-type-icon i {
            font-size: 2.5rem;
            color: #667eea;
            transition: color 0.3s ease;
        }

        .import-type-card h5 {
            text-align: center;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .import-type-card p {
            text-align: center;
            margin-bottom: 1rem;
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .format-badge {
            display: inline-block;
            background: #e2e8f0;
            color: #4a5568;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            position: absolute;
            top: 1rem;
            right: 1rem;
        }

        .format-badge.multilang {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }

        .import-step {
            background: white;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            opacity: 0.6;
            transition: opacity 0.3s ease;
        }

        .import-step.active {
            opacity: 1;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        }

        /* ✅ STANDARDIZED BULK IMPORT STYLES */
        .bulk-import-section {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .step-number {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .template-download-area {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .template-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            background: white;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .template-btn:hover {
            text-decoration: none;
            border-color: #007bff;
            background: #f8f9ff;
            color: #007bff;
            transform: translateY(-2px);
        }

        .template-btn.multilang {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }

        .template-btn.multilang:hover {
            background: #1e7e34;
            color: white;
            transform: translateY(-2px);
        }

        .upload-type-selector {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
        }

        .upload-option {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .upload-option:last-child {
            margin-bottom: 0;
        }

        .upload-option:hover {
            border-color: #007bff;
            background: #f8f9ff;
        }

        .upload-option.active {
            border-color: #007bff;
            background: #007bff;
            color: white;
        }

        .upload-option input[type="radio"] {
            margin-right: 10px;
        }

        /* Professional bulk upload area */
        .bulk-upload-area {
            border: 3px dashed #007bff;
            border-radius: 20px;
            padding: 40px 20px;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-size: 400% 400%;
            animation: gradientShift 6s ease infinite;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            min-height: 220px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.3);
            color: white;
            overflow: hidden;
        }

        .bulk-upload-area::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57, #ff9ff3);
            background-size: 600% 600%;
            border-radius: 22px;
            z-index: -1;
            animation: borderGlow 3s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes borderGlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .bulk-upload-area:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.4);
        }

        .bulk-upload-area.drag-over {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            transform: scale(1.05);
            box-shadow: 0 30px 60px rgba(255, 107, 107, 0.5);
            border-color: #ff6b6b;
            animation: dragPulse 1s ease infinite;
        }

        @keyframes dragPulse {
            0%, 100% { box-shadow: 0 30px 60px rgba(255, 107, 107, 0.5); }
            50% { box-shadow: 0 35px 70px rgba(255, 107, 107, 0.7); }
        }

        .bulk-upload-area.file-selected {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            box-shadow: 0 20px 40px rgba(17, 153, 142, 0.4);
            color: white;
        }

        .upload-icon {
            font-size: 50px;
            color: white;
            margin-bottom: 15px;
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        .file-info {
            display: none;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 15px;
            margin-top: 15px;
            color: #2c3e50;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .bulk-upload-area.file-selected .file-info {
            display: block;
        }

        .bulk-upload-area.file-selected .upload-text {
            display: none;
        }

        .pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .step-header {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
        }

        .step-content h4 {
            margin: 0;
            color: #2d3748;
        }

        .step-content p {
            margin: 0.25rem 0 0 0;
            color: #718096;
            font-size: 0.9rem;
        }

        .step-body {
            padding: 1.5rem;
        }

        .template-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .template-icon i {
            font-size: 2rem;
        }

        .upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .upload-area:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .upload-area.dragover {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }

        .upload-icon i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        .upload-area:hover .upload-icon i {
            color: #667eea;
        }

        .upload-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }

        .upload-hint {
            color: #718096;
            font-size: 0.9rem;
        }

        #import-btn-grocery:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Import Type Selection
            document.querySelectorAll('.import-type-card').forEach(card => {
                card.addEventListener('click', function() {
                    // Remove active from all cards
                    document.querySelectorAll('.import-type-card').forEach(c => c.classList.remove('active'));
                    
                    // Add active to clicked card
                    this.classList.add('active');
                    
                    // Get import type and multilang setting
                    const importType = this.dataset.type;
                    const isMultilang = this.dataset.multilang === 'true';
                    
                    // Update form fields
                    document.getElementById('import-type-grocery').value = importType;
                    document.getElementById('import-multilang-grocery').value = isMultilang;
                    
                    // Update form action based on type and multilang
                    const form = document.getElementById('dynamic-import-form-grocery');
                    if (importType === 'items') {
                        // Always use original routes (multilang reverted to original)
                        form.action = '{{ route('admin.item.bulk-import') }}';
                        document.getElementById('step1-title-grocery').textContent = '{{ translate('messages.download_template') }}';
                        document.getElementById('step1-subtitle-grocery').textContent = '{{ translate('messages.get_formatted_template') }}';
                        document.getElementById('template-title-grocery').textContent = 'Items Template';
                        document.getElementById('template-download-link-grocery').href = '{{ route('admin.item.bulk-import') }}?download=template';
                        document.getElementById('import-btn-text-grocery').textContent = '{{ translate('messages.import_items') }}';
                    } else {
                        // Always use original routes (multilang reverted to original)
                        form.action = '{{ route('admin.store.bulk-import') }}';
                        document.getElementById('step1-title-grocery').textContent = '{{ translate('messages.download_template') }}';
                        document.getElementById('step1-subtitle-grocery').textContent = '{{ translate('messages.get_formatted_template') }}';
                        document.getElementById('template-title-grocery').textContent = 'Stores Template';
                        document.getElementById('template-download-link-grocery').href = '{{ route('admin.store.bulk-import') }}?download=template';
                        document.getElementById('import-btn-text-grocery').textContent = '{{ translate('messages.import_stores') }}';
                    }
                    
                    // Show import interface
                    document.getElementById('import-interface-grocery').style.display = 'block';
                    
                    // Activate step 1
                    document.querySelectorAll('.import-step').forEach(step => step.classList.remove('active'));
                    document.querySelector('.import-step[data-step="1"]').classList.add('active');
                    
                    // Activate step 2 after short delay
                    setTimeout(() => {
                        document.querySelector('.import-step[data-step="2"]').classList.add('active');
                    }, 300);
                });
            });
            
            // Drag & Drop functionality
            const uploadArea = document.getElementById('upload-area-grocery');
            const fileInput = document.getElementById('file-input-grocery');
            
            uploadArea.addEventListener('click', () => fileInput.click());
            
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    updateUploadArea(files[0]);
                }
            });
            
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    updateUploadArea(this.files[0]);
                }
            });
            
            function updateUploadArea(file) {
                uploadArea.innerHTML = `
                    <div class="upload-icon">
                        <i class="fas fa-file-excel text-success"></i>
                    </div>
                    <div class="upload-text text-success">${file.name}</div>
                    <div class="upload-hint">Ready to import (${(file.size / 1024 / 1024).toFixed(2)} MB)</div>
                `;
            }
            
            // Initialize with first card selected
            if (document.querySelector('.import-type-card')) {
                document.querySelector('.import-type-card').click();
            }
            
            // AJAX form submission for grocery embedded import
            window.submitEmbeddedImportGrocery = function() {
                const form = document.getElementById('dynamic-import-form-grocery');
                const fileInput = document.getElementById('file-input-grocery');
                const progressDiv = document.getElementById('import-progress-grocery');
                const resultDiv = document.getElementById('import-result-grocery');
                const importBtn = document.getElementById('import-btn-grocery');

                // Validate file selection
                if (!fileInput || !fileInput.files.length) {
                    if (resultDiv) {
                        resultDiv.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Please select a file to import</div>';
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
                const importType = document.getElementById('import-type-grocery');
                const importMultilang = document.getElementById('import-multilang-grocery');
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
                            resultDiv.innerHTML = `
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> 
                                    <strong>Success!</strong> 
                                    Items imported successfully!
                                </div>
                            `;
                        }
                        
                        // Reset form
                        if (fileInput) fileInput.value = '';
                        const uploadArea = document.getElementById('upload-area-grocery');
                        if (uploadArea) {
                            uploadArea.innerHTML = `
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="upload-text">{{ translate('messages.drag_drop_file') }}</div>
                                <div class="upload-hint">{{ translate('messages.or_click_browse') }} (Excel, CSV - max 10MB)</div>
                            `;
                        }
                        
                        // Refresh page data after delay
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        if (resultDiv) {
                            resultDiv.innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <strong>Error!</strong>
                                    Import failed. Please check your file format.
                                </div>
                            `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Import error:', error);
                    if (progressDiv) progressDiv.style.display = 'none';
                    if (importBtn) importBtn.disabled = false;
                    if (resultDiv) {
                        resultDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Error!</strong>
                                Something went wrong. Please try again.
                            </div>
                        `;
                    }
                });
            };
        });
    </script>

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
                    contentArea.innerHTML = `<div class="alert alert-info">
                        <i class="tio-info"></i> Category bulk import: Use the Items import and select category data type.
                        <br><br>
                        <div class="text-center">
                            <a href="{{ asset('assets/categories_multilang_template.csv') }}" class="btn btn-primary" download="categories_multilang_template.csv">
                                <i class="tio-download"></i> Download Multilingual Template
                            </a>
                        </div>
                    </div>`;
                    return;
                case 'addons':
                    // Addons use the item bulk import with addon parameter
                    contentArea.innerHTML = `<div class="alert alert-info">
                        <i class="tio-info"></i> Addon bulk import: Use the Items import and select addon data type.
                        <br><br>
                        <div class="text-center">
                            <a href="{{ asset('assets/addons_multilang_template.csv') }}" class="btn btn-primary" download="addons_multilang_template.csv">
                                <i class="tio-download"></i> Download Multilingual Template
                            </a>
                        </div>
                    </div>`;
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

        // ✅ STANDARDIZED GROCERY BULK IMPORT FUNCTIONALITY
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('groceryBulkUploadArea');
            const fileInput = document.getElementById('groceryProductsFile');
            const fileName = document.getElementById('groceryFileName');
            const fileDetails = document.getElementById('groceryFileDetails');
            const fileInfo = document.getElementById('groceryFileInfo');
            const uploadBtn = document.getElementById('groceryUploadBtn');
            const resetBtn = document.getElementById('groceryResetBtn');
            const removeBtn = document.getElementById('groceryRemoveFile');
            const form = document.getElementById('groceryBulkImportForm');

            console.log('🥬 Grocery Bulk Import: Initializing...');

            if (!uploadArea || !fileInput) {
                console.error('❌ Grocery Bulk Import: Upload area or file input not found!');
                return;
            }

            // Maximum file size (10MB)
            const maxFileSize = 10 * 1024 * 1024;
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                                'application/vnd.ms-excel', 'text/csv'];

            // File validation
            function validateFile(file) {
                const errors = [];
                
                if (file.size > maxFileSize) {
                    errors.push(`File size exceeds 10MB limit (${(file.size / 1024 / 1024).toFixed(2)}MB)`);
                }
                
                if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
                    errors.push('Invalid file type. Please upload .xlsx, .xls, or .csv files only.');
                }
                
                return errors;
            }

            // Handle file selection
            function handleFileSelection(file) {
                if (!file) return;
                
                const errors = validateFile(file);
                if (errors.length > 0) {
                    alert('File validation failed:\n' + errors.join('\n'));
                    resetUpload();
                    return;
                }
                
                // Set file to input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
                
                // Update UI
                displayFileInfo(file);
                uploadArea.classList.add('file-selected');
                uploadBtn.disabled = false;
                uploadBtn.classList.add('pulse');
                
                setTimeout(() => {
                    uploadBtn.classList.remove('pulse');
                }, 2000);
            }

            // Display file information
            function displayFileInfo(file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileType = file.name.split('.').pop().toUpperCase();
                
                if (fileName) {
                    fileName.textContent = file.name;
                }
                
                if (fileDetails) {
                    fileDetails.textContent = `${fileType} file • ${fileSize} MB`;
                }
            }

            // Reset upload state
            function resetUpload() {
                fileInput.value = '';
                uploadArea.classList.remove('file-selected', 'drag-over');
                uploadBtn.disabled = true;
                uploadBtn.classList.remove('pulse');
                
                // Reset radio buttons
                const newUploadRadio = document.querySelector('input[name="upload_type"][value="import"]');
                if (newUploadRadio) {
                    newUploadRadio.checked = true;
                    const radioOptions = document.querySelectorAll('.upload-option');
                    radioOptions.forEach(opt => opt.classList.remove('active'));
                    newUploadRadio.closest('.upload-option').classList.add('active');
                }
            }

            // Click to browse
            uploadArea.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (e.target.closest('.file-info') || e.target.closest('button')) {
                    return;
                }
                
                fileInput.click();
                console.log('🥬 Grocery: File input clicked');
            });

            // File selection change
            fileInput.addEventListener('change', function(e) {
                if (e.target.files[0]) {
                    handleFileSelection(e.target.files[0]);
                    console.log('🥬 Grocery: File selected via input:', e.target.files[0].name);
                }
            });

            // Drag and drop events
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.add('drag-over');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (!uploadArea.contains(e.relatedTarget)) {
                    uploadArea.classList.remove('drag-over');
                }
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.remove('drag-over');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelection(files[0]);
                    console.log('🥬 Grocery: File dropped:', files[0].name);
                }
            });

            // Reset button
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    resetUpload();
                    console.log('🥬 Grocery: Upload reset');
                });
            }

            // Remove file button
            if (removeBtn) {
                removeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    resetUpload();
                    console.log('🥬 Grocery: File removed');
                });
            }

            // Radio button handling
            const radioOptions = document.querySelectorAll('.upload-option');
            radioOptions.forEach(option => {
                option.addEventListener('click', function() {
                    radioOptions.forEach(opt => opt.classList.remove('active'));
                    option.classList.add('active');
                    const radio = option.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                });
            });

            // Form submission
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (!fileInput.files[0]) {
                        alert('Please select a file to import.');
                        return;
                    }
                    
                    const uploadType = document.querySelector('input[name="upload_type"]:checked').value;
                    const actionText = uploadType === 'import' ? 'import new grocery items' : 'update existing grocery items';
                    
                    if (confirm(`You are about to ${actionText}. Continue?`)) {
                        startImport(uploadType);
                    }
                });
            }

            function startImport(uploadType) {
                // Enhanced loading state
                const originalText = uploadBtn.innerHTML;
                uploadBtn.innerHTML = '<i class="tio-spinner" style="animation: spin 1s linear infinite;"></i> Processing File...';
                uploadBtn.disabled = true;
                uploadBtn.style.background = '#ffc107';
                
                // Add visual feedback to upload area
                uploadArea.style.opacity = '0.7';
                uploadArea.style.pointerEvents = 'none';
                
                // Show progress message
                if (fileInfo) {
                    const progressMsg = document.createElement('div');
                    progressMsg.innerHTML = '<small style="color: #ffc107;"><i class="tio-clock"></i> Processing your grocery items file...</small>';
                    fileInfo.appendChild(progressMsg);
                }
                
                // Submit the form after a brief delay to show loading state
                setTimeout(() => {
                    try {
                        form.submit();
                        console.log('🥬 Grocery: Form submitted successfully');
                    } catch (error) {
                        console.error('🥬 Grocery: Form submission error:', error);
                        alert('Error submitting form. Please try again.');
                        
                        // Restore button state
                        uploadBtn.innerHTML = originalText;
                        uploadBtn.disabled = false;
                        uploadBtn.style.background = '';
                        uploadArea.style.opacity = '';
                        uploadArea.style.pointerEvents = '';
                    }
                }, 500);
            }

            console.log('✅ Grocery Bulk Import: Initialized successfully');
        });
    </script>
@endpush
