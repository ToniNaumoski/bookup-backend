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
       $cities = City::with('municipalities')->get();

        if ($business) {
            return response()->json([
                'business' => [
                    'name' => $business->name,
                    'description' => $business->description,
                    'main_category' => $business->main_category,
                    'sub_category' => $business->sub_category,
                    'city' => $business->city,
                    'municipality' => $business->municipality,
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

    // Check if the selected city has municipalities
    $selectedCity = City::where('name', $data['city'] ?? null)->with('municipalities')->first();
    $cityHasMunicipalities = $selectedCity && $selectedCity->municipalities->count() > 0;

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
        'municipality' => [
            $cityHasMunicipalities ? 'required' : 'nullable',
            Rule::exists('municipalities', 'name')
        ],
        'street' => ['required', 'string', 'min:2', 'max:100'],
        'street_number' => ['required', 'string', 'min:1', 'max:10'],

        // Working hours
        'working_hours' => ['required', 'array'],

        'working_hours.*.open'   => ['nullable', 'date_format:H:i'],
        'working_hours.*.close'  => [
            'nullable',
            'date_format:H:i',
            function ($attribute, $value, $fail) use ($data) {
                if (empty($value)) return;

                // Extract day from attribute, e.g., working_hours.monday.close -> monday
                $parts = explode('.', $attribute);
                if (count($parts) >= 3) {
                    $day = $parts[1];
                    $openTime = $data['working_hours'][$day]['open'] ?? null;

                    if (!empty($openTime)) {
                        $open = \Carbon\Carbon::createFromFormat('H:i', $openTime);
                        $close = \Carbon\Carbon::createFromFormat('H:i', $value);

                        // If close is before open, assume next day
                        if ($close->lessThan($open)) {
                            $close->addDay();
                        }

                        if (!$close->greaterThan($open)) {
                            $fail('Времето за затворање мора да биде после времето за отворање.');
                        }
                    }
                }
            }
        ],
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
        'municipality.required' => 'Општината е задолжителна.',
        'municipality.exists' => 'Избраната општина не постои.',
        'street.required' => 'Улицата е задолжителна.',
        'street.min' => 'Името на улицата мора да има најмалку :min карактери.',
        'street.max' => 'Името на улицата не може да има повеќе од :max карактери.',
        'street_number.required' => 'Бројот на улицата е задолжителен.',
        'street_number.min' => 'Бројот на улицата мора да има најмалку :min карактер.',
        'street_number.max' => 'Бројот на улицата не може да има повеќе од :max карактери.',
        'working_hours.required' => 'Работното време е задолжително.',
        'working_hours.*.open.date_format' => 'Времето за отворање мора да биде во формат ЧЧ:ММ.',
        'working_hours.*.close.date_format' => 'Времето за затворање мора да биде во формат ЧЧ:ММ.',
        'working_hours.*.closed.boolean' => 'Полето за затворено мора да биде да или не.',
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
        'municipality'  => $validated['municipality'] ?? null,
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
