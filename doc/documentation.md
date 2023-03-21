# Backend Dokumentation

Das Backend verwaltet und transferiert die Daten für die App und das Dashboard von keeunit.

Die Grundlegende Architektur des Backends wird von Laravel vorgegeben und
entspricht im Allgemeinen dieser Reihenfolge:

    Kernel -> Router -> Controller -> Blade Template & JS -> HTTP Response
      oder
    Kernel -> Router -> Controller -> JSON Response


## Wichtige Dateien:

| Name:                     | Funktion:                                      |
|---------------------------|------------------------------------------------|
| `Http/Kernel.php`         | Verwaltet alle Web-Anfragen & definiert Routen |
| `Console/Kernel.php`      | Handhabt Timer                                 |
| `routes/*.php`            | Zuweisung von Backend Route zu Controller      |
| `AppSettings.php`         | Globale Benutzer Einstellungen                 |
| `Models/app.php`          | spezifische App-Einstellungen                  |
| `config/app.php`          | ServiceProvider                                |
| `.env`                    | Umgebungsvariablen & Passwörter                |
| `gulpfile.js`             | von Gupl verwendete Web-Ressourcen             |
| `package.json`            | von Webpack verwendete Pakete                  |
| `composer.json`           | PHP Paketmanager                               |
| `vue-component.blade.php` | Vue Meta-Layout-Datei für das Dashboard        |



# Über Router

Das Backend verfügt über mehrere Router die im Ordner `/routes` spezifiziert
sind und von dem `RouteServiceProvider` bereitgestellt werden.

| Name:       | Client:       | Anmerkung:         | Middleware:   |
|-------------|---------------|--------------------|---------------|
| backend     | Dashboard     | HTML mit JQuery    | `web`         |
| backend-api | Dashboard Vue | JSON Daten für Vue | `backend-api` |
| api         | Mobile Client | JSON Daten für Vue | `api`         |

Die meisten Routen verwenden eine Vielzahl von `Middleware`. Diese enthalten
Code der **vor** einem Controller ausgeführt wird und ggf. zu einer Weiterleitung
führen kann, ohne das jemals der eigentliche Controller-Code ausgeführt wird.


## Über Kernel

Der Kernel ist zuständig für das Bearbeiten von Anfragen eines speziellen
Typs. Das Backend verfügt über zwei eigens definierte Kernel, den `Http/Kernel`
und den `Console/Kernel`.


### HTTP Kernel

Der HTTP-Kernel definiert:
* Routen die bei jeder Anfrage durchlaufen werde
* Routen-Gruppen
* Routen die einzeln in einem Controller verwendet werden können

Die hier definierte Middleware implementiert wiederholende Anforderungen beim
Beantworten einer Web-Anfrage. Dabei hervorzuheben ist die
`BackendAccessMiddleware`, welche sicherstellt das der Zugriff auf eine Route
nur von autorisierten Anwendern vorgenommen werden kann.


### Console Kernel

Führt zeitlich gebundene Aufgaben (Timer Tasks) aus.

Die unter `command` aufgeführten Funktionen können mit dem Befehl `php artisan
[command]` ausgeführt werden.

Beispiel: `php artisan stats:cache`



# Laden von Bibliothek und Code Injection

## Web



### Webpack & Vue

Laravel Mix ist die neue Herangehensweise um Web-Ressourcen in Laravel zu
verwalten und wird derzeit nur für das Einbinden von Vue.js verwendet.

| Datei:           | Funktion:                                   |
|------------------|---------------------------------------------|
| `webpack.mix.js` | Konfiguration, ähnlich `guplfile.js`        |
| `package.json`   | Listet verwendete Bibliotheken, z.B. `vue`  |


## PHP

Code Injection wird über Laravel realisiert. Die dafür zuständigen
ServiceProvider werden über die Datei `config/app.php` im System
registriert. Danach stehen die jeweiligen Objekte zur Verfügung (ermöglicht
u.a. auch Singletons).


# Legende

| Benutzer | Person welche mit dem Browser interagiert         |
| Anwender | Dritte Person, sprich, nicht der Browser-Benutzer |


**Ab hier erfolgt eine Auflistung verschiedener Routen-Endpunkte und ihrer Funktionalität**



[//]: # "======================== API - VUE ========================"

# Über die Api-Route

Anfragen durchlaufen die `api` Middleware und werden von einem Controller mit
einem JSON-Objekt beantwortet. All Routen haben derzeit den `api/v1/` Präfix,
z.B.: `http://qa.test/api/v1/healthcheck`



# /healthcheck GET
`Api\HealthCheckController`
Prüft ob die Datenbank, Redis und Deepstream funktionieren.


# /deepstreamlogin POST
`Api\AuthController`  
Erlaubt Deepstream das Validieren von Benutzer Login-Daten.


# /login POST
Middleware: `throttl`  
`Api\AuthController`  
Ermöglicht das Anmelden eines Benutzers über Login-Daten oder JWT (JSON Web
Token). Ist in den App-Einstellungen `save_user_ip_info` bejahrt, wird das
Herkunftsland des Benutzer in der Datenbank gespeichert (wird verwendet um die
UI-Sprach automatisch auszuwählen).


# /signup POST
Middleware: `throttl`  
Ermöglicht das Erstellen eines neuen Benutzer-Kontos und sendet dem Benutzer eine
Willkommensnachricht. Dabei wird der Server-Antwort wird u.a. ein JWT beigefügt.

Ist in den App-Einstellungen `save_user_ip_info`
bejahrt, wird das Herkunftsland des Benutzer in der Datenbank gespeichert.

# /fordsignup POST
Middleware: `throttl`  
`Api\AuthController`  
Das Erstellen eines neuen Benutzer-Kontos ähnelt bei Ford dem allgemeinen
Benutzer-Erstellen weitestgehend. Abgesehen davon das die Ford App-ID bei der
Anfrage verwendet werden muss existieren unterschiedliche Vorgaben bei der
Daten-Validierung und bei der Benutzer-Erzeugung.

## Benutzer Erstellung
Ein inaktiver Dummy-Benutzer-Eintrag muss mit der korrekten PIN & Nachname und
bereits in der Datenbank hinterlegt sein. Dabei dürfen `username` und `email`
nicht bereits vergeben sein.

## Spezifische Anmelde-Vorgaben
| Feld:      | Einschränkung:                 | Verpflichtend: |
|------------|--------------------------------|----------------|
| `lastname` | min: 3                         | ja             |
| `pin`      | min: 3                         | ja             |
| `email`    | min: 3, max: 255               | ja             |
| `password` | min: 6, nur: [a-z] [A-Z] [0-9] | ja             |
| `username` | min: 3, max: 255               | nein           |


# /tmpaccount POST
Middleware: `throttl`  
`Api\AuthController`  
Erlaubt es einem Anwender einen vorübergehendes Benutzer-Konto zu
erstellen. Gleicht bis auf die zufällige Vergabe eines Passworts und E-Mail der
normalen Benutzer-Konten-Erstellung.

Temporäre Konten sind derzeit aktiv für:
* `WEBDEV_QUIZ`
* `LINGOMINT`

**Temporäre Benutzer-Konten werden nicht gelöscht.**

## Besonderheit - LINGOMINT
`LINGOMINT` erfordert zusätzlich, dass der verwendete Benutzername bereits in
der Datenbank hinterlegt wurden.

# /reset-password POST
Middleware: `throttl`  
`Api\AuthController`  
Ermöglicht das Zurücksetzen eines Benutzer-Passworts und benachrichtigt diesen
per E-Mail über das neue, zufällig generierte Passwort.


# /public/pages/{page_id} GET
Sendet den Titel und Inhalt einer Seite zurück, wenn diese vorab `public` und
`visible` markiert wurden.


# /curator/tests GET
Middleware: `auth.active throttl `  
`Api\CuratorController`  
Erlaubt es Curator (Nexus) eine Liste von Tests zu erhalten die nicht bereits
abgelaufen sind.


# /accept-tos POST
Middleware: `auth.active throttl `  
`Api\AuthController`  
Speichert, dass der Benutzer die Benutzerbestimmungen akzeptiert hat.


# /tos GET
Middleware: `auth.active throttl`  
`Api\AuthController`  
Gibt für die entsprechende App die Benutzerbestimmungs-Seite zurück.


# /convertaccount POST
Middleware: `auth.active throttl `  
`Api\AuthController`  
Erlaubt es einen temporären Account in einen regulären umzuwandeln.


# /setgcmid POST
Middleware: `auth.active throttl `  
`Api\AuthController`  
Speichert die GCM (Google Cloud Messaging) Identifikations-Nummer, entsprechend
dem vom Benutzer verwendeten Betriebssystem.


# /setgcmauth POST
Middleware: `auth.active throttl `  
`Api\AuthController`  
Speichert die für GCM (Google Cloud Messaging) notwendigen Felder (`gcm_p256dh`
& `gcm_auth`).


# /user/tags GET
Middleware: `auth.active throttl `  
`Api\UsersController`  
Sendet eine Liste aller Tag-IDs eines Benutzer zurück.


# /user/categories GET
Middleware: `auth.active throttl `  
`Api\UsersController`  
Sendet eine Liste mit Wertepaaren welche die Kategorie-ID und Kategorie-Namen
enthalten zurück.


# /user/language POST
Middleware: `auth.active throttl `  
`Api\UsersController`  
Ändert die Benutzersprache auf den übermittelten Wert.


n# /users/search GET
Middleware: `auth.active throttl `  
`Api\UsersController`  
Sucht Anwender nach Anwender-Name oder E-Mail-Adresse (nur wenn
`hide_emails_frontend` dies erlaubt) und sendet eine Liste von Anwendern zurück
die für ein Spiel geeignet wären. Dabei wird das Exklusiv-Attribut der
Benutzer-Tags und die maximale Anzahl von Spielen die Spieler gegeneinander
führen dürfen berücksichtigt.

Maximale Anzahl von Spielen mit dem selben Spieler: 5

# /users/{user_id} GET
Middleware: `auth.active throttl `  
`Api\UsersController`  

Schickt eine kleine Auswahl von Benutzer-Daten zurück. Dabei wird die Benutzer
E-Mail nicht übermittelt, wenn die App-Einstellungen der Wert `hide_emails` oder
`hide_emails_frontend` gesetzt wurde.

Hat der Benutzer bisher kein Avatar hinterlegt, wird automatisch ein zufälliger generiert.

## Besonderheit - BAYER, SCHWAEBISCH_HALL, GENOAKADEMIE
Oben genannte Firmen haben einen Standard-Avatar hinterlegt und verwenden daher
nicht den zufällig generierten Avatar.


# profile/avatar POST
Middleware: `auth.active throttl `  
`Api\ProfileController`  
Speichert ein vom Benutzer hochgeladenes Avatar-Bild (jpg) in dem Ordner
`storage/avatars/` in drei verschiedenen Größen und sendet das Bild mit der
mittleren Größe zurück.


# /password POST
Middleware: `auth.active throttl `  
`Api\ProfileController`  
Speichert ein neues übermitteltes Passwort.

min. Länge: 6  
nur: [a-z] [A-Z] [0-9]

**Diese Methode kann nur von der Ford-App verwendet werden.**


# /password/set POST
Middleware: `auth.active throttl `  
`Api\ProfileController`  
Erlaubt App Anwender die in `hasInsecurePasswordChange` aufgelistet sind, ihr
Passwort zu ändern. Mindestlänge des neuen Passworts sind 8 Zeichen. Darüber
hinaus darf es sich nicht um einen temporäres Konto handeln.


# competitions GET
Middleware: `auth.active throttl `  
`Api\CompetitionsController`  
Gibt eine Liste aller für den Benutzer zur Verfügung stehende Gewinnspiele
zurück, inklusive der Gewinnspiele die in den letzten 3 Tagen beendet wurden.


# /games POST
Middleware: `auth.active throttl `  
`Api\GamesController`  
Startet ein neues Spiel zwischen dem Benutzer und einem Gegner. Dabei liegt es
dem Benutzer frei sich einen Gegner auszusuchen oder sich zufällig einen
zuweisen zu lassen (beides unter Berücksichtigung der Gegnerzuweisungsregeln).

Maximale Anzahl von Spielen mit dem selben Spieler: 5

# /games/{game_id} GET
Middleware: `auth.active throttl `  
`Api\GamesController`  
Gibt Informationen über ein Spiel zurück (handhabt die außerplanmäßige Beendigung).


# /games/active GET
Middleware: `auth.active throttl `  
`Api\GamesController`  
Gibt eine sortierte List aller offenen Spiele des Benutzers zurück. Spiele bei
denen der Benutzer am Zug ist stehen an vorderster Listenposition.


# /games/recent GET
Middleware: `auth.active throttl `  
`Api\GamesController`  
Gibt eine List er letzten fünf Spiele des Benutzers zurück.


# /games/{game_id}/question GET
Middleware: `auth.active throttl `  
`Api\GamesController`  
Gibt die nächste Spielfrage für den Benutzer zurück (mitsamt der Info ob der Spieler
noch den Joker einsetzen kann). Dabei wird sichergestellt, dass der Benutzer
eine Kategorie ausgewählt hat (ansonsten wird er dazu aufgefordert) und das
Spiel nicht außerplanmäßig beendet wurde. Sollte eine Frage bereits beantwortet
worden sein, wird ein Fehler zurück gegeben.

# /games/{game_id}/categories GET
Middleware: `auth.active throttl `  
`Api\GamesController`  
Gibt die für den Benutzer zur Verfügung stehenden Spiele-Kategorien zurück unter
Berücksichtigung der App-Einstellung `use_subcategory_system` (Unterteilung in
Subkategorie & Oberkategorie).

Sollte der Spieler nicht an der Reihe sein, das Spiel bereits beendet oder die
Kategorie bereits gesetzt worden sein, wird ein Fehler zurück gegeben.

# /games/{game_id}/intro GET
Middleware: `auth.active throttl `  
`Api\GamesController`  
Gibt die Intro-Informationen zurück. Sollte der Benutzer bisher
keine Kategorie ausgewählt haben, wird er dazu aufgefordert. 

Sollte der Spieler nicht an der Reihe sein oder das Spiel bereits beendet worden
sein, wird ein Fehler zurück gegeben.

# /games/{game_id}/categories POST
Middleware: `auth.active throttl `  
`Api\GamesController`  
Setzt die nächste Kategorie für das laufende Spiel unter Berücksichtigung der
App-Einstellung `use_subcategory_system` (Unterteilung in Subkategorie &
Oberkategorie).


# /games/{game_id}/question POST
Middleware: `auth.active throttl `  
`Api\GamesController`  
Speicher die Antwort des Benutzer auf eine Spielefrage. Dabei gilt eine Frage
als unbeantwortet, wenn diese nicht rechtzeitig beantwortet wurde.

Es wird geprüft ob es sich um die letzte Spielfrage gehandelt hat und ob die
laufende Spielrunde beendet werden muss. Sollte noch der alte Spielablauf
verwendet werden (Spieler spielen abwechselnd) wird der nächste Spieler
evtl. über den nächsten Schritt informiert (ist Abhängig vom derzeitigen Spiele-Status).

Der Gewinner des Spiels wird über die Anzahl der gewonnenen Runden ermittelt,
siehe `GameEngine@determineWinnerOfGameByRounds`.

## Besonderheit - WUESTENROT, WOHNDARLEHEN
Der Gewinner eines Spieles wird über die Anzahl der korrekten Spieleantworten
ermittelt, siehe `GameEngine@determineWinnerOfGameByQuestions`.


# /games/{game_id}/joker POST
Middleware: `auth.active throttl `  
`Api\GamesController`  
Erlaubt dem Benutzer den Joker in einem Spiel einzusetzen. Die Antwort des
Backends enthält, zufällig ausgewählt, die Hälfte der falschen Antworten.


# /pages GET
Middleware: `auth.active throttl `  
`Api\PagesController`  
Liste aller für den Benutzer sichtbarer Seiten bezogen auf eine App.


# /pages/{page_id} GET
Middleware: `auth.active throttl `  
`Api\PagesController`  
Gibt eine einzelne Seite zurück.


# /news GET
Middleware: `auth.active throttl `  
`Api\NewsController`  
Liste aller News für einen Benutzer einer App unter Berücksichtigung der ihm
zugeteilten Tags und dem ggf. vorhandenen Ablaufdatum der News.


# /learning-materials GET
Middleware: `auth.active throttl `  
`Api\LearningMaterialsController`  
Gibt eine Liste von Lernmaterial-Ordner zurück, unter Berücksichtigung der dem
Benutzer zugewiesenen Tags. Die Antwort enthält keine Tag-Informationen mehr.


# /stats/players GET
Middleware: `auth.active throttl `  
`Api\StatsController`  
Liste der Spieler-Statistik einer App - bei Umgehung des Caches.

Wenn in den App-Einstellungen `hide_emails` oder `hide_emails_frontend` gesetzt
sind, enthält die Antwort keine Anwender E-Mail-Adressen.


# /stats/groups GET
Middleware: `auth.active throttl `  
`Api\StatsController`  
Liste der Gruppen-Statistik einer App - bei Umgehung des Caches.


# /stats/mine GET
Middleware: `auth.active throttl `  
`Api\StatsController`  
Spieler-Statistik des Benutzers - bei Umgehung des Caches.

Wenn in den App-Einstellungen `hide_emails` oder `hide_emails_frontend` gesetzt
sind, enthält die Antwort keine Anwender E-Mail-Adresse.


# /stats/competitions GET
Middleware: `auth.active throttl `  
`Api\StatsController`  
Liste aller laufenden Gewinnspiele (geordnet nach Listen-Position im Gewinnspiel).


# /stats/position GET
Middleware: `auth.active throttl `  
`Api\StatsController`  
Gibt die derzeitige Position des Benutzers in der Quiz-App zurück.

Wenn in den App-Einstellungen `hide_emails` oder `hide_emails_frontend` gesetzt
sind, enthält die Antwort keine Anwender E-Mail-Adresse.


# /stats/position/{user_id} GET
Middleware: `auth.active throttl `  
`Api\StatsController`  
Gibt die derzeitige Position eines Quiz-App Anwenders zurück.

Wenn in den App-Einstellungen `hide_emails` oder `hide_emails_frontend` gesetzt
sind, enthält die Antwort keine Anwender E-Mail-Adresse.


# /groups GET
Middleware: `auth.active throttl `  
`Api\GroupsController`  
Liste aller Quiz-Teams denen der Benutzer angehört.


# /groups/stats GET
Middleware: `auth.active throttl `  
`Api\GroupsController`  
Liste aller Quiz-Teams denen der Benutzer angehört inklusive relevanter
Statistik-Daten (die Statistik-Abfrage umgeht den Cache).


# /groups/{group_id} GET
Middleware: `auth.active throttl `  
`Api\GroupsController`  
Liste aller Quiz-Teams denen ein Anwender angehört.


# /groups POST
Middleware: `auth.active throttl `  
`Api\GroupsController`  
Erstellt eine neues Quiz-Team innerhalb einer App. Der vorgesehene Quiz-Team-Name darf
dabei noch nicht vergeben sein.


# /groups/{group_id}/members POST
Middleware: `auth.active throttl `  
`Api\GroupsController`  
Fügt einen Anwender einem Quiz-Team hinzu und gibt für den entsprechenden Anwender
die Spieler-Statistik zurück. Diese Funktion ist dem Quiz-Team-Besitzer vorbehalten.


# /groups/{group_id}/members/remove POST
Middleware: `auth.active throttl `  
`Api\GroupsController`  
Löscht einen Anwender aus einem Quiz-Team. Diese Funktion ist dem Quiz-Team-Besitzer vorbehalten.


# /questions/suggest POST
Middleware: `auth.active throttl `  
`Api\QuestionsController`  
Speichert einen Frage-Vorschlag für eine App nach der Datenvalidierung und
benachrichtigt den Frage-Verwalter darüber, wenn in der App die Benachrichtigungs-Option
aktiviert ist.


# /questions/suggestionSettings GET
Middleware: `auth.active throttl `  
`Api\QuestionsController`  
Liste aller Frage-Kategorien, mitsamt der Anzahl der minimal notwendigen
Frage-Antworten, des Benutzers.


# /training/categories GET
Middleware: `auth.active throttl `  
`Api\TrainingController`  
Liste aller Kategorien welche der Benutzer im Trainings-Modus spielen kann.


# /training/categories/{category_id}/questions GET
Middleware: `auth.active throttl `  
`Api\TrainingController`  n
Zufällig sortierte Liste aller aktiver Fragen die einer Kategorie einer App-Angehören.


# /training/saveAnswer/{category_id} GOST
Middleware: `auth.active throttl `  
`Api\TrainingController`  
Speichert eine Frage-Antwort einer Trainings-Frage und gibt die ID der neuen
Antwort zurück.


# /training/stats GET
Middleware: `auth.active throttl `  
`Api\TrainingController`  
Trainings-Statistik des Benutzers.


# /learning GET
Middleware: `auth.active throttl `  
`Api\LearningController`  
Liste aller Kategorien und Kategorie-Gruppen (ohne Trainings-Fragen) und der
dazugehörigen Antworten. Dabei werden die App-Einstellung
`use_subcategory_system` (Unterteilung in Subkategorie & Oberkategorie) berücksichtigt.


# /learning/save POST
Middleware: `auth.active throttl `  
`Api\LearningController`  
Speichert die Änderungen der Lernkarten des Clients im Backend.


# /learning/categories GET
Middleware: `auth.active throttl `  
`Api\LearningController`  
Liste aller Kategorien die der Benutzer spielen kann (ohne
Trainings-Daten). Dabei werden die App-Einstellung `use_subcategory_system`
(Unterteilung in Subkategorie & Oberkategorie) berücksichtigt.


# /learning/statsData GET
Middleware: `auth.active throttl `  
`Api\LearningController`  
Liste aller Lernkarten und der zugehörigen Fragen des Benutzers.


# /learning/category/{category_id}/question GET
Middleware: `auth.active throttl `  
`Api\LearningController`  
Zufällig sortierte Liste aller Fragen einer Kategorie einer App, bezogen auf den Benutzer.


# /learning/category/{category_id}/question/free GET
Middleware: `auth.active throttl `  
`Api\LearningController`  
Zufällig sortierte Liste aller Fragen einer Kategorie einer App (verfügbar für
alle Benutzer einer App).


# /learning/saveAnswer/{question_id} POST
Middleware: `auth.active throttl `  
`Api\LearningController`  
Speichert die Antwort auf eine Frage einer Lernkarte und aktualisiert die Lernkarten.


# /learning/checkAnswer/{question_id} POST
Middleware: `auth.active throttl `  
`Api\LearningController`  
Prüft die Korrektheit einer Antwort auf eine Frage (single & multiple choice).


# /indexcards GET
Middleware: `auth.active throttl `  
`Api\IndexCardsController`  
Gibt eine Liste aller Index-Karten einer App zurück.


# /indexcards/categories GET
Middleware: `auth.active throttl `  
`Api\IndexCardsController`  
Liste aller Kategorien welche von aktiven Index-Karten verwendet werden.


# /indexcards/update POST
Middleware: `auth.active throttl `  
`Api\IndexCardsController`  
Speichert die Änderungen aller übermittelten Index-Karten.

## Besonderheit - FORD
Speichert zusätzlich auch noch `userdata`, `box` und `box_entered_at`.


# /indexcards/savedata GET
Middleware: `auth.active throttl `  
`Api\IndexCardsController`  
Speichert alle neue Karteikarten eines Anwenders einer App.


# /tests GET
Middleware: `auth.active throttl `  
`Api\TestsController`  
Liste aller Tests die derzeit für den Benutzer einer App verfügbar sind. Dabei
werden Tests die der Benutzer bereits bestanden hat und nicht wiederholt werden
können ausgeblendet.


# /tests/results GET
Middleware: `auth.active throttl `  
`Api\TestsController`  
Sortierte Liste aller vom Benutzer abgeschlossenen Tests.


# /tests/{test_id} GET
Middleware: `auth.active throttl `  
`Api\TestsController`  
Gibt zurück ob ein Test vom Benutzer ausgeführt werden kann (prüft z.B. ob der
Test nicht bereits abgelaufen ist) und die Anzahl der Fragen im Test.


# /tests/{test_id}/currentquestion GET
Middleware: `auth.active throttl `  
`Api\TestsController`  
Gibt die derzeitige Test-Frage des Benutzer zurück. Dabei werden die
Antwort-Möglichkeiten. Ist der Test bereits
abgeschlossen erfolgt eine Antwort mit letzten Test-Antwort ID.


# /tests/{test_id}/answer POST
Middleware: `auth.active throttl `  
`Api\TestsController`  
Speichert die Test-Antwort des Benutzers für einen Test.


# /tests/results/{test_id} GET
Middleware: `auth.active throttl `  
`Api\TestsController`  
Test-Resultat für einen Test. Dabei wird die Winrate in Prozent angegeben.



[//]: # "======================== DASHBOARD ========================"


Admin Authentifizierung
=======================
Die meisten Routen benötigen eine Admin-Authentifizierung. Diese erfolgt durch
die `admin` Middleware welche im `Http/Kernel.php` definiert ist.
Dabei wird beim Aufruf einer Route geprüft ob der Benutzer bereits angemeldet ist und ob
es sich um einen Administrator handelt. Je nach Zugriffsart wird dabei ein anderer
`guard` verwendet (definiert in `config/auth.php`).
* Ist der Benutzer nicht authentifiziert wird eine `AuthenticationException`
  geworfen und durch diese auf `/login` weitergeleitet.
* Ist der Benutzer authentifiziert, aber kein Admin, wird dieser mit einer
  Nachricht darauf hingewiese und auf `/login` umgeleitet.
* Ist der Benutzer authentifiziert, kein Admin und erwartet eine JSON-Antwort,
  erfolgt diese mit dem Fehler 401 'Unauthenticated'.


Zusätzliche Anwender Berechtigungen
===================================
Neben der Admin und Super-Admin Berechtigung kann ein Anwender über weitere
Berechtigungen verfügen die ihm den Zugang zu einer Aktion/Subseite gewähren
oder verweigern (DB Tabelle: `user_permissions`).


Route: /login GET
=================
Admin-Authentifizierung: nein  
Middleware: web, throttle
`AuthController`

Zeigt die Login-Seite an, welche es dem Benutzer erlaubt sich mit seiner
E-Mail-Adresse und seinem Passwort im Backend anzumelden. Da ein Benutzer
mehrere Apps verfügen kann, verfolgt die Anmeldeseite neben der Authentifikation
das Ziel, die zu verwendende App-ID zu ermitteln, um den Benutzer das gewünschte
App-Dashboard anzuzeigen.

Eine Checkbox erlaubt es dem Benutzer, wenn gewünscht, angemeldet zu bleiben.
Ist der Benutzer bereits Angemeldet, wird ihm per Button die Möglichkeit gegeben
sich abzumelden oder das Dashboard zu öffnen.

Nach Eingabe der E-Mail-Adresse prüft das Backend automatisch ob diese dort vorhanden
ist und warnt ggf. den Benutzer per Pop-Up. Des weiteren wird nach Eingabe der
E-Mail-Adresse automatisch geprüft ob der Benutzer Assoziationen mit mehreren
Apps verfügt, wenn ja, erscheint ein Dropdown mit allen Apps. Dabei wird die
zuletzt verwendete App vorausgewählt, bzw. wenn diese nicht bekannt ist, die
erste App in der Liste.


/login POST
===========
Admin-Authentifizierung: nein  
Middleware: web, throttle
`AuthController`

Anmeldeanfrage über Login-Formular. Ist diese erfolgreich wird dies im Log
vermerkt, eine Erfolgsmeldung dem Benutzer angezeigt, gefolgt von einer
Weiterleitung zu `/` (dem Dashboard). Stimmen die Anmeldedaten nicht überein,
wird der Benutzer auf den fehlerhaften Anmeldeversuch hingewiesen und auf `/login** umgeleitet.

**Das Benutzer-Passwort wird vor der Weiterleitung aus dem Request-Objekt entfernt.**


/login/apps GET
===============
Admin-Authentifizierung: nein  
Middleware: web, throttle
`AuthController`

Anfrage gibt per JSON eine Liste aller dem Benutzer zugewiesenen Apps und deren
App-ID's zurück. Die Anfrage muss dabei über einen Benutzer mit Administrator-Rechten erfolgen.
Aufruf erfolgt über JQuery mittels `searchApps()` in der Datei `assets/js/login.js`.


/logout GET
===========
Admin-Authentifizierung: nein  
Middleware: web, throttle
`AuthController`

Meldet den Benutzer ab und leitet ihn auf `/login` um.


/users/account-activation GET
=============================
Admin-Authentifizierung: notwendig  
Middleware: web, throttle
Aktiviert einen Benutzer-Account.  
`AuthController`

Nach dem Aktivieren erfolgt eine Weiterleitung auf die aktivierte App. Im Falle
das die gesendete Signatur falsch ist, wird mit dem Error `401` geantwortet.


Route: / GET
============
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\DashboardController`

Das Dashboard stellte die Hauptseite dar. 
TODO


Route: /test GET
================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\DashboardController`

Router wird nicht mehr verwendet - wurde zu Testzwecken erstellt.


Route: /setapp GET
==================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, superadmin  
`Backend\SettingsController`

Erlaubt es einem Superadmin das Dashboard auf eine andere App-Ansicht
umzustellen. Der Benutzer verbleibt dabei auf
der derzeitigen Seite.


Route: /setlang GET
===================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\AuthController`
Stellt die Client-Sprache auf neuen Wert um. Der Benutzer verbleibt dabei auf
der derzeitigen Seite.


Route: /settings GET
===================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, superadmin  
`Backend\SettingsController`

App-Einstellungen - nur sichtbar für den Superadmin.

Diese ist unterteilt in:
- Allgemeine App-Einstellungen
- Backend-Optionen, erlaubt es Module zu aktivieren/deaktivieren
- Sonstiges (Zugangslogs)

Einstellungs-Felder verfügen über Standardwerte. Nach erfolgreichem ändern der
Einstellungen oder bei Fehlkonfiguration, wird der Benutzer benachrichtigt.


Route: /settings/edit POST
=========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, superadmin  
`Backend\SettingsController`

Aktualisiert App-Einstellungen nach Datenvalidierung. Dabei wird darauf geachtet
das z.B. Minimalwerte nicht unterschritten werden. Wird ein Fehler festgestellt
wird eine Antwort mit Fehlermeldung und dem Status `500` zurückgesandt. Im
Erfolgsfall werden die Änderungen gespeichert und die Anfrage mit Status `OK` beantwortet.


Route: /questions GET
=====================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\QuestionsController`

Erlaubt das Anlegen und Bearbeiten neuer Fragen entsprechend der gerade aktiven
App. Eine Suchfunktion, ein Kategorie-Filter und ein Fragentyp-Filter erlauben
dem Benutzer schnell einen Überblick zu erlangen.

// TODO korrekte Berechtigung herausfinden
Diese Seite ist nur aktiv wenn das entsprechende Backend-Modul aktiviert wurde.

Für das Erstellen oder Editieren von Fragen werden Overlay-Fenster
verwendet. Bevor eine Frage zum Spielen verwendet werden kann, muss diese zuvor
als *sichtbar* markiert worden sein.


Erstellen einer neuen Frage
---------------------------
Schritt 1:  
Auswahl des Fragetyps und des Frage-Satzes, welcher auf 100 Zeichen beschränkt ist.

Schritt 2:  
Spezifikation der korrekten und falschen Antworten. Weitere Tabs ermöglichen das
hinzufügen von Medien-Dateien, spezifizieren einer Kategorie oder das Löschen der Frage.

* Die Anzahl Antwortmöglichkeiten einer Frage ist auf 6 beschränkt
* Mediendateien dürfen 10 MB nicht überschreiten (nur Bilder, mp3 oder YouTube-Videos)

Fragetyp: Single Choise, Multiple Choise, Ja/Nein


Route: /questions POST
======================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\QuestionsController`

Erstellt eine neue Frage mit leeren Datenfeldern im Backend.


Route: /questions/{id} GET
==========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\QuestionsController`

Stellt eine einzelne Frage im Detail-Overlay dar (gleich Schritt 2 beim
Erstellen einer neuen Frage). 


Route: /questions/{id} POST
===========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\QuestionsController`

Speichert die übermittelte Frage im Backend. Funktion wird auch zum
Aktualisieren einer Frage verwendet. 


Route: /questions/{id}/attachments POST
=======================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\QuestionsController`

Fügt einer Frage eine Mediendatei hinzu und prüft dabei, dass es sich um einen
erlaubten Medientyp handelt. Die Datei wird im Unterordner
`storage/question_attachments/` abgelegt und der Dateipfad in der Datenbank
gespeichert.


Route: /questions/{id}/delete GET
=================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\QuestionsController`

Löscht, nach Rückfrage mit dem Benutzer, die ausgewählte Frage. Der Benutzer wird
auf Erfolg oder Misserfolg der Löschaktion hingewiesen.


Route: /questions/{questionId}/attachments/{attachmentId}/delete GET
====================================================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\QuestionsController`

Entfernt nach Rückfrage mit dem Benutzer die Ausgewählte Mediendatei und speichert die 
nderung. Gelöscht wird eine Mediendatei allerdings nur, wenn diese alleinig in
der Frage verwendet wurde.


Route: /suggested-questions GET
===============================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\SuggestedQuestionsController`

Übersicht über die von Anwendern eingereichte Quiz-Fragen Vorschläge Fragenmodul
muss aktiviert sein und der Benutzer muss über die Berechtigung `questions-suggested` verfügen.


Route: /suggested-questions/{id} GET
====================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\SuggestedQuestionsController`

Detailansicht eines Frage-Vorschlages, welcher die eigentliche Frage und
Antworten, sowie Knöpfe zum Akzeptieren oder Löschen der Frage.


Route: /suggested-questions/{id}/accept GET
===========================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\SuggestedQuestionsController`

Übernimmt den Frage-Vorschlag und leitet den Benutzer auf die `/questions` Unterseite weiter.


Route: /suggested-questions/{id}/delete GET
===========================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\SuggestedQuestionsController`

Löscht den Frage-Vorschlag und weist den Benutzer, bei Erfolg, darauf hin.


Route: /users GET
=================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\UsersController`

Zentrale Benutzerverwaltung die es neben dem editieren bestehender Anwender, der
Vergabe von Berechtigungen es auch erlaubt neue Anwender zu werben. Die Ansicht
kann durchsucht/gefilter werden, nach: Kategorien, Tags, Anwendername, E-Mail

Des weiteren ist es hier möglich Anwender zum Admin zu erklären oder zu aktivieren/deaktivieren.

**Das Modul Benutzerverwaltung muss aktiviert sein.**


Route: /users/{id} GET
======================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\UsersController`

Zeigt die Anwender-Detailansicht an, die zugleich auch zum Editieren verwendet werden kann.

**Tab - Einstellungen:**  
Ermöglicht es dem Anwender Tags zuzuweisen und seine E-Mail-Adresse zu ändern.

**Tab - Verwaltung**  
Erlaubt es den Anwender erneut Einzuladen, zu löschen und komplett aus dem
System zu entfernen. Wird ein Anwender erneut eingeladen, setzt das Backend das
Passwort des Anwenders automatisch zurück und schickt ihm ein neues, zufällig generiertes, zu.


Route: /users/{id} POST
=======================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\UsersController`

Speichert übermittelte User-Daten im Backend. Dabei wird geprüft das die
angegebene E-Mail-Adresse nicht bereits in der App verwendet wird. Sollte ein
Fehler auftreten wird der Benutzer darauf hingewiesen.


Route: /users/{id}/permissions GET
==================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\UsersController`

Nutzern mit Admin-Rechten können die in `UserPermission` definierten Rechte
zugewiesen werden. Diese erlauben dem Admin z.B. mit dem Recht `groups` das
Verwalten von Nutzer-Quiz-Teams. Der Berechtigungs-Tab ist für Benutzer ohne
Admin-Rechte nicht sichtbar.


Route: /users/{id}/permissions POST
===================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\UsersController`

TODO
Route /users/{id}/permissions POST führt ins Leere,
Backend\UsersController@permissionsUpdate scheint nicht zu existieren.


Route: /users/{id}/softdelete GET
=================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\UsersController`

Vermerkt den Anwender in der Datenbank als gelöscht, ohne ihn tatsächlich zu
entfernen. Dem Benutzer wird daraufhin eine Erfolgsmeldung angezeigt.


Route: /users/{id}/harddelete GET
=================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\UsersController`

Übersichtsseite welche dem Benutzer vor dem unwiderruflichen Löschen eines
Anwenders angezeigt wird und ihm ein letztes Mal die Möglichkeit einräumt vom Löschen abzusehen.


Route: /users/{id}/harddelete POST
==================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\UsersController`

Löscht den Anwender aus der Datenbank und alle mit ihm verbundenen Informationen.


Route: /users/{id}/reinvite POST
================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\UsersController`

Sendet erneut eine Einladung per E-Mail an den spezifizierten Anwender. Dabei wird sein
altes Passwort zurückgesetzt und ein zufälliges Neues generiert. Der Vorgang
wird im Log vermerkt und der Benutzer über die Änderung informiert.


Route: /indexcards GET
======================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\IndexCardsController`

Karteikarten-Übersicht.

**Das Modul Karteikartenmodul muss aktiviert sein.**


Route: /indexcards POST
=======================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\IndexCardsController`

Erstellt eine neue Karteikarte mit Standardwerten und leitet den Benutzer zur
Editieransicht weiter.


Route: /indexcards/{id} GET
===========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\IndexCardsController`

Editieransicht einer Karteikarte. Erlaubt das aktualisieren der Textfelder, das
Speichern, löschen und falls gewünscht, das Hochladen eines bis zu 10 MB großen
Bildes. Das Bild kann vom Benutzer in der Editieransicht zugeschnitten werden.


Route: /indexcards/{id} POST
============================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\IndexCardsController`

Aktualisiert einen Karteikarten-Eintrag in der Datenbank.


Route: /indexcards/{id}/image POST
==================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\IndexCardsController`

Speichert Übertragenes Bild im Ordner `storage/indexcard_attachments/` ab. Dabei
wird es komprimiert und ggf. verkleinert (900 Pixel).


Route: /indexcards/{id}/deleteimage GET
=======================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\IndexCardsController`

Löscht Bild in der Datenbank und dem `storage` Ordner. Leitet den Benutzer
wieder auf die Editieransicht um.


Route: /indexcards/{id}/delete GET
==================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\IndexCardsController`

Löscht nach Rückfrage mit dem Benutzer die Karteikarte und benachrichtigt den
Benutzer im Erfolgsfall. Daraufhin folgt eine Weiterleitung auf die
Karteikarten-Übersicht.


Route: /users/invite GET
========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\InvitationController`

Unterseite der Benutzerverwaltung welche es ermöglicht neue Anwender
einzuladen. Dabei erfolgt das Anlegen neuer Einladungen nacheinander (sie werden
in einer Liste gesammelt), das Versenden erfolgt mit einem einzigen
Knopfdruck. Wurde die App mehrsprachig konfiguriert, enthält der
Einladungs-Dialog ein Sprach-Dropdown.


Route: /users/invite POST
=========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\InvitationController`

Sendet eine Einladung an alle in der Einladungsliste stehenden Personen.
Vorab erfolgt die Erstellung eines neuen Anwenders für jede Person in der
Liste. Dabei wird sichergestellt, dass keine E-Mail-Adresse bereits von einem
anderen Anwender verwendet wird, der Name mindestens 4 Buchstaben lang ist und die
Anwender-Sprache im Backend existiert. Das dabei generierte Passwort ist zufällig.

Der Benutzer wird über den Status aller Einladungen informiert und im Falle
eines Fehlers, auch  über die Fehlerursache. Danach erfolgt eine Umleitung auf
die Einladungs-Übersicht.


Route: /categories GET
======================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\CategoriesController`

Verwaltung der Kategorien innerhalb der App. Die Ansicht kann gefiltert
werden. Klickt der Benutzer auf eine Kategorie, öffnet sich die Editieransicht.

TODO backendaccess prüft nicht existente Berechtigung "hide_categories"


Route: /categories POST
=======================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\CategoriesController`

Erstellt eine neuen Kategorie-Eintrag in der Datenbank, welcher Standardwerte
enthält und leitet den Benutzer auf die Editieransicht weiter.


Route: /categories/{id} GET
===========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\CategoriesController`

Stellt die Editieransicht einer Kategorie dar. Bestehend aus dem Einstellungs und
Layout-Bereich, dem Toggle-Button `aktiv`, sowie den Knöpfen zum Speichern und
löschen einer Kategorie.

Nur der Super-Admin hat das Recht eine Kategorie zu löschen.

**Tab - Einstellung:**  
- Optionale Oberkategorie-Dropdown. Wenn die App `use_subcategory_system** verwendet kann
  eine Oberkategorie der Kategorie zugewiesen werden.
- Tags welche der Kategorie angehören.
- Optional - Anzahl der Punkte die ein Anwender für das korrekte Beantworten einer Frage erh
- Sichtbarkeit der Kategorie: Duellmodus, Trainingsmodus

**Tab - Layout:**  
- Farbe der Kategorie
- Großes Kategorie-Bild (maximal 10 MB)
- Kleines Kategorie-Bild (maximal 10 MB)


Route: /categories/{id} POST
============================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\CategoriesController`

Speichert die Änderungen einer Kategorie und antwortet im Erfolgsfall mit Status `OK`.


Route: /categories/{id}/image POST
==================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\CategoriesController`

Speichert empfangendes Bild im Ordner `storage/categories_attachments/`. Dabei
wird es komprimiert und ggf. verkleinert (900 Pixel).


Route: /categories/{id}/delete GET
==================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\CategoriesController`

Bestätigungsseite zum Löschen einer Kategorie.

Der Benutzer wird in Folgenden Fällen gewarnt:
- Derzeit ein laufendes Spiel in dieser Kategorie stattfindet (verhindert das Löschen)
- Fragen kategorielos würden
- Vorschläge für neue Fragen, die dieser Kategorie angehören, gelöscht würden
- Karteikarten kategorielos würden
- Bestehende Wettbewerbe gelöscht würden
- Spiele gelöscht würden

Solle während des Versuch des Löschens ein Spiel unter dieser Kategorie
stattfinden, wird der Benutzer drauf hingewiesen und der Löschen-Knopf
deaktiviert.


Route: /categories/{id}/delete POST
===================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\CategoriesController`

Erlaubt es dem Super-Admin eine Kategorie zu löschen.


Route: /categories/{id}/deleteimage GET
=======================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\CategoriesController`

Löscht das entsprechende Bild und leitet den Benutzer auf die Editieransicht um.


Route: /categories/{id}/icon POST
=================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\CategoriesController`

Speichert empfangendes Bild im Ordner `storage/categories_attachments/`. Dabei
wird es komprimiert und ggf. verkleinert (250 Pixel).


Route: /categories/{id}/deleteicon GET
======================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\CategoriesController`

Löscht das entsprechende Icon und leitet den Benutzer auf die Editieransicht um.


Route: /categorygroups GET
==========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\CategorygroupsController`

Verwaltung der Oberkategorien mittels Liste. Wenn vorhanden werden Tags der
Oberkategorie in den Listeneinträgen angezeigt.

**Nur sichtbar wenn `use_subcategory_system` in den App-Einstellungen aktiviert wurde.**


Route: /categorygroups POST
===========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\CategorygroupsController`

Erstellt eine neuen Oberkategorie-Eintrag in der Datenbank, welcher Standardwerte
enthält und leitet den Benutzer auf die Editieransicht weiter. Sollte der Name
keinen Wert enthalten, wird stattdessen der Benutzer darauf hingewiesen und auf
die Übersichtsseite umgeleitet.


Route: /categorygroups/{id} GET
===============================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\CategorygroupsController`

Stellte die Editieransicht einer Oberkategorie dar, welche aus dem Namen und den
zugeteilten Tags besteht.


Route: /categorygroups/{id} POST
================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\CategorygroupsController`

Speichert einen Oberkategorie-Eintrag nach vorheriger Verifizierung und im
Erfolgsfall Status `OK` zurück.


Route: /stats GET
=================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\StatsController`

Statistik-bersicht einer App. Anwender können wenn gewünscht die Daten per CSV
herunterladen oder sich Statistik E-Mail Benachrichtigungen zuschicken lassen.

Folgende Tabs existieren mit Detailinformationen:
- Benutzer
- Frage
- Kategorien
- Quiz-Teams

Bei Seitenaufruf wird der Benutzer-Tab angezeigt, da der `Type` Standardwert `players` lautet.

**Das Modul Statistik muss aktiviert sein.**


Route: /stats/players GET
=========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\StatsController`

Inhalt des Benutzer-Tabs, nur hier ist es möglich nach Tags zu filtern. Pro
Tabellenseite werde 300 Einträge angezeigt. Kategorien der Benutzer-Tabelle
lassen ich auf oder absteigend sortieren. Die Statistikdaten stammen von
`StatsEngine`

* Die Nationalflagge wird nur angezeigt wenn in den Einstellungen das Speichern
  der Benutzer-IP aktiviert ist (`save_user_ip_info`).
  * Ebenso werden die Punkte einer Spielers nur angezeigt, wenn dieser über Punkte verfügt


Route: /stats/questions GET
===========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\StatsController`

Übersicht über die korrekt, bzw. falsch beantworteten Fragen.


Route: /stats/categories GET
============================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\StatsController`

Übersicht welche aufzeigt wie viele Fragen in einer Kategorie korrekt oder
falsch beantwortet wurden.


Route: /stats/groups GET
========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\StatsController`

Übersicht welche aufzeigt wie viele Fragen in von dem jeweiligen Quiz-Team korrekt,
bzw. falsch beantwortet wurden.


Route: /stats/CSV/player GET
============================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\StatsCSVController`

Exportiert die Benutzerstatistik-Liste als CSV-Datei durch die Umwandlung einer Blade-Datei in
CSV. Ist in den Einstellungen der Wert `hide_emails_backend` oder `hide_emails`
aktiv, werden die Anwender E-Mail-Adressen ausgeblendet. Ebenfalls wird das Land
aus dem der Anwender stammt nur angezeigt wenn `save_user_ip_info` nicht
deaktiviert wurde.


Route: /stats/CSV/questions GET
===============================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\StatsCSVController`

Exportiert die Fragestatistik-Liste als CSV-Datei.


Route: /stats/CSV/categories GET
================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\StatsCSVController`

Exportiert die Kategoriestatistik-Liste als CSV-Datei.


Route: /stats/CSV/groups GET
============================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\StatsCSVController`

Exportiert die Quiz-Team-Statistik-Liste als CSV-Datei.


Route: /stats/reporting GET
===========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\StatsReportingController`

Subseite um E-Mail Benachrichtigungen zu verwalten. Dabei kann aus verschiedenen
Kategorien und Quiz-Teams gewählt werden und ein einziger Sende-Intervall.


Route: /stats/reporting POST
============================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\StatsReportingController`

Speichert ein Benachrichtigungs-Auftrag. Dabei wird sichergestellt das eine
E-Mail-Adresse angegeben wurde. Nach erfolgreichem Speichern wird der Benutzer
zur `/stats/reporting` weitergeleitet.


Route: /stats/reporting/{id} GET
================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\StatsReportingController`

Aktualisiert einen bestehenden Benachrichtigungs-Auftrag und leitet den Benutzer
auf die vorherige Seite zurück.


Route: /stats/reporting/{id}/test GET
=====================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\StatsReportingController`

Versendet augenblicklich eine E-Mail Benachrichtigung (CSV Format) an einen
Benutzer. Besteht kein Benachrichtiguns-Auftrag, wird die Anfrage ignoriert.

Ford-Anpassung
--------------
Benachrichtigung enthält zusätzlich die Anzahl Spieler-Punkte.


Route: /stats/reporting/{id}/delete GET
=======================================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\StatsReportingController`

Löscht einen bestehenden Benachrichtigungs-Auftrag und leitet den Benutzer
anschließend auf die vorherige Seite weiter.


Route: /pages GET
=================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\PagesController`

Erlaubt das zusätzliche hinzufügen von Info-Menü Eintragen in der Quiz-App und
listet bestehende Einträge auf. Ist ein Eintrag noch nicht für die Anwender
sichtbar, wird dieser in der Liste mit dem Tag `Unsichtbar` versehen. Ein Klick
auf einen Listeneintrag öffnet die Detailansicht (Overlay).

// TODO hide_pages existiert nicht in den AppSettings
// **Das Modul Statistik muss aktiviert sein.**


Route: /pages POST
==================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\PagesController`

Erstellt einen neuen Seiteneintrag und füllt ihn mit Standardwerten. Leitet
daraufhin den Benutzer auf die Detailansicht des Eintrages um.


Route: /pages/{id} GET
======================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\PagesController`

Zeigt die Detailseite eines Seiteneintrags in einem Overlay an. Dabei wird neben
dem Titel und der Text in der jeweiligen Landessprache angezeigt. Klickt der
Benutzer auf `Neue Seite` oder einen bestehenden Eintrag öffnet sich ein Overlay
zum Bearbeiten des Eintrages. Dieses ermöglicht es Titel und Text der Seite
anzugeben. Ist das Attribut `öffentlich` gesetzt wird unterhalb des Titels die
URL zu dem hier editierten Eintrag angeben. Die Textsprache wird dabei
automatisch auf den Wert des Benutzerprofiles gesetzt.

* Einträge mit dem Attribut `öffentlich` sind ohne Registrierung sichtbar
* Nur Einträge mit dem Attribut `Sichtbar` werden in der App angezeigt


Route: /pages/{id} POST
=======================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\PagesController`

Aktualisiert die Daten eines Seiteneintrages.


Route: /mails GET
=================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\MailsController`

Verwaltung der E-Mail Vorlagen. Es können Vorlagen editiert und angesehen
werden, das Erstellen neuer Vorlagen ist allerdings nicht möglich. Sollte eine
Vorlage mehrsprachig sein und eine Übersetzung fehlen, wird der Benutzer darauf
hingewiesen.

// TODO hide_mails existiert nicht in den AppSettings
// **Das Modul Statistik muss aktiviert sein.**


Route: /mails/{type} GET
========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\MailsController`

Editieransicht einer E-Mail Vorlage. Dabei wird der Inhalt aller Wörter, die von
`%`-Zeichen eingeschlossen sind, mit dem Wert des jeweiligen Tags ersetzt. Die zu Verfügung
stehenden Tags hängen davon ab welche Klasse verwendet wird, die das
`CustomMail`-Interface erweitert, z.B. `AuthWelcome`.


Route: /mails/{type} POST
=========================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\MailsController`

Aktualisiert eine Mail-Vorlage und gibt Status `OK` zur


Route: /news GET
================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\NewsController`

Erlaubt das Anlegen und Verwalten der News. Diese können auf eine Zielgruppe
mittels Tags beschränkt werden und oder ein Ablaufdatum enthalten. Auf fehlende
Übersetzungen wird der Benutzer hingewiesen.

**Das Newsmodul muss aktiviert sein.**


Route: /news POST
=================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\NewsController`

Erstellt eine neuen News-Eintrag und füllt diesen mit Standardwerten. Danach
wird dem Benutzer die Editieransicht angezeigt.


Route: /news/{id} GET
=====================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\NewsController`

Editieransicht eines News-Eintrages in einem Overlay. 
Sollte es sich bei der Benutzer-Sprache nicht um die App-Standard-Sprache
handeln, wird der News-Inhalt, wenn eine Übersetzung vorliegt, in der
Benutzer-Sprache angezeigt.


Route: /news/{id} POST
======================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\NewsController`

Aktualisiert einen News-Eintrag.


Route: /news/{id}/delete GET
============================
Admin-Authentifizierung: notwendig  
Middleware: web, admin, backendaccess  
`Backend\NewsController`

Löscht einen News-Eintrag **ohne** Benutzer-Rückmeldung.


Route: /misc/faq GET
====================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\MiscController`

Erlaubt das Einsehen der App spezifischen FAQ's und das Editieren und erstellen
dieser, wenn der Benutzer über Super-Admin Rechte verfügt. Jeder FAQ-Eintrag
wird in einem eigenen Tab dargestellt. Neben Text ist es dem Benutzer möglich
Medien-Dateien oder Links zu bekannten Plattformen, z.B. Facebook oder YouTube
in den FAQ-Text einzubinden.


Route: /misc/faq POST
=====================
Admin-Authentifizierung: notwendig  
Middleware: web, admin  
`Backend\MiscController`

Speichert vom Super-Admin hochgeladene Bilder (unkomprimiert) im
`storage/page_attachments/` Ordner. Dem Client wird daraufhin eine Liste mit
allen Bilder-Pfaden im JSON-Format zurück gesandt.


[//]: # "======================== ARTISAN KOMMANDOS ========================"

# CacheAPIPlayerStats
Befehl: `api:stats:cache:players {appid?}`  
Erstellt Spieler- und Quiz-Team-Statistiken für die API einer App-ID und speichert diese im
Cache. Wird keine App-ID übergeben, werden die Statistiken für alle
Spieler/Quiz-Teams erstellt.


# CacheCategoriesStats
Befehl: `stats:cache:categories`  
Lädt alle Kategorie-Statistiken in den Cache.


# CacheCompetitionStats
Befehl: `stats:cache:competitions`  
Lädt alle Spieler-Statistiken in den Cache.


# CacheQuizTeamStats
Befehl: `stats:cache:quizteams`  
Lädt alle Quiz-Team-Statistiken in den Cache.


# CachePlayerStats
Befehl: `stats:cache:players`  
Lädt alle Spieler-Listen und Spieler-Statistiken in den Cache.


# CacheQuestionStats
Befehl: `stats:cache:questions {appid?}`  
Lädt alle Frage-Statistiken einer App, oder wenn keine App-ID vorhanden, aller
Apps, in den Cache.


# CacheStats
Befehl: `stats:cache`  
Lädt alle Statistiken in den Cache (nachdem der Cache geleert wurde). Dabei
werden erfasst:
* Spieler-Statistiken
* Quiz-Team-Statistiken
* Frage-Statistiken
* Kategorie-Statistiken
* Gewinnspiel-Statistiken
* API Spieler-Statistiken


# CalculateGameWinners
Befehl: `game:calculatewinners {appId?}`  
Errechnet die Gewinner aller Spiele einer App, oder wenn App-ID nicht übergeben
wurde, aller Spiele aller Apps.

Wurde erstellt für `LINGOMINT`.
// TODO warum
// determineWinnerOfGame -> App::ID_WUESTENROT, App::ID_WOHNDARLEHEN


# CheckLastGames
Befehl: `game:check {last=10}`  n
Gibt die Anzahl der gegebenen Antworten für die letzten `last`-Spiele der
letzten 10 Tage zurück.


# CompetitionReferee
Befehl: `competition:finish`  
Prüft ob die Laufzeit eines Gewinnspiel abgelaufen ist und benachrichtigt
ggf. die Teilnehmer über das Resultat. Zusätzlich erhält der Quiz-Team-Besitzer
eine E-Mail mit zusätzlichen Informationen.

Nachrichten werden nur versandt wenn dieses nicht in den App-Einstellungen
deaktiviert wurde `block_mail_*`

## Besonderheit - SCHWAEBISCH_HALL GENOAKADEMIE
Es werden keine Nachrichten versandt.


# Emailtest
Befehl: `emailtest`  
Sendet eine Testmail an `p.mohr@sopamo.de`.


# ExportForBumAgency
Befehl: `export:bum {appid : app to export}`  
Exportiert Spieler-Statistikdaten im CSV-Format einer App auf dem keeunit SFTP Server.

Anmerkung: Funktion wurde für `Buben und Mädchen agency` erstellt.


# ExportQuestions
Befehl: `export:questions {appid : app to export}`  
Speichert alle aktiven Fragen einer App im CSV-Format im `export`-Ordner.


# FixImportAppData
Befehl: `import:appdatafix`  
Importiert Quiz-App Daten von einer anderen Datenbank (behebt Probleme mit
Spiel-Daten & Spielern).


# FixImportAppData2
Befehl: `import:appdatafix2`  
Importiert Quiz-App Daten von einer anderen Datenbank (behebt Probleme mit
Fragen & Kategorie-Daten).


# FixQuestionAnswers
Befehl: `fixquestionanswers`  
Behebt den Fehler das Frage-Antworten mehr als eine korrekte Antwort besitzen.


# GameTerminatorIsBack
Befehl: `terminate:game`  
Beendet alle Spiele die über `round_answer_time` (24 Stunden) liegen. Wenn
`no_weekend_grace_period` in den App-Einstellungen aktiviert ist, laufen Spiele
auch am Wochenende ab.

Duelle laufen nach `round_initial_answer_time` (72 Stunden) ab.


# Gametest
Befehl: `game:test`  
Gibt Spiele-Antworten für den Tag `2017-02-07` aus.


# ImportAppData
Befehl: `import:appdata
         {newappid : The id of the app the content should be assigned to}
		 {oldappid : The id of the app in the old database}`  
Importiert Quiz-App Daten (MySQL import).


# ImportFordUsers
Befehl: `import:fordusers {
          csvfile : The path to the csv file, relative to storage/userimports}`  
Importiert von einer CSV-Datei Ford-Anwender.


# ImportXML
Befehl: `questions:importxml {file}`  
Importiert Fragen aus einer XML-Datei für die `WUERTTEMBERGISCHE`.


# Inspire
Befehl: `inspire`  
Gibt eine inspirierendes Zitat auf aus.


# InviteCSVUsers
Befehl: `users:invite 
         {csvfile : The path to the csv file, relative to storage/userimports} 
         {appid : The id of the app the users should be assigned to}
         {tagid=0 : The id of the tag the users should be assigned to}`  
Lädt alle Anwendern welche in der CSV-Datei aufgeführt werden zur App `appid`
ein. Für jeden Anwender wird vorab bereits ein Benutzer-Konto erstellt mit einem
zufälligen Passwort, welches in der E-Mail enthalten ist.


# LingoCron
Befehl: `lingomint:cron`  
Beendet offene Runden und Spiele für `LINGOMINT`. Ist es 17 Uhr werden an
alle Anwender, die länger als fünf Tage inaktiv waren, E-Mails versandt.


# ListSchedule
Befehl: `schedule:list`  
Listet auf wann planmäßige Kommandos ausgeführt werden.


# MigrateWohndarlehen
Befehl: `migrate:wohndarlehen`  
Migriert Tags, Kategorien und Fragen von `WOHNDARLEHEN` zu `WUESTENROT`
App. Dabei werden alle `WUESTENROT` Spiele beendet.


# RandomizeUserLearnBoxCards
Befehl: `randomizeuserlearnboxcards {user}`  
Mischt die Index-Karten eines Benutzer zufällig durch, außer bei Benutzern mit
der ID: 4805, 4580, 4579, 4246


# RoundTerminatorIsBack
Befehl: `terminate:round`  
Beendet alle unvollständige Spiel-Runden.


# SchedulerEnd
Befehl: `scheduler:end`  
Gibt aus wann die planmäßigen Kommandos gestoppt wurden (besteht nur aus Log-Anweisungen).


# SchedulerStart
Befehl: `scheduler:start`  
Gibt aus wann die planmäßigen Kommandos gestartet wurden (besteht nur aus Log-Anweisungen).


# SendReportings
Befehl: `reportings:send`  
Versendet alle ausstehende E-Mail Benachrichtigungen. Dabei werden die
App-Einstellungen berücksichtigt und E-Mail-Adressen oder Anwender-IP-Adressen
ggf. nicht in den Benachrichtigungen erwähnt (`hide_emails`
`hide_emails_backend` `save_user_ip_info`).


# SetAnswerResults
Befehl: `players:setanswerresults`  
Setzt bei allen nicht beantworteten Frage-Antworten dessen `question_answer_id`
ungleich `-1` ist die korrekte Antwort und speichert diese.


# SetupMissingDefaultMailTemplates
Befehl: `mails:setupMissingDefaultTemplates`  
Erstellt für die App mit der ID 0 alle fehlenden E-Mail Vorlagen.


# ShowGame
Befehl: `game:show {gameid}`  
Zeigt Informationen für alle Spiel an die Spiele-Runden, Spiele-Fragen oder
Spiele-Antworten besitzen.


# TimeDisplay
Befehl: `time:display`  
Gibt die derzeitige Uhrzeit auf die Sekunde genau aus.


# TranslationsMigrate
Befehl: `translations:migrate {model : Model to migrate}`  
Migriert alle Modelle des spezifizierten Typs zum neuen Lokalisierungs-Format.


# UpdateGeoliteDB
Befehl: `update:geolite`  
Lädt die GeoLite2 Länder-Datenbank herunter und speichert diese in
`storage/app/GeoLite2-Country.mmdb`.


# UsageReminder
Befehl: `reminder:send`  
Fordert alle Anwender per E-Mail auf weiter zu spielen, die:
* bereits ein Spiel gespielt haben
* und die TOS akzeptiert haben
* und länger als fünf Tage inaktiv waren

## Besonderheit - `SCHWAEBISCH_HALL` `GENOAKADEMIE`
Anwender der obigen Apps erhalten keine Benachrichtigung.
