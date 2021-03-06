<?php

namespace App\Models\Queries;

trait FindNotEmptyGrids
{
    /**
     * Find the not empty grids.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection|\App\Models\Grid[]
     */
    public function findNotEmptyGrids($columns = ['*'])
    {
        return $this->grids()
            ->whereNotNull('building_id')
            ->get($columns);
    }
}
