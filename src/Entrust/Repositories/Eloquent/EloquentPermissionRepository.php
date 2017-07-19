<?php

namespace Cloty\Entrust\Repositories\Eloquent;

use Cloty\Entrust\Contracts\Repositories\PermissionRepository;
use Cloty\Entrust\Exceptions\PermissionExistsException;
use Cloty\Entrust\EntrustPermission;
use Illuminate\Contracts\Foundation\Application;
use Cloty\Entrust\Traits\PermissionTrait;

/**
 * Class EloquentPermissionRepository.
 */
class EloquentPermissionRepository extends AbstractEloquentRepository implements PermissionRepository
{
    use PermissionTrait;
    /**
     * @param Application $app
     * @param Permission  $model
     */
    public function __construct(Application $app, EntrustPermission $model)
    {
        parent::__construct($app, $model);
    }

    /**
     * Create a new permission using the given name.
     *
     * @param array $attributes
     *
     * @throws PermissionExistsException
     *
     * @return Permission
     */
    public function create(array $attributes = [])
    {
        if (!array_key_exists('name', $attributes) || empty($attributes['name'])) {
            throw new RoleExistsException('the current array not exists the key is name');
        }

        if (false == $name = $this->setPermissionName($attributes)) {
            throw new PermissionExistsException('The permission ' . $attributes['name'] . ' illegitimate naming');
        }

        $attributes['name'] = $name;

        if (!is_null($this->findByName($attributes['name']))) {
            // TODO: add translation support
            throw new PermissionExistsException('The permission ' . $attributes['name'] . ' already exists');
        }

        return $permission = $this->model->create($attributes);
    }
}
