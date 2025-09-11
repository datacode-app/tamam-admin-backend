<div class="row">
    <div class="col-lg-12 text-center">
        <h1>{{ translate('Store_List_Multilingual') }}</h1>
    </div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>{{ translate('Filter_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ translate('Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}
                        <br>
                        {{ translate('Zone') }}: {{ $data['zone'] ?? translate('All') }}
                        <br>
                        {{ translate('Languages') }}: Arabic, Kurdish, English
                        <br>
                        {{ translate('Total_Stores') }}: {{ $data['data']->count() }}
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>

                <tr>
                    <!-- Import Template Compatible Headers -->
                    <th>Id</th>
                    <th>Name</th>
                    <th>Logo</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <!-- Multilingual Fields (Import Compatible) -->
                    <th>name_ar</th>
                    <th>name_ckb</th>
                    <th>address_ar</th>
                    <th>address_ckb</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['multilingual_stores'] as $key => $store)
                    <tr>
                        <!-- Import Template Compatible Data -->
                        <td>{{ $store['id'] ?? '' }}</td>
                        <td>{{ $store['name'] ?? '' }}</td>
                        <td>{{ $store['logo'] ?? '' }}</td>
                        <td>{{ $store['email'] ?? '' }}</td>
                        <td>{{ $store['phone'] ?? '' }}</td>
                        <td>{{ $store['address'] ?? '' }}</td>
                        <td>{{ ($store['status'] ?? 0) == 1 ? 'active' : 'inactive' }}</td>
                        <td>{{ ($store['featured'] ?? 0) == 1 ? 'yes' : 'no' }}</td>
                        
                        <!-- Multilingual Fields -->
                        <td>{{ $store['name_ar'] ?? '' }}</td>
                        <td>{{ $store['name_ckb'] ?? '' }}</td>
                        <td>{{ $store['address_ar'] ?? '' }}</td>
                        <td>{{ $store['address_ckb'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>