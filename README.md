# JobBoy


## Introduzione

JobBoy nasce dall'esigenza di gestire dei processi batch.

Inizialmente era stato sviluppato JobMan (contrazione di JobManager) che si proponeva di gestire molti più
aspetti ma con i primi utilizzi ci siamo resi conto che alcune feature non le avremmo
utilizzate per ragioni di semplicità a scapito di una presunta minore efficienza.

Così è nata l'idea di realizzare un JobMan più piccolo, portandoci un po' del codice
di JobMan e l'esperienza accumulata nel realizzarlo.

Forse un giorno JobBoy crescerà e diventerò un nuovo JobMan.

## Example ##
A Symfony4 example application using JobBoy is available [here](https://github.com/danielsan80/jobboy-example) 


## Getting started

Add JobBoy to your composer.json

```
composer require dansan/jobboy
```

Register the JobBoyBundle in your Syfmony app.

```php
<?php
# config/bundles.php

return [
    ...
    JobBoy\Bundle\JobBoyBundle\JobBoyBundle::class => ['all' => true],
];
```

Configure the bundle adding `config/packages/job_boy.yaml`. If you don't configure the bundle
 the default configuration is:

```yaml
# config/packages/job_boy.yaml
job_boy:
  process_repository: in_memory
  process_class: JobBoy\Process\Domain\Entity\Process
```

The InMemory ProcessRepository is only for test purposes so it is a bad idea to not configure the
bundle.

### ProcessRepository on Redis

The configuration to store the processes on Redis is:

```yaml
# config/packages/job_boy.yaml

parameters:
  env(JOBBOY_REDIS_HOST): ''
  env(JOBBOY_REDIS_PORT): ''

job_boy:
  process_repository: redis

  redis:
    host: '%env(resolve:JOBBOY_REDIS_HOST)%'
    port: '%env(resolve:JOBBOY_REDIS_PORT)%'
```

### ProcessRepository on Doctrine

The configuration to store the processes on Doctrine (mysql or mariadb) is:
```yaml
# config/packages/job_boy.yaml

job_boy:
  process_repository: doctrine
```

You need to install [Doctrine and DoctrineMigrations](https://symfony.com/doc/current/doctrine.html).


Ignore from schema updates all table starting with `__`

```yaml
#config/packages/doctrine.yaml

doctrine:
    dbal:
        ...
        schema_filter: ~^(?!__)~

```

Then create a migration (your first one) to create the `__process` table.
For example you could add [this one](./doc/php/Version00000000000000.php) to your `src/Migrations` folder.

This is the approach used in Broadway for the DbalEventStore.

## Sviluppo

Per iniziare clonare e avviare il progetto, poi eseguire i test.

```
git clone git@github.com:danielsan80/jobboy.git <project_dir>
cd <project_dir>
./dc up -d
./dc enter
test-all
```

Prima di fare `./dc up -d` la prima volta è meglio fare `cp .env.dist .env` e modificare il `.env`
opportunamente.

Se non ci sono test `@ignored` (e ad ora non ci sono) è sufficiente eseguire `test`
anziché `test-all`.

Se si usa PhpStorm (>=2018.03), per visualizzare l'uml in PlantUML presente dei .md installare Graphviz
(sulla macchina host)


```
sudo apt-get install graphviz
```


## Come funziona?

- [Il Process](./doc/process.md)
- [Il ProcessStatus](./doc/process_status.md)
- [Le date del Process](./doc/process_dates.md)
- [ProcessParameters e ProcessStore](./doc/process_parameters_and_store.md)
- [Il Clock](./doc/clock.md)
- [Il Lock](./doc/lock.md)
- [Il ProcessRepository](./doc/process_repository.md)
- [IterationMaker](./doc/iteration_maker.md)
- [ProcessIterator](./doc/process_iterator.md)
- [ProcessHandlers](./doc/process_handlers.md)
- [Gli Application services](./doc/application_services.md)
- [L'EventBus](./doc/event_bus.md)
- [I Console Command](./doc/console_commands.md)
- [Il Bundle](./doc/bundle.md)
- [JobBoy e JobMan](./doc/jobman.md)


## Credits

Thanks to [Broadway](https://github.com/broadway/broadway) for the inspiring good code
(EventBus, DbalEventStore, Assertions, ...)

Thanks to [Akeneo](https://github.com/akeneo/pim-community-dev) for the original AkeneoBatchBundle(v1.7) design. 

## Risorse

[Notes](doc/notes.md)

## To do
- Testare gli appliation services
- Creare i comandi mancanti per gli application services
- add cron example
- test overlay dei `work`
- Separare il repo in `jobboy-bundle`, `jobboy`, `jobboy-process-redis`, `jobboy-process-doctrine`
- Ottimizzare `RedisProcessRepository`
- Ottimizzare `DoctrineProcessRepository`?

