<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Enums;

enum DepositType: string
{
    case Amount = 'Amount';
    case Percentage = 'Percentage';
    case AdvancePayments = 'AdvancePayments';
}
