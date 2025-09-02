<?php

namespace Bdf\Fixtures\Service;

use Bdf\Api\Api;
use Bdf\Api\Application;
use Bdf\Api\Definition\Loader\PhpFileLoader as DefinitionFileLoader;
use Bdf\Api\Keys\FileRepository;
use Bdf\Api\Listeners\ApiKeyListener;
use Bdf\Api\Mapping\Loader\PhpFileLoader as MappingFileLoader;
use Bdf\Api\Mapping\Mapper;
use Bdf\Api\Mapping\Registry;
use Bdf\Api\Providers\ApiProviderInterface;
use Bdf\Api\Server\Listeners\MappingListener;
use Bdf\Soap\SoapProtocol;

class MyServiceProvider implements ApiProviderInterface
{
    public function path(): string
    {
        return '/api/{protocol}/{key}/myservice';
    }

    public function configureApi(Api $api, Application $app)
    {
        $mappingRegistry = new Registry();

        $api->setName('myservice')
            ->setServiceClass(MyService::class)
            ->setDefinition((new DefinitionFileLoader(__DIR__.'/definitions.php'))->load())
            ->addServerListener(new MappingListener(
                new Mapper(
                    $app,
                    (new MappingFileLoader(__DIR__.'/mapping.php', $mappingRegistry))->load()
                )
            ))
            ->setProtocolOptions('soap', [
                SoapProtocol::INTERNAL_WSDL => true,
            ])
        ;

        $api->setListeners([
            new ApiKeyListener(
                new FileRepository(__DIR__.'/../keys.php', 'MyService')
            )
        ]);
    }
}
