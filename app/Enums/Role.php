<?php
namespace App\Enums;
enum Role: string
{
    case Customer = 'customer';
    case Provider = 'provider';
    case Admin = 'admin';
}
