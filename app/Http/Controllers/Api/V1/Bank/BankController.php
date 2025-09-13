<?php

namespace App\Http\Controllers\Api\V1\Bank;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BankRequest;
use App\Http\Resources\PaymentTypeResource;
use App\Models\Admin\Bank;
use App\Models\PaymentType;
use App\Models\UserBank;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    use HttpResponses;

    public function all()
    {
        $player = Auth::user();

        $data = Bank::where('agent_id', $player->agent_id)->get();

        return $this->success(PaymentTypeResource::collection($data), 'Payment Type list successfule');
    }

    public function paymentType()
    {
        $data = PaymentType::all();

        return $this->success($data);
    }
}
