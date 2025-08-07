<?php

/**
 * Exemplo de uso do BaseTestCase melhorado
 * 
 * Este arquivo demonstra como usar as novas funcionalidades
 * implementadas no BaseTestCase para criar testes robustos.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Tests\BaseTestCase;
use Core\Tests\Mocks\RedisMock;

/**
 * Exemplo de classe de teste que estende BaseTestCase
 */
class UserApiTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        
        // Configura SQLite para testes
        static::configDbSqlite();
        
        // Mock do Redis para testes
        $redisMock = new RedisMock([
            'user:1' => ['id' => 1, 'name' => 'João Silva'],
            'user:2' => ['id' => 2, 'name' => 'Maria Santos']
        ]);
        static::mockContainerRedis($redisMock);
    }
    
    /**
     * Teste de criação de usuário
     */
    public function testCreateUser()
    {
        // Usa o helper createTestData para criar dados de teste
        $userData = $this->createTestData([
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'age' => 30
        ]);
        
        // Faz requisição POST
        $response = $this->post('/api/users', $userData);
        
        // Usa o helper assertJsonResponse para validar resposta
        $data = $this->assertJsonResponse($response, 201);
        
        // Assertions específicas
        $this->assertEquals('João Silva', $data['name']);
        $this->assertEquals('joao@email.com', $data['email']);
        $this->assertEquals(30, $data['age']);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('created_at', $data);
    }
    
    /**
     * Teste de busca de usuário
     */
    public function testGetUser()
    {
        // Requisição GET
        $response = $this->get('/api/users/1');
        
        // Valida resposta JSON
        $data = $this->assertJsonResponse($response, 200);
        
        // Assertions
        $this->assertEquals(1, $data['id']);
        $this->assertArrayHasKey('name', $data);
    }
    
    /**
     * Teste de atualização de usuário
     */
    public function testUpdateUser()
    {
        $updateData = [
            'name' => 'João Silva Atualizado',
            'email' => 'joao.novo@email.com'
        ];
        
        // Requisição PUT
        $response = $this->put('/api/users/1', $updateData);
        
        // Valida resposta
        $data = $this->assertJsonResponse($response, 200);
        
        // Assertions
        $this->assertEquals('João Silva Atualizado', $data['name']);
        $this->assertEquals('joao.novo@email.com', $data['email']);
    }
    
    /**
     * Teste de exclusão de usuário
     */
    public function testDeleteUser()
    {
        // Requisição DELETE
        $response = $this->delete('/api/users/1');
        
        // Valida resposta
        $this->assertJsonResponse($response, 204);
    }
    
    /**
     * Teste com headers customizados
     */
    public function testWithCustomHeaders()
    {
        $headers = [
            'Authorization' => 'Bearer token123',
            'X-API-Version' => 'v2'
        ];
        
        $response = $this->get('/api/users/1', [], [], $headers);
        $data = $this->assertJsonResponse($response, 200);
        
        $this->assertArrayHasKey('id', $data);
    }
    
    /**
     * Teste com cookies
     */
    public function testWithCookies()
    {
        $cookies = [
            'session_id' => 'abc123',
            'user_preference' => 'dark_mode'
        ];
        
        $response = $this->get('/api/users/1', [], $cookies);
        $data = $this->assertJsonResponse($response, 200);
        
        $this->assertArrayHasKey('id', $data);
    }
    
    /**
     * Teste de validação de erro
     */
    public function testValidationError()
    {
        // Dados inválidos
        $invalidData = [
            'email' => 'email-invalido'
        ];
        
        $response = $this->post('/api/users', $invalidData);
        
        // Espera erro 422 (Unprocessable Entity)
        $data = $this->assertJsonResponse($response, 422);
        
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('email', $data['errors']);
    }
    
    /**
     * Teste de autenticação
     */
    public function testAuthenticationRequired()
    {
        $response = $this->get('/api/protected/users');
        
        // Espera erro 401 (Unauthorized)
        $data = $this->assertJsonResponse($response, 401);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Unauthorized', $data['message']);
    }
    
    /**
     * Teste de limpeza de dados
     */
    public function testDataCleanup()
    {
        // Cria dados de teste
        $userData = $this->createTestData([
            'name' => 'Usuário Teste',
            'email' => 'teste@email.com'
        ]);
        
        $response = $this->post('/api/users', $userData);
        $this->assertJsonResponse($response, 201);
        
        // Limpa dados após o teste
        $this->cleanTestData('users');
    }
    
    public function tearDown(): void
    {
        // Limpa dados de teste
        $this->cleanTestData('users');
        parent::tearDown();
    }
}

/**
 * Exemplo de como executar os testes
 */
if (php_sapi_name() === 'cli') {
    echo "=== Exemplo de Uso do BaseTestCase Melhorado ===\n\n";
    
    echo "1. Configuração de Ambiente:\n";
    echo "   export TEST_USER=\$(whoami)\n\n";
    
    echo "2. Executar testes:\n";
    echo "   vendor/bin/phpunit tests/UserApiTest.php\n\n";
    
    echo "3. Executar com debug:\n";
    echo "   php -d display_errors=1 vendor/bin/phpunit tests/UserApiTest.php\n\n";
    
    echo "4. Verificar logs:\n";
    echo "   tail -f /var/log/php_errors.log\n\n";
    
    echo "=== Funcionalidades Disponíveis ===\n\n";
    
    echo "✅ Métodos HTTP:\n";
    echo "   - \$this->get(\$path, \$params, \$cookies, \$headers)\n";
    echo "   - \$this->post(\$path, \$data, \$cookies, \$headers)\n";
    echo "   - \$this->put(\$path, \$data, \$cookies, \$headers)\n";
    echo "   - \$this->delete(\$path, \$data, \$cookies, \$headers)\n\n";
    
    echo "✅ Helpers:\n";
    echo "   - \$this->assertJsonResponse(\$response, \$statusCode)\n";
    echo "   - \$this->createTestData(\$overrides)\n";
    echo "   - \$this->cleanTestData(\$table)\n\n";
    
    echo "✅ Configuração:\n";
    echo "   - static::configDbSqlite()\n";
    echo "   - static::mockContainerRedis(\$mock)\n\n";
    
    echo "✅ Segurança:\n";
    echo "   - Comandos shell seguros com escapeshellarg()\n";
    echo "   - Tratamento de erros robusto\n";
    echo "   - Logs detalhados\n";
    echo "   - Permissões configuráveis\n\n";
}
