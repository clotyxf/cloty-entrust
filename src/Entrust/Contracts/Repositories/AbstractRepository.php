<?php

namespace Cloty\Entrust\Contracts\Repositories;

/**
 * Interface AbstractRepository.
 */
interface AbstractRepository
{
    /**
     * @return mixed
     */
    public function all();

    /**
     * @param int $perPage
     *
     * @return mixed
     */
    public function paginate($perPage = 10);

    /**
     * @param array $with
     *
     * @return mixed
     */
    public function make(array $with = []);

    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findById($id);

    /**
     * @param string $name
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findByName($name);

    /**
     * @param array $value
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getList($columns = ['*']);
}
