<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    protected const SUB_AGENT_ROlE = 'Sub Agent';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $agent = $this->getAgent() ?? Auth::user();

        $contacts = Contact::where('agent_id', $agent->id)->get();
        $types = collect($contacts)->pluck('type_id')->unique();

        return view('admin.contact.index', compact('contacts', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = ContactType::all();

        return view('admin.contact.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'value' => 'required',
            'type_id' => 'required',
        ]);
        $agent = $this->getAgent() ?? Auth::user();
        $contact = Contact::where('agent_id', $agent->id)->where('type_id', $request->type_id)->first();

        if ($contact) {
            return redirect()->back()->with('error', 'Already Created for this contact type');
        }

        Contact::create([
            'name' => $request->name,
            'value' => $request->value,
            'agent_id' => $agent->id,
            'type_id' => $request->type_id,
        ]);

        return redirect(route('admin.contact.index'))->with('success', 'New Contact Created Successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        $types = ContactType::all();

        return view('admin.contact.edit', compact('contact', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        $request->validate([
            'name' => 'required',
            'value' => 'required',
        ]);

        $contact->update([
            'name' => $request->name,
            'value' => $request->value,
            'type_id' => $request->type_id,
        ]);

        return redirect(route('admin.contact.index'))->with('success', 'New Contact Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect(route('admin.contact.index'))->with('success', 'New Contact Updated Successfully.');
    }

    private function isExistingAgent($userId)
    {
        $user = User::find($userId);

        return $user && $user->hasRole(self::SUB_AGENT_ROlE) ? $user->parent : null;
    }

    private function getAgent()
    {
        return $this->isExistingAgent(Auth::id());
    }
}
