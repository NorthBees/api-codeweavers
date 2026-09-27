<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests\Concerns;

use NorthBees\CodeweaversApi\Support\Payload;

/**
 * Lets callers send documented fields the SDK does not model, e.g.
 * `$parameters->with(['PromoCode' => 'SPRING'])`. Extra fields are merged last, so they win.
 *
 * Classes using this trait must have a promoted `array $extra` constructor parameter.
 */
trait HasExtraFields
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function with(array $extra): static
    {
        return new static(...[...get_object_vars($this), 'extra' => array_replace_recursive($this->extra, $extra)]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function payload(array $payload): array
    {
        /** @var array<string, mixed> */
        return Payload::filter(array_replace_recursive($payload, $this->extra));
    }
}
