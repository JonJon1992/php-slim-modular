<?php

namespace Core\Tests\Mocks;

use Core\Traits\Dynamo;
use Aws\Result;
use Jonjon\PhpDynamo\Item\DynamoItem;
use Jonjon\PhpDynamo\DynamoManager;

class DynamoMock extends Dynamo
{
    private array $data;
    private array $tables;
    private DynamoManager $originalManager;

    /**
     * @param array $data Dados mockados organizados por tabela
     * @param array $tables Configuração das tabelas mockadas
     */
    public function __construct(array $data = [], array $tables = [])
    {
        $this->data = $data;
        $this->tables = $tables;
        
        // Cria uma instância da classe pai usando o método estático
        $parentInstance = parent::create([
            'key' => 'mock-key',
            'secret' => 'mock-secret',
            'region' => 'us-east-1'
        ]);
        
        // Armazena o manager original
        $this->originalManager = $parentInstance->getManager();
        
        // Copia as propriedades da instância pai usando reflection
        $this->copyParentProperties($parentInstance);
    }

    /**
     * Copia as propriedades da instância pai usando reflection
     * 
     * @param Dynamo $parentInstance
     * @return void
     */
    private function copyParentProperties(Dynamo $parentInstance): void
    {
        $reflection = new \ReflectionClass($parentInstance);
        $managerProperty = $reflection->getProperty('manager');
        $managerProperty->setAccessible(true);
        
        // Cria um manager mock
        $mockManager = $this->createMockManager();
        
        // Define o manager mock na instância atual
        $thisReflection = new \ReflectionClass($this);
        $thisManagerProperty = $thisReflection->getProperty('manager');
        $thisManagerProperty->setAccessible(true);
        $thisManagerProperty->setValue($this, $mockManager);
    }

    /**
     * Cria um manager mock simples
     * 
     * @return object
     */
    private function createMockManager(): object
    {
        return new class($this->data, $this->tables) {
            private array $mockData;
            private array $mockTables;
            
            public function __construct(array $data, array $tables)
            {
                $this->mockData = $data;
                $this->mockTables = $tables;
            }
            
            public function save($item) {
                return new Result(['@metadata' => ['statusCode' => 200]]);
            }
            
            public function delete($item) {
                return new Result(['@metadata' => ['statusCode' => 200]]);
            }
            
            public function find($tableName, $key, $class = null) {
                return null;
            }
            
            public function query($tableName, $keyConditionExpression, $values, $filterExpression = null, $projectionExpression = null, $expressionAttributeNames = null, $params = [], $class = null) {
                return [];
            }
            
            public function scan($tableName, $filterExpression = null, $values = null, $params = [], $class = null) {
                return [];
            }
            
            public function update($tableName, $key, $updateExpression, $values, $condition = null) {
                return new Result(['@metadata' => ['statusCode' => 200]]);
            }
            
            public function listTables() {
                return array_keys($this->mockTables);
            }
            
            public function describeTable($tableName) {
                if (isset($this->mockTables[$tableName])) {
                    return new Result([
                        'Table' => [
                            'TableName' => $tableName,
                            'TableStatus' => 'ACTIVE',
                            'CreationDateTime' => date('c'),
                            'ItemCount' => isset($this->mockData[$tableName]) ? count($this->mockData[$tableName]) : 0
                        ]
                    ]);
                }
                return new Result(['Table' => null]);
            }
            
            public function createTable($tableSettings) {
                $tableName = $tableSettings['TableName'];
                $this->mockTables[$tableName] = $tableSettings;
                $this->mockData[$tableName] = [];
                
                return new Result([
                    'TableDescription' => [
                        'TableName' => $tableName,
                        'TableStatus' => 'CREATING',
                        'CreationDateTime' => date('c')
                    ]
                ]);
            }
            
            public function deleteTable($tableName) {
                if (isset($this->mockTables[$tableName])) {
                    unset($this->mockTables[$tableName]);
                    unset($this->mockData[$tableName]);
                }
                return new Result(['@metadata' => ['statusCode' => 200]]);
            }
        };
    }

    /**
     * Cria uma instância do DynamoMock com configuração automática
     * 
     * @param array|null $config Configuração customizada (ignorada no mock)
     * @param string|null $namespace Namespace para os items (ignorado no mock)
     * @return DynamoMock
     */
    public static function create(?array $config = null, ?string $namespace = null): DynamoMock
    {
        return new DynamoMock();
    }

    /**
     * Cria uma instância com credenciais específicas (ignoradas no mock)
     * 
     * @param string $key AWS Access Key (ignorada no mock)
     * @param string $secret AWS Secret Key (ignorada no mock)
     * @param string $region AWS Region (ignorada no mock)
     * @param string|null $endpoint Endpoint customizado (ignorado no mock)
     * @param string|null $namespace Namespace para os items (ignorado no mock)
     * @return DynamoMock
     */
    public static function createWithCredentials(
        string $key,
        string $secret,
        string $region,
        ?string $endpoint = null,
        ?string $namespace = null
    ): DynamoMock {
        return new DynamoMock();
    }





    /**
     * Sobrescreve o método save para usar dados mockados
     * 
     * @param DynamoItem $item Item a ser salvo
     * @return Result
     */
    public function save(DynamoItem $item): Result
    {
        $tableName = $item->getTableName();
        $key = $this->extractKey($item);
        
        if (!isset($this->data[$tableName])) {
            $this->data[$tableName] = [];
        }
        
        $this->data[$tableName][$this->generateKeyString($key)] = $item->toArray();
        
        return new Result(['@metadata' => ['statusCode' => 200]]);
    }

    /**
     * Sobrescreve o método delete para usar dados mockados
     * 
     * @param DynamoItem $item Item a ser removido
     * @return Result
     */
    public function delete(DynamoItem $item): Result
    {
        $tableName = $item->getTableName();
        $key = $this->extractKey($item);
        $keyString = $this->generateKeyString($key);
        
        if (isset($this->data[$tableName][$keyString])) {
            unset($this->data[$tableName][$keyString]);
        }
        
        return new Result(['@metadata' => ['statusCode' => 200]]);
    }

    /**
     * Sobrescreve o método find para usar dados mockados
     * 
     * @param string $tableName Nome da tabela
     * @param array $key Chave do item
     * @param string|null $class Classe para hidratação
     * @return mixed
     */
    public function find(string $tableName, array $key, ?string $class = null)
    {
        $keyString = $this->generateKeyString($key);
        
        if (isset($this->data[$tableName][$keyString])) {
            $itemData = $this->data[$tableName][$keyString];
            
            if ($class) {
                return $this->hydrateToClass($itemData, $class);
            }
            
            return $itemData;
        }
        
        return null;
    }

    /**
     * Executa uma query no DynamoDB mock
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
        if (!isset($this->data[$tableName])) {
            return [];
        }
        
        $results = [];
        
        foreach ($this->data[$tableName] as $itemData) {
            if ($this->matchesQuery($itemData, $keyConditionExpression, $values)) {
                if ($filterExpression && !$this->matchesFilter($itemData, $filterExpression, $values)) {
                    continue;
                }
                
                if ($class) {
                    $results[] = $this->hydrateToClass($itemData, $class);
                } else {
                    $results[] = $itemData;
                }
            }
        }
        
        return $results;
    }

    /**
     * Executa um scan no DynamoDB mock
     * 
     * @param string $tableName Nome da tabela
     * @param string|null $filterExpression Expressão de filtro
     * @param array|null $values Valores para a expressão
     * @param array $params Parâmetros adicionais
     * @param string|null $class Classe para hidratação
     * @return array
     */
    public function scan(
        string $tableName,
        ?string $filterExpression = null,
        ?array $values = null,
        array $params = [],
        ?string $class = null
    ): array {
        if (!isset($this->data[$tableName])) {
            return [];
        }
        
        $results = [];
        
        foreach ($this->data[$tableName] as $itemData) {
            if ($filterExpression && $values && !$this->matchesFilter($itemData, $filterExpression, $values)) {
                continue;
            }
            
            if ($class) {
                $results[] = $this->hydrateToClass($itemData, $class);
            } else {
                $results[] = $itemData;
            }
        }
        
        return $results;
    }

    /**
     * Atualiza um item no DynamoDB mock
     * 
     * @param string $tableName Nome da tabela
     * @param array $key Chave do item
     * @param string $updateExpression Expressão de atualização
     * @param array $values Valores para atualização
     * @param string|null $condition Condição para atualização
     * @return Result
     */
    public function update(
        string $tableName,
        array $key,
        string $updateExpression,
        array $values,
        ?string $condition = null
    ): Result {
        $keyString = $this->generateKeyString($key);
        
        if (isset($this->data[$tableName][$keyString])) {
            $itemData = $this->data[$tableName][$keyString];
            
            // Simula atualização baseada na expressão
            foreach ($values as $attribute => $value) {
                $itemData[$attribute] = $value;
            }
            
            $this->data[$tableName][$keyString] = $itemData;
        }
        
        return new Result(['@metadata' => ['statusCode' => 200]]);
    }

    /**
     * Sobrescreve o método listTables para usar dados mockados
     * 
     * @return array
     */
    public function listTables(): array
    {
        return array_keys($this->tables);
    }

    /**
     * Sobrescreve o método describeTable para usar dados mockados
     * 
     * @param string $tableName Nome da tabela
     * @return Result|array
     */
    public function describeTable(string $tableName): Result|array
    {
        if (isset($this->tables[$tableName])) {
            return new Result([
                'Table' => [
                    'TableName' => $tableName,
                    'TableStatus' => 'ACTIVE',
                    'CreationDateTime' => date('c'),
                    'ItemCount' => isset($this->data[$tableName]) ? count($this->data[$tableName]) : 0,
                    'TableSizeBytes' => 0,
                    'KeySchema' => $this->tables[$tableName]['KeySchema'] ?? [],
                    'AttributeDefinitions' => $this->tables[$tableName]['AttributeDefinitions'] ?? []
                ]
            ]);
        }
        
        return new Result(['Table' => null]);
    }

    /**
     * Sobrescreve o método createTable para usar dados mockados
     * 
     * @param array $tableSettings Configurações da tabela
     * @return Result|array
     */
    public function createTable(array $tableSettings): Result|array
    {
        $tableName = $tableSettings['TableName'];
        $this->tables[$tableName] = $tableSettings;
        $this->data[$tableName] = [];
        
        return new Result([
            'TableDescription' => [
                'TableName' => $tableName,
                'TableStatus' => 'CREATING',
                'CreationDateTime' => date('c')
            ]
        ]);
    }

    /**
     * Sobrescreve o método deleteTable para usar dados mockados
     * 
     * @param string $tableName Nome da tabela
     * @return Result|array
     */
    public function deleteTable(string $tableName): Result|array
    {
        if (isset($this->tables[$tableName])) {
            unset($this->tables[$tableName]);
            unset($this->data[$tableName]);
        }
        
        return new Result(['@metadata' => ['statusCode' => 200]]);
    }

    /**
     * Extrai a chave de um DynamoItem
     * 
     * @param DynamoItem $item
     * @return array
     */
    private function extractKey(DynamoItem $item): array
    {
        $key = [];
        $itemData = $item->toArray();
        
        // Simula extração da chave primária
        if (isset($itemData['id'])) {
            $key['id'] = $itemData['id'];
        }
        
        if (isset($itemData['pk'])) {
            $key['pk'] = $itemData['pk'];
        }
        
        return $key;
    }

    /**
     * Gera uma string única para a chave
     * 
     * @param array $key
     * @return string
     */
    private function generateKeyString(array $key): string
    {
        return md5(serialize($key));
    }

    /**
     * Verifica se um item corresponde à query
     * 
     * @param array $itemData
     * @param string $keyConditionExpression
     * @param array $values
     * @return bool
     */
    private function matchesQuery(array $itemData, string $keyConditionExpression, array $values): bool
    {
        // Implementação simplificada - sempre retorna true
        // Em uma implementação real, seria necessário parsear a expressão
        return true;
    }

    /**
     * Verifica se um item corresponde ao filtro
     * 
     * @param array $itemData
     * @param string $filterExpression
     * @param array $values
     * @return bool
     */
    private function matchesFilter(array $itemData, string $filterExpression, array $values): bool
    {
        // Implementação simplificada - sempre retorna true
        // Em uma implementação real, seria necessário parsear a expressão
        return true;
    }

    /**
     * Hidrata dados para uma classe específica
     * 
     * @param array $data
     * @param string $class
     * @return object
     */
    private function hydrateToClass(array $data, string $class): object
    {
        if (class_exists($class)) {
            $object = new $class();
            foreach ($data as $property => $value) {
                if (property_exists($object, $property)) {
                    $object->$property = $value;
                }
            }
            return $object;
        }
        
        return (object) $data;
    }

    /**
     * Obtém todos os dados mockados
     * 
     * @return array
     */
    public function getMockData(): array
    {
        return $this->data;
    }

    /**
     * Define dados mockados
     * 
     * @param array $data
     * @return void
     */
    public function setMockData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Limpa todos os dados mockados
     * 
     * @return void
     */
    public function clearMockData(): void
    {
        $this->data = [];
        $this->tables = [];
    }
}
