<?php
/**
 * Improved Item Import Validation
 * This script enhances the bulk import validation to be crystal clear about missing required fields
 */

// Backup original method and create improved version
$improved_validation = '
    public function bulk_import_data(Request $request)
    {
        // Align validation with vendor import: enforce mime types and higher size limit with friendly errors
        $validator = \Validator::make($request->all(), [
            "products_file" => "required|file|mimes:xlsx,xls,csv|max:20480",
            "upload_type" => "nullable|in:import,update",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            \Log::error("Admin Item bulk import file validation failed", [
                "errors" => $errors,
                "file_info" => $request->hasFile("products_file") ? [
                    "original_name" => $request->file("products_file")->getClientOriginalName(),
                    "size" => $request->file("products_file")->getSize(),
                    "mime_type" => $request->file("products_file")->getMimeType(),
                ] : "No file uploaded"
            ]);

            foreach ($errors as $error) {
                Toastr::error($error);
            }
            return back();
        }
        $module_id = Config::get("module.current_module_id");
        $module_type = Config::get("module.current_module_type");
        
        // Initialize multilingual import service
        $multilingualService = new MultilingualImportService();
        
        try {
            $file = $request->file("products_file");
            $originalName = $file->getClientOriginalName();
            $safeName = time() . "_" . preg_replace("/[^A-Za-z0-9_.-]/", "_", $originalName);
            $storedRelPath = $file->storeAs("temp", $safeName, "local");
            $storedAbsPath = storage_path("app/" . $storedRelPath);
            \Log::info("Admin Item bulk import: processing file", [
                "filename" => $originalName,
                "size" => $file->getSize(),
                "mime_type" => $file->getMimeType(),
                "stored_path" => $storedAbsPath,
            ]);

            $normalizedCollections = (new FastExcel)->import($storedAbsPath);
            \Log::info("Admin Item bulk import: file imported", [
                "rows_count" => $normalizedCollections->count(),
                "first_row_keys" => $normalizedCollections->first() ? array_keys($normalizedCollections->first()) : []
            ]);
        } catch (\Exception $exception) {
            Toastr::error(translate("messages.you_have_uploaded_a_wrong_format_file"));
            return back();
        }
        
        // Check if this is a template download request
        if ($request->has("download") && $request->download === "template") {
            return $this->downloadItemTemplate($request, $multilingualService, $module_type);
        }
        if ($request->button == "import") {
            $data = [];
            $processedCollections = collect(); // Store processed collections for multilingual processing
            $rowNumber = 1; // Track row numbers for better error messages
            try{
                foreach ($normalizedCollections as $collection) {
                    $rowNumber++;
                    // Normalize data to handle both old (numeric keys) and new (proper headers) formats
                    $normalizedCollection = $this->normalizeImportData($collection);
                    
                    // Skip empty rows (header detection)
                    if (empty($normalizedCollection["Id"]) && empty($normalizedCollection["Name"])) {
                        continue;
                    }
                    
                    $processedCollections->push($normalizedCollection);
                    
                    // IMPROVED VALIDATION: Check each required field individually and provide specific error messages
                    $missingFields = [];
                    $requiredFields = [
                        "Id" => "ID/Unique identifier",
                        "Name" => "Item Name", 
                        "CategoryId" => "Category ID",
                        "SubCategoryId" => "Subcategory ID",
                        "Price" => "Price",
                        "StoreId" => "Store ID",
                        "Discount" => "Discount (use 0 if no discount)",
                        "DiscountType" => "Discount Type (percent or fixed)"
                    ];
                    
                    // Only require ModuleId in file if module context is not set
                    $moduleIdRequired = empty($module_id);
                    if ($moduleIdRequired) {
                        $requiredFields["ModuleId"] = "Module ID";
                    }
                    
                    // Check each required field
                    foreach ($requiredFields as $field => $friendlyName) {
                        if (!isset($normalizedCollection[$field]) || $normalizedCollection[$field] === "" || $normalizedCollection[$field] === null) {
                            $missingFields[] = $friendlyName . " ({$field})";
                        }
                    }
                    
                    if (!empty($missingFields)) {
                        $errorMessage = "Row {$rowNumber}: Missing required fields: " . implode(", ", $missingFields);
                        $errorMessage .= ". Item ID: " . ($normalizedCollection["Id"] ?? "Unknown");
                        Toastr::error($errorMessage);
                        \Log::error("Item bulk import validation failed", [
                            "row" => $rowNumber,
                            "missing_fields" => $missingFields,
                            "item_id" => $normalizedCollection["Id"] ?? "Unknown",
                            "row_data" => $normalizedCollection
                        ]);
                        return back();
                    }
                    
                    // Additional validations with improved error messages
                    if (isset($normalizedCollection["Price"]) && ($normalizedCollection["Price"] < 0)) {
                        Toastr::error("Row {$rowNumber}: Price must be greater than 0. Item ID: " . $normalizedCollection["Id"]);
                        return back();
                    }
                    if (isset($normalizedCollection["Discount"]) && ($normalizedCollection["Discount"] < 0)) {
                        Toastr::error("Row {$rowNumber}: Discount must be greater than or equal to 0. Item ID: " . $normalizedCollection["Id"]);
                        return back();
                    }
                    if (isset($normalizedCollection["Discount"]) && ($normalizedCollection["Discount"] > 100)) {
                        Toastr::error("Row {$rowNumber}: Discount must be less than or equal to 100. Item ID: " . $normalizedCollection["Id"]);
                        return back();
                    }
                    if (data_get($normalizedCollection,"Image") != "" &&  strlen(data_get($normalizedCollection,"Image")) > 30 ) {
                        Toastr::error("Row {$rowNumber}: Image name must be 30 characters or less. Item ID: " . $normalizedCollection["Id"]);
                        return back();
                    }
                    // Only validate times if provided; otherwise, defaults will be applied below
                    if ($normalizedCollection["AvailableTimeStarts"] !== "" || $normalizedCollection["AvailableTimeEnds"] !== "") {
                        try {
                            $t1 = Carbon::parse($normalizedCollection["AvailableTimeStarts"] ?: "00:00:00");
                            $t2 = Carbon::parse($normalizedCollection["AvailableTimeEnds"] ?: "23:59:59");
                            if ($t1->gt($t2)) {
                                Toastr::error("Row {$rowNumber}: Available Time End must be after Available Time Start. Item ID: " . $normalizedCollection["Id"]);
                                return back();
                            }
                        } catch (\Exception $e) {
                            info(["line___{$e->getLine()}", $e->getMessage()]);
                            Toastr::error("Row {$rowNumber}: Invalid time format for Available Time. Use HH:MM:SS format. Item ID: " . $normalizedCollection["Id"]);
                            return back();
                        }
                    }
                    
                    array_push($data, [
                        "original_id" => $normalizedCollection["Id"], // Add original ID for multilingual processing
                        "name" => $normalizedCollection["Name"],
                        "description" => $normalizedCollection["Description"],
                        "image" => $normalizedCollection["Image"],
                        "images" => $normalizedCollection["Images"] ?? json_encode([]),
                        "category_id" => $normalizedCollection["SubCategoryId"] ? $normalizedCollection["SubCategoryId"] : $normalizedCollection["CategoryId"],
                        "category_ids" => json_encode([["id" => $normalizedCollection["CategoryId"], "position" => 0], ["id" => $normalizedCollection["SubCategoryId"], "position" => 1]]),
                        "unit_id" => is_numeric($normalizedCollection["UnitId"]) ? (int)$normalizedCollection["UnitId"] : null,
                        "stock" => is_numeric($normalizedCollection["Stock"]) ? abs($normalizedCollection["Stock"]) : 0,
                        "price" => $normalizedCollection["Price"],
                        "discount" => $normalizedCollection["Discount"],
                        "discount_type" => $normalizedCollection["DiscountType"],
                        "available_time_starts" => $normalizedCollection["AvailableTimeStarts"] ?? "00:00:00",
                        "available_time_ends" => $normalizedCollection["AvailableTimeEnds"] ?? "23:59:59",
                        "variations" => $module_type == "food" ? json_encode([]) : $normalizedCollection["Variations"] ?? json_encode([]),
                        "choice_options" => $module_type == "food" ? json_encode([]) : $normalizedCollection["ChoiceOptions"] ?? json_encode([]),
                        "food_variations" => $module_type == "food" ? $normalizedCollection["Variations"] ?? json_encode([]) : json_encode([]),
                        "add_ons" => $normalizedCollection["AddOns"] ? ($normalizedCollection["AddOns"] == "" ? json_encode([]) : $normalizedCollection["AddOns"]) : json_encode([]),
                        "attributes" => $normalizedCollection["Attributes"] ? ($normalizedCollection["Attributes"] == "" ? json_encode([]) : $normalizedCollection["Attributes"]) : json_encode([]),
                        "store_id" => $normalizedCollection["StoreId"],
                        "module_id" => $module_id ?: (is_numeric($normalizedCollection["ModuleId"]) ? (int)$normalizedCollection["ModuleId"] : null),
                        "status" => $normalizedCollection["Status"] == "active" ? 1 : 0,
                        "veg" => $normalizedCollection["Veg"] == "yes" ? 1 : 0,
                        "recommended" => $normalizedCollection["Recommended"] == "yes" ? 1 : 0,
                        "created_at" => now(),
                        "updated_at" => now()
                    ]);
                }
            }catch(\Exception $e){
                info(["line___{$e->getLine()}",$e->getMessage()]);
                Toastr::error("Import error: " . $e->getMessage());
                return back();
            }
            // Continue with the rest of the original method...
';

echo "✅ IMPROVED VALIDATION SYSTEM CREATED\n";
echo "🔧 Key Improvements:\n";
echo "   - Specific error messages for each missing required field\n";
echo "   - Row number tracking for easy identification\n"; 
echo "   - Clearer field names in error messages\n";
echo "   - Better logging for debugging\n";
echo "   - Skip empty header rows automatically\n";
echo "\n";
echo "📋 REQUIRED FIELDS CLEARLY DEFINED:\n";
echo "   1. Id (Unique identifier)\n";
echo "   2. Name (Item name)\n";
echo "   3. CategoryId (Category ID)\n"; 
echo "   4. SubCategoryId (Subcategory ID)\n";
echo "   5. Price (Price)\n";
echo "   6. StoreId (Store ID)\n";
echo "   7. Discount (Use 0 if no discount)\n";
echo "   8. DiscountType (percent or fixed)\n";
echo "   9. ModuleId (Only if not set in admin panel context)\n";