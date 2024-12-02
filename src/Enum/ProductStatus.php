<?php

namespace App\Enum;

enum ProductStatus: string
{
    case AVAILABLE = 'disponible';
    case OUT_OF_STOCK = 'en rupture';
    case PRE_ORDER = 'en précommande';
}
