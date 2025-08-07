<?php

namespace Core\Tests;


use Core\App\App;
use Core\Tests\Mocks\RedisMock;
use Core\Tests\Mocks\DynamoMock;
use Core\Utils\Config;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;


class BaseTestCase extends TestCase
{
    protected static ContainerInterface $container;
    protected static string $module;
    protected array $default_headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
    static bool $sqlite_executed = false;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        self::$container = self::getInstance()->getContainer();
    }

    /**
     * @param ContainerInterface|null $container
     * @return App
     */
    protected static function getInstance(?ContainerInterface $container = null): App
    {
        return app();
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $headers
     * @param array $data
     * @param array $cookies
     * @return ServerRequestInterface
     */
    protected function getRequest(
        string $method,
        string $path,
        array  $headers = [],
        array  $data = [],
        array  $cookies = [],
    ): ServerRequestInterface
    {
        $params = [];
        if ($method === "GET") {
            $params = $data;
        }
        $req = $this->createRequest($method, $path, $params, $headers, $cookies);
        if ($method === "GET") {
            $req = $req->withQueryParams($data);
        }
        if (in_array($method, ["POST", "PUT", "PATCH"])) {
            $req = $req->withParsedBody($data);
        }
        return $req;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $headers
     * @param array $cookies
     * @param array $serverParams
     * @return Request|ServerRequestInterface
     */
    protected function createRequest(
        string $method,
        string $path,
        array  $headers = [],
        array  $cookies = [],
        array  $serverParams = []
    ): Request|ServerRequestInterface
    {
        $uri = new Uri('', '', 80, $path);
        $handler = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handler);

        $headers = array_merge($headers, $this->default_headers);
        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new Request($method, $uri, $h, $cookies, $serverParams, $stream);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function sendHttpRequest(ServerRequestInterface $request): ResponseInterface
    {
        $app = self::getInstance();
        $response = $app->handle($request);
        $response->getBody()->rewind();
        return $response;

    }

    /**
     * @param string $path
     * @param array $params
     * @param array $cookies
     * @param array $headers
     * @return ResponseInterface
     */
    protected function get(string $path, array $params = [], array $cookies = [], array $headers = []): ResponseInterface
    {
        $req = $this->getRequest("GET", $path, $params, $headers, $cookies);
        return $this->sendHttpRequest($req);

    }

    /**
     * @param string $path
     * @param array $data
     * @param array $cookies
     * @param array $headers
     * @return ResponseInterface
     */
    protected function post(string $path, array $data = [], array $cookies = [], array $headers = []): ResponseInterface
    {
        $req = $this->getRequest("POST", $path, $data, $headers, $cookies);
        return $this->sendHttpRequest($req);

    }

    /**
     * @param string $path
     * @param array $data
     * @param array $cookies
     * @param array $headers
     * @return ResponseInterface
     */
    protected function put(string $path, array $data = [], array $cookies = [], array $headers = []): ResponseInterface
    {
        $req = $this->getRequest("PUT", $path, $data, $headers, $cookies);
        return $this->sendHttpRequest($req);

    }

    /**
     * @param string $path
     * @param array $data
     * @param array $cookies
     * @param array $headers
     * @return ResponseInterface
     */
    protected function delete(string $path, array $data = [], array $cookies = [], array $headers = []): ResponseInterface
    {
        $req = $this->getRequest("DELETE", $path, $data, $headers, $cookies);
        return $this->sendHttpRequest($req);

    }

    /**
     * Executa um comando shell de forma segura
     * @param string $command
     * @param string $errorMessage
     * @return bool
     * @throws \RuntimeException
     */
    protected static function executeCommand(string $command, string $errorMessage): bool
    {
        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            $outputStr = implode("\n", $output);
            error_log("Comando falhou: {$command}");
            error_log("Saída: {$outputStr}");
            throw new \RuntimeException("{$errorMessage}: {$outputStr}");
        }
        
        return true;
    }

    /**
     * @return void
     */
    protected static function configDbSqlite(): void
    {
        if (static::$sqlite_executed) {
            return;
        }

        try {
            $dbs = Config::singleton()->get('db', []);
            $testUser = getenv('TEST_USER') ?: 'www-data';
            
            foreach ($dbs as $alias => $settings) {
                if ($alias == 'default') {
                    continue;
                }
                if ($settings['driver'] == 'sqlite') {
                    continue;
                }

                $dir = dirname($settings['database']);
                $arquivo = $settings['database'];

                if (empty($dir)) {
                    continue;
                }

                error_log("Configurando SQLite para testes: {$arquivo}");

                // Remove arquivo existente se existir
                if (file_exists($arquivo)) {
                    static::executeCommand(
                        "rm -f " . escapeshellarg($arquivo),
                        "Falha ao remover arquivo SQLite existente"
                    );
                }

                // Cria diretório se não existir
                if (!is_dir($dir)) {
                    static::executeCommand(
                        "mkdir -p " . escapeshellarg($dir),
                        "Falha ao criar diretório para SQLite"
                    );
                }

                // Cria arquivo SQLite
                static::executeCommand(
                    "touch " . escapeshellarg($arquivo),
                    "Falha ao criar arquivo SQLite"
                );

                // Define permissões
                static::executeCommand(
                    "chown -R {$testUser}:{$testUser} " . escapeshellarg($dir),
                    "Falha ao definir permissões do diretório"
                );
                
                static::executeCommand(
                    "chown {$testUser}:{$testUser} " . escapeshellarg($arquivo),
                    "Falha ao definir permissões do arquivo"
                );

                // Define permissões de escrita
                static::executeCommand(
                    "chmod 755 " . escapeshellarg($dir),
                    "Falha ao definir permissões de acesso do diretório"
                );
                
                static::executeCommand(
                    "chmod 644 " . escapeshellarg($arquivo),
                    "Falha ao definir permissões de acesso do arquivo"
                );

                error_log("SQLite configurado com sucesso: {$arquivo}");
            }
            
            static::$sqlite_executed = true;
            
        } catch (\Exception $e) {
            error_log("Erro ao configurar SQLite: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param RedisMock $mock
     * @return void
     *
     */
    protected static function mockContainerRedis(RedisMock $mock): void
    {
        $container = self::getInstance()->getContainer();
        $configs = Config::singleton();

        foreach ($configs as $alias => $settings) {
            if ($alias == 'default') {
                continue;
            }
            $container->set("cache.$alias", function () use ($mock) {
                return $mock;
            });
        }
    }

    /**
     * @param DynamoMock $mock
     * @return void
     */
    protected static function mockContainerDynamo(DynamoMock $mock): void
    {
        $container = self::getInstance()->getContainer();
        $configs = Config::singleton();

        foreach ($configs as $alias => $settings) {
            if ($alias == 'default') {
                continue;
            }
            $container->set("dynamo.$alias", function () use ($mock) {
                return $mock;
            });
        }
    }

    /**
     * Helper para validar resposta JSON
     * @param ResponseInterface $response
     * @param int $expectedStatusCode
     * @return array
     */
    protected function assertJsonResponse(ResponseInterface $response, int $expectedStatusCode = 200): array
    {
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
        
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        
        $this->assertNotFalse($data, 'Resposta deve ser JSON válido');
        
        return $data;
    }

    /**
     * Helper para criar dados de teste
     * @param array $overrides
     * @return array
     */
    protected function createTestData(array $overrides = []): array
    {
        $defaultData = [
            'id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return array_merge($defaultData, $overrides);
    }

    /**
     * Helper para limpar dados de teste
     * @param string $table
     * @return void
     */
    protected function cleanTestData(string $table): void
    {
        try {
            $container = self::getInstance()->getContainer();
            $db = $container->get('db.default');
            $db->exec("DELETE FROM {$table}");
        } catch (\Exception $e) {
            error_log("Erro ao limpar dados de teste: " . $e->getMessage());
        }
    }

}