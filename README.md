# LiveQA

*LiveQA* ist ein kleine Communityseite für Q&A Livestreams.

## Seitenaufbau

- Stream

  - Fragen

    *Sortierung nach Interessant-Votes absteigend*

    *(User: Interessant-Button)*
    *(Streamer: Forum-Verweisung, Nächste Frage, Frage löschen)*

    - Impulse

      *Sortierung nach Kommentare absteigend, Zeit absteigend*

      *(Streamer: Impuls löschen)*

      - Kommentare

        *Sortierung nach Zeit aufsteigend*

        *(Streamer: Kommentar löschen)*

  - Umfrage

    *Nur eine Umfrage zur Zeit*

## Datenbank

### fragen

| Spalte | Beschreibung                                             | Typ  |
| ------ | -------------------------------------------------------- | ---- |
| id     | auto_increment                                           | int  |
| user   | user.id                                                  | int  |
| inhalt | (Max. 300 Zeichen)                                       | text |
| forum  | 0: Nein (Default), 1: Ja                                 | int  |
| status | 0: Keinen (Default), 1: Aktuelle Frage, 2: Nächste Frage | int  |
| time   | UNIX Timestamp                                           | int  |

### interesse

| Spalte | Beschreibung   | Typ  |
| ------ | -------------- | ---- |
| id     | auto_increment | int  |
| user   | user.id        | int  |
| frage  | fragen.id      | int  |

### kommentare

| Spalte | Beschreibung         | Typ  |
| ------ | -------------------- | ---- |
| id     | auto_increment       | int  |
| user   | user.id              | int  |
| typ    | 0: Frage, 1: Umfrage |      |
| frage  | fragen.id            | int  |
| inhalt | (Max. 300 Zeichen)   | text |

### umfrage

| Spalte | Beschreibung         | Typ  |
| ------ | -------------------- | ---- |
| id     | auto_increment       | int  |
| typ    | 0: Frage, 1: Antwort | int  |
| inhalt |                      | text |

### ergebnis

| Spalte  | Beschreibung   | Typ  |
| ------- | -------------- | ---- |
| id      | auto_increment | int  |
| user    | user.id        | int  |
| antwort | umfrage.id     | int  |

### user

| Spalte   | Beschreibung                                           | Typ  |
| -------- | ------------------------------------------------------ | ---- |
| id       | auto_increment                                         | int  |
| session  | PHP Session ID                                         | text |
| nickname | (Max. 30 Zeichen)                                      | text |
| os       | (Max. 30 Zeichen )                                     | text |
| level    | 0: Anfänger, 1: Nutzer, 2: Fortgeschrittener, 3: Profi | int  |

