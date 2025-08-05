<?php

declare(strict_types=1);

namespace Core\Action;

use Core\App\Action;
use Core\Utils\Path;
use Core\Utils\Str;
use Core\Utils\Time;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Health Check Action
 *
 * Endpoint para verificação de saúde dos serviços da aplicação.
 * Verifica conectividade com cache (Redis), banco de dados, storage (S3) e DynamoDB.
 *
 * @package Core\Action
 * @author JonJon1992
 * @version 2.3.0
 */
class ImAliveAction extends Action
{
    private const HEALTHY_CHECK_FILE = '/im-alive.log';
    private const CACHE_DURATION = 30; // segundos
    private const MAX_LOG_ENTRIES = 100;

    private Filesystem $filesystem;
    private LoggerInterface $logger;

    protected array $responseData = [];
    private int $deadServices = 0;
    private float $startTime;

    /**
     * Executa a verificação de saúde dos serviços
     *
     * @return array|ResponseInterface
     * @throws Exception
     */
    protected function action(): array|ResponseInterface
    {
        $this->startTime = microtime(true);
        $this->filesystem = new Filesystem();
        $this->logger = logger();

        // Verifica se deve usar cache
        if ($this->shouldUseCache()) {
            return $this->getCachedResponse();
        }

        $service = $this->queryParam('service', '');

        // Verifica serviços de cache
        $this->checkCacheServices($service);

        // Verifica serviços de banco de dados
        $this->checkDatabaseServices($service);

        // Verifica serviços de storage
        $this->checkStorageServices($service);

        // Verifica serviços DynamoDB
        $this->checkDynamoServices($service);

        // Adiciona métricas gerais
        $this->addGeneralMetrics();

        // Processa resultado
        if ($this->deadServices > 0) {
            return $this->handleFailure();
        }

        // Salva log de sucesso
        $this->saveSuccessLog();

        return $this->responseData;
    }

    /**
     * Verifica se deve usar resposta em cache
     *
     * @return bool
     */
    private function shouldUseCache(): bool
    {
        $forceCheck = $this->queryParam('force', '');
        $cacheFile = Path::temp(self::HEALTHY_CHECK_FILE);

        return $this->filesystem->exists($cacheFile) && empty($forceCheck);
    }

    /**
     * Retorna resposta em cache
     *
     * @return array
     */
    private function getCachedResponse(): array
    {
        return [
            'status' => 'OK',
            'cached' => true,
            'timestamp' => time(),
            'message' => 'Using cached health check result'
        ];
    }

    /**
     * Verifica serviços de cache (Redis)
     *
     * @param string $service
     * @return void
     */
    private function checkCacheServices(string $service): void
    {
        $cacheConfigs = config('cache', []);

        foreach (array_keys($cacheConfigs) as $alias) {
            if ($alias === 'default') {
                continue;
            }

            $key = "cache.$alias";
            if (empty($service) || Str::has($key, $service)) {
                $this->responseData[$key] = $this->checkCache($alias);
                $this->updateDeadCount($this->responseData[$key]['status']);
            }
        }
    }

    /**
     * Verifica serviços de banco de dados
     *
     * @param string $service
     * @return void
     */
    private function checkDatabaseServices(string $service): void
    {
        $dbConfigs = config('db', []);

        foreach (array_keys($dbConfigs) as $alias) {
            if ($alias === 'default') {
                continue;
            }

            $key = "db.$alias";
            if (empty($service) || Str::has($key, $service)) {
                $this->responseData[$key] = $this->checkDatabase($alias);
                $this->updateDeadCount($this->responseData[$key]['status']);
            }
        }
    }

    /**
     * Verifica serviços de storage (S3)
     *
     * @param string $service
     * @return void
     */
    private function checkStorageServices(string $service): void
    {
        $storageConfigs = config('storage', []);

        foreach (array_keys($storageConfigs) as $alias) {
            if ($alias === 'default') {
                continue;
            }

            $key = "storage.$alias";
            if (empty($service) || Str::has($key, $service)) {
                $this->responseData[$key] = $this->checkStorage($alias);
                $this->updateDeadCount($this->responseData[$key]['status']);
            }
        }
    }

    /**
     * Verifica serviços DynamoDB
     *
     * @param string $service
     * @return void
     */
    private function checkDynamoServices(string $service): void
    {
        $dynamoConfigs = config('dynamo', []);

        foreach (array_keys($dynamoConfigs) as $alias) {
            if ($alias === 'default') {
                continue;
            }

            $key = "dynamo.$alias";
            if (empty($service) || Str::has($key, $service)) {
                $this->responseData[$key] = $this->checkDynamo($alias);
                $this->updateDeadCount($this->responseData[$key]['status']);
            }
        }
    }

    /**
     * Adiciona métricas gerais à resposta
     *
     * @return void
     */
    private function addGeneralMetrics(): void
    {
        $totalTime = microtime(true) - $this->startTime;

        $this->responseData['_metrics'] = [
            'total_services' => count($this->responseData),
            'dead_services' => $this->deadServices,
            'healthy_services' => count($this->responseData) - $this->deadServices,
            'total_time' => round($totalTime * 1000, 2), // em milissegundos
            'timestamp' => time(),
            'status' => $this->deadServices > 0 ? 'unhealthy' : 'healthy'
        ];
    }

    /**
     * Atualiza contador de serviços mortos
     *
     * @param string $status
     * @return void
     */
    private function updateDeadCount(string $status): void
    {
        if ($status === 'dead') {
            $this->deadServices++;
        }
    }

    /**
     * Trata falha na verificação de saúde
     *
     * @return ResponseInterface
     */
    private function handleFailure(): ResponseInterface
    {
        $this->logger->critical('Health check failed', [
            'dead_services' => $this->deadServices,
            'response' => $this->responseData,
            'timestamp' => time()
        ]);

        return $this->respondWithJSON($this->responseData, 503);
    }

    /**
     * Salva log de sucesso
     *
     * @return void
     */
    private function saveSuccessLog(): void
    {
        $logFile = Path::temp(self::HEALTHY_CHECK_FILE);
        $timestamp = time();

        // Adiciona nova entrada
        $this->filesystem->append($logFile, $timestamp . "\n");

        // Limita número de entradas no log
        $this->limitLogEntries($logFile);
    }

    /**
     * Limita número de entradas no arquivo de log
     *
     * @param string $logFile
     * @return void
     */
    private function limitLogEntries(string $logFile): void
    {
        if (!$this->filesystem->exists($logFile)) {
            return;
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (count($lines) > self::MAX_LOG_ENTRIES) {
            $lines = array_slice($lines, -self::MAX_LOG_ENTRIES);
            $this->filesystem->put($logFile, implode("\n", $lines) . "\n");
        }
    }

    /**
     * Retorna informações padrão de saúde
     *
     * @return array
     */
    private function getDefaultHealthInfo(): array
    {
        return [
            'status' => 'alive',
            'latency' => 0,
            'checked_at' => time()
        ];
    }

    /**
     * Verifica serviço de cache (Redis)
     *
     * @param string $alias
     * @return array
     */
    private function checkCache(string $alias): array
    {
        $info = $this->getDefaultHealthInfo();
        $start = microtime(true);

        try {
            redis($alias)->ping();
            $info['details'] = 'Redis connection successful';
        } catch (Exception $e) {
            $info['status'] = 'dead';
            $info['error'] = $e->getMessage();
            $info['details'] = 'Redis connection failed';
        }

        $info['latency'] = Time::latency($start);
        return $info;
    }

    /**
     * Verifica serviço de banco de dados
     *
     * @param string $alias
     * @return array
     */
    private function checkDatabase(string $alias): array
    {
        $info = $this->getDefaultHealthInfo();
        $start = microtime(true);

        try {
            db($alias)->getPdo();
            $info['details'] = 'Database connection successful';
        } catch (Exception $e) {
            $info['status'] = 'dead';
            $info['error'] = $e->getMessage();
            $info['details'] = 'Database connection failed';
        }

        $info['latency'] = Time::latency($start);
        return $info;
    }

    /**
     * Verifica serviço de storage (S3)
     *
     * @param string $alias
     * @return array
     */
    private function checkStorage(string $alias): array
    {
        $info = $this->getDefaultHealthInfo();
        $start = microtime(true);

        try {
            $s3Client = s3($alias);
            $bucketExists = $s3Client->isExistBucket();

            if ($bucketExists) {
                $info['details'] = 'S3 bucket accessible';
            } else {
                $info['status'] = 'dead';
                $info['details'] = 'S3 bucket not found';
            }
        } catch (Exception $e) {
            $info['status'] = 'dead';
            $info['error'] = $e->getMessage();
            $info['details'] = 'S3 connection failed';
        }

        $info['latency'] = Time::latency($start);
        return $info;
    }

    /**
     * Verifica serviço DynamoDB
     *
     * @param string $alias
     * @return array
     */
    private function checkDynamo(string $alias): array
    {
        $info = $this->getDefaultHealthInfo();
        $start = microtime(true);

        try {
            $dynamoClient = dynamo($alias);

            // Tenta listar tabelas para verificar conectividade
            $tables = $dynamoClient->listTables();
            $info['details'] = 'DynamoDB connection successful';
            $info['tables_count'] = count($tables);
        } catch (Exception $e) {
            $info['status'] = 'dead';
            $info['error'] = $e->getMessage();
            $info['details'] = 'DynamoDB connection failed';
        }

        $info['latency'] = Time::latency($start);
        return $info;
    }
}