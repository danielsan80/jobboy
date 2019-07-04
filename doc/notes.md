## Notes

### Scenario
Voglio lanciare un job.

Lancio un comando da shell dove specifico il codice del job che voglio eseguire
e i parametri di questo job

Viene quindi creato un processo in stato di `starting` con questi valori e aggiunto al Repository


Un Worker viene avviato da cron,
chiede il lock per il permesso di agire sullo scheduling dei processi,
chiede al repository se ci sono dei processi `handled`.
Se ve ne sono evidentemente o qualcun altro sta maneggiando i processi
oppure l'esecuzione del Worker è stata interrotta.

> L'uso del Lock dovrebbe impedire a due worker di girare contemporaneamente
e che altri comandi interferiscano con quello del worker. Forse i servizi applicativi potrebbero
verificare che sia stato preso il lock e dare errore se qualcuno cerca di maneggiare i processi.

Escludendo il primo caso prendo il processo `handled` e lo mette in stato di `failing`
e lo passa al MainProcessIterator

Se non ci sono processi `handled` chiede al repository se ci sono processi in stato `failing`

Un ProcessIterator interverrà gestendo il fallimento e mettendo il processo in stato di `failed`.

> In caso di un multistep ci saranno diversi ProcessIterator per lo stato `failing`. In teoria ciascuno potrebbero
scattare per il proprio step, lasciare lo stato a `failing` ed impostare lo step precedente nel processo
fino a fare una rollback completa.

Se non ci sono processi in stato `failing` controlla se ci sono in stato `ending`

Se non ci sono processi in stato `ending` controlla se ci sono in stato `running`

Se non ci sono processi in stato `running` controlla se ci sono in stato `starting`

Se non ci sono processi `attivi` va in idle


In pratica il Worker prima cerca di risolvere i problemi poi di terminare ciò che è iniziato e solo per ultimo
avvia i nuovi processi.


Se è attivo un Worker l'utente dovra eseguire `startProcess` e attendere che venga gestito

Se non è attivo un Worker l'utente potrà lanciare `startProcess` ma nessuno prenderà in carico il suo processo
ammeno che non lanci manualmente il comando del worker `iterateProcesses` (`alek6:work`)

quindi potrò lanciare `executeProcess` che eseguirà tutte le iterazioni in un solo processo con il rischio di esaurire
la memoria.

Se questo viene fatto mentre è attivo il worker è possibile che il worker o il comando terminino per via del lock.

Idea: si può lanciare sempre una `executeProcess` cosicché se è attivo il worker fallirà nel tentativo di iterare, ma poi
ci penserà il worker, altrimenti itererà lei fino al completamento.

Praticamente sposto il lock da Alek6 al layer applicativo del jobboy.









 











Un Worker viene avviato da cron, chiede al repository se ci sono processi in stato `evolving`
ossia `running`|`ending`(|`waiting`)

Se ce ne sono 
 


 