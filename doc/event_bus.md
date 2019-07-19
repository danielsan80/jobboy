## L'EventBus

In JobBoy è stato creato un EventBus di dominio.

L'EventBus è fortemente ispirato a quello di Broadway ed è molto semplice.

Lo scopo dell'EventBus è quello di permettere ai servizi di dominio di notificare il verificarsi
di alcuni eventi significativi.

In questo modo un ConsoleCommand può mostrare un output di quello che sta accadendo oppure aprire e chiudere
transazioni sul db o fare flush.

Tendenzialmente un servizio che ha qualcosa da dire dipende opzionalmente dall'`EventBusInterface`, istanzia un 
`NullEventBus` se non viene passato dall'esterno e pubblica degli eventi su di esso.

Ovviamente il `NullEventBus` ignora listener ed eventi.




