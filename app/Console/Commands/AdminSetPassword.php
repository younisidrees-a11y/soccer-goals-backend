<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

#[Signature('admin:set-password')]
#[Description('Create an admin panel account or reset its password, via hidden prompts')]
class AdminSetPassword extends Command
{
    public function handle(): int
    {
        $existing = AdminUser::pluck('email')->all();

        $email = $this->ask('Email address for this admin account'.($existing ? ' (existing: '.implode(', ', $existing).')' : ''));

        $validator = Validator::make(['email' => $email], ['email' => 'required|email']);
        if ($validator->fails()) {
            $this->error('That does not look like a valid email address.');

            return self::FAILURE;
        }

        $user = AdminUser::where('email', $email)->first();

        if (! $user) {
            if (! $this->confirm("No account exists for {$email}. Create a new one?")) {
                return self::SUCCESS;
            }

            $name = $this->ask('Name for this account');
            $role = $this->choice('Role', ['admin', 'editor'], 1);
            $user = new AdminUser(['email' => $email, 'name' => $name, 'role' => $role]);
        }

        $password = $this->secret('New password (input is hidden, minimum 8 characters)');
        $confirm = $this->secret('Type the password again to confirm');

        if ($password !== $confirm) {
            $this->error('Passwords did not match. Nothing was changed.');

            return self::FAILURE;
        }

        if (strlen((string) $password) < 8) {
            $this->error('Password must be at least 8 characters. Nothing was changed.');

            return self::FAILURE;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password set successfully for {$email}.");

        return self::SUCCESS;
    }
}
