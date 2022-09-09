# Utastájékoztató
A vasútállomásokon találhatóakhoz hasonló utastájékoztató felület az érkező és induló vonatokról.

## Használata

Paraméterek:
- állomásnév (kötelező)
    - pl.: domain.tld/?station=szeged
    - bármely magyarországi állomásnév megadható, amennyiben azt az API felismeri

## Képernyőkép
![Képernyőkép a programról](https://github.com/kissmarcell/utastajekoztato/blob/master/docs/img/screenshot.png)

## API
A weboldal az [oroszi.net féle](https://bitbucket.org/oroce/elvira-api/wiki/Home) API-t használja, az ezen rendszer által felismert állomásnevek automatikusan használhatóak.