# LiveQA

*LiveQA* ist ein kleine Communityseite für Q&A Livestreams.

## Seitenaufbau

- Stream

  - Fragen

    *Sortierung nach Interessant-Votes absteigend*

    *(User: Interessant-Button)*
    *(Streamer: Forum-Verweisung, Nächste Frage, Frage löschen)*

    - Kommentare

      *Sortierung nach Zeit aufsteigend*

      *(Streamer: Kommentar löschen)*

  - Umfrage

    *Nur eine Umfrage zur Zeit*

## Events

### sys

```json
{
    "data": [
        [
            "type": "text",
            "host": "",
            "headline": ""
        ],
        [
            "type": "css",
            "key": "--var-xyz",
            "value": ""
        ],
        [
            "type": "project",
            "id": 0,
            "name": "",
            "active": 0,
            "remove": 0
        ],
        [
            "type": "user",
            "username": "",
            "session": "",
            "level": "",
            "os": "",
            "mod": 0
        ]
    ]
}
```



### content

```json
{
    "data": [
        [
            "type": "frage",
            "id": 0,
            "remove": 0,
            "username": "",
            "level": "",
            "os": "",
            "forum": 0,
            "interessant": 0,
            "inhalt": "",
            "status": 0
        ],
        [
            "type": "kommentar",
            "frage": 0,
            "id": 0,
            "remove": 0,
            "username": "",
            "inhalt": ""
        ],
        [
            "type": "umfrage",
            "id": 0,
            "remove": 0,
            "inhalt": ""
        ],
        [
            "type": "antwort",
            "umfrage": 0,
            "id": 0,
            "remove": 0,
            "inhalt": "",
            "stimmen": 0
        ]
    ]
}
```



## API

| mod  | group | action | type      | property    | id    | content |
| ---- | ----- | ------ | --------- | ----------- | ----- | ------- |
| :x:  | sys   | new    | project   |             |       | :x:     |
| :x:  | sys   | set    | project   | active      | :x:   |         |
|      |       |        |           |             |       |         |
|      | sys   | set    | user      | name        |       | :x:     |
|      | sys   | set    | user      | session     |       | :x:     |
|      | sys   | set    | user      | level       |       | :x:     |
|      | sys   | set    | user      | os          |       | :x:     |
|      |       |        |           |             |       |         |
|      | data  | new    | frage     |             |       | :x:     |
| :x:  | data  | remove | frage     |             | :x:   |         |
|      | data  | new    | frage     | interessant | :x:   |         |
|      | data  | remove | frage     | interessant | :x:   |         |
| :x:  | data  | new    | frage     | forum       | :x:   |         |
| :x:  | data  | remove | frage     | forum       | :x:   |         |
| :x:  | data  | set    | frage     | current     | :x:   |         |
| :x:  | data  | set    | frage     | next        | :x:   |         |
|      |       |        |           |             |       |         |
|      | data  | new    | kommentar |             | frage | :x:     |
| :x:  | data  | remove | kommentar |             | :x:   |         |
|      |       |        |           |             |       |         |
|      |       |        |           |             |       |         |
|      |       |        |           |             |       |         |

## Datenbank

### projects

| Spalte | Beschreibung       | Typ        |
| ------ | ------------------ | ---------- |
| id     |                    | int        |
| name   |                    | varchar 30 |
| active | **0: Nein**, 1: Ja | int        |

### user

| Spalte  | Beschreibung                                               | Typ        |
| ------- | ---------------------------------------------------------- | ---------- |
| id      |                                                            | int        |
| session | PHP Session ID                                             | varchar 32 |
| name    |                                                            | varchar 30 |
| os      | Betriebsystem                                              | varchar 30 |
| level   | 0: Anfänger, **1: Nutzer**, 2: Fortgeschrittener, 3: Profi | int        |

### fragen

| Spalte  | Beschreibung                                       | Typ         |
| ------- | -------------------------------------------------- | ----------- |
| id      |                                                    | int         |
| user    | user.id                                            | int         |
| project | project.id                                         | int         |
| time    | Unix Timestamp                                     | int         |
| forum   | **0: Nein**, 1: Ja                                 | int         |
| status  | **0: Keinen**, 1: Aktuelle Frage, 2: Nächste Frage | int         |
| inhalt  |                                                    | varchar 300 |

### interesse

| Spalte | Beschreibung | Typ  |
| ------ | ------------ | ---- |
| id     |              | int  |
| user   | user.id      | int  |
| frage  | frage.id     | int  |

### kommentare

| Spalte | Beschreibung | Typ         |
| ------ | ------------ | ----------- |
| id     |              | int         |
| user   | user.id      | int         |
| frage  | frage.id     | int         |
| inhalt |              | varchar 300 |

### umfragen

| Spalte  | Beschreibung | Typ         |
| ------- | ------------ | ----------- |
| id      |              | int         |
| project | project.id   | int         |
| inhalt  |              | varchar 300 |

### antworten

| Spalte  | Beschreibung | Typ         |
| ------- | ------------ | ----------- |
| id      |              | int         |
| umfrage | umfragen.id  | int         |
| inhalt  |              | varchar 300 |

### stimmen

| Spalte  | Beschreibung | Typ  |
| ------- | ------------ | ---- |
| id      |              | int  |
| user    | user.id      | int  |
| frage   | fragen.id    | int  |
| antwort | antworten.id | int  |

### sockets

| Spalte | Beschreibung | Typ  |
| ------ | ------------ | ---- |
| id     |              | int  |
| port   |              | int  |
| user   | user.id      | int  |
