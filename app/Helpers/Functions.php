<?php

use Illuminate\Support\Facades\Mail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

if (!function_exists('mssql')) {
    /**
     * @return \App\Helpers\Mssql
     */
    function mssql(): \App\Helpers\Mssql
    {
        return new App\Helpers\Mssql();
    }
}

if (!function_exists('procedure')) {
    /**
     * @throws Exception
     */
    function procedure(string $name, $data = [], bool $single = false): object|array
    {
        $mssql = new App\Helpers\Mssql();
        $query = $mssql->query($name, $data);
        return $mssql->run($query, $single);
    }
}

if (!function_exists('isFailed')) {
    /**
     * @param $response
     * @return bool
     */
    function isFailed($response): bool
    {
        if (is_array($response) && !empty($response[0]->Result) && $response[0]->Result == 400) {
            return true;
        }
        elseif (is_object($response) && !empty($response->Result) && $response->Result == 400) {
            return true;
        }
        return false;
    }
}

if (!function_exists('verify')) {
    /**
     * @param int $user_id
     * @param string $receiver
     * @return bool|string
     */
    function verify(int $user_id, string $receiver): bool|string
    {
        $code = mssql()->generateVerifyCode($user_id);
        if ($code) {
            $subject = 'PlanVisio Access Code';
            verifyMail($receiver, [
                'email' => $receiver,
                'subject' => $subject,
                'code' => $code
            ]);
            return $code;
        }

        return false;
    }
}

if (!function_exists('verifyMail')) {
    /**
     * @param string $receiver
     * @param array $data
     * @return bool
     */
    function verifyMail(string $receiver, array $data): bool
    {
        $data['body'] = view('emails.verify', [
            'email' => $receiver,
            'code' => $data['code'],
            'subject' => $data['subject']
        ])->render();

        return sendEmail($receiver, $data);
    }
}

if (!function_exists('sendEmail')) {
    /**
     * @param string $receiver
     * @param array $data
     * @return bool
     */
    function sendEmail(string $receiver, array $data): bool
    {
        //$usernameSmtp = 'AKIA5VJHLVLV3JNO3LTX';
        //$passwordSmtp = 'BHFGmTAq2Zj/YmrLrYk0iNSb1I+3pxXPjkkvutfZz7dB';
        //$configurationSet = 'my-first-configuration-set';
        //$host = 'email-smtp.us-east-1.amazonaws.com';
        //$port = 587;
        // $subject = 'Amazon SES test (SMTP interface accessed using PHP)';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            //$mail->SMTPDebug = 4;
            $mail->setFrom(config('mail.from.address'), config('mail.from.name'));
            $mail->Username   = config('mail.mailers.smtp.username');
            $mail->Password   = config('mail.mailers.smtp.password');
            $mail->Host       = config('mail.mailers.smtp.host');
            $mail->Port       = config('mail.mailers.smtp.port');
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = config('mail.mailers.smtp.encryption');
            $mail->addCustomHeader(
                'X-SES-CONFIGURATION-SET', env('MAIL_SES_CONFIGURATION', 'my-first-configuration-set')
            );

            $mail->addAddress($receiver);

            $mail->isHTML(true);
            $mail->Subject    = $data['subject'] ?? 'Planvisio Mail';
            $mail->Body       = $data['body'] ?? '';
            $mail->AltBody    = strip_tags($data['body'] ?? '');
            $mail->Send();

            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
