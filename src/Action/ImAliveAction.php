<?php

namespace Core\Action;

use Core\App\Action;
use Core\App\SlimAction;
use Core\Utils\Path;
use Core\Utils\Str;
use Core\Utils\Time;
use \Exception;
use Illuminate\Filesystem\Filesystem;
use Psr\Http\Message\ResponseInterface;

class ImAliveAction extends Action
{
    const HEALTY_CHECK = '/im-alive.log';

    /**
     * @return array|ResponseInterface|string[]
     */

    protected function action(): array|ResponseInterface
    {
        $fs = new Filesystem();

        if ($fs->exists(Path::temp(self::HEALTY_CHECK)) && $this->queryParam('force') !== '') {
            return [
                "status" => 'OK'
            ];
        }
        $response = [];
        $dead = 0;
        $service = $this->queryParam('service');

        foreach (array_keys(config('cache', [])) as $alias) {
            $key = "cache.$alias";
            if ($alias != 'default' && (empty($service) || Str::has($key, $service))) {
                $response[$key] = $this->checkCache($alias);
                $response[$key]['status'] == 'dead' ? $dead++ : null;
            }
        }
        foreach (array_keys(config('db', [])) as $alias) {
            $key = "db.$alias";
            if ($alias != 'default' && (empty($service) || Str::has($key, $service))) {
                $response[$key] = $this->checkDatabase($alias);
                $response[$key]['status'] == 'dead' ? $dead++ : null;
            }
        }
        foreach (array_keys(config('storage', [])) as $alias) {
            $key = "storage.$alias";
            if ($alias != 'default' && (empty($service) || Str::has($key, $service))) {
                $response[$key] = $this->checkStorage($alias);
                $response[$key]['status'] == 'dead' ? $dead++ : null;
            }
        }

        if ($dead > 0) {
            logger()->critical('im-alive with dead', $response);
            $this->response->getBody()->write(json_encode($response));

            return $this->response->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        } else {
            $fs->append(Path::temp(self::HEALTY_CHECK), time() . "\n");
        }
        return $response;
    }

    /**
     * @return array
     */
    protected function info(): array
    {
        return [
            'status' => 'alive',
            'latency' => 0
        ];
    }

    /**
     * @param $alias
     * @return array
     */
    protected function checkCache($alias): array
    {
        $info = $this->info();

        $start = microtime(true);
        try {
            redis($alias)->ping();
        } catch (\Exception) {
            $info['status'] = 'dead';
        }
        $info['latency'] = Time::latency($start);

        return $info;
    }

    /**
     * @param $alias
     * @return array
     */
    protected function checkDatabase($alias): array
    {
        $info = $this->info();

        $start = microtime(true);
        try {
            db($alias)->getPdo();
        } catch (\Exception) {
            $info['status'] = 'dead';
        }
        $info['latency'] = Time::latency($start);

        return $info;
    }

    /**
     * @param $alias
     * @return array
     */
    protected function checkStorage($alias): array
    {
        $info = $this->info();

        $start = microtime(true);
        try {
            $info['status'] = s3($alias)->isExistBucket() ? $info['status'] : 'dead';
        } catch (\Exception) {
            $info['status'] = 'dead';
        }
        $info['latency'] = Time::latency($start);

        return $info;

    }

}