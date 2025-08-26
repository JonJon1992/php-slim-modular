<?php
declare(strict_types=1);

namespace Core\Commands;

use Core\Abstract\AbstractCommand;
use Core\Utils\Swoole;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Comando para gerenciar o servidor Swoole
 * 
 * Este comando permite iniciar, parar e verificar o status do servidor Swoole.
 * Segue o padrão dos outros comandos do projeto.
 * 
 * @package Core\Commands
 * @version 2.9.5
 */
class SwooleServerCommand extends AbstractCommand
{
    private SymfonyStyle $io;

    /**
     * @return void
     */
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

    /**
     * @return void
     * @throws Throwable
     */
    protected function handler(): void
    {
        $this->io = new SymfonyStyle($this->input, $this->output);
        $this->io->title('🚀 Gerenciador do Servidor Swoole');

        // Verifica se o Swoole está disponível
        if (!Swoole::isAvailable()) {
            $this->showSwooleNotAvailable();
            return;
        }

        // Exibe informações do Swoole
        if ($this->input->getOption('info')) {
            $this->showInfo();
            return;
        }

        // Verifica status
        if ($this->input->getOption('status')) {
            $this->showStatus();
            return;
        }

        // Para o servidor
        if ($this->input->getOption('stop')) {
            $this->stopServer();
            return;
        }

        // Reinicia o servidor
        if ($this->input->getOption('restart')) {
            $this->restartServer();
            return;
        }

        // Inicia o servidor (padrão)
        if ($this->input->getOption('start') || !$this->input->getOption('stop')) {
            $this->startServer();
            return;
        }
    }

    /**
     * Exibe mensagem quando Swoole não está disponível
     */
    private function showSwooleNotAvailable(): void
    {
        $this->io->error('❌ Swoole não está disponível');
        $this->io->text([
            'Para instalar o Swoole:',
            '  <info>pecl install swoole</info>',
            '',
            'Alternativa: Use o servidor padrão do PHP',
            '  <info>composer serve</info>'
        ]);
    }

    /**
     * Exibe informações do Swoole
     */
    private function showInfo(): void
    {
        $info = Swoole::getInfo();
        $diagnostics = Swoole::getDiagnostics();

        $this->io->section('📊 Informações do Swoole');

        if ($info['available']) {
            $this->io->success('✅ Swoole está disponível');
            $this->io->text([
                "Versão: <info>{$info['version']}</info>",
                "Funcionalidades: <info>" . implode(', ', $info['features']) . "</info>"
            ]);

            $this->io->section('🔧 Configuração');
            $config = Swoole::getConfig();
            $this->io->table(
                ['Configuração', 'Valor'],
                [
                    ['Host', $config['host']],
                    ['Porta', (string) $config['port']],
                    ['Workers', (string) $config['workers']],
                    ['Max Request', (string) $config['max_request']],
                    ['Coroutines', $config['enable_coroutine'] ? 'Habilitado' : 'Desabilitado'],
                ]
            );

            $this->io->section('💻 Ambiente');
            $this->io->table(
                ['Item', 'Valor'],
                [
                    ['PHP Version', $diagnostics['environment']['php_version']],
                    ['SAPI', $diagnostics['environment']['sapi']],
                    ['Memory Limit', $diagnostics['environment']['memory_limit']],
                    ['Max Execution Time', $diagnostics['environment']['max_execution_time']],
                ]
            );
        } else {
            $this->io->error('❌ Swoole não está disponível');
            $this->io->text($info['message']);
        }
    }

    /**
     * Exibe o status do servidor
     */
    private function showStatus(): void
    {
        if (!Swoole::isRunning()) {
            $this->io->warning('⚠️  Servidor Swoole não está rodando');
            return;
        }

        $config = Swoole::getConfig();
        $pidFile = $config['pid_file'];
        $pid = file_get_contents($pidFile);

        $this->io->success('✅ Servidor Swoole está rodando');
        $this->io->text([
            "PID: <info>{$pid}</info>",
            "Arquivo PID: <info>{$pidFile}</info>"
        ]);

        // Tenta obter estatísticas
        try {
            $stats = Swoole::getStats();
            if (!empty($stats) && !isset($stats['error'])) {
                $this->io->section('📈 Estatísticas');
                $this->io->table(
                    ['Métrica', 'Valor'],
                    [
                        ['Conexões Ativas', $stats['server']['connection_num'] ?? 'N/A'],
                        ['Requests', $stats['server']['request_count'] ?? 'N/A'],
                        ['Memory Usage', $this->formatBytes($stats['memory']['usage'])],
                        ['Memory Peak', $this->formatBytes($stats['memory']['peak'])],
                    ]
                );
            }
        } catch (Throwable $e) {
            $this->io->note('Não foi possível obter estatísticas: ' . $e->getMessage());
        }
    }

    /**
     * Para o servidor
     */
    private function stopServer(): void
    {
        $this->io->text('🛑 Parando servidor Swoole...');

        if (Swoole::stopServer()) {
            $this->io->success('✅ Servidor Swoole parado');
        } else {
            $this->io->error('❌ Não foi possível parar o servidor');
        }
    }

    /**
     * Reinicia o servidor
     */
    private function restartServer(): void
    {
        $this->io->text('🔄 Reiniciando servidor Swoole...');
        
        $this->stopServer();
        sleep(2);
        $this->startServer();
    }

    /**
     * Inicia o servidor
     */
    private function startServer(): void
    {
        // Verifica se já está rodando
        if (Swoole::isRunning()) {
            $this->io->error('❌ Servidor Swoole já está rodando');
            $this->io->text("Use <info>--stop</info> para parar ou <info>--restart</info> para reiniciar");
            return;
        }

        // Configura o servidor
        $config = [
            'host' => $this->input->getOption('host'),
            'port' => (int) $this->input->getOption('port'),
            'workers' => (int) $this->input->getOption('workers'),
            'daemonize' => $this->input->getOption('daemon'),
        ];

        Swoole::setConfig($config);

        $this->io->section('🚀 Iniciando Servidor Swoole');
        $this->io->text([
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
                    $psrRequest = Swoole::createPsr7Request($request);
                    
                    // Processa através do Slim
                    $psrResponse = $app->handle($psrRequest);
                    
                    // Converte resposta PSR-7 para Swoole
                    Swoole::createSwooleResponse($psrResponse, $response);
                    
                } catch (Throwable $e) {
                    // Trata erros
                    Swoole::handleError($e, $response);
                    
                    // Log do erro
                    $this->io->error("❌ Erro: {$e->getMessage()}");
                }
            };

            // Inicia o servidor
            Swoole::startServer($requestHandler);

        } catch (Throwable $e) {
            $this->io->error('❌ Erro ao iniciar servidor: ' . $e->getMessage());
        }
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
