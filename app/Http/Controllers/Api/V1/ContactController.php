<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    use HttpResponses;

    public function get()
    {
        $player = Auth::user();

        $contact = Contact::where('agent_id', $player->agent_id)->get();

        return $this->success(ContactResource::collection($contact));
    }
}
