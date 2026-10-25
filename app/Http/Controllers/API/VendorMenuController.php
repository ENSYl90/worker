<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class VendorMenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole('manager')) {
            return response()->json([
                "message" => __("You are not allowed to perform this operation")
            ], 400);
        }

        return Menu::where('vendor_id', $user->vendor_id)
            ->when($request->keyword, function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->keyword . '%');
            })
            ->orderBy('in_order', 'ASC')
            ->paginate($this->perPage);
    }

    public function show($id)
    {
        $user = Auth::user();

        try {
            $menu = Menu::where('vendor_id', $user->vendor_id)->findOrFail($id);
            return response()->json($menu->load('products'));
        } catch (Exception $ex) {
            return $this->errorResponse($ex, 'Menu fetch failed', __("Menu not found"), 404);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole('manager') || !$user->vendor_id) {
            return response()->json([
                "message" => __("You are not allowed to perform this operation")
            ], 400);
        }

        $request->validate([
            'names' => 'required',
            'names.*' => 'string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $names = is_array($request->names) ? $request->names : [$request->names];

        try {
            DB::beginTransaction();

            $menus = collect($names)->map(function ($name) use ($user, $request) {
                return Menu::create([
                    'name' => $name,
                    'vendor_id' => $user->vendor_id,
                    'is_active' => $request->is_active ?? true,
                ]);
            });

            DB::commit();

            return response()->json([
                "message" => __("Menu created successfully"),
                "menu" => $menus->count() > 1 ? $menus : $menus->first(),
            ], 200);
        } catch (Exception $ex) {
            DB::rollback();
            return $this->errorResponse($ex, 'Menu creation failed', __("Menu creation failed"));
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $menu = Menu::where('vendor_id', $user->vendor_id)->find($id);

        if (!$menu || !$user->hasAnyRole('manager')) {
            return response()->json([
                "message" => __("You are not allowed to perform this operation")
            ], 400);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $menu->update($request->only(['name', 'is_active']));

            DB::commit();

            return response()->json([
                "message" => __("Menu updated successfully"),
                "menu" => $menu,
            ], 200);
        } catch (Exception $ex) {
            DB::rollback();
            return $this->errorResponse($ex, 'Menu update failed', __("Menu update failed"));
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();

        $menu = Menu::where('vendor_id', $user->vendor_id)->find($id);

        if (!$menu || !$user->hasAnyRole('manager')) {
            return response()->json([
                "message" => __("You are not allowed to perform this operation")
            ], 400);
        }

        try {
            DB::beginTransaction();

            // unattach products from the menu before deleting to avoid orphaned pivot issues
            $menu->products()->detach();

            $menu->delete();

            DB::commit();

            return response()->json([
                "message" => __("Menu deleted successfully"),
            ], 200);
        } catch (Exception $ex) {
            DB::rollback();
            return $this->errorResponse($ex, 'Menu deletion failed', __("Menu deletion failed"));
        }
    }
}
