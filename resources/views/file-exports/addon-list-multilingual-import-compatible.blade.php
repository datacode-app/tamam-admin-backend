<div class="row">
    <div class="col-lg-12 text-center">
        <h1>{{ Config::get('module.current_module_type') == 'food' ? translate('Food_AddOn_List_Multilingual') : translate('AddOn_List_Multilingual') }}</h1>
    </div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>{{ translate('Filter_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ translate('Module') }}: {{ $data['module_name'] ?? translate('All') }}
                        <br>
                        {{ translate('Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}
                        <br>
                        {{ translate('Languages') }}: Arabic, Kurdish, English
                        <br>
                        {{ translate('Total_AddOns') }}: {{ $data['data']->count() }}
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>

                <tr>
                    <!-- Import Template Compatible Headers -->
                    <th>Id</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>StoreId</th>
                    <th>Status</th>
                    <!-- Multilingual Fields (Import Compatible) -->
                    <th>name_ar</th>
                    <th>name_ckb</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['multilingual_addons'] as $key => $addon)
                    <tr>
                        <!-- Import Template Compatible Data -->
                        <td>{{ $addon['id'] ?? '' }}</td>
                        <td>{{ $addon['name'] ?? '' }}</td>
                        <td>{{ $addon['price'] ?? 0 }}</td>
                        <td>{{ $addon['store_id'] ?? '' }}</td>
                        <td>{{ ($addon['status'] ?? 0) == 1 ? 'active' : 'inactive' }}</td>
                        
                        <!-- Multilingual Fields -->
                        <td>{{ $addon['name_ar'] ?? '' }}</td>
                        <td>{{ $addon['name_ckb'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>