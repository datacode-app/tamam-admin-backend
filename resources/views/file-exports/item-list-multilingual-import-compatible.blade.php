<div class="row">
    <div class="col-lg-12 text-center">
        <h1>{{ Config::get('module.current_module_type') == 'food' ? translate('Food_List_Multilingual') : translate('Item_List_Multilingual') }}</h1>
    </div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>{{ translate('Filter_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ translate('Store') }}: {{ $data['store'] ?? translate('All') }}
                        <br>
                        {{ translate('Module') }}: {{ $data['module_name'] ?? translate('N/A') }}
                        <br>
                        {{ translate('category') }}: {{ $data['category'] ?? translate('N/A') }}
                        <br>
                        {{ translate('Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}
                        <br>
                        {{ translate('Languages') }}: Arabic, Kurdish, English
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
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
                    <th>Description</th>
                    <th>CategoryId</th>
                    <th>SubCategoryId</th>
                    <th>StoreId</th>
                    <th>Price</th>
                    <th>DiscountType</th>
                    <th>Discount</th>
                    <th>Unit</th>
                    <th>Stock</th>
                    <th>MaxOrderQuantity</th>
                    <th>Veg</th>
                    <th>Image</th>
                    <th>AvailableTimeStarts</th>
                    <th>AvailableTimeEnds</th>
                    <th>Status</th>
                    <th>Recommended</th>
                    <th>PopularFlag</th>
                    <th>UnitId</th>
                    <th>Variations</th>
                    <th>ChoiceOptions</th>
                    <th>AddOns</th>
                    <th>Attributes</th>
                    <th>ModuleId</th>
                    <!-- Multilingual Fields (Import Compatible) -->
                    <th>name_ar</th>
                    <th>name_ckb</th>
                    <th>description_ar</th>
                    <th>description_ckb</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['multilingual_items'] as $key => $item)
                    <tr>
                        <!-- Import Template Compatible Data -->
                        <td>{{ $item['id'] ?? '' }}</td>
                        <td>{{ $item['name'] ?? '' }}</td>
                        <td>{{ $item['description'] ?? '' }}</td>
                        <td>{{ $item['category_ids'] ?? '' }}</td>
                        <td>{{ $item['sub_category_id'] ?? '' }}</td>
                        <td>{{ $item['store_id'] ?? '' }}</td>
                        <td>{{ $item['price'] ?? 0 }}</td>
                        <td>{{ $item['discount_type'] ?? 'amount' }}</td>
                        <td>{{ $item['discount'] ?? 0 }}</td>
                        <td>{{ $item['original_item']?->unit?->unit ?? '' }}</td>
                        <td>{{ $item['stock'] ?? 0 }}</td>
                        <td>{{ $item['maximum_cart_quantity'] ?? 999 }}</td>
                        <td>{{ ($item['veg'] ?? 0) == 1 ? 'yes' : 'no' }}</td>
                        <td>{{ $item['image'] ?? '' }}</td>
                        <td>{{ $item['available_time_starts'] ?? '00:00:00' }}</td>
                        <td>{{ $item['available_time_ends'] ?? '23:59:59' }}</td>
                        <td>{{ ($item['status'] ?? 0) == 1 ? 'active' : 'inactive' }}</td>
                        <td>{{ ($item['recommended'] ?? 0) == 1 ? 'yes' : 'no' }}</td>
                        <td>{{ ($item['popular'] ?? 0) == 1 ? 'yes' : 'no' }}</td>
                        <td>{{ $item['unit_id'] ?? '' }}</td>
                        <td>{{ is_array($item['food_variations'] ?? null) ? json_encode($item['food_variations']) : ($item['food_variations'] ?? '') }}</td>
                        <td>{{ is_array($item['choice_options'] ?? null) ? json_encode($item['choice_options']) : ($item['choice_options'] ?? '') }}</td>
                        <td>{{ is_array($item['add_ons'] ?? null) ? json_encode($item['add_ons']) : ($item['add_ons'] ?? '') }}</td>
                        <td>{{ is_array($item['attributes'] ?? null) ? json_encode($item['attributes']) : ($item['attributes'] ?? '') }}</td>
                        <td>{{ $item['module_id'] ?? Config::get('module.current_module_id') }}</td>
                        
                        <!-- Multilingual Fields -->
                        <td>{{ $item['name_ar'] ?? '' }}</td>
                        <td>{{ $item['name_ckb'] ?? '' }}</td>
                        <td>{{ $item['description_ar'] ?? '' }}</td>
                        <td>{{ $item['description_ckb'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>