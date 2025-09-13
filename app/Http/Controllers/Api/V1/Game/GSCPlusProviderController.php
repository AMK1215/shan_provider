<?php

namespace App\Http\Controllers\Api\V1\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AdsBannerResource;
use App\Http\Resources\Api\BankResource;
use App\Http\Resources\Api\BannerResource;
use App\Http\Resources\Api\BannerTextResource;
use App\Http\Resources\Api\ContactResource;
use App\Http\Resources\Api\GameListResource;
use App\Http\Resources\Api\GameProviderResource;
use App\Http\Resources\Api\GameTypeResource;
use App\Http\Resources\Api\PromotionResource;
use App\Http\Resources\Slot\GameDetailResource;
use App\Models\Admin\Bank;
use App\Models\Admin\Banner;
use App\Models\Admin\BannerAds;
use App\Models\Admin\BannerText;
use App\Models\Admin\Promotion;
use App\Models\Admin\TopTenWithdraw;
use App\Models\Contact;
use App\Models\GameList;
use App\Models\GameType;
use App\Models\PaymentType;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GSCPlusProviderController extends Controller
{
    use HttpResponses;

    public function gameTypes()
    {
        $types = GameType::with(['products' => function ($query) {
            $query->where('status', 1);
            $query->orderBy('order', 'asc');
        }])->where('status', 1)->get();

        return $this->success(GameTypeResource::collection($types));
    }

    public function providers($type)
    {
        $providers = GameType::with(['products' => function ($query) {
            $query->where('game_list_status', 1);
            $query->orderBy('order', 'asc');
        }])->where('code', $type)->where('status', 1)->first();
        if ($providers) {
            return $this->success(new GameProviderResource($providers));
        } else {
            return $this->error('', 'Providers Not Found', 404);
        }
    }

    public function gameLists($type, $provider, Request $request)
    {
        $gameLists = GameList::with('product')
            ->where('product_id', $provider)
            ->where('game_type_id', $type)
            ->where('is_active', 1)
            ->OrderBy('order', 'asc')
            ->where('game_name', 'like', '%'.$request->game_name.'%')
            ->paginate(20);

        return $this->success(GameListResource::collection($gameLists));
    }

    public function NewgameLists($type, $provider, Request $request)
    {
        $gameLists = GameList::with('product')
            ->where('product_code', $provider)
            ->where('game_type_id', $type)
            ->where('is_active', 1)
            ->OrderBy('order', 'asc')
            ->where('game_name', 'like', '%'.$request->game_name.'%')
            ->paginate(20);

        return $this->success(GameListResource::collection($gameLists));
    }

    public function hotGameLists()
    {
        $hot_games = GameList::where('hot_status', '1')->get();

        return $this->success(GameListResource::collection($hot_games));
    }

    public function banks()
    {
        $player = Auth::user();
        $data = Bank::where('agent_id', $player->agent_id)->get();

        return $this->success(BankResource::collection($data), 'Payment Type list successfule');
    }

    public function paymentType()
    {
        $data = PaymentType::all();

        return $this->success($data);
    }
}
