<?php

namespace Cloty\Entrust;

use Illuminate\Database\Eloquent\Model;
use Cloty\Entrust\Traits\User\RoleTrait;

/**
 * Class EntrustRole.
 */
class EntrustRole extends Model
{
    use RoleTrait;

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

        $this->table = config('entrust.roles_table', 'entrust_roles');
    }
}
