<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case STRIPE = 'stripe';
    case COD = 'cod';
}
