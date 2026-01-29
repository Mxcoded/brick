<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('account::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('account::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('account::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('account::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

     /**
     * Update the specified resource in storage.
     */
    public function payment(Request $request, $id)
    {
        /*
        $transaction->status = 'success';
        $transaction->save();

        $invoice->amount_paid += $transaction->amount;

        if ($invoice->amount_paid >= $invoice->amount_due) {
            $invoice->status = 'paid';
            $invoice->order->status = 'confirmed';
        } else {
            $invoice->status = 'partial';
        }

        $invoice->save(); */
    }
}
