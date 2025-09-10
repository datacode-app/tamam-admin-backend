@extends('layouts.admin.app')

@section('title',\App\Models\BusinessSetting::where(['key'=>'business_name'])->first()?->value ?? 'Not Set'??translate('messages.dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        @if(auth('admin')->user()->role_id == 1)
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center py-2">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <img src="{{asset('assets/admin/img/grocery.svg')}}" alt="img">
                        <div class="w-0 flex-grow pl-2">
                            <h1 class="page-header-title mb-0">{{translate('messages.welcome')}}, {{auth('admin')->user()->f_name}}.</h1>
                            <p class="page-header-text m-0">{{translate('messages.welcome_message')}}</p>
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
                                <input type="radio" name="statistics" hidden checked>
                                <span>{{ translate('This_Year') }}</span>
                            </label>
                            <label>
                                <input type="radio" name="statistics" hidden>
                                <span>{{ translate('This_Month') }}</span>
                            </label>
                            <label>
                                <input type="radio" name="statistics" hidden>
                                <span>{{ translate('This_Week') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row g-2" id="order_stats">
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/food/items.svg')}}" alt="dashboard/grocery">
                            <h6 class="name">Items</h6>
                            <h3 class="count">33,451</h3>
                            <div class="subtxt">12 newly added</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/food/orders.svg')}}" alt="dashboard/grocery">
                            <h6 class="name">Orders</h6>
                            <h3 class="count">30M+</h3>
                            <div class="subtxt">12 newly added</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/food/stores.svg')}}" alt="dashboard/grocery">
                            <h6 class="name">Grocery Stores</h6>
                            <h3 class="count">556</h3>
                            <div class="subtxt">12 newly added</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/food/customers.svg')}}" alt="dashboard/grocery">
                            <h6 class="name">Customers</h6>
                            <h3 class="count">1M+</h3>
                            <div class="subtxt">566 newly added</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row g-2">
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['delivered'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/food/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('messages.unassigned_orders')}}</span>
                                        </h6>
                                        <span class="card-title text-3F8CE8">
                                            {{$data['searching_for_dm']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['refunded'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/food/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('Accepted by Delivery Man')}}</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{$data['accepted_by_dm']}}
                                        </span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['canceled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/food/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('Packaging')}}</span>
                                        </h6>
                                        <span class="card-title text-FFA800">
                                            {{$data['preparing_in_rs']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['failed'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/food/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
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

        

        <!-- Multi-Language Store Import -->
        <div class="card mb-3">
            <div class="card-header border-0">
                <h5 class="card-header-title">
                    <i class="tio-cloud-upload mr-2"></i>
                    {{translate('Multi-Language Stores Import')}}
                </h5>
            </div>
            <div class="card-body">
                <div class="export-steps-2">
                    <div class="row g-4">
                        <div class="col-sm-6 col-lg-4">
                            <div class="export-steps-item-2 h-100">
                                <div class="top">
                                    <div>
                                        <h3 class="fs-20">{{translate('Step 1')}}</h3>
                                        <div>
                                            {{translate('Download Multi-Language CSV Template')}}
                                        </div>
                                    </div>
                                    <img src="{{asset('assets/admin/img/bulk-import-1.png')}}" alt="">
                                </div>
                                <h4>{{ translate('Instruction') }}</h4>
                                <ul class="m-0 pl-4">
                                    <li>
                                        {{ translate('Download the multi-language template and fill it with proper data in English, Arabic, and Kurdish.') }}
                                    </li>
                                    <li>
                                        {{ translate('Each restaurant must have names and addresses in all three languages.') }}
                                    </li>
                                    <li>
                                        {{ translate('You can download the example file to understand the multi-language format.') }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="export-steps-item-2 h-100">
                                <div class="top">
                                    <div>
                                        <h3 class="fs-20">{{translate('Step 2')}}</h3>
                                        <div>
                                            {{translate('Prepare Multi-Language Data')}}
                                        </div>
                                    </div>
                                    <img src="{{asset('assets/admin/img/bulk-import-2.png')}}" alt="">
                                </div>
                                <h4>{{ translate('Instruction') }}</h4>
                                <ul class="m-0 pl-4">
                                    <li>
                                        {{ translate('Fill name_en, name_ar, name_ku columns for English, Arabic, and Kurdish names.') }}
                                    </li>
                                    <li>
                                        {{ translate('Fill address_en, address_ar, address_ku columns for addresses in all languages.') }}
                                    </li>
                                    <li>
                                        {{ translate('Make sure phone numbers and email addresses are unique.') }}
                                    </li>
                                    <li>
                                        {{ translate('Place restaurant images in public/storage/restaurant_images/ folder.') }}
                                    </li>
                                    <li>
                                        {{ translate('Use proper UTF-8 encoding to support Arabic and Kurdish characters.') }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="export-steps-item-2 h-100">
                                <div class="top">
                                    <div>
                                        <h3 class="fs-20">{{translate('Step 3')}}</h3>
                                        <div>
                                            {{translate('Import Multi-Language Data')}}
                                        </div>
                                    </div>
                                    <img src="{{asset('assets/admin/img/bulk-import-3.png')}}" alt="">
                                </div>
                                <h4>{{ translate('Instruction') }}</h4>
                                <ul class="m-0 pl-4">
                                    <li>
                                        {{ translate('Upload your CSV file with multi-language restaurant data.') }}
                                    </li>
                                    <li>
                                        {{ translate('All restaurants will be imported with automatic translation support.') }}
                                    </li>
                                    <li>
                                        {{ translate('Vendors will be auto-created with default password: 12345678') }}
                                    </li>
                                    <li>
                                        {{ translate('Images will be automatically processed and linked.') }}
                                    </li>
                                    <li>
                                        {{ translate('API and mobile apps will automatically show correct language based on user preference.') }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center pb-4">
                    <h3 class="mb-3 export--template-title font-regular">{{translate('Download Multi-Language Template')}}</h3>
                    <div class="btn--container justify-content-center export--template-btns">
                        <a href="{{route('admin.store.download-multilang-sample')}}" class="btn btn--primary">
                            <i class="fa fa-download"></i> {{ translate('Multi-Language CSV Template') }}
                        </a>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> 
                            {{ translate('This template includes columns for English (en), Arabic (ar), and Kurdish (ku) languages') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <form class="product-form" id="multilang_import_form" action="{{route('admin.store.bulk-import')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="button" id="btn_value">
            
            <div class="card mt-2 rest-part">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <h5 class="text-capitalize mb-3">{{ translate('Multi-Language Import Options') }}</h5>
                            <div class="module-radio-group border rounded">
                                <label class="form-check form--check">
                                    <input class="form-check-input" value="import" type="radio" name="upload_type" checked>
                                    <span class="form-check-label py-20">
                                        <i class="fa fa-plus-circle text-success"></i>
                                        {{ translate('Import New Multi-Language Stores') }}
                                    </span>
                                </label>
                            </div>
                            
                            <div class="mt-3 p-3 bg-light rounded">
                                <h6><i class="fa fa-globe text-primary"></i> {{ translate('Supported Languages') }}</h6>
                                <div class="d-flex gap-3">
                                    <span class="badge badge-soft-primary">🇬🇧 English (en)</span>
                                    <span class="badge badge-soft-success">🇸🇦 Arabic (ar)</span>
                                    <span class="badge badge-soft-warning">Kurdistan Kurdish (ku)</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6">
                            <h5 class="text-capitalize mb-3">{{ translate('Upload Multi-Language CSV File') }}</h5>
                            <label class="uploadDnD d-block">
                                <div class="form-group inputDnD input_image input_image_edit position-relative">
                                    <div class="upload-text">
                                        <div>
                                            <img src="{{asset('assets/admin/img/bulk-import-3.png')}}" alt="">
                                        </div>
                                        <div class="filename">{{translate('Upload CSV file with multi-language restaurant data')}}</div>
                                        <div class="mt-2">
                                            <small class="text-muted">{{ translate('Accepts: .csv files with UTF-8 encoding') }}</small>
                                        </div>
                                    </div>
                                    <input type="file" name="restaurants_file" class="form-control-file text--primary font-weight-bold action-upload-section-dot-area" id="restaurants_file" accept=".csv">
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Image Upload Instructions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fa fa-image"></i> {{ translate('Image Upload Instructions') }}</h6>
                                <ul class="mb-0">
                                    <li>{{ translate('Place restaurant images in: public/storage/restaurant_images/') }}</li>
                                    <li>{{ translate('Use filenames exactly as specified in CSV (logo_filename, cover_photo_filename)') }}</li>
                                    <li>{{ translate('Supported formats: JPG, PNG, WEBP') }}</li>
                                    <li>{{ translate('Recommended sizes: Logo 200x200px, Cover 600x300px') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="btn--container justify-content-end mt-3">
                        <button id="reset_btn" type="reset" class="btn btn--reset">
                            <i class="fa fa-refresh"></i> {{translate('messages.reset')}}
                        </button>
                        <button type="button" class="btn btn--primary multilang_import_btn">
                            <i class="fa fa-globe"></i> {{translate('Import Multi-Language Stores')}}
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <!-- End Multi-Language Store Import -->

        <div class="row g-2">
            <div class="col-lg-8 col--xl-8">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center __gap-12px">
                            <div class="__gross-amount">
                                <h6>$855.8K</h6>
                                <span>Gross Sale</span>
                            </div>
                            <div class="chart--label __chart-label p-0 move-left-100 ml-auto">
                                <span class="indicator chart-bg-2"></span>
                                <span class="info">
                                    Sale (2022)
                                </span>
                            </div>
                            <select class="custom-select border-0 text-center w-auto ml-auto">
                                <option>
                                    {{translate('This Month')}}
                                </option>
                                <option>
                                    {{translate('This Year')}}
                                </option>
                            </select>
                        </div>
                        <div id="grow-sale-chart"></div>
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
                        <select class="custom-select border-0 text-center w-auto user_overview_stats_update" name="user_overview">
                            <option
                                value="this_month" {{$params['user_overview'] == 'this_month'?'selected':''}}>
                                {{translate('This month')}}
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
          name: 'Gross Sale',
          data: [60, 40, 80, 31, 42, 109, 100, 50, 30, 80, 65, 35]
        }],
          chart: {
          height: 350,
          type: 'area',
          toolbar: {
            show:false
        }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth',
          width: 2,
        },
        fill: {
            type: 'gradient',
            colors: ['#76ffcd'],
        },
        xaxis: {
        //   type: 'datetime',
          categories: ["{{ translate('Jan') }}", "{{ translate('Feb') }}", "{{ translate('Mar') }}", "{{ translate('Apr') }}", "{{ translate('May') }}", "{{ translate('Jun') }}", "{{ translate('Jul') }}", "{{ translate('Aug') }}", "{{ translate('Sep') }}", "{{ translate('Oct') }}", "{{ translate('Nov') }}", "{{ translate('Dec') }}" ]
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
                    $('#user-overview-board').html(data.user_overview);
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
        })

        $('.user_overview_stats_update').on('change', function (){
            let type = $(this).val();
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
        })

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

        $('#reset_btn').click(function(){
            $('#restaurants_file').val('');
            $('.filename').text('{{translate('Upload CSV file with multi-language restaurant data')}}');
        });

        $(".action-upload-section-dot-area").on("change", function () {
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                reader.onload = () => {
                    let imgName = this.files[0].name;
                    $(this).closest(".uploadDnD").find('.filename').text(imgName);
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        $(document).on("click", ".multilang_import_btn", function(e){
            e.preventDefault();
            
            let fileInput = $('#restaurants_file');
            if (!fileInput.val()) {
                Swal.fire({
                    title: '{{ translate('No File Selected') }}',
                    text: '{{ translate('Please select a CSV file to upload') }}',
                    type: 'warning',
                    confirmButtonText: '{{ translate('OK') }}'
                });
                return;
            }

            let upload_type = $('input[name="upload_type"]:checked').val();
            multilangImportConfirm(upload_type);
        });

        function multilangImportConfirm(data) {
            Swal.fire({
                title: '{{ translate('Import Multi-Language Restaurants?') }}',
                html: `
                    <div class="text-left">
                        <p>{{ translate('This will import restaurants with support for:') }}</p>
                        <ul class="text-left">
                            <li>🇬🇧 {{ translate('English names and addresses') }}</li>
                            <li>🇸🇦 {{ translate('Arabic names and addresses') }}</li>
                            <li>Kurdistan {{ translate('Kurdish names and addresses') }}</li>
                        </ul>
                        <p class="text-warning">
                            <i class="fa fa-exclamation-triangle"></i> 
                            {{ translate('Make sure your CSV file has proper UTF-8 encoding for Arabic and Kurdish text.') }}
                        </p>
                    </div>
                `,
                type: 'question',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{translate('messages.no')}}',
                confirmButtonText: '{{translate('Yes, Import Now')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $('#btn_value').val(data);
                    $("#multilang_import_form").submit();
                }
            });
        }
    </script>
@endpush
