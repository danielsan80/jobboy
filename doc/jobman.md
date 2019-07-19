## JobBoy e JobMan

In JobBoy si rinuncia all'idea di poter avere più worker che gestiscono in parallelo
i job con sync key differenti.

JobMan si appoggia ad Alek6 per la creazione dei worker che lanciano i suoi servizi.
Ad ogni worker viene assegnato un nome e Alek6 impedisce l'esecuzione parallela di worker con lo stesso nome attraverso
il locking (un semaforo).

In JobBoy il meccanismo di locking è stato portato dentro al servizio che gestisce i processi
per cui che si usi Alek6 o un Symfony console command scritto ad hoc per il worker, non sarà mai possibile
eseguire lo stesso servizio in due processi php differenti.

