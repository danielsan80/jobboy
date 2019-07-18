## Gli Application Services

Il JobBoy contiene alcuni pochi Application Service per utilizzarlo.

L'utilizzo standard di JobBoy è descritto dai seguenti servizi:

- **StartProcess** permette di creare un `Process`
- **Work** permette di eseguire un ciclo di iterazioni sui processi per un certo periodo di tempo (`timeout`) e
quanto tempo attendere prima di ritentare se non c'è nulla da fare `idle-time`. Praticamente è da usare così com'è
in un console command per creare un worker.

Alternativamente si può eseguire in maniera apparentemente sincrona il servizio:
  
- **ExecuteProcess** permette di creare ed eseguire un `Process`. Se è attivo un worker che utilizza l'`IterationMaker`
attenderà fino a che il processo creato non verrà completato, altrimenti lo itererà lui stesso usando l`IterationMaker`
fino al completamento.

Nell'ipotesi di voler utilizzare Alek6:

- **IterateOneProcess** esegue una sola iterazione usando l'`IterationMaker`. Si può quindi creare un `WorkerTask`
per Alek6 che utilizza questo servizio anziché usare il `Work` in un console command.


Alcune utilities:

- **RemoveOldProcesses** cancella i vecchi processi completati o falliti (dopo 90 giorni di default). I `Process`
non sono un dato importante e passato un certo periodo di tempo possono essere cancellati.

- **ListProcesses** restituisce i processi presenti sotto forma di un DTO.