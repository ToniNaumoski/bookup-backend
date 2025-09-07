<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\City;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CreateBusinessController extends Controller
{
    public function form(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'business') {
            return response()->json(['error' => 'Недозволено'], 403);
        }

        $business = Business::where('user_id', $user->id)->first();

       $categories = Category::whereNull('parent_id')
            ->with('children')
            ->get();
        $cities = City::all();

        if ($business) {
            return response()->json([
                'business' => [
                    'name' => $business->name,
                    'description' => $business->description,
                    'main_category' => $business->main_category,
                    'sub_category' => $business->sub_category,
                    'city' => $business->city,
                    'street' => $business->street,
                    'street_number' => $business->street_number,
                    'working_hours' => $business->working_hours,
                    'gallery' => $business->gallery,
                ],
                'categories' => $categories,
                'cities' => $cities,
            ]);
        } else {
            return response()->json([
                'business' => null,
                'categories' => $categories,
                'cities' => $cities,
            ]);
        }
    }
// public function store(Request $request)
// {
//     $user = Auth::user();

//     if ($user->role !== 'business') {
//         return response()->json(['error' => 'Недозволено'], 403);
//     }

//     // Use $data for validation
//     $data = $request->all();

//     // Decode working_hours JSON string to array
//     if (isset($data['working_hours']) && is_string($data['working_hours'])) {
//         $data['working_hours'] = json_decode($data['working_hours'], true);
//     }

//     // Ensure closed flags are boolean
//     if (isset($data['working_hours'])) {
//         foreach ($data['working_hours'] as $day => &$hours) {
//             $hours['closed'] = filter_var($hours['closed'], FILTER_VALIDATE_BOOLEAN);
//         }
//         unset($hours);
//     }

//     // Validate $data instead of $request
//     $validated = Validator::make($data, [
//         'name'           => ['required', 'string', 'max:255'],
//         'description'    => ['required', 'string'],
//         'main_category'  => ['required', Rule::exists('categories', 'name')->whereNull('parent_id')],
//         'sub_category'   => [
//             'required',
//             Rule::exists('categories', 'name')
//                 ->whereNotNull('parent_id')
//                 ->where(function ($query) use ($data) {
//                     if (!empty($data['main_category'])) {
//                         $query->where('parent_id', function($q) use ($data) {
//                             $q->select('id')
//                               ->from('categories')
//                               ->where('name', $data['main_category'])
//                               ->whereNull('parent_id');
//                         });
//                     }
//                 }),
//         ],
//         'city'           => ['required', Rule::exists('cities', 'name')],
//         'street'         => ['required', 'string', 'max:255'],
//         'street_number'  => ['required', 'string', 'max:50'],

//         // Working hours validation
//         'working_hours' => ['required', 'array'],

//         'working_hours.monday' => ['required', 'array'],
//         'working_hours.monday.open'  => ['required_unless:working_hours.monday.closed,true','date_format:H:i'],
//         'working_hours.monday.close' => ['required_unless:working_hours.monday.closed,true','date_format:H:i','after:working_hours.monday.open'],
//         'working_hours.monday.closed'=> ['boolean'],

//         // repeat for tuesday..sunday
//         'working_hours.tuesday' => ['required', 'array'],
//         'working_hours.tuesday.open'  => ['required_unless:working_hours.tuesday.closed,true','date_format:H:i'],
//         'working_hours.tuesday.close' => ['required_unless:working_hours.tuesday.closed,true','date_format:H:i','after:working_hours.tuesday.open'],
//         'working_hours.tuesday.closed'=> ['boolean'],

//         'working_hours.wednesday' => ['required', 'array'],
//         'working_hours.wednesday.open'  => ['required_unless:working_hours.wednesday.closed,true','date_format:H:i'],
//         'working_hours.wednesday.close' => ['required_unless:working_hours.wednesday.closed,true','date_format:H:i','after:working_hours.wednesday.open'],
//         'working_hours.wednesday.closed'=> ['boolean'],

//         'working_hours.thursday' => ['required', 'array'],
//         'working_hours.thursday.open'  => ['required_unless:working_hours.thursday.closed,true','date_format:H:i'],
//         'working_hours.thursday.close' => ['required_unless:working_hours.thursday.closed,true','date_format:H:i','after:working_hours.thursday.open'],
//         'working_hours.thursday.closed'=> ['boolean'],

//         'working_hours.friday' => ['required', 'array'],
//         'working_hours.friday.open'  => ['required_unless:working_hours.friday.closed,true','date_format:H:i'],
//         'working_hours.friday.close' => ['required_unless:working_hours.friday.closed,true','date_format:H:i','after:working_hours.friday.open'],
//         'working_hours.friday.closed'=> ['boolean'],

//         'working_hours.saturday' => ['required', 'array'],
//         'working_hours.saturday.open'  => ['required_unless:working_hours.saturday.closed,true','date_format:H:i'],
//         'working_hours.saturday.close' => ['required_unless:working_hours.saturday.closed,true','date_format:H:i','after:working_hours.saturday.open'],
//         'working_hours.saturday.closed'=> ['boolean'],

//         'working_hours.sunday' => ['required', 'array'],
//         'working_hours.sunday.open'  => ['nullable','date_format:H:i'],
//         'working_hours.sunday.close' => ['nullable','date_format:H:i','after:working_hours.sunday.open'],
//         'working_hours.sunday.closed'=> ['boolean'],

//         'gallery' => ['required', 'array', 'min:1'],
//         'gallery.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
//     ], [
//         'main_category.exists' => 'Избраната категорија не постои.',
//         'sub_category.exists'  => 'Избраната подкатегорија не постои или не припаѓа на категоријата.',
//         'city.exists'          => 'Избраниот град не постои.',
//         'gallery.required'     => 'Мора да прикачите најмалку една слика.',
//         'gallery.*.image'      => 'Секоја датотека мора да биде слика.',
//     ])->validate();

//     // Save or update the business
//     $business = Business::updateOrCreate(
//         ['user_id' => $user->id],
//         $validated
//     );

//     return response()->json(['message' => 'Бизнисот е успешно зачуван', 'business' => $business]);
// }
public function store(Request $request)
{
    $user = Auth::user();

    if ($user->role !== 'business') {
        return response()->json(['error' => 'Недозволено'], 403);
    }

    $data = $request->all();

    // Decode working_hours JSON string
    if (isset($data['working_hours']) && is_string($data['working_hours'])) {
        $data['working_hours'] = json_decode($data['working_hours'], true);
    }

    // Normalize "closed" flags to boolean
    if (!empty($data['working_hours'])) {
        foreach ($data['working_hours'] as &$hours) {
            $hours['closed'] = filter_var($hours['closed'], FILTER_VALIDATE_BOOLEAN);
        }
        unset($hours);
    }

    // Get main category ID (for sub_category validation)
    $mainCategoryId = Category::where('name', $data['main_category'] ?? null)
        ->whereNull('parent_id')
        ->value('id');

    $validated = Validator::make($data, [
        'name'        => ['required', 'string', 'max:255'],
        'description' => ['required', 'string'],
        'main_category' => [
            'required',
            Rule::exists('categories', 'name')->whereNull('parent_id')
        ],
        'sub_category' => [
            'required',
            Rule::exists('categories', 'name')
                ->where('parent_id', $mainCategoryId),
        ],
        'city'   => ['required', Rule::exists('cities', 'name')],
        'street' => ['required', 'string', 'max:255'],
        'street_number' => ['required', 'string', 'max:50'],

        // Working hours
        'working_hours' => ['required', 'array'],

        'working_hours.*.open'   => ['nullable', 'date_format:H:i'],
        'working_hours.*.close'  => ['nullable', 'date_format:H:i', 'after:working_hours.*.open'],
        'working_hours.*.closed' => ['boolean'],

        // Gallery
        'gallery'   => ['required', 'array', 'min:1'],
        'gallery.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
    ], [
        'main_category.exists' => 'Избраната категорија не постои.',
        'sub_category.exists'  => 'Избраната подкатегорија не постои или не припаѓа на категоријата.',
        'city.exists'          => 'Избраниот град не постои.',
        'gallery.required'     => 'Мора да прикачите најмалку една слика.',
        'gallery.*.image'      => 'Секоја датотека мора да биде слика.',
    ])->validate();

    // Process gallery upload
    $galleryPaths = [];
    if ($request->hasFile('gallery')) {
        foreach ($request->file('gallery') as $file) {
            $galleryPaths[] = $file->store('gallery', 'public'); // storage/app/public/gallery
        }
    }

    // Save or update the business
    $business = Business::updateOrCreate(
        ['user_id' => $user->id],
        [
            'name'          => $validated['name'],
            'description'   => $validated['description'],
            'main_category' => $validated['main_category'],
            'sub_category'  => $validated['sub_category'],
            'city'          => $validated['city'],
            'street'        => $validated['street'],
            'street_number' => $validated['street_number'],
            'working_hours' => json_encode($data['working_hours']), // store as JSON
            'gallery'       => json_encode($galleryPaths),          // store as JSON
        ]
    );

    return response()->json([
        'message'  => 'Бизнисот е успешно зачуван',
        'business' => $business,
        'gallery_urls' => collect($galleryPaths)->map(fn($p) => asset('storage/' . $p))
    ]);
}

}