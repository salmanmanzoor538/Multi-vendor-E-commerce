<?php

namespace App\Enums;

enum AddressType: string
{
    case SHIPPING = 'shipping';
    case BILLING = 'billing';
}
