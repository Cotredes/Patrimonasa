<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CrearUsuario extends Command
{
    protected $signature = 'patrimonasa:crear-usuario
                            {email : Correo de la cuenta}
                            {--nombre= : Nombre de la persona}
                            {--admin : Concede el rol de administrador}
                            {--password= : Contraseña (si se omite, se pide de forma oculta)}';

    protected $description = 'Crea una cuenta familiar (o la actualiza si ya existe). Solo por SSH, nunca público.';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $name = trim((string) ($this->option('nombre') ?: explode('@', $email)[0]));

        $validator = Validator::make(
            ['email' => $email, 'name' => $name],
            ['email' => ['required', 'email', 'max:255'], 'name' => ['required', 'string', 'max:100']]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $password = (string) $this->option('password');
        if ($password === '') {
            $password = (string) $this->secret('Contraseña (8 caracteres como mínimo)');
            $repeat = (string) $this->secret('Repite la contraseña');
            if ($password !== $repeat) {
                $this->error('Las contraseñas no coinciden.');

                return self::FAILURE;
            }
        }

        if (strlen($password) < 8) {
            $this->error('La contraseña debe tener al menos 8 caracteres.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
                'role' => $this->option('admin') ? 'admin' : $user->role,
            ]);
            $this->info("Cuenta actualizada: {$email} (rol: {$user->role}).");

            return self::SUCCESS;
        }

        $role = $this->option('admin') || User::count() === 0 ? 'admin' : 'family';
        User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password), 'role' => $role]);
        $this->info("Cuenta creada: {$email} (rol: {$role}).");

        return self::SUCCESS;
    }
}
