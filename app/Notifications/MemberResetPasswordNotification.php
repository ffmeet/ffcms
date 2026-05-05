<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $token,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $email = $notifiable->getEmailForPasswordReset();
        $path = route('password.reset', [
            'token' => $this->token,
            'email' => $email,
        ], false);

        $url = rtrim((string) config('app.url'), '/') . $path;
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('重置你的登录密码')
            ->greeting('你好，')
            ->line('我们收到了你的密码重置请求。')
            ->action('设置新密码', $url)
            ->line("这条重置链接将在 {$expire} 分钟后失效。")
            ->line('如果这不是你的操作，可以忽略这封邮件。');
    }
}
