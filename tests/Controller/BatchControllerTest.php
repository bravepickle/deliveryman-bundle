<?php
/**
 * Date: 15.01.19
 * Time: 13:48
 */

namespace DeliverymanBundleTest\Controller;

use Deliveryman\Entity\BatchRequest;
use Deliveryman\Entity\BatchResponse;
use DeliverymanBundle\Controller\BatchController;
use DeliverymanBundle\DeliverymanBundle;
use DeliverymanBundle\EventListener\AfterSendEvent;
use DeliverymanBundle\EventListener\BeforeSendEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class BatchControllerTest extends TestCase
{
    /**
     * @throws \Deliveryman\Exception\SendingException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Exception
     */
    public function testHttpGraph()
    {
        $dispatcher = new EventDispatcher();
        $controller = new BatchController();
        $controller->setContainer($this->getContainer());
        $request = new Request([], [], [], [], [], [], '{"data":[]}');

        $response = $controller->httpGraph($request, $dispatcher);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('{"data":null,"status":"aborted","errors":{"data":["This value should not be blank."]},"failed":null}',
            $response->getContent())
        ;
    }

    /**
     * Test setting config name for controller
     */
    public function testConfigNameSet()
    {
        $controller = new BatchController();

        $this->assertEquals('default', $controller->getConfigName()); // default value

        $configName = 'test_config_name';
        $controller->setConfigName($configName);
        $this->assertEquals($configName, $controller->getConfigName());
    }

    /**
     * @throws \Deliveryman\Exception\SendingException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Exception
     */
    public function testHttpGraphEventDispatching()
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener('deliveryman_bundle.after_send', function($event) {
            /** @var AfterSendEvent $event */
            $this->assertInstanceOf(AfterSendEvent::class, $event);
            $this->assertInstanceOf(BatchResponse::class, $event->getBatchResponse());
            $this->assertEquals('http_graph', $event->getType());
        });

        $dispatcher->addListener('deliveryman_bundle.before_send', function($event) {
            /** @var BeforeSendEvent $event */
            $this->assertInstanceOf(BeforeSendEvent::class, $event);
            $this->assertInstanceOf(BatchRequest::class, $event->getBatchRequest());
            $this->assertEquals('http_graph', $event->getType());
            $this->assertNull($event->getBatchRequest()->getData());
        });

        $controller = new BatchController();
        $controller->setContainer($this->getContainer());
        $request = new Request([], [], [], [], [], [], '{"data":null}');

        $controller->httpGraph($request, $dispatcher);
    }

    /**
     * @return ContainerInterface
     * @throws \Exception
     */
    protected function getContainer()
    {
        $containerBuilder = new ContainerBuilder();
        $bundle = new DeliverymanBundle();
        $extension = $bundle->getContainerExtension();

        $extension->load([[
            'instances' => [
                'default' => [
                    'type' => 'default',
                ],
            ],
        ]], $containerBuilder);

        $bundle->build($containerBuilder);
        $containerBuilder->loadFromExtension('deliveryman');

        $this->addSerializer($containerBuilder);

        $containerBuilder->compile();

        return $containerBuilder;
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addSerializer(ContainerBuilder $container): void
    {
        $encoderDef = new Definition(JsonEncoder::class);
        $encoderDef->addTag('serializer.encoder');
        $container->setDefinition('serializer.encoder.json', $encoderDef);

        $normalizerDef = new Definition(GetSetMethodNormalizer::class);
        $normalizerDef->addTag('serializer.normalizer');
        $container->setDefinition('customNormalizer', $normalizerDef);

        $definition = new Definition(Serializer::class);
        $definition->setArgument(0, []);
        $definition->setArgument(1, []);
        $definition->setPublic(true);

        $container->setDefinition('serializer', $definition);
        $container->addCompilerPass(new SerializerPass());
    }
}
