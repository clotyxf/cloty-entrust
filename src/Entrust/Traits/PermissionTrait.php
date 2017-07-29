<?php

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Cloty\Entrust
 *
 */

namespace Cloty\Entrust\Traits;

use Cloty\Entrust\EntrustPermission;
use Cloty\Entrust\EntrustPermissionRole;

trait PermissionTrait
{
    protected $permAttributes;
    /**
     * Checks if the permission is highest
     *
     * @param int $pid
     *
     * @return bool
     */
    public function isHighestPerms($pid)
    {
        return $pid == 0;
    }

    /**
     * Check the permission is specification
     *
     * @param array $attributes
     *
     * @return bool
     */
    public function setPermissionName(array $attributes)
    {
        $name = $attributes['name'];

        if (!array_key_exists('p_id', $attributes) || $this->isHighestPerms($attributes['p_id'])) {
            return $name;
        }

        $this->permAttributes = $attributes;

        $permissionName = $this->getParentPermissionName($attributes['p_id']);

        if (!$permissionName) {
            return false;
        }

        $prefix = $permissionName . '_';

        if (!$this->permNamePrefixExists($prefix)) {
            return $prefix . $name;
        }

        return $name;
    }

    /**
     * get parent permission by p_id
     * @param int $pid
     * @return bool|string
     */
    public function getParentPermissionName($pid)
    {
        $permissionName = EntrustPermission::where('id', $pid)->value('name');

        if (is_null($permissionName)) {
            return false;
        }

        return $this->getNamePrefix($permissionName);
    }

    /**
     * get permission prefix
     * @param string $permissionName
     * @return string
     */
    public function getNamePrefix($permissionName)
    {
        if (!str_finish($permissionName, '_')) {
            return $permissionName . '_';
        }

        return $permissionName;
    }

    /**
     * Check if a permission with the given prefix exists.
     *
     * @param string $prefix
     *
     * @return bool
     */
    public function permNamePrefixExists($prefix)
    {
        $name = $this->permAttributes['name'];

        return starts_with($name, $prefix);
    }

    public function getPermissionTree($roleId)
    {
        $permissions = EntrustPermission::all(['id', 'name', 'p_id', 'display_name', 'description'])->toArray();

        $tree = $hasPermissions = [];

        if (!empty($roleId)) {
            $hasPermissions = EntrustPermissionRole::where('role_id', $roleId)->get(['permission_id'])->toArray();

            $hasPermissions = array_pluck($hasPermissions, 'permission_id');
        }


        foreach ($permissions as $key => $data) {
            if (!empty($hasPermissions)) {
                if (in_array($data['id'], $hasPermissions)) {
                    $permissions[$key]['is_perm'] = true;
                } else {
                    $permissions[$key]['is_perm'] = false;
                }
            }

            $array[$data['id']] =& $permissions[$key];
        }

        foreach ($permissions as $key => $data) {
            $parentId =  $data['p_id'];

            if ($parentId == 0) {
                $tree[] =& $permissions[$key];
            } else {
                if (isset($array[$parentId])) {

                    $parent =& $array[$parentId];

                    $parent['_child'][] =& $permissions[$key];
                }
            }
        }

        return $tree;
    }

    /**
     * Refresh the cache
     *
     * @return boolean
     */
    public function flushCache()
    {
        if (Cache::store(config('entrust.cache_driver'))->getStore() instanceof TaggableStore) {
            logger()->info('flush_cache');
            Cache::store(config('entrust.cache_driver'))->tags(Config::get('entrust.permission_role_table'))->flush();
        }

        return true;
    }
}
