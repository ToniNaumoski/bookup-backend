<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use App\Models\User;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'test@example.com';

        $this->info('Испраќање тест емаил на: ' . $email);

        try {
            // Create a test user or use existing one
            $user = User::first() ?? User::factory()->create([
                'name' => 'Test User',
                'email' => $email,
                'email_verified_at' => null
            ]);

            // Send verification email
            $user->sendEmailVerificationNotification();

            $this->info('✅ Емаилот е успешно испратен!');
            $this->info('Проверете го вашиот емаил inbox/spam фолдер.');

        } catch (\Exception $e) {
            $this->error('❌ Грешка при испраќање на емаил: ' . $e->getMessage());
            $this->error('Детали: ' . $e->getTraceAsString());
        }

        return Command::SUCCESS;
    }
}