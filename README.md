# Calypte

Calypte é uma ótima opção de cache para aplicações PHP. Ele é extremamente rápido, trabalha bem com pouca memória, permite o armazenamento de dados na memória secundária e tem suporte transacional. Ele pode ser usado para fazer o cache de páginas, compartilhamento de variáveis globais e manipulação de sessões. Também é uma opção de cache no Laravel.

# Instalação

Calypte pode ser instalado usando Composer.

Adicione no arquivo composer.json em require "calypte/calypte": "1.0.*".

```
{
    "name": "meuprojeto",
    ...
    "require": {
    ...
    "calypte/calypte": "1.0.*"
    },
 
    ...
 
}
```

Depois de atualizado o arquivo de configuração, execute o comando composer update.

Outra forma de adicionar o Calypte ao seu projeto é executando o comando composer require calypte/calypte.

# Laravel

## Instalação

Executar o o comando composer require calypte/calypte.

## Configuração do cache.php

Aguarde o termino da atualização do projeto e abra o arquivo config/cache.php e dentro do arranjo stores[] adicione:

```
'calypte' => [
            'driver' => 'calypte',
            'host' => env('CALYPTE_HOST', '127.0.0.1'),
            'port' => env('CALYPTE_PORT', 1044),
    ],
```

## Configuração do app.php

Abra o arquivo config/app.php e dentro do arranjo providers[] adicione calypte\CalypteServiceProvider::class.

```
'providers' => [
        ...
        calypte\CalypteServiceProvider::class,
        ...
],
```

## Configuração do .env

Para finalizar abra o arquivo .env e adicione:

```
CACHE_DRIVER=calypte
SESSION_DRIVER=calypte
 
...
 
CALYPTE_HOST=127.0.0.1
CALYPTE_PORT=1044
```
