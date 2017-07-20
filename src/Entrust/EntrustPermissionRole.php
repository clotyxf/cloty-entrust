<?php

namespace Cloty\Entrust;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EntrustPermissionRole.
 */
class EntrustPermissionRole extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table;

    /**
     * Mass-assignment whitelist.
     *
     * @var array
     */
    protected $fillable = [
        'p_id',
        'name',
        'display_name',
        'description'
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('entrust.permission_role_table', 'entrust_permission_roles');
    }
}
