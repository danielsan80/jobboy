## IterationMaker

Veniamo al cuore esecutivo di tutto il sistema.

Una volta creato un `Process` e aggiunto al `ProcessRepository` ci aspettiamo che qualcosa lo prenda,
esegua il lavoro che il `Process` rappresenta, ne alteri progressivamente lo stato interno e
lo porti allo stato `completed`.

L'idea alla base di JobBoy (e precedentemente di JobMan) è che su un `Process` debbano essere fatte una serie
di "iterazioni" le quali fanno una parte del lavoro da svolgere fino al completamento o al fallimento
del lavoro stesso. C'è quindi bisogno di qualcosa che esegua una singola iterazione su uno dei `Process` da processare
presenti nel `ProcessRepository`.

Ebbene il servizio di dominio che fa questo è l'`IterationMaker`.

Quando si usa il metodo `work()` dell'`IterationMaker` questo per prima cosa chiede il `Lock` sul `process-management`:
questo come spiegato in precedenza impedisce che un'altra istanza dell'`IterationMaker`
presente in un'altro processo PHP possa esegure la sua `work()` in concorrenza con esso.

Se il `Lock` viene concesso, l'`IterationMaker` chiede al `ProcessPrepository` se ci sono `Process` da iterare.

Prima chiede se ci sono dei `Process` `handled` (quindi rimasti tali in seguito ad un'eventuale precedente interruzione
non prevista del metodo `work()` ) ed in caso li gestisce.

Se non ha trovato nulla chiede se ci sono `Process` negli stati `evolving` al fine di portare a termine
ciò che era stato iniziato ed in caso li gestisce.

Infine se non c'è nulla negli stati `evolving` chiede se ci sono `Process` in `starting` ed in caso li gestisce.

Se proprio non c'è nulla da gestire non fa nulla... ovviamente.

Infine il `Lock` precedentemente acquisito viene rilasciato.

Il metodo `work()` restituisce sempre un `IterationResponse` attraverso il quale può comunicare al suo client
se ha fatto qualcosa oppure no in modo che il client possa decidere sul da farsi:

- chiedere all'`IterationMaker` di effettuare subito un'altra iterazione
- OPPURE attendere un po' prima di rieseguire il metodo `work()` dell'`IterationMaker`
- OPPURE qualsiasi altra cosa

questa scelta non è di competenza dell'`IterationMaker` il quale si limita a fornire informazioni su cosa ha fatto
o non ha fatto al suo client, attualmente solo se ha lavorato oppure no.

Ma come fa l'`IterationMaker` a iterare un `Process`?

L'`IterationMaker` si limita a selezionare il `Process` e poi chiede al `ProcessIterator` di effettuare effettivamente
l'iterazione con il suo metodo `iterate()`. Anche quest'ultimo metodo restituisce un `IterationResponse` e
l`IterationMaker` in tal caso restituirà direttamente quello.


