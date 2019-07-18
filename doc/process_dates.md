## Le date del Process

Tra i dati che vengono memorizzati in un `Process` ci sono alcune date:

- **createdAt**: è la data nella quale il `Process` è stato creato (in stato di `starting`)
- **updatedAt**: è la data dell'ultima modifica avvenuta ed è impostata dal metodo `touch()`
- **statedAt**: è la data nella quale il `Process` è stato evvettivamente avviato (quando è passato dallo stato `starting`
allo stato `running`)
- **endedAt**: è la data nella quale il `Process` è stato termiato in qualche modo (quando è passato allo stato `completed`
o `failed`)
- **handledAt**: è la data nella quale il `Process` è stato preso in carico da un
`ProcessHandler`. Il fatto che sia impostata rende il `Process` `handled` mentre se è a `null` lo rende
`unhandled`

Il tipo utilizzato per le date è sempre il `DateTimeImmutable` e viene sempre usato `Clock`
per generarle.