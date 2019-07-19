# JobBoy


## Getting started


## Sviluppo

Per iniziare clonare e avviare il progetto, poi eseguire i test.

```
git clone [repository] <project_dir>
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

## Introduzione

JobBoy nasce dall'esigenza di gestire dei processi batch.

Inizalmente era stato sviluppato JobMan (contrazione di JobManager) che si proponeva di gestire molti più
aspetti ma con i primi utilizzi ci siamo resi conto che alcune feature non le avremmo
utilizzate per ragioni di semplicità a scapito di una presunta minore efficienza.

Così è nata l'idea di realizzare un JobMan più piccolo, portandoci un po' del codice
di JobMan e l'esperienza accumulata nel realizzarlo.

Forse un giorno JobBoy crescerà e diventerò un nuovo JobMan.


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
- [Gli Application services](./doc/application_services.md)
- [L'EventBus](./doc/event_bus.md)
- [I Console Command](./doc/console_commands.md)
- [Il Bundle](./doc/bundle.md)
- [JobBoy e JobMan](./doc/jobman.md)


## Risorse

[Notes](doc/notes.md)

## To do
