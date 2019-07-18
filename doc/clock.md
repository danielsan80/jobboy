## Il Clock

`Clock` è la classe incaricata di creare gli oggetti `DateTime`.

Il testing del tempo è un grosso problema irrisolto. Probabilmente si dovrebbe intervenire supportando
il mocking del tempo nativamente, a livello Php.

In JobBoy anziché fare `$date = new \DateTimeImmutable()` si fa `$date = Clock::createDateTimeImmutable()`.

Il vantaggio sta nel fatto che è possibile sostituire la `TimeFactory` di `Clock` nei test.

E' fornita
un'implementazione della `TimeFactory` fatta con `Carbon` in grado di "freezzare" il tempo ad un momento
specifico.

E' anche fornita una `DefaultTimeFactory` che non fa altro che fare `new \DateTimeImmutable()`.




 