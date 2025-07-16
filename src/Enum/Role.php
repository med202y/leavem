<?php

namespace App\Enum;

enum Role:string
{
    case User = 'ROLE_USER';
    case EMPLOYEE = 'ROLE_EMPLOYEE';
    case MANAGER = 'ROLE_MANAGER';
}
