<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = 'users';
    protected $fillable = ['created', 'api_token', 'phone', 'pin', 'recovery_phrase'];
    public $timestamps = false;

    public function code()
    {
        return $this->hasOne('App\Models\Code', 'phone', 'phone');
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }

    public function generateAddress()
    {
        return app('BlockIo')->get_new_address(['label' => $this->getNextLabel()]);
    }

    public function getLastAddress()
    {
        $cacheKey = $this->getLastLabel() . '_address';

        if (app('cache')->has($cacheKey))
           return app('cache')->get($cacheKey);

        $address = app('BlockIo')->get_address_by_label(['label' => $this->getLastLabel()]);
        $address = $address->data;

        app('cache')->put($cacheKey, $address, 5);

        return $address;
    }

    public function getHistory()
    {
        $cacheKey = $this->getAllLabels() . '_history';

        if (app('cache')->has($cacheKey))
            return app('cache')->get($cacheKey);

        $received = app('BlockIo')->get_transactions(['type' => 'received', 'labels' => $this->getAllLabels()]);
        $sent = app('BlockIo')->get_transactions(['type' => 'sent', 'labels' => $this->getAllLabels()]);

        $receivedTxs = isset($received->data->txs) ? $received->data->txs : [];
        $sentTxs = isset($sent->data->txs) ? $sent->data->txs : [];

        foreach($receivedTxs as $receivedTx)
        {
            $amount = 0;

            foreach($receivedTx->amounts_received as $amountReceived)
                $amount += $amountReceived->amount;

            $this->transactions()->firstOrCreate([
                'transaction_id' => $receivedTx->txid
            ], [
                'type' => 'received',
                'time' => $receivedTx->time,
                'amount' => $amount,
                'addresses' => implode(',', $receivedTx->senders)
            ]);
        }

        foreach($sentTxs as $sentTx)
        {
            $amount = 0;
            $address = '';

            foreach($sentTx->amounts_sent as $amountSent) {
                $address = $amountSent->recipient;
                $amount  += $amountSent->amount;
            }

            $this->transactions()->firstOrCreate([
                'transaction_id' => $sentTx->txid
            ], [
                'type' => 'sent',
                'time' => $sentTx->time,
                'amount' => $amount,
                'addresses' => $address
            ]);
        }

        $history = $this->transactions()->orderBy('time', 'DESC')->get();

        app('cache')->put($cacheKey, $history, 15);

        return $history;
    }

    public function getBalance()
    {
        $balance = app('BlockIo')->get_address_balance(['labels' => $this->getAllLabels()]);
        return $balance->data;
    }

    public function withdraw($amount, $to)
    {
        $response = app('BlockIo')->withdraw_from_labels([
            'amounts' => $amount,
            'from_labels' => $this->getAllLabels(),
            'to_addresses' => $to
        ]);

        return $response;
    }

    private function getAllLabels()
    {
        $labels = [];

        for($i = 1; $i <= $this->attributes['last_address']; $i++)
            $labels[] = $this->getLabelBase() . $i;

        return implode(',', $labels);
    }

    private function getNextLabel()
    {
        $this->last_address += 1;
        $this->save();

        return $this->getLabelBase() . $this->last_address;
    }

    private function getLastLabel()
    {
        return $this->getLabelBase() . $this->last_address;
    }

    private function getLabelBase()
    {
        return 'UserId' . $this->id;
    }
}
