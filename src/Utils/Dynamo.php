<?php

namespace Core\Utils;

use Exception;
use Jonjon\PhpDynamo\DynamoManager;
use Jonjon\PhpDynamo\Item\DynamoItem;
use Jonjon\PhpDynamo\Util\DynamoConfig;
use Aws\Result;

class Dynamo
{
    private DynamoManager $manager;

    private function __construct(DynamoManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Cria uma instância do DynamoDB com configuração automática
     * 
     * @param array|null $config Configuração customizada
     * @param string|null $namespace Namespace para os items
     * @return Dynamo
     */
    public static function create(?array $config = null, ?string $namespace = null): Dynamo
    {
        $dynamoConfig = null;
        
        if ($config) {
            $dynamoConfig = new DynamoConfig(
                $config['key'] ?? $_ENV['AWS_ACCESS_KEY_ID'] ?? 'test',
                $config['secret'] ?? $_ENV['AWS_SECRET_ACCESS_KEY'] ?? 'test',
                $config['region'] ?? $_ENV['AWS_REGION'] ?? 'us-east-1',
                $config['endpoint'] ?? $_ENV['DYNAMODB_ENDPOINT'] ?? null
            );
        }
        
        $manager = DynamoManager::create($dynamoConfig, $namespace);
        return new Dynamo($manager);
    }

    /**
     * Cria uma instância com credenciais específicas
     * 
     * @param string $key AWS Access Key
     * @param string $secret AWS Secret Key
     * @param string $region AWS Region
     * @param string|null $endpoint Endpoint customizado (para LocalStack)
     * @param string|null $namespace Namespace para os items
     * @return Dynamo
     */
    public static function createWithCredentials(
        string $key,
        string $secret,
        string $region,
        ?string $endpoint = null,
        ?string $namespace = null
    ): Dynamo {
        $manager = DynamoManager::createWithCredentials($key, $secret, $region, $endpoint, $namespace);
        return new Dynamo($manager);
    }

    /**
     * Salva um item no DynamoDB
     * 
     * @param DynamoItem $item Item a ser salvo
     * @return Result
     * @throws Exception
     */
    public function save(DynamoItem $item): Result
    {
        try {
            return $this->manager->save($item);
        } catch (Exception $e) {
            throw new Exception("Erro ao salvar item no DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Remove um item do DynamoDB
     * 
     * @param DynamoItem $item Item a ser removido
     * @return Result
     * @throws Exception
     */
    public function delete(DynamoItem $item): Result
    {
        try {
            return $this->manager->delete($item);
        } catch (Exception $e) {
            throw new Exception("Erro ao remover item do DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Busca um item por chave
     * 
     * @param string $tableName Nome da tabela
     * @param array $key Chave do item (PK, SK)
     * @param string|null $class Classe para hidratação
     * @return mixed
     * @throws Exception
     */
    public function find(string $tableName, array $key, ?string $class = null)
    {
        try {
            return $this->manager->find($tableName, $key, $class);
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar item no DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Executa uma query no DynamoDB
     * 
     * @param string $tableName Nome da tabela
     * @param string $keyConditionExpression Expressão de condição da chave
     * @param array $values Valores para a expressão
     * @param string|null $filterExpression Expressão de filtro
     * @param string|null $projectionExpression Expressão de projeção
     * @param array|null $expressionAttributeNames Nomes dos atributos
     * @param array $params Parâmetros adicionais
     * @param string|null $class Classe para hidratação
     * @return array
     * @throws Exception
     */
    public function query(
        string $tableName,
        string $keyConditionExpression,
        array $values,
        ?string $filterExpression = null,
        ?string $projectionExpression = null,
        ?array $expressionAttributeNames = null,
        array $params = [],
        ?string $class = null
    ): array {
        try {
            return $this->manager->query(
                $tableName,
                $keyConditionExpression,
                $values,
                $filterExpression,
                $projectionExpression,
                $expressionAttributeNames,
                $params,
                $class
            );
        } catch (Exception $e) {
            throw new Exception("Erro ao executar query no DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Executa um scan na tabela
     * 
     * @param string $tableName Nome da tabela
     * @param string|null $filterExpression Expressão de filtro
     * @param array|null $values Valores para a expressão
     * @param array $params Parâmetros adicionais
     * @param string|null $class Classe para hidratação
     * @return array
     * @throws Exception
     */
    public function scan(
        string $tableName,
        ?string $filterExpression = null,
        ?array $values = null,
        array $params = [],
        ?string $class = null
    ): array {
        try {
            return $this->manager->scan($tableName, $filterExpression, $values, $params, $class);
        } catch (Exception $e) {
            throw new Exception("Erro ao executar scan no DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Atualiza um item no DynamoDB
     * 
     * @param string $tableName Nome da tabela
     * @param array $key Chave do item
     * @param string $updateExpression Expressão de atualização
     * @param array $values Valores para atualização
     * @param string|null $condition Condição para atualização
     * @return Result
     * @throws Exception
     */
    public function update(
        string $tableName,
        array $key,
        string $updateExpression,
        array $values,
        ?string $condition = null
    ): Result {
        try {
            return $this->manager->update($tableName, $key, $updateExpression, $values, $condition);
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar item no DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Insere múltiplos items em lote
     * 
     * @param array $items Array de items para inserir
     * @param array $config Configuração do lote
     * @return mixed
     * @throws Exception
     */
    public function insertItems(array $items, array $config = [])
    {
        try {
            return $this->manager->insertItems($items, $config);
        } catch (Exception $e) {
            throw new Exception("Erro ao inserir items em lote no DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Remove múltiplos items em lote
     * 
     * @param array $items Array de items para remover
     * @param array $config Configuração do lote
     * @return mixed
     * @throws Exception
     */
    public function deleteItems(array $items, array $config = [])
    {
        try {
            return $this->manager->deleteItems($items, $config);
        } catch (Exception $e) {
            throw new Exception("Erro ao remover items em lote do DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Lista todas as tabelas
     * 
     * @return array
     * @throws Exception
     */
    public function listTables(): array
    {
        try {
            return $this->manager->listTables();
        } catch (Exception $e) {
            throw new Exception("Erro ao listar tabelas do DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Descreve uma tabela
     * 
     * @param string $tableName Nome da tabela
     * @return Result|array
     * @throws Exception
     */
    public function describeTable(string $tableName): Result|array
    {
        try {
            return $this->manager->describeTable($tableName);
        } catch (Exception $e) {
            throw new Exception("Erro ao descrever tabela do DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Cria uma nova tabela
     * 
     * @param array $tableSettings Configurações da tabela
     * @return Result|array
     * @throws Exception
     */
    public function createTable(array $tableSettings): Result|array
    {
        try {
            return $this->manager->createTable($tableSettings);
        } catch (Exception $e) {
            throw new Exception("Erro ao criar tabela no DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Remove uma tabela
     * 
     * @param string $tableName Nome da tabela
     * @return Result|array
     * @throws Exception
     */
    public function deleteTable(string $tableName): Result|array
    {
        try {
            return $this->manager->deleteTable($tableName);
        } catch (Exception $e) {
            throw new Exception("Erro ao remover tabela do DynamoDB: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Converte um item para DynamoItem
     * 
     * @param mixed $item Item a ser convertido
     * @param string $class Classe do DynamoItem
     * @return DynamoItem
     * @throws Exception
     */
    public function itemToDynamoItem($item, string $class): DynamoItem
    {
        try {
            return $this->manager->itemToDynamoItem($item, $class);
        } catch (Exception $e) {
            throw new Exception("Erro ao converter item para DynamoItem: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Converte items para diferentes formatos
     * 
     * @param mixed $item Item a ser convertido
     * @param string $hydrate Tipo de hidratação (object, array, stdclass)
     * @param string|null $class Classe para hidratação
     * @return mixed
     * @throws Exception
     */
    public function convert($item, string $hydrate = 'object', ?string $class = null)
    {
        try {
            return $this->manager->convert($item, $hydrate, $class);
        } catch (Exception $e) {
            throw new Exception("Erro ao converter item: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Converte múltiplos items
     * 
     * @param array $items Items a serem convertidos
     * @param string $hydrate Tipo de hidratação
     * @param string|null $class Classe para hidratação
     * @return array
     * @throws Exception
     */
    public function convertItems(array $items, string $hydrate = 'object', ?string $class = null): array
    {
        try {
            return $this->manager->convertItems($items, $hydrate, $class);
        } catch (Exception $e) {
            throw new Exception("Erro ao converter items: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Obtém o DynamoManager interno
     * 
     * @return DynamoManager
     */
    public function getManager(): DynamoManager
    {
        return $this->manager;
    }

    /**
     * Obtém o cliente DynamoDB interno
     * 
     * @return mixed
     */
    public function getClient()
    {
        return $this->manager->getDynamo()->getClient();
    }

    /**
     * Obtém o gerenciador de transações
     * 
     * @return mixed
     */
    public function getTransaction()
    {
        return $this->manager->getTransaction();
    }
} 