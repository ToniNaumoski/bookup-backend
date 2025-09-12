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
                    'admin_messages' => $business->admin_messages,
                    'read_messages' => $business->read_messages,
                    'review_status' => $business->review_status,
                    'status' => $business->status,
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
        'name'        => ['required', 'string', 'min:3', 'max:100'],
        'description' => ['required', 'string', 'min:50', 'max:1000'],
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
        'street' => ['required', 'string', 'min:2', 'max:100'],
        'street_number' => ['required', 'string', 'min:1', 'max:10'],

        // Working hours
        'working_hours' => ['required', 'array'],

        'working_hours.*.open'   => ['nullable', 'date_format:H:i'],
        'working_hours.*.close'  => ['nullable', 'date_format:H:i', 'after:working_hours.*.open'],
        'working_hours.*.closed' => ['boolean'],

        // Gallery - only required for new businesses, optional for updates
        'gallery'   => ['nullable', 'array', 'max:10'],
        'gallery.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        'existing_gallery' => ['nullable', 'string'],
    ], [
        'name.required' => 'Името на бизнисот е задолжително.',
        'name.min' => 'Името мора да има најмалку :min карактери.',
        'name.max' => 'Името не може да има повеќе од :max карактери.',
        'description.required' => 'Описот е задолжителен.',
        'description.min' => 'Описот мора да има најмалку :min карактери.',
        'description.max' => 'Описот не може да има повеќе од :max карактери.',
        'main_category.required' => 'Главната категорија е задолжителна.',
        'main_category.exists' => 'Избраната категорија не постои.',
        'sub_category.required' => 'Подкатегоријата е задолжителна.',
        'sub_category.exists'  => 'Избраната подкатегорија не постои или не припаѓа на категоријата.',
        'city.required' => 'Градот е задолжителен.',
        'city.exists' => 'Избраниот град не постои.',
        'street.required' => 'Улицата е задолжителна.',
        'street.min' => 'Името на улицата мора да има најмалку :min карактери.',
        'street.max' => 'Името на улицата не може да има повеќе од :max карактери.',
        'street_number.required' => 'Бројот на улицата е задолжителен.',
        'street_number.min' => 'Бројот на улицата мора да има најмалку :min карактер.',
        'street_number.max' => 'Бројот на улицата не може да има повеќе од :max карактери.',
        'working_hours.required' => 'Работното време е задолжително.',
        'gallery.required' => 'Мора да прикачите најмалку една слика.',
        'gallery.*.image' => 'Секоја датотека мора да биде слика.',
        'gallery.*.mimes' => 'Сликите мора да бидат во JPG, JPEG или PNG формат.',
        'gallery.*.max' => 'Секоја слика не може да биде поголема од 5MB.',
    ])->validate();

    // Check if business already exists
    $existingBusiness = Business::where('user_id', $user->id)->first();

    // Process gallery upload
    $galleryPaths = [];

    // Handle existing images (URLs from frontend)
    $existingImages = [];
    if ($request->has('existing_gallery') && !empty($request->input('existing_gallery'))) {
        $existingImages = json_decode($request->input('existing_gallery'), true) ?? [];
    }

    // Handle new uploaded files
    if ($request->hasFile('gallery')) {
        foreach ($request->file('gallery') as $file) {
            $galleryPaths[] = $file->store('gallery', 'public'); // storage/app/public/gallery
        }
    }

    // Combine existing images with new uploads
    $allGalleryImages = array_merge($existingImages, $galleryPaths);

    // Validate total image count (max 10)
    if (count($allGalleryImages) > 10) {
        return response()->json([
            'message' => 'Максималниот број на слики е 10.',
            'errors' => ['gallery' => ['Не можете да имате повеќе од 10 слики.']]
        ], 422);
    }

    // Prepare update data
    $updateData = [
        'name'          => $validated['name'],
        'description'   => $validated['description'],
        'main_category' => $validated['main_category'],
        'sub_category'  => $validated['sub_category'],
        'city'          => $validated['city'],
        'street'        => $validated['street'],
        'street_number' => $validated['street_number'],
        'working_hours' => json_encode($data['working_hours']), // store as JSON
    ];

    // Set default status for new businesses
    if (!$existingBusiness) {
        $updateData['status'] = 'pending';
        $updateData['review_status'] = 'pending';
    }

    // Update gallery if we have images
    if (!empty($allGalleryImages)) {
        $updateData['gallery'] = json_encode($allGalleryImages);
    } elseif (!$existingBusiness) {
        // For new businesses, require at least one image
        return response()->json([
            'message' => 'Мора да прикачите најмалку една слика за новиот бизнис.',
            'errors' => ['gallery' => ['Мора да прикачите најмалку една слика.']]
        ], 422);
    }
    // If no new files and business exists, keep existing gallery

    // Save or update the business
    $business = Business::updateOrCreate(
        ['user_id' => $user->id],
        $updateData
    );

    return response()->json([
        'message'  => 'Бизнисот е успешно зачуван',
        'business' => $business,
        'gallery_urls' => collect($galleryPaths)->map(fn($p) => asset('storage/' . $p))
    ]);
}

public function markMessageAsRead(Request $request)
{
    $user = Auth::user();

    if ($user->role !== 'business') {
        return response()->json(['error' => 'Недозволено'], 403);
    }

    $request->validate([
        'message_index' => 'required|integer|min:0'
    ]);

    $business = Business::where('user_id', $user->id)->first();

    if (!$business) {
        return response()->json(['error' => 'Бизнис не е пронајден'], 404);
    }

    // Get current read messages array
    $readMessages = $business->read_messages ?? [];

    // Add the message index to read messages if not already there
    if (!in_array($request->message_index, $readMessages)) {
        $readMessages[] = $request->message_index;
    }

    // Update the business
    $business->update(['read_messages' => $readMessages]);

    return response()->json([
        'message' => 'Пораката е означена како прочитана',
        'read_messages' => $readMessages
    ]);
}

}
