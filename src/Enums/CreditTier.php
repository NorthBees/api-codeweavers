<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Enums;

/**
 * Customer selected credit tiers, used by lenders with risk based pricing.
 */
enum CreditTier: string
{
    case BelowAverage = 'BelowAverage';
    case Fair = 'Fair';
    case Good = 'Good';
    case VeryGood = 'VeryGood';
    case Excellent = 'Excellent';
}
