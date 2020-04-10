<?php

namespace Tests\Mougrim\XdebugProxy\Unit\Xml;

use Amp\Socket\ClientSocket;
use Amp\Socket\ServerSocket;
use Mougrim\XdebugProxy\Config\IdeServer;
use Mougrim\XdebugProxy\Handler\DefaultIdeHandler;
use Mougrim\XdebugProxy\Xml\XmlConverter;
use Mougrim\XdebugProxy\Xml\XmlDocument;
use Psr\Log\LoggerInterface;
use Tests\Mougrim\XdebugProxy\TestCase;

/**
 * @author Mougrim <rinat@mougrim.ru>
 */
class HandlerTest extends TestCase
{
    /**
     * @var ServerSocket
     */
    public $xdebugSocket;

    /**
     * @var ServerSocket
     */
    public $ideSocket;

    /**
     * @var ClientSocket
     */
    public $clientSocket;

    /**
     * @var DefaultIdeHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var IdeServer $config */
        $config = $this->createMock(IdeServer::class);

        /** @var XmlConverter $xmlConverter */
        $xmlConverter = $this->createMock(XmlConverter::class);

        $this->handler = new DefaultIdeHandler($logger, $config, $xmlConverter, []);

        $this->ideSocket = $this->createMock(ServerSocket::class);
        $this->xdebugSocket = $this->createMock(ServerSocket::class);
        $this->clientSocket = $this->createMock(ClientSocket::class);

    }

    public function testHandle()
    {
        $generator = $this->handler->handle($this->ideSocket);

        $this->assertIsIterable($generator);
    }

    public function testProcessRequest()
    {
        /** @var XmlDocument $xmlRequest */
        $xmlRequest = $this->createMock(XmlDocument::class);

        $rawRequest = 'rawRequest';

        $generator = $this->handler->processRequest($xmlRequest, $rawRequest, $this->xdebugSocket);

        $this->assertIsIterable($generator);
    }

    public function testClose()
    {
        $generator = $this->handler->close($this->xdebugSocket);

        $this->assertIsIterable($generator);
    }

    public function testHandleIde()
    {
        $ideKey = '???';
        $generator = $this->handler->handleIde($ideKey, $this->xdebugSocket, $this->clientSocket);

        $this->assertIsIterable($generator);
    }

    public function testParseCommand()
    {
        $request = 'breakpoint_remove -i TRANSACTION_ID -d BREAKPOINT_ID';
        $expectedRequest = $this->handler->parseCommand($request);

        $this->assertEquals($expectedRequest, ['breakpoint_remove', ['-i' => 'TRANSACTION_ID', '-d' => 'BREAKPOINT_ID']]);
    }

    public function testBuildCommand()
    {
        $command = 'breakpoint_remove';
        $args = ['-i' => 'TRANSACTION_ID', '-d' => 'BREAKPOINT_ID'];
        $expectedCommand = $this->handler->buildCommand($command, $args);

        $this->assertEquals($expectedCommand, 'breakpoint_remove -i TRANSACTION_ID -d BREAKPOINT_ID');
    }
}
