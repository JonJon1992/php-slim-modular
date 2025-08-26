<?php
declare(strict_types=1);

namespace Core\Commands;

use Core\Abstract\AbstractCommand;
use Core\Utils\Swoole;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Comando para gerenciar o servidor Swoole
 * 
 * Este comando permite iniciar, parar e verificar o status do servidor Swoole.
 * 
 * @package Core\Commands
 * @version 2.9.5
 */
class SwooleServerCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setName('swoole:server')
            ->setDescription('Gerencia o servidor Swoole')
            ->addOption('start', null, InputOption::VALUE_NONE, 'Inicia o servidor Swoole')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Para o servidor Swoole')
            ->addOption('restart', null, InputOption::VALUE_NONE, 'Reinicia o servidor Swoole')
            ->addOption('status', null, InputOption::VALUE_NONE, 'Verifica o status do servidor Swoole')
            ->addOption('info', null, InputOption::VALUE_NONE, 'Exibe informações do Swoole')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Host do servidor', '0.0.0.0')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Porta do servidor', '9501')
            ->addOption('workers', null, InputOption::VALUE_REQUIRED, 'Número de workers', '4')
            ->addOption('daemon', null, InputOption::VALUE_NONE, 'Executa em modo daemon')
            ->setHelp(<<<'HELP'
Este comando permite gerenciar o servidor Swoole para alta performance.

Exemplos de uso:
  <info>php cli.php swoole:server --start</info>     Inicia o servidor
  <info>php cli.php swoole:server --stop</info>      Para o servidor
  <info>php cli.php swoole:server --restart</info>   Reinicia o servidor
  <info>php cli.php swoole:server --status</info>    Verifica o status
  <info>php cli.php swoole:server --info</info>      Exibe informações

Opções de configuração:
  <info>--host=127.0.0.1</info>    Define o host do servidor
  <info>--port=8080</info>         Define a porta do servidor
  <info>--workers=8</info>         Define o número de workers
  <info>--daemon</info>            Executa em modo daemon

HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🚀 Gerenciador do Servidor Swoole');

        // Verifica se o Swoole está disponível
        if (!Swoole::isAvailable()) {
            $io->error('❌ Swoole não está disponível');
            $io->text([
                'Para instalar o Swoole:',
                '  <info>pecl install swoole</info>',
                '',
                'Alternativa: Use o servidor padrão do PHP',
                '  <info>composer serve</info>'
            ]);
            return self::FAILURE;
        }

        // Exibe informações do Swoole
        if ($input->getOption('info')) {
            return $this->showInfo($io);
        }

        // Verifica status
        if ($input->getOption('status')) {
            return $this->showStatus($io);
        }

        // Para o servidor
        if ($input->getOption('stop')) {
            return $this->stopServer($io);
        }

        // Reinicia o servidor
        if ($input->getOption('restart')) {
            $this->stopServer($io);
            sleep(2);
            return $this->startServer($input, $io);
        }

        // Inicia o servidor (padrão)
        if ($input->getOption('start') || !$input->getOption('stop')) {
            return $this->startServer($input, $io);
        }

        return self::SUCCESS;
    }

    /**
     * Exibe informações do Swoole
     */
    private function showInfo(SymfonyStyle $io): int
    {
        $info = Swoole::getInfo();
        $diagnostics = Swoole::getDiagnostics();

        $io->section('📊 Informações do Swoole');

        if ($info['available']) {
            $io->success('✅ Swoole está disponível');
            $io->text([
                "Versão: <info>{$info['version']}</info>",
                "Funcionalidades: <info>" . implode(', ', $info['features']) . "</info>"
            ]);

            $io->section('🔧 Configuração');
            $config = Swoole::getConfig();
            $io->table(
                ['Configuração', 'Valor'],
                [
                    ['Host', $config['host']],
                    ['Porta', (string) $config['port']],
                    ['Workers', (string) $config['workers']],
                    ['Max Request', (string) $config['max_request']],
                    ['Coroutines', $config['enable_coroutine'] ? 'Habilitado' : 'Desabilitado'],
                ]
            );

            $io->section('💻 Ambiente');
            $io->table(
                ['Item', 'Valor'],
                [
                    ['PHP Version', $diagnostics['environment']['php_version']],
                    ['SAPI', $diagnostics['environment']['sapi']],
                    ['Memory Limit', $diagnostics['environment']['memory_limit']],
                    ['Max Execution Time', $diagnostics['environment']['max_execution_time']],
                ]
            );
        } else {
            $io->error('❌ Swoole não está disponível');
            $io->text($info['message']);
        }

        return self::SUCCESS;
    }

    /**
     * Exibe o status do servidor
     */
    private function showStatus(SymfonyStyle $io): int
    {
        $pidFile = Swoole::getConfig()['pid_file'];

        if (!file_exists($pidFile)) {
            $io->warning('⚠️  Servidor Swoole não está rodando');
            return self::SUCCESS;
        }

        $pid = file_get_contents($pidFile);
        if (!$pid || !posix_kill((int) $pid, 0)) {
            $io->warning('⚠️  Servidor Swoole não está rodando (PID inválido)');
            unlink($pidFile);
            return self::SUCCESS;
        }

        $io->success('✅ Servidor Swoole está rodando');
        $io->text([
            "PID: <info>{$pid}</info>",
            "Arquivo PID: <info>{$pidFile}</info>"
        ]);

        // Tenta obter estatísticas
        try {
            $stats = Swoole::getStats();
            if (!empty($stats) && !isset($stats['error'])) {
                $io->section('📈 Estatísticas');
                $io->table(
                    ['Métrica', 'Valor'],
                    [
                        ['Conexões Ativas', $stats['server']['connection_num'] ?? 'N/A'],
                        ['Requests', $stats['server']['request_count'] ?? 'N/A'],
                        ['Memory Usage', $this->formatBytes($stats['memory']['usage'])],
                        ['Memory Peak', $this->formatBytes($stats['memory']['peak'])],
                    ]
                );
            }
        } catch (\Throwable $e) {
            $io->note('Não foi possível obter estatísticas: ' . $e->getMessage());
        }

        return self::SUCCESS;
    }

    /**
     * Para o servidor
     */
    private function stopServer(SymfonyStyle $io): int
    {
        $pidFile = Swoole::getConfig()['pid_file'];

        if (!file_exists($pidFile)) {
            $io->warning('⚠️  Servidor Swoole não está rodando');
            return self::SUCCESS;
        }

        $pid = file_get_contents($pidFile);
        if (!$pid) {
            $io->error('❌ PID inválido');
            unlink($pidFile);
            return self::FAILURE;
        }

        $io->text("🛑 Parando servidor Swoole (PID: {$pid})...");

        // Envia sinal SIGTERM
        if (posix_kill((int) $pid, SIGTERM)) {
            $io->success('✅ Sinal de parada enviado');
            
            // Aguarda até 10 segundos
            for ($i = 0; $i < 10; $i++) {
                if (!posix_kill((int) $pid, 0)) {
                    $io->success('✅ Servidor Swoole parado');
                    unlink($pidFile);
                    return self::SUCCESS;
                }
                sleep(1);
            }

            // Se não parou, força com SIGKILL
            $io->warning('⚠️  Forçando parada...');
            posix_kill((int) $pid, SIGKILL);
            sleep(1);
            
            if (!posix_kill((int) $pid, 0)) {
                $io->success('✅ Servidor Swoole forçado a parar');
                unlink($pidFile);
                return self::SUCCESS;
            }
        }

        $io->error('❌ Não foi possível parar o servidor');
        return self::FAILURE;
    }

    /**
     * Inicia o servidor
     */
    private function startServer(InputInterface $input, SymfonyStyle $io): int
    {
        // Verifica se já está rodando
        $pidFile = Swoole::getConfig()['pid_file'];
        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            if ($pid && posix_kill((int) $pid, 0)) {
                $io->error('❌ Servidor Swoole já está rodando');
                $io->text("Use <info>--stop</info> para parar ou <info>--restart</info> para reiniciar");
                return self::FAILURE;
            }
            unlink($pidFile);
        }

        // Configura o servidor
        $config = [
            'host' => $input->getOption('host'),
            'port' => (int) $input->getOption('port'),
            'workers' => (int) $input->getOption('workers'),
            'daemonize' => $input->getOption('daemon'),
        ];

        Swoole::setConfig($config);

        $io->section('🚀 Iniciando Servidor Swoole');
        $io->text([
            "Host: <info>{$config['host']}</info>",
            "Porta: <info>{$config['port']}</info>",
            "Workers: <info>{$config['workers']}</info>",
            "Modo: <info>" . ($config['daemonize'] ? 'Daemon' : 'Foreground') . "</info>"
        ]);

        try {
            // Carrega a aplicação
            $app = require __DIR__ . '/../../bootstrap.php';

            // Handler de requisição
            $requestHandler = function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($app) {
                try {
                    // Converte requisição Swoole para PSR-7
                    $psrRequest = $this->createPsr7Request($request);
                    
                    // Processa através do Slim
                    $psrResponse = $app->handle($psrRequest);
                    
                    // Converte resposta PSR-7 para Swoole
                    $this->createSwooleResponse($psrResponse, $response);
                    
                } catch (\Throwable $e) {
                    $this->handleSwooleError($e, $response);
                }
            };

            // Inicia o servidor
            Swoole::startServer($requestHandler);

        } catch (\Throwable $e) {
            $io->error('❌ Erro ao iniciar servidor: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Converte requisição Swoole para PSR-7
     */
    private function createPsr7Request(\Swoole\Http\Request $swooleRequest): \Psr\Http\Message\ServerRequestInterface
    {
        $uri = new \Slim\Psr7\Uri(
            $swooleRequest->server['request_scheme'] ?? 'http',
            $swooleRequest->header['host'] ?? 'localhost',
            $swooleRequest->server['server_port'] ?? 80,
            $swooleRequest->server['request_uri'] ?? '/',
            $swooleRequest->server['query_string'] ?? ''
        );

        $headers = [];
        foreach ($swooleRequest->header as $name => $value) {
            $headers[strtolower($name)] = $value;
        }

        $body = new \Slim\Psr7\Stream(fopen('php://temp', 'r+'));
        if ($swooleRequest->rawContent()) {
            $body->write($swooleRequest->rawContent());
            $body->rewind();
        }

        $request = new \Slim\Psr7\Request(
            $swooleRequest->server['request_method'] ?? 'GET',
            $uri,
            $headers,
            $body,
            '1.1',
            []
        );

        // Adiciona parâmetros
        if (!empty($swooleRequest->get)) {
            $request = $request->withQueryParams($swooleRequest->get);
        }
        if (!empty($swooleRequest->post)) {
            $request = $request->withParsedBody($swooleRequest->post);
        }
        if (!empty($swooleRequest->cookie)) {
            $request = $request->withCookieParams($swooleRequest->cookie);
        }

        return $request;
    }

    /**
     * Converte resposta PSR-7 para Swoole
     */
    private function createSwooleResponse(\Psr\Http\Message\ResponseInterface $psrResponse, \Swoole\Http\Response $swooleResponse): void
    {
        $swooleResponse->status($psrResponse->getStatusCode());

        foreach ($psrResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }

        $body = $psrResponse->getBody();
        $body->rewind();
        $swooleResponse->end($body->getContents());
    }

    /**
     * Trata erros no contexto do Swoole
     */
    private function handleSwooleError(\Throwable $e, \Swoole\Http\Response $response): void
    {
        $statusCode = 500;
        $message = 'Internal Server Error';
        
        if ($e instanceof \Slim\Exception\HttpException) {
            $statusCode = $e->getCode();
            $message = $e->getMessage();
        }
        
        $response->status($statusCode);
        $response->header('Content-Type', 'application/json');
        
        $errorData = [
            'error' => [
                'message' => $message,
                'code' => $statusCode,
                'timestamp' => date('c'),
            ]
        ];
        
        if ($_ENV['APP_DEBUG'] ?? false) {
            $errorData['error']['trace'] = $e->getTraceAsString();
            $errorData['error']['file'] = $e->getFile();
            $errorData['error']['line'] = $e->getLine();
        }
        
        $response->end(json_encode($errorData, JSON_PRETTY_PRINT));
    }

    /**
     * Formata bytes para leitura humana
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
