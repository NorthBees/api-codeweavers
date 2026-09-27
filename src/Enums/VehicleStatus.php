<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Enums;

enum VehicleStatus: string
{
    case PreOwned = 'PreOwned';
    case Preregistered = 'Preregistered';
    case Demonstrator = 'Demonstrator';
    case New = 'New';
    case BuildToOrder = 'BuildToOrder';
}
