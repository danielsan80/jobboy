## Il ProcessStatus

Il ProcessStatus è la rinomina del JobStatus del JobMan con alcune piccole modifiche.

Nel JobStatus era previsto lo stato `waiting` che costringeva la `JobExecution` ad avere e a gestire
la data `waitingUntil`.

Questo stato un po' anomalo rispetto agli alri è stato rimosso perché complicava le cose, sarebbe stato utilizzato
raramente ed il suo effetto si può ottenere in un altro modo.

> Ad esempio si può aggiungere una chiave `wait_until` nel `ProcessStore` del `Process`, configurare
due `ProcessHandler` che interventono esclusivamente nei due casi
`[status: running, wait_until: null]` e `[status: running, wait_until: <a_date>]`.
Il `ProcessHandler` che gestirà il `waiting` potrà dichiarare di non aver lavorato facendo si che il worker
vada in idle.

E' stato aggiunto poi lo stato di `failing` per gestire il tear down in caso di fallimento che potrebbe essere diverso
dal tear down in caso di completamento. 

Le transizioni di stato principali sono le seguenti: 


```puml
[*] --> starting
starting --> running
running --> ending
ending --> completed
running --> failing
failing --> failed
failed --> [*]
completed --> [*]
```

Altre transizioni sono possibili e altre sono vietati: sono indicate nel test dedicato al `ProcessStatus`.
In ogn caso lo state diagram indica il normale flusso di un `Process`


Esistono altri due macrostati, `active` ed `evolving` descritti dal quest state diagram
più dettagliato.

```puml
[*] --> active
state active {
    [*] --> starting
    
    starting --> evolving
    
    state evolving {
        [*] --> running
        running --> ending
        running --> failing
    }
    
    
    
}

failed --> [*]
ending --> completed
failing --> failed
completed --> [*]

```

Oltre al `ProcessStatus` c'è anche il concetto di `handling`. Un `Process` è `handled`
quando un `ProcessHandler` lo sta maneggiando e `unhandled` quando quest'ultimo lo rilascia.

```puml
    
    [*] --> active
    active --> completed
    active --> failed
    
    failed --> [*]
    completed --> [*]
    
    --
    
    [*] --> unhandled 
    unhandled --> handled
    handled --> unhandled
    unhandled --> [*]

```


E' possibile che il processo php del worker venga interrotto lasciando il `Process` `handled` anche
se non vi è più alcun `ProcessHandler` a maneggiarlo. E' previsto che si scrivano dei `ProcessHandler`
per gestire questo caso: il più semplice non fa altro che mettere il processo in `failing` e rilasciarlo
ma ad esempio un ProcessHandler potrebbe in alcuni casi essere in grado di effettuare una `recovery`
e rimettere il `Process` in `running`.

