## Il Lock

In Alek6 non dovevano mai essere eseguite in concorrenza due istanze di due worker battezzati con lo stesso nome.
Il secondo si sarebbe dovuto rendere conto della concorrenza con un altro worker identico e autoterminarsi.

Per fare questo ci si era chiesti se ci fosse un modo per implementare un semaforo in PHP.
Il semaforo è uno degli strumenti teorici per gestire la concorrenza.

In breve, poiché alcuni frammenti di codice non devono andare in nessun caso in concorrenza con altri
frammenti di codice eseguiti in parallelo, prima di esequirli si chiede il lock ad un semaforo e lo si rilascia quando
si ha finito.

E' quindi necessario appoggiarsi ad una primitiva del sistema oparativo
che sabbiamo essere atomica ossia che non possa in nessun caso essere sospesa dallo scheduler
durante un context switch. Solo in questo modo possiamo essere certi che anche l'operazione di lock
non vada in concorrenza e che non vengano rilasciate 2 autorizzazioni a procedere contemporaneamente.

Dopo breve avevamo scoperto che tramite la primitiva flock di PHP era possibile fare un locking atomico.

Dopo aver implementato il nostro concetto del `Lock` ci siamo è resi conto che Symfony aveva un componente che faceva
la stessa cosa e che praticamente aveva proposto la stessa implementazione della nosta.

Conseguentemente l'implementazione del `Lock` presente in JobBoy è un po' più simile a quella di Symfony di
quella di JobMan. A livello infrastrutturale poi utilizziamo il componente Lock di Symfony


In pratica esistono solo due classi, la `LockFactory` e la `Lock`. Alla `LockFactory` chiediamo di crearci/darci
un `Lock` specificando il nome che lo identifica. Al `Lock` poi chiediamo di poter accedere alla risorsa
che rappresenta in maniera esclusiva attraverso il metodo `acquire()`. `acquire()` ritorna `true`
se la risorsa è disponibile e `false` se non lo è. Nel primo caso possiamo proseguire mentre nel secondo no e
dobbiamo interrompere o attendere o la lanciare un'eccezione.

Quando abbiamo finito possiamo rilasciare la risorsa con il metodo `release()` del `Lock`.

Vedremo in seguito dove avremo bisogno di questo strumento.
  