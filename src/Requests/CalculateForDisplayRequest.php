<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests;

use NorthBees\CodeweaversApi\Requests\Concerns\HasExtraFields;

/**
 * Full finance quotes (with display content) for one or more vehicles.
 */
final readonly class CalculateForDisplayRequest
{
    use HasExtraFields;

    /**
     * @param  list<VehicleRequest>  $vehicles
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public array $vehicles,
        public array $extra = [],
    ) {}

    public static function forVehicle(string $id, PhysicalVehicle $vehicle, FinanceParameters $parameters = new FinanceParameters): self
    {
        return new self([new VehicleRequest($id, $vehicle, $parameters)]);
    }

    /**
     * Run every vehicle request as the given organisation, unless it names its own.
     */
    public function withDefaultOrganisation(OrganisationIdentifier $organisation): self
    {
        return new self(
            array_map(
                fn (VehicleRequest $request): VehicleRequest => $request->parameters->organisation === null
                    ? $request->withParameters($request->parameters->withOrganisation($organisation))
                    : $request,
                $this->vehicles,
            ),
            $this->extra,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload([
            'VehicleRequests' => array_map(fn (VehicleRequest $request): array => $request->toArray(), $this->vehicles),
        ]);
    }
}
