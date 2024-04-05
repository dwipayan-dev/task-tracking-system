<?php

namespace App\Providers;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\Role;
use App\Models\Permission;
use Exception;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        Passport::hashClientSecrets();
        try {
            // roles gate
            $roles = Role::all();
            foreach($roles as $role) {
                Gate::define($role->name, function($user) use ($role) {
                    return $user->hasRole($role->name);
                });
            }

            // permissions gate
            $permissions = Permission::all();
            foreach($permissions as $permission) {
                Gate::define($permission->name, function($user) use ($permission) {
                    return $user->hasPermission($permission->name);
                });
            }
        } catch(Exception $e) {
            return $e;
        }
    }
}
