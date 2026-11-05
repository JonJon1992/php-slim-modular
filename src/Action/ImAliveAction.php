<?php

declare(strict_types=1);

namespace Core\Action;

use Core\App\Action;
use Core\Utils\Env;
use Core\Utils\Path;
use Core\Utils\Str;
use Core\Utils\Time;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

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

        $service = (string) $this->queryParam('service', '');

        if ($this->shouldUseCache($service)) {
            return $this->getCachedResponse();
        }

        $this->checkCacheServices($service);

        $this->checkDatabaseServices($service);

        $this->checkStorageServices($service);

        $this->checkDynamoServices($service);

        $this->addGeneralMetrics();

        if ($this->deadServices > 0) {
            return $this->handleFailure();
        }

        // Só a verificação completa alimenta o cache; uma checagem parcial (?service=)
        // não pode fazer a próxima checagem completa responder OK sem testar tudo.
        if (empty($service)) {
            $this->saveSuccessLog();
        }

        return $this->responseData;
    }

    /**
     * Usa o resultado em cache apenas se a última verificação completa bem-sucedida
     * tiver ocorrido há menos de CACHE_DURATION segundos.
     *
     * @param string $service
     * @return bool
     */
    private function shouldUseCache(string $service): bool
    {
        if (!empty($service) || !empty($this->queryParam('force', ''))) {
            return false;
        }

        $cacheFile = Path::temp(self::HEALTHY_CHECK_FILE);

        if (!$this->filesystem->exists($cacheFile)) {
            return false;
        }

        return (time() - $this->filesystem->lastModified($cacheFile)) < self::CACHE_DURATION;
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
        $this->filesystem->delete(Path::temp(self::HEALTHY_CHECK_FILE));

        $this->logger->critical('Health check failed', [
            'dead_services' => $this->deadServices,
            'response' => $this->responseData,
            'timestamp' => time()
        ]);

        return $this->respondWithJSON($this->publicResponseData(), 503);
    }

    /**
     * Em produção/staging, omite as mensagens de erro das conexões da resposta pública
     * (elas continuam registradas no log em handleFailure).
     *
     * @return array
     */
    private function publicResponseData(): array
    {
        if (!Env::real()) {
            return $this->responseData;
        }

        return array_map(function ($item) {
            if (is_array($item)) {
                unset($item['error']);
            }
            return $item;
        }, $this->responseData);
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

        $this->filesystem->append($logFile, $timestamp . "\n");
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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

            $tables = $dynamoClient->listTables();
            $info['details'] = 'DynamoDB connection successful';
            $info['tables_count'] = count($tables);
        } catch (Throwable $e) {
            $info['status'] = 'dead';
            $info['error'] = $e->getMessage();
            $info['details'] = 'DynamoDB connection failed';
        }

        $info['latency'] = Time::latency($start);
        return $info;
    }
}