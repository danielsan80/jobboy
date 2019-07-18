# JobBoy

[Notes](doc/notes.md)

## Getting started

## Introduzione

JobBoy nasce dall'esigenza di gestire dei processi batch.

Inizalmente era stato sviluppato JobMan (contrazione di JobManager) che si proponeva di gestire molti più
aspetti ma con i primi utilizzi ci siamo resi conto che alcune feature non le avremmo
utilizzate per ragioni di semplicità a scapito di una presunta minore efficienza.

Così è nata l'idea di realizzare un JobMan più piccolo, portandoci un po' del codice
di JobMan e l'esperienza accumulata nel realizzarlo.

Forse un giorno JobBoy crescerà e diventerò un nuovo JobMan.





## Scaletta

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

- JobBoy e JobMan
- Il Bundle
- I Console Command
- L'EventBus



## Differenze con JobMan
In JobBoy si rinuncia all'idea di poter avere più worker che gestiscono in parallelo
i job con sync key differenti.
JobMan si appoggia ad Alek6 per la creazione dei worker che lanciano i suoi servizi.
Ad ogni worker viene assegnato un nome e Alek6 impedisce l'esecuzione parallela di worker con lo stesso nome attraverso
il locking (un semaforo)
In JobBoy il meccanismo di locking è stato portato dentro al servizio che gestisce i processi
per cui che si usi Alek6 o un Symfony console command scritto ad hoc per il worker, non sarà mai possibile
eseguire lo stesso servizio in due processi php differenti.


## Risorse

## To do
