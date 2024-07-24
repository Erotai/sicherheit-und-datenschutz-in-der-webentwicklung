## Username Leaks
**Meinung zur meiner Implementierung**

Die Verhinderung der Anzeige des Usernamens gelingt mit dieser Implementierung leider nur zu 70 %. An den öffentlichen Stellen, die den Usernamen anzeigen, wird das Plugin der Aufgabenstellung gerecht. Es gibt jedoch ein Problem mit Kommentaren auf Posts. Das Plugin verschleiert von anderen Nutzern den Usernamen leider nicht, sondern nur den Kommentar des Postautoren, wenn er selbst nochmal unter seinen Post kommentiert. Wirklich nützlich ist es also nur, wenn die Kommentarfunktion ausgestellt werden würde und man auf Posts keine Kommentare geben könnte. Das ist aber nicht der Zweck einer öffentlichen Plattform. Daher könnte dafür bestimmt auch eine Lösung gefunden werden, aber ich habe diese nicht finden können. Die Funktion, die an sonstigen Stellen der HTML den Nutzernamen verbirgt, hätte auch die der Post-Kommentare verschleiern sollen. Außerdem bin ich nicht ganz zufrieden mit dem Verbergen der URL. Wenn ein Autor einen Post schreibt, sollte man trotzdem auf seine Posts gelangen können. Bestimmt hätte man das so lösen können, dass der Nutzername aus der URL entfernt wird und man anstatt über den Nutzernamen über die ID weitergeleitet werden könnte, sodass man von dieser Person weitere Posts sehen kann, was aber auch wieder Sicherheitslücken darstellen würde. Da uns keine bessere Lösung eingefallen ist, mussten wir die Weiterleitung einfach blockieren. Der Aufgabenstellung zu entnehmen dürfte das genügen, jedoch ist es irgendwo dennoch nicht sehr schön anzusehen. Nicht so unschön wie die Nichtverschleierung der Kommentare anderer Nutzer auf einem Post, aber dennoch unschön. Des Weiteren gibt es aber noch einige gute Worte zur Implementierung. Eine sehr starke Seite an dem Plugin ist, dass der Username gezwungenermaßen durch einen Nicknamen ersetzt wird, wenn einer verfügbar ist. Zu umgehen, dass das passiert, ist ohne Weiteres nicht möglich, und wenn man es dennoch hinbekommt, wird dem Admin sehr verlässlich angezeigt, dass der Username von User XY sichtbar wäre. Jedoch wäre der Username dann nur sichtbar, wenn die Funktion nicht packen würde, dass, wenn der Anzeigename dem Usernamen gleicht, anonym angezeigt wird. Diese Funktion gibt es glücklicherweise, aber dennoch, wie schon erwähnt, stößt das Ganze auch auf seine Grenzen. Außerdem finde ich auch sehr gut gelöst, wie das Plugin in REST den Username verbirgt und zudem auch noch den Slug, welcher ebenfalls den Username beinhaltet. So kann durch keinen Zugriff auf die User direkt herausgefunden werden, welchen Nutzernamen sie haben.

### Zusammenfassend
#### Gute Aspekte:

1. Anonymisierung in öffentlichen Bereichen: Das Plugin verbirgt den Usernamen an den meisten öffentlichen Stellen erfolgreich.
2. Nicknamen-Ersetzung: Der Username wird durch einen Nicknamen ersetzt, falls einer verfügbar ist. Dies ist zuverlässig und schwer zu umgehen.
3. REST-API-Schutz: Der Username und der Slug werden in der REST-API ebenfalls verborgen, was zusätzlichen Schutz bietet.

#### Schlechte Aspekte:

1. Unzureichende Kommentaranonymisierung: Der Username wird in Kommentaren anderer Nutzer auf Posts nicht verschleiert, nur der Kommentar des Postautors wird anonymisiert.
2. Probleme mit URLs: Beim Verfassen eines Posts kann man den Autor nicht durch die URL aufrufen. Dies wurde durch Blockieren der Weiterleitung gelöst, was jedoch unschön ist.

#### Verbesserungsmöglichkeiten:

1. Kommentaranonymisierung verbessern: Eine Lösung finden, um auch die Usernamen in den Kommentaren anderer Nutzer auf Posts zu verschleiern.
2. URL-Weiterleitung optimieren: Eine Methode entwickeln, bei der der Username aus der URL entfernt wird und stattdessen über eine ID weitergeleitet wird, ohne Sicherheitslücken zu erzeugen.

### Fazit
Die Implementierung erfüllt die Aufgabenstellung größtenteils, hat aber deutliche beziehungsweise unschöne Schwächen. Die Anonymisierung funktioniert in vielen Bereichen ziemlich gut, besonders durch die Ersetzung von Benutzernamen durch Nicknamen und den Schutz in der REST-API. Allerdings bleibt das Problem der Anonymisierung in Kommentaren und der unschönen Lösung bei der URL-Weiterleitung bestehen. Hier gibt es noch Verbesserungsbedarf. Trotz dieser Mängel bietet das Plugin eine solide Grundlage und zeigt gute Ansätze im Umgang mit Nutzernamen.