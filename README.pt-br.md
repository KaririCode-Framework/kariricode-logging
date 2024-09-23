# KaririCode Framework: Logging Component

[![en](https://img.shields.io/badge/lang-en-red.svg)](README.md)
[![pt-br](https://img.shields.io/badge/lang-pt--br-green.svg)](README.pt-br.md)

![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Makefile](https://img.shields.io/badge/Makefile-1D1D1D?style=for-the-badge&logo=gnu&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PHPUnit](https://img.shields.io/badge/PHPUnit-78E130?style=for-the-badge&logo=phpunit&logoColor=white)

Um componente de logging robusto, flexível e compatível com PSR-3 para o Framework KaririCode, fornecendo capacidades abrangentes de logging para aplicações PHP.

## Características

- Compatível com PSR-3
- Suporte a múltiplos canais de log (arquivo, Slack, Papertrail, Elasticsearch)
- Criptografia de logs
- Suporte a logging assíncrono
- Logging de consultas e desempenho
- Formatadores de log flexíveis
- Suporte a rotação e limpeza de logs
- Lógica de circuit breaker e retry para logging
- Logging detalhado de contexto e estruturado

## Instalação

Para instalar o componente de Logging do KaririCode, execute o seguinte comando:

```bash
composer require kariricode/logging
```

## Uso Básico

### Passo 1: Configuração do Ambiente

O **Componente de Logging do KaririCode** depende de várias variáveis de ambiente para configurar canais de log, níveis de log, serviços externos e outros parâmetros. Essas variáveis são definidas em um arquivo `.env`, e o projeto vem com um `.env.example` padrão que deve ser copiado para `.env` para a configuração inicial.

Para copiar e criar seu arquivo `.env`, execute o seguinte comando:

```bash
make setup-env
```

Este comando criará um arquivo `.env` se ele ainda não existir. Depois disso, você pode modificar os valores de acordo com suas necessidades. Abaixo estão algumas variáveis importantes e suas descrições:

```ini
# Ambiente da aplicação (ex: production, develop)
KARIRICODE_APP=develop

# Versão do PHP e porta usada pelo serviço Docker
KARIRICODE_PHP_VERSION=8.3
KARIRICODE_PHP_PORT=9303

# Canal de log padrão (ex: file, stderr, slack)
LOG_CHANNEL=file

# Nível de log (ex: debug, info, warning, error)
LOG_LEVEL=debug

# Chave de criptografia para dados de log (mantenha isso seguro)
LOG_ENCRYPTION_KEY=83302e6472acda6a8aeadf78409ceda3959994991393cdafbe23d2a46a148ba4

# Configuração do Slack para enviar logs críticos
SLACK_BOT_TOKEN=xoxb-seu-token-de-bot-aqui
SLACK_CHANNEL=#nome-do-seu-canal

# Configuração do serviço de logging Papertrail
PAPERTRAIL_URL=logs.papertrailapp.com
PAPERTRAIL_PORT=12345

# Formatador para logs escritos em stderr
LOG_STDERR_FORMATTER=json

# Índice do Elasticsearch para armazenar logs
ELASTIC_LOG_INDEX=logging-logs

# Habilitar ou desabilitar logging assíncrono
ASYNC_LOG_ENABLED=true

# Habilitar ou desabilitar logging de consultas e configurar limites
QUERY_LOG_ENABLED=true
QUERY_LOG_CHANNEL=file
QUERY_LOG_THRESHOLD=100

# Habilitar ou desabilitar logging de desempenho e configurar limites
PERFORMANCE_LOG_ENABLED=true
PERFORMANCE_LOG_CHANNEL=file
PERFORMANCE_LOG_THRESHOLD=1000

# Habilitar ou desabilitar logging de erros
ERROR_LOG_ENABLED=true
ERROR_LOG_CHANNEL=file

# Configuração de limpeza de logs (remoção automática de logs mais antigos que o número de dias especificado)
LOG_CLEANER_ENABLED=true
LOG_CLEANER_KEEP_DAYS=30

# Configuração de circuit breaker para gerenciar retentativas de log
CIRCUIT_BREAKER_FAILURE_THRESHOLD=3
CIRCUIT_BREAKER_RESET_TIMEOUT=60

# Configuração de retentativa para falhas de log
RETRY_MAX_ATTEMPTS=3
RETRY_DELAY=1000
RETRY_MULTIPLIER=2
RETRY_JITTER=100
```

Cada uma dessas variáveis pode ser ajustada de acordo com suas necessidades específicas:

- **Canais de Log:** Você pode escolher entre diferentes canais de logging como `file`, `slack`, ou `stderr`. Por exemplo, `LOG_CHANNEL=slack` enviará logs críticos para um canal do Slack.
- **Níveis de Log:** Isso define o nível mínimo de severidade para os logs serem registrados (ex: `debug`, `info`, `warning`, `error`, `critical`).
- **Serviços Externos:** Se você quiser enviar logs para serviços externos como Slack ou Papertrail, certifique-se de configurar corretamente `SLACK_BOT_TOKEN`, `PAPERTRAIL_URL`, e `PAPERTRAIL_PORT`.

### Passo 2: Carregando Variáveis de Ambiente e Configurações

Após configurar seu arquivo `.env`, você precisa carregar as variáveis de ambiente em sua aplicação e especificar o caminho para o arquivo de configuração de logging. Isso é feito na inicialização da aplicação.

Aqui está como configurar isso no seu arquivo `application.php`:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerRegistry;
use KaririCode\Logging\Service\LoggerServiceProvider;
use KaririCode\Logging\Util\Config;

// Carrega variáveis de ambiente do arquivo .env
Config::loadEnv();

// Especifica o caminho para o arquivo de configuração de logging
$configPath = __DIR__ . '/../config/logging.php';

// Inicializa a configuração do logger
$loggerConfig = new LoggerConfiguration();
$loggerConfig->load($configPath);

// Cria a fábrica de logger e o registro
$loggerFactory = new LoggerFactory($loggerConfig);
$loggerRegistry = new LoggerRegistry();

// Registra os loggers usando o provedor de serviço
$serviceProvider = new LoggerServiceProvider(
    $loggerConfig,
    $loggerFactory,
    $loggerRegistry
);
$serviceProvider->register();
```

### Passo 3: Exemplo de Logging

Uma vez que as variáveis de ambiente e a configuração estão carregadas, você pode começar a usar os loggers. Aqui está um exemplo de logging de mensagens em diferentes níveis:

```php
$defaultLogger = $loggerRegistry->getLogger('console');

// Registra mensagens com diferentes níveis de severidade
$defaultLogger->debug('O email do usuário é john.doe@example.com');
$defaultLogger->info('O IP do usuário é 192.168.1.1');
$defaultLogger->notice('O número do cartão de crédito do usuário é 1234-5678-1234-5678', ['contexto' => 'cartão de crédito']);
$defaultLogger->warning('O número de telefone do usuário é (11) 91234-7890', ['contexto' => 'telefone']);
$defaultLogger->error('Ocorreu um erro com o email john.doe@example.com', ['contexto' => 'erro']);
$defaultLogger->critical('Problema crítico com o IP 192.168.1.1', ['contexto' => 'crítico']);
$defaultLogger->alert('Alerta referente ao cartão de crédito 1234-5678-1234-5678', ['contexto' => 'alerta']);
$defaultLogger->emergency('Emergência com o número de telefone 123-456-7890', ['contexto' => 'emergência']);
```

### Passo 4: Usando Loggers Especializados

O Componente de Logging do KaririCode também suporta loggers especializados, como para logging assíncrono, logging de consultas e logging de desempenho. Aqui está como você pode usar esses loggers:

```php
// Logger assíncrono
$asyncLogger = $loggerRegistry->getLogger('async');
if ($asyncLogger) {
    for ($i = 0; $i < 3; ++$i) {
        $asyncLogger->info("Mensagem de log assíncrono {$i}", ['contexto' => "lote {$i}"]);
    }
}

// Logger de consultas para consultas de banco de dados
$queryLogger = $loggerRegistry->getLogger('query');
$queryLogger->info('Executando consulta', ['query' => 'SELECT * FROM users', 'bindings' => []]);

// Logger de desempenho para rastrear tempo de execução
$performanceLogger = $loggerRegistry->getLogger('performance');
$performanceLogger->debug('Log de desempenho', ['tempo_de_execucao' => 1000]);

// Logger de erros para lidar com erros críticos
$errorLogger = $loggerRegistry->getLogger('error');
$errorLogger->error('Ocorreu um erro crítico', ['contexto' => 'Detalhes do erro']);
```

### Passo 5: Enviando Logs Críticos para o Slack

Se você configurou o Slack como um canal de logging no arquivo `.env`, você pode enviar logs críticos diretamente para um canal especificado do Slack:

```php
$slackLogger = $loggerRegistry->getLogger('slack');
$slackLogger->critical('Esta é uma mensagem crítica enviada para o Slack', ['contexto' => 'slack']);
```

Certifique-se de que você configurou seu `SLACK_BOT_TOKEN` e `SLACK_CHANNEL` no arquivo `.env` para que isso funcione corretamente.

## Testes

Para executar testes para o Componente de Logging do KaririCode, você pode usar o PHPUnit. Execute o seguinte comando dentro do seu contêiner Docker:

```bash
make test
```

Para cobertura de testes:

```bash
make coverage
```

## Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## Suporte e Comunidade

- **Documentação**: [https://kariricode.org](https://kariricode.org)
- **Rastreador de Problemas**: [GitHub Issues](https://github.com/KaririCode-Framework/kariricode-contract/issues)
- **Comunidade**: [Comunidade KaririCode Club](https://kariricode.club)
- **Suporte Profissional**: Para suporte de nível empresarial, entre em contato conosco em support@kariricode.org

## Agradecimentos

- A equipe do Framework KaririCode e contribuidores.
- A comunidade PHP por seu contínuo suporte e inspiração.

---

Construído com ❤️ pela equipe KaririCode. Capacitando desenvolvedores para construir aplicações PHP mais robustas e flexíveis.

Mantido por Walmir Silva - [walmir.silva@kariricode.org](mailto:walmir.silva@kariricode.org)
