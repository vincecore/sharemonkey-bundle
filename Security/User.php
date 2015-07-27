<?php

namespace ShareMonkey\ShareMonkeyBundle\Security;

use IMAG\LdapBundle\User\LdapUser;

class User extends LdapUser
{
    public function __construct()
    {
        $this->addRole('ROLE_USER');
    }
}
