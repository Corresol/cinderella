<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Code;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @ApiDescription(section="User", description="Create a verification code")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/api/user/verification")
     * @ApiParams(name="phone", type="string (15)", nullable=false, description="User's phone number", sample="+123456789")
     * @ApiParams(name="reset", type="boolean", nullable=true, description="If you want to reset the request. Available after 60 seconds.", sample="1")
     * @ApiHeaders(name="9", type="INVALID_DATA_PROVIDED")
     * @ApiHeaders(name="30", type="CODE_ALREADY_SENT")
     * @ApiHeaders(name="32", type="USER_ALREADY_EXISTS")
     * @ApiBody(sample="
     * status = SUCCESS
     * data
     *     user_id")
     */
    public function postVerification(Request $request)
    {
        $this->validate($request, $this->getPhoneValidation());

        $phone = $request->get('phone');

        $user = User::wherePhone($phone)->first();

        if ($user)
            return self::sendError(\APIError::USER_ALREADY_EXISTS);

        $code = Code::wherePhone($phone)->first();

        if ($code){
            if (Carbon::now()->diffInSeconds($code->created_at) >= 60){
                $reset = $request->get('reset');

                if ($reset)
                    $code->delete();
                else
                    return self::sendError(\APIError::CODE_ALREADY_SENT);
            }else{
                return self::sendError(\APIError::CODE_ALREADY_SENT);
            }
        }

        /** @var \Twilio\Rest\Client $twilio */
        $twilio = app('Twilio');

        $code = random_int(100000, 999999);

        $twilio->messages->create($phone, ['from' => env('TWILIO_NUMBER'), 'body' => "Your code is: {$code}"]);

        Code::create([
            'phone' => $phone,
            'code'  => $code,
            'created_at' => Carbon::now()->toDateTimeString()
        ]);

        return $this::sendSuccess([]);
    }

    /**
     * @ApiDescription(section="User", description="Create an user")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/api/user/create")
     * @ApiParams(name="phone", type="string (15)", nullable=false, description="User's phone number", sample="+123456789")
     * @ApiParams(name="pin", type="string (4,16)", nullable=false, description="User's code (password)", sample="12345678")
     * @ApiParams(name="code", type="string (6)", nullable=true, description="Pin code received by Twilio", sample="123456")
     * @ApiHeaders(name="9", type="INVALID_DATA_PROVIDED")
     * @ApiHeaders(name="31", type="WRONG_CODE")
     * @ApiBody(sample="
     * status = SUCCESS
     * data
     * api_token
     * recovery_phrase
     * premium_news
     * balance
     *         network
     *         available_balance
     *         pending_received_balance
     *         balances (array)
     *             user_id
     *             label
     *             address
     *             available_balance
     *             pending_received_balance")
     */
    public function postCreate(Request $request)
    {
        $this->validate($request, array_merge($this->getPhoneValidation(), $this->getCodeValidation(), $this->getPinValidation()));

        $phone = trim($request->get('phone'));
        $code  = $request->get('code');
        $pin   = $request->get('pin');

        $sentCode = Code::wherePhone($phone)->firstOrFail();

        if ($sentCode->code !== $code)
            return self::sendError(\APIError::WRONG_CODE);

        $sentCode->delete();

        $apiToken       = str_random(32);
        $pin            = app('hash')->make($pin);
        $recoveryPhrase = get_recovery_phrase();

        // Create the new user
        /** @var User $user */
        $user = User::create([
            'api_token'       => $apiToken,
            'created'         => Carbon::now()->toDateString(),
            'phone'           => $phone,
            'pin'             => $pin,
            'recovery_phrase' => $recoveryPhrase,
        ]);

        $user->generateAddress();

        return $this::sendSuccess($this->getSuccessData($user, $apiToken));
    }

    /**
     * @ApiDescription(section="User", description="Login")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/api/user/login")
     * @ApiParams(name="phone", type="string (15)", nullable=false, description="User's phone number", sample="+123456789")
     * @ApiParams(name="pin", type="string (4,16)", nullable=false, description="User's code (password)", sample="12345678")
     * @ApiHeaders(name="10", type="INVALID_USER")
     * @ApiHeaders(name="11", type="INVALID_PIN")
     * @ApiBody(sample="
     * status = SUCCESS
     * data
     * api_token
     * recovery_phrase
     * premium_news
     * balance
     *         network
     *         available_balance
     *         pending_received_balance
     *         balances (array)
     *             user_id
     *             label
     *             address
     *             available_balance
     *             pending_received_balance")
     */
    public function postLogin(Request $request)
    {
        // Validate input
        $this->validate($request, array_merge($this->getPhoneValidation(), $this->getPinValidation()));

        // Obtain request data
        $phone = $request->get('phone');
        $pin   = $request->get('pin');

        // Does the user exists?
        $user = User::wherePhone($phone)->first();

        if (!$user)
            return $this::sendError(\APIError::INVALID_USER);

        // Is the pin correct?
        if (app('hash')->check($pin, $user->pin)) {
            $apiToken = str_random(32);

            $user->api_token = $apiToken;
            $user->save();

            return $this::sendSuccess($this->getSuccessData($user, $apiToken));
        }

        return $this::sendError(\APIError::INVALID_PIN);
    }

    /**
     * @ApiDescription(section="User", description="Change PIN")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/api/user/change_pin")
     * @ApiParams(name="api_token", type="string (15)", nullable=false, description="User's phone number", sample="+123456789")
     * @ApiParams(name="old_pin", type="string (4,16)", nullable=true, description="Old pin", sample="123456")
     * @ApiParams(name="new_pin", type="string (4,16)", nullable=true, description="New pin", sample="123456")
     * @ApiHeaders(name="12", type="INVALID_OLD_PIN")
     * @ApiBody(sample="
     * status = SUCCESS
     * data
     *     user_id")
     */
    public function postChangePin(Request $request)
    {
        $this->validate($request, array_merge($this->getChangePinValidation()));

        /** @var User $user */
        $user = \Auth::user();

        $oldPin = $request->get('old_pin');
        $newPin = $request->get('new_pin');

        // Check that the old PIN is correct
        if (app('hash')->check($oldPin, $user->pin)) {
            $user->pin = app('hash')->make($newPin);
            $user->save();

            return $this::sendSuccess([]);
        }

        return $this::sendError(\APIError::INVALID_OLD_PIN);
    }

    /**
     * @ApiDescription(section="User", description="Enable premium news for one year")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/api/user/enable_premium_news")
     * @ApiParams(name="api_token", type="string (32)", nullable=false, description="User's phone number", sample="ME2sJapnqCIk4nTmZfUxycEz0RYChHPn")
     * @ApiBody(sample="
     * status = SUCCESS
     * data
     *     user_id
     *     expire (date)")
     */
    public function postEnablePremiumNews()
    {
        /** @var User $user */
        $user = \Auth::user();

        $expire =   Carbon::now()->addYear()->toDateString();

        $user->premium_news = $expire;
        $user->save();

        return self::sendSuccess([
            'expire' => $expire
        ]);
    }

    private function getSuccessData($user, $apiToken)
    {
        return [
            'api_token' => $apiToken,
            'recovery_phrase' => $user->recovery_phrase,
            'balance'   => $user->getBalance(),
            'premium_news' => $user->premium_news
        ];
    }

    private function getPhoneValidation()
    {
        return [
            'phone' => 'required|max:15'
        ];
    }

    private function getCodeValidation()
    {
        return [
            'code' => 'required|size:6'
        ];
    }

    private function getPinValidation()
    {
        return [
            'pin' => 'required|between:4,16'
        ];
    }

    private function getChangePinValidation()
    {
        return [
            'new_pin' => 'required|between:4,16',
            'old_pin' => 'required|between:4,16'
        ];
    }
}