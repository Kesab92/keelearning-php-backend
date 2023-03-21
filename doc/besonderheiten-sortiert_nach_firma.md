<!-- ID 1 -->
# Wüstenrot 
`/games/{game_id}/question POST`  
Der Gewinner eines Spieles wird über die Anzahl der korrekten Spieleantworten
ermittelt.

Command: `CalculateGameWinner`  
Ermitteln den Spielgewinner über die Anzahl korrekt beantworteter Fragen.

Command: `MigrateWohndarlehen`  
Migriert Tags, Kategorien und Fragen von `WOHNDARLEHEN` zu `WUESTENROT`
App. Dabei werden alle `WUESTENROT` Spiele beendet.


<!-- ID 2 -->
# Keeunit Demo
Methode: `App::hasInsecurePasswordChange`  
Erlaubt es dem Benutzer über die App das Passwort zu ändern.

Methode: `App::hasDeepstream`  
App verwendet Deepstream.


<!-- ID 3 -->
# Webdev Quiz
`/tmpaccount`  
Temporäre Konten sind aktiv. Allerdings muss der angegebene Benutzername in der Datenbank hinterlegt worden sein.

Methode: `App::hasSignup`  
Erlaubt das sich neue Benutzer registrieren dürfen.


<!-- ID 5 -->
# Deutschkurs Medizin
Methode: `App::hasSignup`
Erlaubt das sich neue Benutzer registrieren dürfen.


<!-- ID 7 -->
# Württembergische Versicherung
Command: `ImportXML`  
Importiert Fragen aus einer XML-Date.


<!-- ID 9 -->
# WohnDarlehen
`/games/{game_id}/question POST`  
Der Gewinner eines Spieles wird über die Anzahl der korrekten Spieleantworten
ermittelt.

Command: `CalculateGameWinner`  
Ermitteln den Spielgewinner über die Anzahl korrekt beantworteter Fragen.


<!-- ID 10 -->
# Ford
`/fordsignup POST`  
Beim Erstellen eines Ford-Benutzerkontos muss ein bereits bestehendes
Benutzerkonto durch Ergänzen von PIN & Nachname aktiviert werden. Der angegebene
Benutzername und die E-Mail dürfen dabei nicht bereits vergeben sein.


Anforderungen an die Login-Felder:

| Feld:    | Einschränkung:                   | Verpflichtend: |
|----------|----------------------------------|----------------|
| lastname | min: 3                           | ja             |
| pin      | min: 3                           | ja             |
| email    | min: 3, max: 255                 | ja             |
| password | min: 6, nur: `[a-z] [A-Z] [0-9]` | ja             |
| username | min: 3, max: 255                 | nein           |

`/password POST`  
Speichert ein neu übermitteltes Passwort.
Mindestlänge: 6  
Erlaubte Zeichen: `[a-z] [A-Z] [0-9]`

`/indexcards/update POST`  
Speichert neben den übermittelten Indexkarten auch noch zusätzlich folgende Daten: `userdata box box_entered_at`

Command: `ImportFordUsers`  
Ford-Anwender Import über eine CSV-Datei.

Methode: `App::hasDeepstream`  
App verwendet Deepstream.


<!-- ID 11 -->
# Schwäbisch Hall
`/users/{user_id} GET`  
Verwenden einen eigenen Standard-Avatar.

Command: `CompetitionReferee`  
Nach dem Gewinnspielablauf werden keine Benachrichtigungen verschickt.

Command: `UsageReminder`  
Anwender dieses Kundens erhalten keine Aufforderung weiter zu spielen.

Methode: `App::hasSignup`  
Erlaubt das sich neue Benutzer registrieren dürfen.

Methode: `App::hasInsecurePasswordChange`  
Erlaubt es dem Benutzer über die App das Passwort zu ändern.


<!-- ID 13 -->
# Lingomint
`/tmpaccount`  
Temporäre Konten sind aktiv.

Command: `LingoCron`  
Beendet offene Runden und Spiele .Ist es 17 Uhr werden an alle Anwender, die
länger als fünf Tage inaktiv waren, Benachrichtigungen (E-Mail) versandt.


Methode: `App::hasSignup`  
Erlaubt das sich neue Benutzer registrieren dürfen.

Methode: `App::uniqueUsernames`  
Benutzernamen dürfen nicht doppelt vorkommen.

Methode: `App::hasDeepstream`  
App verwendet Deepstream.


<!-- ID 14 -->
# GenoAkademie
`/users/{user_id} GET`  
Verwenden einen eigenen Standard-Avatar.


Command: `CompetitionReferee`  
Nach dem Gewinnspielablauf werden keine Benachrichtigungen verschickt.

Command: `UsageReminder`  
Anwender dieses Kundens erhalten keine Aufforderung weiter zu spielen.


<!-- ID 15 -->
# Open Grid
Methode: `App::hasSignup`  
Erlaubt das sich neue Benutzer registrieren dürfen.

Methode: `App::hasInsecurePasswordChange`  
Erlaubt es dem Benutzer über die App das Passwort zu ändern.

Methode: `App::isMailValid`  
E-Mail-Adressen werden nach vom Kunden spezifizierten Gesichtspunkte validiert.


<!-- ID 17 -->
# M2
Methode: `App::hasInsecurePasswordChange`  
Erlaubt es dem Benutzer über die App das Passwort zu ändern.


<!-- ID 18 -->
# Bayer
`/users/{user_id} GET`  
Verwenden einen eigenen Standard-Avatar.

`/users/{user_id} GET`  
Verwenden einen eigenen Standard-Avatar.

Methode: `App::hasInsecurePasswordChange`  
Erlaubt es dem Benutzer über die App das Passwort zu ändern.

Methode: `App::hasDeepstream`  
App verwendet Deepstream.

Methode: `App::isMailValid`  
E-Mail-Adressen werden nach vom Kunden spezifizierten Gesichtspunkte validiert.


<!-- ID 19 -->
# Curator
Methode: `App::hasInsecurePasswordChange`  
Erlaubt es dem Benutzer über die App das Passwort zu ändern.

Methode: `App::hasDeepstream`  
App verwendet Deepstream.


<!-- ID 20 -->
# Wika
Methode: `App::hasSignup`  
Erlaubt das sich neue Benutzer registrieren dürfen.

Methode: `App::hasInsecurePasswordChange`  
Erlaubt es dem Benutzer über die App das Passwort zu ändern.

Methode: `App::hasDeepstream`  
App verwendet Deepstream.

Methode: `App::isMailValid`  
E-Mail-Adressen werden nach vom Kunden spezifizierten Gesichtspunkte validiert.


<!-- ID 21 -->
# Reiffeisen
Methode: `App::hasInsecurePasswordChange`  
Erlaubt es dem Benutzer über die App das Passwort zu ändern.

Methode: `App::hasDeepstream`  
App verwendet Deepstream.


<!-- ID 23 -->
# Heidelberg
Methode: `App::hasInsecurePasswordChange`  
Erlaubt es dem Benutzer über die App das Passwort zu ändern.


<!-- ID 24 -->
# THM - Technische Hochschule Mittelhessen
Methode: `App::hasSignup`  
Erlaubt das sich neue Benutzer registrieren dürfen. 

Methode: `App::hasInsecurePasswordChange`  
Erlaubt es dem Benutzer über die App das Passwort zu ändern.

Methode: `App::needsAccountActivation`  
Account ist inaktiv bis dieser über einen bei der Regestrierung zugestellten
Link aktiviert wurde.

Methode: `App::hasDeepstream`  
App verwendet Deepstream.

Methode: `App::isMailValid`  
E-Mail-Adressen werden nach vom Kunden spezifizierten Gesichtspunkte validiert.
