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
                </tr>

                <tr>
                    <th>{{ translate('sl') }}</th>
                    <th>{{ translate('Image') }}</th>
                    <th>{{ translate('Item_Name') }} (EN)</th>
                    <th>{{ translate('Item_Name') }} (AR)</th>
                    <th>{{ translate('Item_Name') }} (KU)</th>
                    <th>{{ translate('Description') }} (EN)</th>
                    <th>{{ translate('Description') }} (AR)</th>
                    <th>{{ translate('Description') }} (KU)</th>
                    <th>{{ translate('Category_Name') }}</th>
                    <th>{{ translate('Sub_Category_Name') }}</th>
                    @if (Config::get('module.current_module_type') == 'food')
                        <th>{{ translate('Food_Type') }}</th>
                    @else
                        <th>{{ translate('Available_Stock') }}</th>
                    @endif
                    <th>{{ translate('Price') }}</th>
                    <th>{{ translate('Available_Variations') }}</th>
                    @if (Config::get('module.current_module_type') == 'food')
                        <th>{{ translate('Available_Addons') }}</th>
                    @else
                        <th>{{ translate('Item_Unit') }}</th>
                    @endif
                    <th>{{ translate('Discount') }}</th>
                    <th>{{ translate('Discount_Type') }}</th>
                    <th>{{ translate('Available_From') }}</th>
                    <th>{{ translate('Available_Till') }}</th>
                    <th>{{ translate('Store_Name') }}</th>
                    <th>{{ translate('Tags') }}</th>
                    <th>{{ translate('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['multilingual_items'] as $key => $item)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>&nbsp;</td>
                        
                        <!-- Multilingual Name Fields -->
                        <td>{{ $item['name_en'] ?? $item['name'] ?? '' }}</td>
                        <td>{{ $item['name_ar'] ?? '' }}</td>
                        <td>{{ $item['name_ckb'] ?? '' }}</td>
                        
                        <!-- Multilingual Description Fields -->
                        <td>{{ $item['description_en'] ?? $item['description'] ?? '' }}</td>
                        <td>{{ $item['description_ar'] ?? '' }}</td>
                        <td>{{ $item['description_ckb'] ?? '' }}</td>
                        
                        <!-- Category Information -->
                        <td>{{ \App\CentralLogics\Helpers::get_category_name($item['category_ids']) }}</td>
                        <td>{{ \App\CentralLogics\Helpers::get_sub_category_name($item['category_ids']) ?? translate('N/A') }}</td>
                        
                        <!-- Food Type / Stock -->
                        <td>
                            @if (Config::get('module.current_module_type') == 'food')
                                {{ ($item['veg'] ?? 0) == 1 ? translate('Veg') : translate('Non_Veg') }}
                            @else
                                {{ $item['stock'] ?? 0 }}
                            @endif
                        </td>
                        
                        <!-- Price -->
                        <td>{{ \App\CentralLogics\Helpers::format_currency($item['price'] ?? 0) }}</td>
                        
                        <!-- Variations -->
                        <td>
                            @if (Config::get('module.current_module_type') == 'food')
                                {{ \App\CentralLogics\Helpers::get_food_variations($item['food_variations'] ?? []) == "  " ? translate('N/A') : \App\CentralLogics\Helpers::get_food_variations($item['food_variations'] ?? []) }}
                            @else
                                {{ \App\CentralLogics\Helpers::get_attributes($item['choice_options'] ?? []) == "  " ? translate('N/A') : \App\CentralLogics\Helpers::get_attributes($item['choice_options'] ?? []) }}
                            @endif
                        </td>
                        
                        <!-- Add-ons / Unit -->
                        <td>
                            @if (Config::get('module.current_module_type') == 'food')
                                {{ \App\CentralLogics\Helpers::get_addon_data($item['add_ons'] ?? []) == 0 ? translate('N/A') : \App\CentralLogics\Helpers::get_addon_data($item['add_ons'] ?? []) }}
                            @else
                                {{ $item['original_item']?->unit?->unit ?? translate('N/A') }}
                            @endif
                        </td>
                        
                        <!-- Discount -->
                        <td>{{ ($item['discount'] ?? 0) == 0 ? translate('N/A') : $item['discount'] }}</td>
                        <td>{{ $item['discount_type'] ?? 'amount' }}</td>
                        
                        <!-- Availability Times -->
                        <td>{{ Config::get('module.current_module_type') != 'grocery' && isset($item['available_time_starts']) ? \Carbon\Carbon::parse($item['available_time_starts'])->format("H:i A") : translate('N/A') }}</td>
                        <td>{{ Config::get('module.current_module_type') != 'grocery' && isset($item['available_time_ends']) ? \Carbon\Carbon::parse($item['available_time_ends'])->format("H:i A") : translate('N/A') }}</td>
                        
                        <!-- Store Name -->
                        <td>{{ $item['original_item']?->store?->name ?? '' }}</td>
                        
                        <!-- Tags -->
                        <td>
                            @if (isset($data['table']) && $data['table'] == 'TempProduct')
                                @php($tagids = json_decode($item['tag_ids'] ?? '[]') ?? [])
                                @php($tags = \App\Models\Tag::whereIn('id', $tagids)->get('tag'))
                                @forelse($tags as $c) {{ $c->tag . ',' }} @empty {{ translate('N/A') }} @endforelse
                            @else
                                @forelse ($item['original_item']->tags ?? [] as $c) {{ $c->tag . ',' }} @empty {{ translate('N/A') }} @endforelse
                            @endif
                        </td>
                        
                        <!-- Status -->
                        <td>
                            @if (isset($data['table']) && $data['table'] == 'TempProduct')
                                {{ ($item['is_rejected'] ?? 0) == 1 ? translate('Rejected') : translate('Pending') }}
                            @else
                                {{ ($item['status'] ?? 0) == 1 ? translate('Active') : translate('Inactive') }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>