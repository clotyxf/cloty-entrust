<?php

namespace Cloty\Entrust\Repositories\Eloquent;

use Cloty\Entrust\EntrustRole;
use Illuminate\Contracts\Foundation\Application;
use Cloty\Entrust\Exceptions\RoleExistsException;
use Cloty\Entrust\Contracts\Repositories\RoleRepository;

/**
 * Class EloquentRoleRepository.
 */
class EloquentRoleRepository extends AbstractEloquentRepository implements RoleRepository
{
    /**
     * @param Application $app
     * @param Role        $model
     */
    public function __construct(Application $app, EntrustRole $model)
    {
        parent::__construct($app, $model);
    }

    /**
     * Create a new role with the given name.
     *
     * @param array $attributes
     *
     * @throws \Exception
     *
     * @return Role
     */

    public function create(array $attributes = [])
    {
        if (!array_key_exists('name', $attributes)) {
            throw new RoleExistsException('A role with the given name not exists');
        }

        if (! is_null($this->findByName($attributes['name']))) {
            // TODO: add translation support
            throw new RoleExistsException('A role with the given name already exists');
        }

        return $role = $this->model->create($attributes);
    }
}
