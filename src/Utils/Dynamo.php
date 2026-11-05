<?php

namespace Core\Utils;

use Exception;
use Jonjon\PhpDynamo\DynamoManager;
use Jonjon\PhpDynamo\Item\DynamoItem;
use Jonjon\PhpDynamo\Util\DynamoConfig;
use Aws\Result;

/**
 * Fachada sobre o DynamoManager. As exceções do SDK/manager são propagadas sem
 * encapsulamento, para que o chamador possa tratar o tipo original (ex.: DynamoDbException
 * com getAwsErrorCode() === 'ConditionalCheckFailedException').
 */
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
                $config['key'],
                $config['secret'],
                $config['region'],
                $config['endpoint']
            );
        }
        return new Dynamo(DynamoManager::create($dynamoConfig, $namespace));
    }

    /**
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
        return new Dynamo(DynamoManager::createWithCredentials($key, $secret, $region, $endpoint, $namespace));
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
        return $this->manager->save($item);
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
        return $this->manager->delete($item);
    }

    /**
     * @param string $tableName Nome da tabela
     * @param array $key Chave do item (PK, SK)
     * @param string|null $class Classe para hidratação
     * @return mixed
     * @throws Exception
     */
    public function find(string $tableName, array $key, ?string $class = null)
    {
        return $this->manager->find($tableName, $key, $class);
    }

    /**
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
    }

    /**
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
        return $this->manager->scan($tableName, $filterExpression, $values, $params, $class);
    }

    /** 
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
        return $this->manager->update($tableName, $key, $updateExpression, $values, $condition);
    }

    /**
     * @param array $items Array de items para inserir
     * @param array $config Configuração do lote
     * @return mixed
     * @throws Exception
     */
    public function insertItems(array $items, array $config = [])
    {
        return $this->manager->insertItems($items, $config);
    }

    /**
     * @param array $items Array de items para remover
     * @param array $config Configuração do lote
     * @return mixed
     * @throws Exception
     */
    public function deleteItems(array $items, array $config = [])
    {
        return $this->manager->deleteItems($items, $config);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function listTables(): array
    {
        return $this->manager->listTables();
    }

    /**
     * @param string $tableName Nome da tabela
     * @return Result|array
     * @throws Exception
     */
    public function describeTable(string $tableName): Result|array
    {
        return $this->manager->describeTable($tableName);
    }

    /**
     * @param array $tableSettings Configurações da tabela
     * @return Result|array
     * @throws Exception
     */
    public function createTable(array $tableSettings): Result|array
    {
        return $this->manager->createTable($tableSettings);
    }

    /**
     * @param string $tableName Nome da tabela
     * @return Result|array
     * @throws Exception
     */
    public function deleteTable(string $tableName): Result|array
    {
        return $this->manager->deleteTable($tableName);
    }

    /**
     * @param mixed $item Item a ser convertido
     * @param string $class Classe do DynamoItem
     * @return DynamoItem
     * @throws Exception
     */
    public function itemToDynamoItem($item, string $class): DynamoItem
    {
        return $this->manager->itemToDynamoItem($item, $class);
    }

    /**
     * @param mixed $item Item a ser convertido
     * @param string $hydrate Tipo de hidratação (object, array, stdclass)
     * @param string|null $class Classe para hidratação
     * @return mixed
     * @throws Exception
     */
    public function convert($item, string $hydrate = 'object', ?string $class = null)
    {
        return $this->manager->convert($item, $hydrate, $class);
    }

    /**
     * @param array $items Items a serem convertidos
     * @param string $hydrate Tipo de hidratação
     * @param string|null $class Classe para hidratação
     * @return array
     * @throws Exception
     */
    public function convertItems(array $items, string $hydrate = 'object', ?string $class = null): array
    {
        return $this->manager->convertItems($items, $hydrate, $class);
    }

    /**
     * @return DynamoManager
     */
    public function getManager(): DynamoManager
    {
        return $this->manager;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->manager->getDynamo()->getClient();
    }

    /**
     * @return mixed
     */
    public function getTransaction()
    {
        return $this->manager->getTransaction();
    }
}
