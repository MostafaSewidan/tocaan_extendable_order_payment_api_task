<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\BaseRepository;
use App\Repositories\Contracts\RepositoryInterface;

class UserRepository extends BaseRepository implements RepositoryInterface
{
    public function model()
    {
        return new User;
    }
}
