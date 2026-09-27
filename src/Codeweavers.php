<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi;

use NorthBees\CodeweaversApi\Enums\Environment;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversMissingCredentialsException;
use NorthBees\CodeweaversApi\Http\HttpTransport;
use NorthBees\CodeweaversApi\Requests\OrganisationIdentifier;
use NorthBees\CodeweaversApi\Resources\FinanceResource;
use NorthBees\CodeweaversApi\Resources\JsonFinanceResource;

/**
 * Entry point for the Codeweavers API. Immutable: the with*() methods return new
 * instances, so one tenant's credentials or dealer never leak into another's.
 */
final readonly class Codeweavers
{
    /**
     * @param  array<string, mixed>  $config  the resolved `codeweavers` config
     */
    public function __construct(
        private HttpTransport $transport,
        private array $config,
        private ?CodeweaversCredentials $credentials = null,
        private ?Environment $environment = null,
        private ?OrganisationIdentifier $organisation = null,
    ) {}

    public function withCredentials(CodeweaversCredentials $credentials): self
    {
        return new self($this->transport, $this->config, $credentials, $this->environment, $this->organisation);
    }

    public function withEnvironment(Environment $environment): self
    {
        return new self($this->transport, $this->config, $this->credentials, $environment, $this->organisation);
    }

    /**
     * The organisation (dealer) requests are run as, when a request does not name its own.
     */
    public function withOrganisation(OrganisationIdentifier $organisation): self
    {
        return new self($this->transport, $this->config, $this->credentials, $this->environment, $organisation);
    }

    /**
     * Instance credentials, falling back to config.
     *
     * @throws CodeweaversMissingCredentialsException
     */
    public function credentials(): CodeweaversCredentials
    {
        return $this->credentials
            ?? CodeweaversCredentials::fromConfig($this->config)
            ?? throw new CodeweaversMissingCredentialsException('A Codeweavers API key has not been configured.');
    }

    public function hasCredentials(): bool
    {
        return ($this->credentials ?? CodeweaversCredentials::fromConfig($this->config)) !== null;
    }

    public function environment(): Environment
    {
        return $this->environment
            ?? Environment::tryFrom(strtolower((string) ($this->config['environment'] ?? 'production')))
            ?? Environment::Production;
    }

    /**
     * Instance organisation, falling back to the configured dealer key.
     */
    public function organisation(): ?OrganisationIdentifier
    {
        $dealerKey = $this->config['dealer_key'] ?? null;

        return $this->organisation
            ?? (is_string($dealerKey) && $dealerKey !== '' ? OrganisationIdentifier::associatedDealerKey($dealerKey) : null);
    }

    public function baseUrl(): string
    {
        $configured = data_get($this->config, 'base_urls.'.$this->environment()->value);

        return is_string($configured) && $configured !== '' ? $configured : $this->environment()->defaultBaseUrl();
    }

    public function finance(): FinanceResource
    {
        return new FinanceResource($this, $this->transport);
    }

    public function jsonFinance(): JsonFinanceResource
    {
        return new JsonFinanceResource($this, $this->transport);
    }
}
