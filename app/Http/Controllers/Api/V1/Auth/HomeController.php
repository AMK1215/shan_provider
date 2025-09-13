<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Admin\Banner;
use App\Models\Admin\BannerAds;
use App\Models\Admin\BannerText;
use App\Models\Admin\Promotion;
use App\Models\Contact;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use HttpResponses;

    public function home()
    {
        $user = Auth::user();
        $admin = $user->parent->parent->parent->parent;

        // Fetch all the required data
        $contacts = Contact::where('agent_id', $user->agent_id)->get();
        $banners = Banner::where('admin_id', $admin->agent_id)->get();
        $bannerTexts = BannerText::where('admin_id', $admin->agent_id)->get();
        $adsBanners = BannerAds::where('admin_id', $admin->agent_id)->get();
        $promotions = Promotion::where('admin_id', $admin->agent_id)->get();

        // Return the data in a structured response
        return $this->success([
            'contacts' => ContactResource::collection($contacts),
            'banners' => $banners,
            'banner_texts' => $bannerTexts,
            'ads_banners' => $adsBanners,
            'promotions' => $promotions,
        ], 'Home data retrieved successfully.');
    }
}
