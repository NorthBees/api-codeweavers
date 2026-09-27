<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Enums;

enum VatStatus: string
{
    case NotPaid = 'NotPaid';
    case FullyPaid = 'FullyPaid';
    case Margin = 'Margin';
    case StandardVatAsDeposit = 'StandardVatAsDeposit';
}
