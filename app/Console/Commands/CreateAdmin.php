<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email? : Admin email address} {--name= : Admin display name}';

    protected $description = 'Create a new TradeYatra administrator';

    public function handle(): int
    {
        $email = strtolower((string) ($this->argument('email') ?: $this->ask('Email address')));
        $name = (string) ($this->option('name') ?: $this->ask('Display name'));
        $password = (string) $this->secret('Password (minimum 10 characters, letters and numbers)');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(compact('name', 'email', 'password') + ['password_confirmation' => $confirmation], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:254', 'unique:admins,email'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Admin account created for {$email}.");

        return self::SUCCESS;
    }
}
