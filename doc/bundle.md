## Il Bundle

Per integrare JobBoy in un progetto Symfony è stato creato un bundle.
 
Questo registra i vari servizi di JobBoy nel DependencyInjectionContainer.

Definisce la sua configurazione.

Semplifica la selezione dei ProcessRepository forniti `redis` e `doctrine` ma permette anche
di scriverne uno con altre tecnologie.

In caso di repository `redis` permette di configurare host e porta.


Un CompilerPass configura il `ProcessRepository` selezionato in configurazione.

Un CompilerPass permette di aggiungere i `ProcessHandler` definiti nell'applicazione finale al
`ProcessHandlerRegistry`.

Un CompilerPass permette di sottoscrivere degli `EventListener` all'`EventBus` di dominio per intercettare
gli eventi di dominio.

Infine il Bundle crea dei Console Command nell'applicazione finale con prefisso `jobboy` per eseguire
gli application service di JobBoy.


Quello che basta fare quindi è aggiungere la dipendenza del bundle nel composer.json e registrarlo tra i propri bundle,
creare il file di configurazione, creare i propri `ProcessHandler` e taggarli opportunamente.

Infine o si usano gli application service nella propria applicazione oppure si usano i comandi da console per
lanciare ed eseguire i processi.

Si può creare un worker in cron che lanci ogni minuto il comando `Work` oppure si può creare un `WorkerTask`
in Alek6 che usa il servizio `IterateOneProcess`.

Ogni settimana (in cron) si deve lanciare il comando per la rimozione dei vecchi processi.