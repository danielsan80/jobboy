## ProcessParameters e ProcessStore

`ProcessParameters` e `ProcessStore` sono due value object immutabili per certe versi molto simili ma che hanno
scopi differenti nel `Process`. Sono entrambi delle `ParametersBag` ed offrono metodi per l'interrogazione
e la manipolazione del loro contenuto. Essendo immutabili i metodi di manipolazione tuttavia restituiscono
nuove istanze di loro stessi con le opportune modifiche.

Il `ProcessParameters` viene creato alla creazione del `Process` e quest'ultimo poi lo congela e non permette
più di modificarlo. I parametri rimangono invariati per tutta la vita del `Process`.

Il `ProcessStore` invece può essere "manipolato" attraverso alcuni metodi del `Process`. Il suo scopo è quello
di memorizzare contatori, posizioni di file temporanei, il nome dello step corrente, la reason in caso di fallimento,
sottostati, informazioni da mostrare in UI... E' a discrezione del dev utilizzatore.

Poiché il sistema garantisce che il servizio `MakeIteration` che manipola i `Process` non possa essere eseguito
in concorrenza con se stesso non ci sarà neppure concorrenza nell'accesso ai metodi di manipolazione del `Process`.


