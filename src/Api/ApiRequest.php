<?php

namespace Bdf\Api;

use Bdf\Api\Keys\ApiKey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Throwable;

/**
 * Class ApiRequest
 *
 * @package Bdf\Api
 */
class ApiRequest extends Request
{
    protected int $threadId;
    protected ?ApiKey $apiKey = null;
    protected ?Throwable $exception = null;


    /**
     * {@inheritdoc}
     */
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);

        $this->threadId = mt_rand(0, 2000);
    }

    public function getThreadId(): int
    {
        return $this->threadId;
    }

    public function getApiName(): string
    {
        $api = $this->attributes->get('_api');

        if (!$api instanceof Api) {
            return '';
        }

        return $api->getName();
    }

    public function getApiUri(): string
    {
        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $this->getPathInfo();
    }

    public function getApiProtocol(): ?string
    {
        return $this->attributes->get('protocol');
    }

    public function getApiVersion(): ?string
    {
        return $this->attributes->get('version');
    }

    /**
     * Get array of options for the API
     *
     * Option format : (-)optionName:(-)otherOption...
     * With - for set false on the option
     *
     * Ex: '-nillable|minOccurs' => ['nillable' => false, 'minOccurs' => true]
     *
     * @return bool[]
     */
    public function getApiOptions(): array
    {
        if (!$this->attributes->has('options')) {
            return [];
        }

        $options = [];

        foreach (explode(':', $this->attributes->get('options')) as $option) {
            if (empty($option)) {
                continue;
            }

            if ($option[0] === '-') {
                $options[substr($option, 1)] = false;
            } else {
                $options[$option] = true;
            }
        }

        return $options;
    }

    public function getEnvironment(): ?string
    {
        return $this->server->get('APPLICATION_ENV') ?: $this->server->get('APP_ENV');
    }

    public function setApiKey(ApiKey $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getApiKey(): ApiKey
    {
        return $this->apiKey ??= (new Keys\NullRepository())->get(null);
    }
    
    public function api(): ?Api
    {
        $api = $this->attributes->get('_api');

        return $api instanceof Api ? $api : null;
    }

    public function setException(Throwable $exception): void
    {
        $this->exception = $exception;
    }

    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    public function hasException(): bool
    {
        return $this->exception !== null;
    }

    public static function createFromBase(BaseRequest $request): static
    {
        if ($request instanceof static) {
            return $request;
        }

        $new = (new static)->duplicate(
            $request->query->all(), $request->request->all(), $request->attributes->all(),
            $request->cookies->all(), $request->files->all(), $request->server->all()
        );

        $new->session = $request->session;
        $new->defaultLocale = $request->defaultLocale;
        $new->locale = $request->locale;

        return $new;
    }
}
