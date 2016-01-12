<?php

namespace AppBundle\Entity;

class UserRepository
{
    public function create($username)
    {
        $entity = new User($username);

        return $entity;
    }
}
