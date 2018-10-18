# Calypte

Calypte é um sistema de cache de propósito geral com suporte transacional. 
Permite o armazenamento de dados na forma de chave-valor em memoria e disco. É extremamente rápido, 
tanto para escrita como para leitura, podendo chegar a mais de 600.000 operações por segundo. 
Não é necessária grandes quantidades de memória para seu funcionamento. Ele trabalha de forma eficiente 
com pouca memória.

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
