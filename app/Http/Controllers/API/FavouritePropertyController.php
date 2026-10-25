<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Favourite;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavouritePropertyController extends Controller
{

    public function index(Request $request)
    {
        $favourites = Favourite::with([
            'property' => function ($query) {
                // Apply the same scopes we generally do
                $query->active()->withCount('ratings');
            }
        ])
            ->where('user_id', Auth::id())
            ->whereNotNull('property_id')
            ->paginate($request->per_page ?? 20);

        return response()->json($favourites);
    }

    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        try {
            $model = Favourite::where('user_id', Auth::id())
                ->where('property_id', $request->property_id)
                ->first();

            if (!empty($model)) {
                return response()->json([
                    "message" => __("Property already in wishlist")
                ], 400);
            }

            $model = new Favourite();
            $model->user_id = Auth::id();
            $model->property_id = $request->property_id;
            $model->save();

            return response()->json([
                "message" => __("Property added to wishlist"),
                "favourite" => $model
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                "message" => $ex->getMessage()
            ], 400);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            // Can handle deleting by property_id directly for convenience
            $favourite = Favourite::where('user_id', Auth::id())
                ->where(function ($q) use ($id) {
                    $q->where('id', $id)
                        ->orWhere('property_id', $id);
                })
                ->whereNotNull('property_id')
                ->firstOrFail();

            $favourite->delete();

            return response()->json([
                "message" => __("Property removed from wishlist")
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                "message" => __("Wishlist item not found")
            ], 404);
        }
    }
}
