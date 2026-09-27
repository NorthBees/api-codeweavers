<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Enums;

enum VehicleType: string
{
    case Car = 'Car';
    case Lcv = 'Lcv';
    case Bike = 'Bike';
    case Other = 'Other';
}
