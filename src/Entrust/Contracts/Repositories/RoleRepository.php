<?php

namespace Cloty\Entrust\Contracts\Repositories;

/**
 * Interface RoleRepository.
 */
interface RoleRepository extends AbstractRepository
{
    /**
     * Create a new role with the given name.
     *
     * @param string $roleName
     *
     * @throws \Exception
     *
     * @return \Cloty\Entrust\EntrustRole
     */
    public function create(array $attributes = []);
}
