<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /** @var \App\Models\User */
    private $user;

    public function __construct()
    {
        $this->user = \Auth::user();
    }

    /**
     * @ApiDescription(section="Wallet", description="Get balance")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/api/wallet/balance")
     * @ApiParams(name="api_token", type="string (32)", nullable=false, description="User's phone number", sample="ME2sJapnqCIk4nTmZfUxycEz0RYChHPn")
     * @ApiBody(sample="
     * status = SUCCESS
     * data
     *     user_id
     *     address
     *         network
     *         available_balance
     *         pending_received_balance
     *         balances
     *             user_id
     *             label
     *             address
     *             available_balance
     *             pending_received_balance")
     */
    public function postBalance()
    {
        return $this::sendSuccess(['address' => $this->user->getBalance()]);
    }

    /**
     * @ApiDescription(section="Wallet", description="Get a new address")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/api/wallet/new_address")
     * @ApiParams(name="api_token", type="string (32)", nullable=false, description="User's phone number", sample="ME2sJapnqCIk4nTmZfUxycEz0RYChHPn")
     * @ApiBody(sample="
     * status = SUCCESS
     * data
     *     user_id
     *     address
     *         network
     *         user_id
     *         label
     *         address
     *         available_balance
     *         pending_received_balance")
     */
    public function postNewAddress()
    {
        $this->user->generateAddress();

        return $this::sendSuccess(['address' => $this->user->getLastAddress()]);
    }

    /**
     * @ApiDescription(section="Wallet", description="Get last address")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/api/wallet/last_address")
     * @ApiParams(name="api_token", type="string (32)", nullable=false, description="User's phone number", sample="ME2sJapnqCIk4nTmZfUxycEz0RYChHPn")
     * @ApiBody(sample="
     * status = SUCCESS
     * data
     *     user_id
     *     address
     *         network
     *         user_id
     *         label
     *         address
     *         available_balance
     *         pending_received_balance")
     */
    public function postLastAddress()
    {
        return $this::sendSuccess(['address' => $this->user->getLastAddress()]);
    }

    /**
     * @ApiDescription(section="Wallet", description="Get user transaction history")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/api/wallet/history")
     * @ApiParams(name="api_token", type="string (32)", nullable=false, description="User's phone number", sample="ME2sJapnqCIk4nTmZfUxycEz0RYChHPn")
     * @ApiBody(sample="
     * status = SUCCESS
     * data
     *     user_id
     *     history
     *         transaction_id
     *         time
     *         type (sent / received)
     *         amount
     *         addresses (separated by comma)")
     */
    public function postHistory()
    {
        return $this::sendSuccess(['history' => $this->user->getHistory()]);
    }

    /**
     * @ApiDescription(section="Wallet", description="Spend bitcoins")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/api/wallet/spend")
     * @ApiParams(name="api_token", type="string (32)", nullable=false, description="User's phone number", sample="ME2sJapnqCIk4nTmZfUxycEz0RYChHPn")
     * @ApiParams(name="amount", type="float", nullable=false, description="Amount", sample="0.00551520")
     * @ApiParams(name="address", type="string", nullable=false, description="Address", sample="34qkc2iac6RsyxZVfyE2S5U5WcRsbg2dpK")
     * @ApiBody(sample="
     * status = SUCCESS
     * data
     *     user_id
     *     txid
     *     amount_withdrawn
     *     amount_sent
     *     network_fee
     *     blockio_fee")
     */
    public function postSpend(Request $request)
    {
        $this->validate($request, [
            'amount'  => 'required|numeric',
            'address' => 'required'
        ]);

        $amount  = $request->get('amount');
        $address = $request->get('address');

        $response = $this->user->withdraw($amount, $address);

        if ($response->status == 'success'){
            $data = $response->data;

            $this->user->transactions()->create([
                'transaction_id' => $data->txid,
                'type' => 'sent',
                'time' => Carbon::now()->timestamp,
                'amount' => $amount,
                'addresses' => $address
            ]);

            return $this::sendSuccess($response->data);
        }
        else
            return $this::sendError(\APIError::WITHDRAW_ERROR);
    }
}