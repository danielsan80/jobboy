## ProcessHandlers

Il `ProcessHandlerRegistry` è un registro nel quale vengono aggiunti tutti i `ProcessHandler` necessari
a gestire tutti i job supportati dall'applicazione finale e i loro `Process`.

Quando si registra un `ProcessHandler` è possibile specificare una priorità e un canale.

Nella più semplice delle ipotesi ogni `ProcessHandler` gestisce una casistica in maniera esclusiva
ed il loro insieme copre tutte le casistiche possibili, facendo si che sia sufficiente registrarli tutti
con la priorità di default (100).

E' comunque possibile lasciare scoperte alcune casistiche sulla priorità di default ed aggiungere dei
`ProcessHandler` di fallback per casistiche generiche con priorità superiori a 100. Allo stesso modo si potrebbero
aggiungere `ProcessHandler` per casistiche più specifiche con priorità inferiori a 100.

> Ad esempio si potrebbe gestire il caso dei `Process` rimasti `handled` con un `ProcessHandler` generico
che li mette tutti in stato di `failing` indipendente dal `code` del `Process`(questo `ProcessHandler` è disponibile
JobBoy con il nome `FreeHandled` ma non viene automaticamente registrato).
Tuttavia potrebbe essere anche aggiunto un `ProcessHandler` per uno specifico `code` che invece tenta il
ripristino del `Process`. 



Il `ProcessIterator` esegue la `get()` del `ProcessHandlerRegistry` sempre e solo sul canale di default `default`.
E' tuttavia possibile registrare `ProcessHandler` su altri canali utilizzati gerarchicamente da un `ProcessHandler`
presente sul canale di default. Questo può essere utile quando il numero di `ProcessHandler` cresce sensibilmente e
una ricerca lineare del `ProcessHandler` che gestisce un `Process` può dare problemi di performance.

Questo complica un po' le cose e rende difficile gestire la copertura di tutte le casistiche, pertanto è da prendere
in considerazione solo se necessario.

Un `ProcessHandler` quindi ha il metodo `supports()`, con il quale il `ProcessHandlerRegistry` determina quale di quelli
registrati è in grado di supportare il `Process` dato, e il metodo `handle()` con il quale il `ProcessHandler`
gestisce effettivamente il `Process`.

> ATTENZIONE: il `Process` viene passato al metodo supports() prima di essere in qualche modo modificato.
INVECE viene passato al metodo handle() già `handled` quindi qui non sarà più possibile fare considerazioni sul fatto
che prima fosse `handled` o meno.


