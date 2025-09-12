<div class="row">
    <div class="col-lg-12 text-center">
        <h1>{{ Config::get('module.current_module_type') == 'food' ? translate('Food_Category_List_Multilingual') : translate('Category_List_Multilingual') }}</h1>
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
                        {{ translate('Total_Categories') }}: {{ $data['data']->count() }}
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>

                <tr>
                    <!-- Import Template Compatible Headers -->
                    <th>Id</th>
                    <th>Name</th>
                    <th>ParentId</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <!-- Multilingual Fields (Import Compatible) -->
                    <th>name_ar</th>
                    <th>name_ckb</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['multilingual_categories'] as $key => $category)
                    <tr>
                        <!-- Import Template Compatible Data -->
                        <td>{{ $category['id'] ?? '' }}</td>
                        <td>{{ $category['name'] ?? '' }}</td>
                        <td>{{ $category['parent_id'] ?? 0 }}</td>
                        <td>{{ $category['position'] ?? 0 }}</td>
                        <td>{{ ($category['status'] ?? 0) == 1 ? 'active' : 'inactive' }}</td>
                        <td>{{ $category['priority'] ?? 0 }}</td>
                        
                        <!-- Multilingual Fields -->
                        <td>{{ $category['name_ar'] ?? '' }}</td>
                        <td>{{ $category['name_ckb'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>