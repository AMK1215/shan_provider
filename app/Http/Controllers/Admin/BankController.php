<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankRequest;
use App\Models\Admin\Bank;
use App\Models\PaymentType;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    protected const SUB_AGENT_ROlE = 'Sub Agent';

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $agent = $this->getAgent() ?? Auth::user();

        $banks = Bank::where('agent_id', $agent->id)->get();

        return view('admin.bank.index', compact('banks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $paymentTypes = PaymentType::all();

        return view('admin.bank.create', compact('paymentTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BankRequest $request)
    {
        $agent = $this->getAgent() ?? Auth::user();

        Bank::create([
            'agent_id' => $agent->id,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'payment_type_id' => $request->payment_type_id,
        ]);

        return redirect()->route('admin.bank.index')->with('success', 'Bank created successfully');
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
    public function edit(Bank $bank): View
    {
        $paymentTypes = PaymentType::all();

        return view('admin.bank.edit', compact('bank', 'paymentTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BankRequest $request, Bank $bank)
    {
        $bank->update($request->all());

        return redirect()->route('admin.bank.index')->with('success', 'Bank updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank)
    {
        $bank->delete();

        return redirect()->route('admin.bank.index')->with('success', 'Bank deleted successfully');

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
