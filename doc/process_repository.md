## Il ProcessRepository

Come di consueto i `Process` sono organizzati in un `ProcessRepository` che può essere considerato come una collezione
in memoria di `Process`.

Per il `ProcessRepository` è stata definita un'interfaccia ed è stato realizzato un TestCase astratto per testarla.

Ogni implementazione andrà testata estendendo il suddetto TestCase ed implementando i due metodi per la creazione
del Repository concreto e della Factory.

Sono stati realizzati 3 versioni infrastrutturali del `ProcessRepository`:

- **InMemory** dove il `PocessRepository` è effettivamente una collezione in memoria di `Process` ed è utilizzabile
solo nei test 
- **Doctrine** implementato usando Doctrine Dbal fortemente ispirato dal DbalEventStore di Broadway
- **Redis** implementato usando Redis non in modalità cache (non deve cancellare le chiavi autonomamente)
ma come db temporaneo.

Le implementazioni Doctrine e Redis attualmente non prevedono nessun tipo di `flush`: ad ogni modifica il `Process`
viene salvato. Non prevedono attualmente neppure un caching dei processi: ad ogni query sul repository viene fatta una
query al database sottostante.

Ovviamente sarà necessario intervenire su questi aspetti per razionalizzare l'interazione con i db sottostanti
ma per ora vanno bene così.

I ProcessRepository Doctrine e Redis sono disponibili in package separati,
rispettivamente `dansan/jobboy-processes-doctrine` e `dansan/jobboy-processes-redis`.