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

## Dependencies

- Webserver
- PHP8
- PHP8-mysql
- PHP8-sockets
- mariadb

## Dinge, über die man sich Gedanken machen sollte

- Name
- Maximale MySQL Verbindungen
- Vollständige, also wirklich **vollständige** Funktionalität der automatischen Schließung ungenutzer Sockets

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
            "mod": 0,
            "unset": 0
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
            "user_interessant": 0,
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
| :x:  | data  | remove | frage     |             | :x:   |         |
| :x:  | data  | new    | frage     | forum       | :x:   |         |
| :x:  | data  | remove | frage     | forum       | :x:   |         |
| :x:  | data  | set    | frage     | current     | :x:   |         |
| :x:  | data  | set    | frage     | next        | :x:   |         |
| :x:  | data  | remove | kommentar |             | :x:   |         |
|      |       |        |           |             |       |         |
|      | sys   | set    | user      | name        |       | :x:     |
|      | sys   | set    | user      | session     |       | :x:     |
|      | sys   | set    | user      | level       |       | :x:     |
|      | sys   | set    | user      | os          |       | :x:     |
|      | data  | new    | frage     |             |       | :x:     |
|      | data  | new    | frage     | interessant | :x:   |         |
|      | data  | remove | frage     | interessant | :x:   |         |
|      | data  | new    | kommentar |             | frage | :x:     |

## Datenbank

Siehe `/config/liveqa.sql`.

Importieren über: 

````bash
podman exec -i $CONTAINER /bin/sh -c 'echo "source /app/config/liveqa.sql" | mysql -u root --password='
````
