<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PREPARING = 'en préparation';
    case SHIPPED = 'expédiée';
    case DELIVERED = 'livrée';
    case CANCELLED = 'annulée';
}
