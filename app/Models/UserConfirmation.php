<?php

namespace App\Models;

use App\Http\Services\UserNotificationService;
use App\Mail\ActivationAccountMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserConfirmation extends Model
{

    const TYPE_EMAIL = 'email';
    const TYPE_SMS = 'sms';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'code',
        'is_active',
        'context',
    ];

    /**
     * send code via sms or email
     * @param array $params
     * @return bool
     */
    public function send(array $params): bool
    {
        try {
            $this->sendEmail($params);
            $this->sendSms($params);
        } catch (\Throwable $ex) {
            Log::info($ex->getMessage(), $ex->getTrace());
            return false;
        }

        return true;
    }

    public function sendEmail(array $params): void
    {
        if (isset($params['email'])) {
            //disable old activation codes
            UserConfirmation::query()
                ->where('type',self::TYPE_EMAIL)
                ->where('context',$params['email'])
                ->where('is_active',true)
                ->update(['is_active' => false]);

            $confirmation = UserConfirmation::query()->create([
                'type' => self::TYPE_EMAIL,
                'code' => random_int(100000, 999999),
                'is_active' => true,
                'context' => $params['email']
            ]);

            UserNotificationService::sendEmail($params['email'], $confirmation);
        }
    }

    public function sendSms(array $params): void
    {
        if (isset($params['phone'])) {
            //disable old codes
            UserConfirmation::query()
                ->where('type',self::TYPE_SMS)
                ->where('context', $params['phone'])
                ->where('is_active',true)
                ->update(['is_active' => false]);

            $confirmation = UserConfirmation::query()->create([
                'type' => self::TYPE_SMS,
                'code' => random_int(100000, 999999),
                'is_active' => true,
                'context' => $params['phone']
            ]);
        }
    }
}
