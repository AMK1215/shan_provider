<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use App\Models\UserPayment;
use App\Policies\UserPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentTypes = UserPayment::with('paymentType')->get();

        return view('admin.payment_types.index', compact('paymentTypes'));
    }

    public function create()
    {
        $paymentTypes = PaymentType::all();

        return view('admin.payment_types.create', compact('paymentTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_type_id' => ['required'],
            'account_name' => ['required'],
            'account_no' => ['required'],
        ]);

        UserPayment::create(array_merge(['user_id' => Auth::id()], $request->all()));

        return redirect()->route('admin.paymentTypes.index');
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
    public function edit(string $id)
    {
        $userPayment = UserPayment::where('id', $id)->first();
        $paymentTypes = PaymentType::all();

        return view('admin.payment_types.edit', compact('paymentTypes', 'userPayment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $userPayment = UserPayment::findOrFail($id);

        $userPayment->update($request->all());

        return redirect()->route('admin.paymentTypes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $userPayment = UserPayment::findOrFail($id);

        $userPayment->delete();

        return redirect()->route('admin.paymentTypes.index');

    }
}
