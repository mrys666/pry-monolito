<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Domain\Access\Enums\UserRole;
use App\Domain\Organization\Actions\CreateOrganizationAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Inyectamos la Action mediante el constructor.
     * Fortify instanciará esta clase a través del contenedor de Laravel,
     * por lo que resolverá la dependencia automáticamente.
     */
    public function __construct(
        private CreateOrganizationAction $createOrganization
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {

            // Usamos la propiedad inyectada en el constructor
            $organization = $this->createOrganization->handle(
                $input['name'] . "'s Organization"
            );

            return User::create([
                'organization_id' => $organization->id,
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => UserRole::Admin,
            ]);
        });
    }
}
