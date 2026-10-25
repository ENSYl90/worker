<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Models\User;
use App\Traits\GoogleMapApiTrait;
use Illuminate\Http\Request;
use Exception;

class ProductController extends Controller
{

    use GoogleMapApiTrait;

    public function index(Request $request)
    {
        //get user from auth
        $user = User::find(auth('api')->id());

        // Sanitize array inputs
        if ($request->has('category_ids') && !is_array($request->category_ids)) {
            $request->merge(['category_ids' => json_decode($request->category_ids, true) ?? []]);
        }
        if ($request->has('sub_category_ids') && !is_array($request->sub_category_ids)) {
            $request->merge(['sub_category_ids' => json_decode($request->sub_category_ids, true) ?? []]);
        }
        if ($request->has('menu_ids') && !is_array($request->menu_ids)) {
            $request->merge(['menu_ids' => json_decode($request->menu_ids, true) ?? []]);
        }

        if ($request->type == "vendor") {
            return Product::with("categories", "sub_categories", "menus")->inorder()->when($request->keyword, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    //use where raw to search
                    return $q->whereRaw('name LIKE ?', ["%$request->keyword%"])
                        ->orWhereRaw('description LIKE ?', ["%$request->keyword%"])
                        ->orWhereRaw('barcode LIKE ?', ["%$request->keyword%"]);

                });
            })->latest()
                ->when($user != null && $user->hasRole('manager'), function ($query) use ($user) {
                    return $query->where("vendor_id", $user->vendor_id);
                })->paginate($this->productsPerPage);
        }
        return Product::active()
            ->currentlyOpen()
            ->when($request->vendor_type_id, function ($query) use ($request) {
                return $query->whereHas('vendor', function ($query) use ($request) {
                    return $query->active()->where('vendor_type_id', $request->vendor_type_id);
                });
            })
            ->when($request->vendor_id, function ($query) use ($request) {
                return $query->active()->where('vendor_id', $request->vendor_id);
            })
            ->inorder()->when($request->type == "best", function ($query) {
                return $query->withCount('sales')->orderBy('sales_count', 'DESC');
            })
            ->when($request->keyword, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    //use where raw to search
                    return $q->whereRaw('name LIKE ?', ["%$request->keyword%"])
                        ->orWhereRaw('description LIKE ?', ["%$request->keyword%"])
                        ->orWhereRaw('barcode LIKE ?', ["%$request->keyword%"]);
                });
            })
            ->when($request->category_id, function ($query) use ($request) {
                return $query->whereHas('categories', function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id);
                });
            })
            //handle array of category ids
            ->when($request->category_ids, function ($query) use ($request) {
                return $query->whereHas('categories', function ($query) use ($request) {
                    return $query->whereIn('category_id', $request->category_ids);
                });
            })
            //show products tied to a certain sub cateogry
            ->when($request->sub_category_id, function ($query) use ($request) {
                return $query->whereHas('sub_categories', function ($query) use ($request) {
                    return $query->where('subcategory_id', $request->sub_category_id);
                });
            })
            //handle array of sub category ids
            ->when($request->sub_category_ids, function ($query) use ($request) {
                return $query->whereHas('sub_categories', function ($query) use ($request) {
                    return $query->whereIn('subcategory_id', $request->sub_category_ids);
                });
            })
            //show products tied to a certain menu
            ->when($request->menu_id, function ($query) use ($request) {
                return $query->whereHas('menus', function ($query) use ($request) {
                    return $query->where('menu_id', $request->menu_id);
                });
            })
            //handle array of menu ids
            ->when($request->menu_ids, function ($query) use ($request) {
                return $query->whereHas('menus', function ($query) use ($request) {
                    return $query->whereIn('menu_id', $request->menu_ids);
                });
            })
            ->when($request->is_open, function ($query) use ($request) {
                return $query->where('is_open', "=", $request->is_open);
            })
            ->when($request->type == "you", function ($query) {

                if (auth('sanctum')->user()) {
                    return $query->whereHas('purchases')->withCount('purchases')->orderBy('purchases_count', 'DESC');
                } else {
                    return $query->inRandomOrder();
                }
            })
            // NEW ONES
            ->when($request->type == "flash", function ($query) {
                return $query->where('discount_price', ">", 0)->orderBy('discount_price', 'DESC');
            })
            ->when($request->type == "new", function ($query) {
                return $query->orderBy('created_at', 'DESC');
            })
            ->when($request->type == "featured", function ($query) {
                return $query->where('featured', 1);
            })
            // NEW ONES END HERE
            ->when(fetchDataByLocation() && $request->latitude, function ($query) use ($request) {
                return $query->byDeliveryZone($request->latitude, $request->longitude);
            })
            //order by in_order
            ->orderBy('in_order', 'ASC')
            ->paginate($this->productsPerPage);
    }

    public function show(Request $request, $id)
    {

        try {

            $optionGroupIds = Option::whereHas('products', function ($query) use ($id) {
                return $query->where('product_id', "=", $id);
            })->pluck('option_group_id');
            //
            $optionGroups = OptionGroup::active()->with([
                'options' => function ($query) use ($id) {
                    $query->whereHas('products', function ($query) use ($id) {
                        return $query->where('product_id', "=", $id);
                    });
                }
            ])->whereIn('id', $optionGroupIds)->get();

            $product = Product::findorfail($id);
            $product["option_groups"] = $optionGroups;
            return response()->json($product);
        } catch (\Exception $ex) {
            return response()->json([
                "message" => $ex->getMessage() ?? __("No Product Found")
            ], 400);
        }
    }



}