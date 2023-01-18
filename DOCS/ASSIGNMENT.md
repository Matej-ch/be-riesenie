# Backend

## Zadanie

Je daný produktový katalóg, treba navrhnúť tieto **API endpointy**:
* listing produktov
* detail produktu
* vyhľadávanie produktov
* vkladanie a editáciu produktu


Ako DB je použitá **MySQL**, ale každý produkt musí byť uložený aj do **ElasticSearch**, ktorý sa
používa pre vyhľadávanie
Listing produktov a detail produktu používa **cache**, napr. Redis.

Len na úrovni ER diagramu, resp. SQL pre vytvorenie tabuliek je potrebné vypracovať:
* každý Produkt patrí práve do jednej Kategórie, každá Kategória môže mať n Produktov
* každý Produkt môže mať max. 3 Obrázky

## Popis

* pre úspešné vypracovanie stačí koncept, teda bez konrétnej implementácie metód _DOPLNIT
  ASPON CAST REALNE ZAPRACOVANU_

* je potrebné navrhnúť controllery, services, modely ... atď.
* metódy môžu byť prázdne, iba s popisom ich navrhovanej funkčnosti
* výsledok je potrebné odovzdať cez akýkoľvek GIT repozitár
* použitie frameworku nie je podmienkou, zaujíma nás vrstvenie kódu, SOLID princípy apod.
* pre návrh DB stačí ER diagram, resp. SQL pre vytvorenie tabuliek