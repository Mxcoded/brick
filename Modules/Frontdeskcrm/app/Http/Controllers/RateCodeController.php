<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Http\Requests\StoreRateCodeRequest;
use Modules\Frontdeskcrm\Http\Requests\UpdateRateCodeRequest;
use Modules\Frontdeskcrm\Models\RateCode;

class RateCodeController extends Controller
{
    public function index(Request $request)
    {
        $rateCodes = RateCode::with('prices.roomType')
            ->ordered()
            ->paginate(20);

        return view('frontdeskcrm::rate-codes.index', compact('rateCodes'));
    }

    public function create()
    {
        $roomTypes = RoomType::active()->ordered()->get();

        return view('frontdeskcrm::rate-codes.create', compact('roomTypes'));
    }

    public function store(StoreRateCodeRequest $request)
    {
        $rateCode = RateCode::create($request->safe()->except('prices'));

        if ($request->filled('prices')) {
            foreach ($request->input('prices', []) as $price) {
                $rateCode->prices()->create([
                    'room_type_id' => $price['room_type_id'],
                    'price' => $price['price'],
                ]);
            }
        }

        return redirect()->route('frontdesk.rate-codes.index')
            ->with('success', "Rate code '{$rateCode->name}' created.");
    }

    public function show(RateCode $rateCode)
    {
        $rateCode->load('prices.roomType');

        return view('frontdeskcrm::rate-codes.show', compact('rateCode'));
    }

    public function edit(RateCode $rateCode)
    {
        $rateCode->load('prices');
        $roomTypes = RoomType::active()->ordered()->get();

        return view('frontdeskcrm::rate-codes.edit', compact('rateCode', 'roomTypes'));
    }

    public function update(UpdateRateCodeRequest $request, RateCode $rateCode)
    {
        $rateCode->update($request->safe()->except('prices'));

        if ($request->has('prices')) {
            $rateCode->prices()->delete();

            foreach ($request->input('prices', []) as $price) {
                $rateCode->prices()->create([
                    'room_type_id' => $price['room_type_id'],
                    'price' => $price['price'],
                ]);
            }
        }

        return redirect()->route('frontdesk.rate-codes.index')
            ->with('success', "Rate code '{$rateCode->name}' updated.");
    }

    public function destroy(RateCode $rateCode)
    {
        $rateCode->delete();

        return redirect()->route('frontdesk.rate-codes.index')
            ->with('success', 'Rate code deleted.');
    }
}
