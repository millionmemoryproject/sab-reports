<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateUser extends Command
{
    protected $signature = 'user:create {email?} {--name=} {--role=manager : administrator or manager}';

    protected $description = 'Create a reporting user (login-only, no public registration)';

    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Email');
        $name = $this->option('name') ?: $this->ask('Name');
        $role = $this->option('role');
        $password = $this->secret('Password');

        $validator = Validator::make(
            compact('email', 'name', 'password', 'role'),
            [
                'email' => ['required', 'email', 'unique:users,email'],
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
                'role' => ['required', Rule::enum(UserRole::class)],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // The User model casts 'password' as 'hashed', so it is hashed on save.
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ]);

        $this->info("Created {$user->role->value} {$user->email} (id {$user->id}).");

        return self::SUCCESS;
    }
}
