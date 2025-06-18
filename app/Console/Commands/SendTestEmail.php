<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-test-email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi email kiểm tra đến một địa chỉ email cụ thể';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipientEmail = $this->argument('email');
        $this->info("Đang cố gắng gửi email kiểm tra đến {$recipientEmail}...");

        try {
            Mail::to($recipientEmail)->send(new TestMail());
            $this->info("✅ Email đã được gửi thành công!");
            $this->info("Vui lòng kiểm tra hộp thư đến (và cả thư mục Spam).");
        } catch (\Exception $e) {
            $this->error("❌ Đã xảy ra lỗi khi gửi email:");
            $this->error($e->getMessage());
            $this->info("Vui lòng kiểm tra lại cấu hình mail trong file .env của bạn.");
        }
    }
}
