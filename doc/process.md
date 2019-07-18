## Il Process

L'entità `Process` di JobBoy corrisponde all'entità `JobExecution` di JobMan.
La fondamentale differenza sta nelle relazioni che la `JobExecution` ha con altre entità:

In JobMan infatti si possono creare dei `Job` ossia oggetti che aggregano tutti i servizi che concorrono
alla definizione di un job.
Da un `Job` è quindi possibile accedere:
- al suo `JobBehaviour` cioé il codice da eseguire per portare a termine il lavoro.
- al suo codice univoco identificativo
- ai suoi tags utili per organzizzarne la selezione nell'interfaccia
- ai suoi parametri di default
- alla form da creare nell'interfaccia per raccogliere i parametri dall'utente
(tramite Form Framework Symfony ad esempio)
- ai vincoli da applicare ai parametri inseriti dall'utente nell'interfaccia
(tramite Validator Symfony ad esempio)
- a tutto ciò che può servire per definire un `Job`

Lo stato di un `Job` non viene salvato in nessun caso su un database o un file: è statica, scritta nelle classi
o in file di configurazione, definita dal dev, non dall'utente finale.

> Ad ora il `Job` non gestisce ancora tutto quanto scritto. L'idea è di fare in modo che ciò che può essere allegato
ad un job sia dinamico, con alcune cose opzinali ed altre obbligatorie. Ad esempio il `JobBehaviour` è obbligatorio
ma la definizione di una Form no se non c'è una UI.

Di ogni `Job` è possibile creare `n` `JobInstance`, entità a cui viene assegnato un nome e dei parametri eventualmente
diversi da quelli di default del `Job`. Le `JobInstance` sono profili diversi per lo stesso `Job`. Nel caso più
semplice esiste una sola `JobInstance` che ha il codice del `Job` come nome e mantiene i parametri di default.

Per ogni `JobInstance` è possibile lanciare `n` `JobExecution` in momenti diversi. E' quest'ultima che viene data
in pasto al `JobBehavior` corrispondente, il quale ne manipolerà lo stato fino a portarla in uno stato di
`completed` oppure `failed`.

Per gestire i parametri di `Job`, `JobInstance` e `JobExecution` esiste un value object `JobParameters`.

Per memorizzare contatori e dati temporanei (o di output) inoltre, è possibile creare dei `JobData`, relazionati
con le `JobExecution`: ad ogni `JobExecution` corrispondono `n` `JobData`.


```puml
class Job <<aggregate>> {
    parameters: JobParameters <<vo>>
}
class JobInstance <<entity>> {
    parameters: JobParameters <<vo>>
}

class JobExecution <<entity>> {
    parameters: JobParameters <<vo>>
    status: JobStatus <<vo>>
}

class JobData <<entity>>

JobBehaviour ..>  JobExecution: <<manage>>
Job --> JobBehaviour
Job <-- "*" JobInstance
JobInstance <-- "*" JobExecution

JobData "*" --> JobExecution
```

In JobBoy non esiste più una classe `Job` che aggrega tutto il necessario. Il Job è stato ridotto al `code` del
`Process`, ad una mera stringa di testo.

Non esiste più la possibilità di creare diversi profili con parametri diversi attraverso la `JobInstance`.
Si può solo creare dei `Process` con lo stesso codice ma parametri diversi.

I `JobData` che in JobMan erano entità a se stanti utili per gestire la concorrenza sulla stessa `JobExecution`
(ed esempio per incrementare un contatore, un'operazione atomica nei DBMS) in JobBoy diventano un value object
(il `ProcessStore`) memorizzato tra le proprietà del `Process`.

Il `JobStatus` diventa `ProcessStatus`: un paio di piccole modifiche ma praticamente è lo stessa cosa.

Il `JobBehaviour` che in alcuni casi si era dimostrato troppo rigido è stato sostituito da un set di `ProcessHandler`
aggregati in un `ProcessHandlerRegistry`.


```puml
class Process <<entity>> {
    code: string
    parameters: ProcessParameters <<vo>>
    status: ProcessStatus <<vo>>
    store: ProcessStore <<vo>>
}
```



Qualora i concetti di `Job`, `JobInstance` e `JobData` fossero necessari dovranno essere implementati nell'applicazione
finale. 

 
