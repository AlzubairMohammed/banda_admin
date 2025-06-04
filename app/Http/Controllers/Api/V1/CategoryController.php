<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CategoryLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Item;
use App\Models\PriorityList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function get_categories(Request $request,$search=null)
    {
        try {
            $category_list_default_status = BusinessSetting::where('key', 'category_list_default_status')->first()?->value ?? 1;
            $category_list_sort_by_general = PriorityList::where('name', 'category_list_sort_by_general')->where('type','general')->first()?->value ?? '';
            $zone_id=  $request->header('zoneId') ? json_decode($request->header('zoneId'), true) : [];
            $key = explode(' ', $search);
            $featured = $request->query('featured');
            $categories = Category::withCount(['products','childes'=> function($query){
                $query->where('status',1);
            } ])->with(['childes' => function($query)  {
                $query->where('status',1)->withCount(['products','childes'=> function($query){
                    $query->where('status',1);
                }]);
            }])
            ->where(['position'=>0,'status'=>1])
            ->when(config('module.current_module_data'), function($query){
                $query->module(config('module.current_module_data')['id']);
            })
            ->when($featured, function($query){
                $query->featured();
            })
            ->when($search, function($query)use($key){
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%". $value."%");
                    }
                });
            })
            ->when($category_list_default_status  == 1 , function ($query) {
                $query->orderBy('priority','desc');
            })


            ->when($category_list_default_status  != 1 &&  $category_list_sort_by_general == 'latest', function ($query) {
                $query->latest();
            })
            ->when($category_list_default_status  != 1 &&  $category_list_sort_by_general == 'oldest', function ($query) {
                $query->oldest();
            })
            ->when($category_list_default_status  != 1 &&  $category_list_sort_by_general == 'a_to_z', function ($query) {
                $query->orderby('name');
            })
            ->when($category_list_default_status  != 1 &&  $category_list_sort_by_general == 'z_to_a', function ($query) {
                $query->orderby('name','desc');
            })
            ->get();

            if(count($zone_id) > 0){
                foreach ($categories as $category) {
                    $productCountQuery = Item::active()
                        ->whereHas('store', function ($query) use ($zone_id) {
                            $query->whereIn('zone_id', $zone_id);
                        })
                        ->whereHas('category',function($q)use($category){
                            return $q->whereId($category->id)->orWhere('parent_id', $category->id);
                        })
                        ->withCount('orders');

                    $productCount = $productCountQuery->count();
                    $orderCount = $productCountQuery->sum('order_count');

                    $category['products_count'] = $productCount;
                    $category['order_count'] = $orderCount;
                }
                if($category_list_default_status  != 1 &&  $category_list_sort_by_general == 'order_count'){

                    $categories = $categories->sortByDesc('order_count')->values()->all();
                }
            }
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function get_childes($id)
    {
        try {
            $categories = Category::with('parent')->where(['parent_id' => $id,'status'=>1])->orderBy('priority','desc')->get();
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function get_products($id, Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id= $request->header('zoneId');

        $type = $request->query('type', 'all');

        $data = CategoryLogic::products($id, $zone_id, $request['limit'], $request['offset'], $type);
        $data['products'] = Helpers::product_data_formatting($data['products'] , true, false, app()->getLocale());
        return response()->json($data, 200);
    }

    public function get_category_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
            'category_ids' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id= $request->header('zoneId');

        $type = $request->query('type', 'all');
        $category_ids = $request['category_ids']?json_decode($request['category_ids']):'';

        $data = CategoryLogic::category_products($category_ids, $zone_id, $request['limit'], $request['offset'], $type);
        $data['products'] = Helpers::product_data_formatting($data['products'] , true, false, app()->getLocale());
        return response()->json($data, 200);
    }


    public function get_stores($id, Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id= $request->header('zoneId');
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $type = $request->query('type', 'all');

        $data = CategoryLogic::stores($id, $zone_id, $request['limit'], $request['offset'], $type,$longitude,$latitude);
        $data['stores'] = Helpers::store_data_formatting($data['stores'] , true);
        return response()->json($data, 200);
    }

    public function get_category_stores(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
            'category_ids' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id= $request->header('zoneId');
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $type = $request->query('type', 'all');
        $category_ids = $request['category_ids']?json_decode($request['category_ids']):'';

        $data = CategoryLogic::category_stores($category_ids, $zone_id, $request['limit'], $request['offset'], $type,$longitude,$latitude);
        $data['stores'] = Helpers::store_data_formatting($data['stores'] , true);
        return response()->json($data, 200);
    }



    public function get_all_products($id,Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id= $request->header('zoneId');

        try {
            return response()->json(Helpers::product_data_formatting(CategoryLogic::all_products($id, $zone_id), true, false, app()->getLocale()), 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function get_featured_category_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id= $request->header('zoneId');

        $type = $request->query('type', 'all');

        $data = CategoryLogic::featured_category_products($zone_id, $request['limit'], $request['offset'], $type);
        $data['products'] = Helpers::product_data_formatting($data['products'] , true, false, app()->getLocale());
        return response()->json($data, 200);
    }

    public function get_popular_category_list(){

        $avg_items=Item::where('order_count','>=', 1 )->avg('order_count') ?? 0;

        $items= Item::where('order_count','>', $avg_items )->pluck('category_ids');
        $get_popular_category_ids = $items->flatMap(function($categoryIds) {
            $categories = json_decode($categoryIds, true);
                return collect($categories)->pluck('id');
            })->unique();
        $categories= Category::when(config('module.current_module_data'), function($query){
            $query->module(config('module.current_module_data')['id']);
        })
        ->whereIn('id',$get_popular_category_ids->toArray())->where(['position'=>0,'status'=>1])->take(20)->get();
        return response()->json($categories, 200);
    }
}
