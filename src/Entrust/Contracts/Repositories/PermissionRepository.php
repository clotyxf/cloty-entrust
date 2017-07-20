<?php

namespace Cloty\Entrust\Contracts\Repositories;

/**
 * Interface PermissionRepository.
 */
interface PermissionRepository extends AbstractRepository
{
    /**
     * Create a new permission using the given name.
     *
     * @param array $attributes
     *
     * @throws \Cloty\Entrust\Exceptions\PermissionExistsException
     *
     * @return \Cloty\Entrust\EntrustPermission;
     */
    public function create(array $attributes = []);

    /**
     * get permission.
     *
     * @param int $roleId
     *
     * @return \Cloty\Entrust\EntrustPermission;
     */
    public function getPermissionTree($roleId);
}
