<?php

declare(strict_types=1);

namespace Core\Utils;

use Composer\Autoload\ClassLoader;
use ReflectionClass;

/**
 * Classe utilitária para gerenciamento de caminhos do projeto
 * 
 * Fornece métodos estáticos para acessar diferentes diretórios
 * do projeto de forma consistente e centralizada.
 * 
 * @package Core\Utils
 * @author JonJon1992
 * @version 2.4.0
 */
class Path
{
    /**
     * Resolve um caminho base com um caminho adicional opcional
     * 
     * @param string $base Caminho base
     * @param string|null $path Caminho adicional opcional
     * @return string Caminho completo resolvido
     */
    protected static function resolve(string $base, ?string $path = null): string
    {
        return $base . ($path ?? '');
    }

    /**
     * Retorna o caminho raiz do projeto
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório raiz
     */
    public static function root(?string $path = null): string
    {
        $reflection = new ReflectionClass(ClassLoader::class);
        return self::resolve(dirname($reflection->getFileName()) . '/../..', $path);
    }

    /**
     * Retorna o caminho para o diretório de cache
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório de cache
     */
    public static function cache(?string $path = null): string
    {
        return self::resolve(self::root('/var/cache'), $path);
    }

    /**
     * Retorna o caminho para o diretório de configurações
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório de configurações
     */
    public static function configs(?string $path = null): string
    {
        return self::resolve(self::root('/configs'), $path);
    }

    /**
     * Retorna o caminho para o diretório de logs
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório de logs
     */
    public static function logs(?string $path = null): string
    {
        return self::resolve(self::root('/logs'), $path);
    }

    /**
     * Retorna o caminho para o diretório de módulos
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório de módulos
     */
    public static function modules(?string $path = null): string
    {
        return self::resolve(self::root('/modules'), $path);
    }

    /**
     * Retorna o caminho para o diretório público
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório público
     */
    public static function public(?string $path = null): string
    {
        return self::resolve(self::root('/public'), $path);
    }

    /**
     * Retorna o caminho para o diretório temporário
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório temporário
     */
    public static function temp(?string $path = null): string
    {
        return self::resolve(self::root('/var/temp'), $path);
    }

    /**
     * Retorna o caminho para o diretório var
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório var
     */
    public static function var(?string $path = null): string
    {
        return self::resolve(self::root('/var'), $path);
    }

    /**
     * Retorna o caminho para o diretório src
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório src
     */
    public static function src(?string $path = null): string
    {
        return self::resolve(self::root('/src'), $path);
    }

    /**
     * Retorna o caminho para o diretório de views
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório de views
     */
    public static function views(?string $path = null): string
    {
        return self::resolve(self::root('/views'), $path);
    }

    /**
     * Retorna o caminho para o diretório de testes
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório de testes
     */
    public static function tests(?string $path = null): string
    {
        return self::resolve(self::root('/tests'), $path);
    }

    /**
     * Retorna o caminho para o diretório de documentação
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório de documentação
     */
    public static function docs(?string $path = null): string
    {
        return self::resolve(self::root('/docs'), $path);
    }

    /**
     * Retorna o caminho para o diretório de exemplos
     * 
     * @param string|null $path Caminho adicional a ser anexado
     * @return string Caminho completo para o diretório de exemplos
     */
    public static function examples(?string $path = null): string
    {
        return self::resolve(self::root('/examples'), $path);
    }
}