## ProcessIterator

L'`IterationMaker` ha appena selezionato un `Process` e lanciato la `iterate()` sul `ProcessIterator` passandgli
l'`id` di questo `Process`.

Il `ProcessIterator` chiede quindi al `ProcessHandlerRegistry` di dargli il `ProcessHandler` che è effettivamente
in grado di maneggiare il `Process` nel suo attuale stato, effettua una `handle()` sul `Process` per chiedere al
`ProcessHandler` di maneggiarlo.

Il `ProcessHandler` restituirà il solito `IterationResponse`, il `ProcessIterator` effettuerà la `release()`
sul `Process` per poi restituire l'`IterationResponse` così com'è.

Il `ProcessIterator` non fa molto ma si è preferito dedicare una classe specifica per questa responsabilità di
coordinamento.

