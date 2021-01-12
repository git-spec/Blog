<?php
/******************************************************************************************************/


				/**
				*
				*	Stellt eine Verbindung zu einer Datenbank mittels PDO her
				*
				*	@param [String $dbname		Name der zu verbindenden Datenbank]
				*
				*	@return Object					DB-Verbindungsobjekt
				*
				*/
				function dbConnect($dbname=DB_NAME) {
if(DEBUG_DB)	echo "<p class='debugDb'><b>Line " . __LINE__ . ":</b> Versuche mit der DB <b>$dbname</b> zu verbinden... <i>(" . basename(__FILE__) . ")</i></p>\r\n";					
					
					// EXCEPTION-HANDLING (Umgang mit Fehlern)
					// Versuche, eine DB-Verbindung aufzubauen
					try {
						// wirft, falls fehlgeschlagen, eine Fehlermeldung "in den leeren Raum"
						
						// $pdo = new PDO("mysql:host=localhost; dbname=blog; charset=utf8mb4", "phpmyadmin", "12345678");
						$pdo = new PDO(DB_SYSTEM . ":host=" . DB_HOST . "; dbname=$dbname; charset=utf8mb4", DB_USER, DB_PWD);
					
					// falls eine Fehlermeldung geworfen wurde, wird sie hier aufgefangen					
					} catch(PDOException $error) {
						// Ausgabe der Fehlermeldung
if(DEBUG_DB)		echo "<p class='error'><b>Line " . __LINE__ . ":</b> <i>FEHLER: " . $error->GetMessage() . " </i> <i>(" . basename(__FILE__) . ")</i></p>\r\n";
						// Skript abbrechen
						exit;
					}
					// Falls das Skript nicht abgebrochen wurde (kein Fehler), geht es hier weiter
if(DEBUG_DB)	echo "<p class='debugDb ok'><b>Line " . __LINE__ . ":</b> Erfolgreich mit der DB <b>$dbname</b> verbunden. <i>(" . basename(__FILE__) . ")</i></p>\r\n";

					// DB-Verbindungsobjekt zurückgeben
					return $pdo;
				}
				
				
/******************************************************************************************************/


				/**
				* Führt eine definierte Datenbank-Operation durch
				*
				* @param 	Object 	$pdo			Das Datenbankverbindungs-Objekt
				* @param 	String 	$sql			SQL-Statement, das ausgeführt werden soll
				* @param 	[Array 	$params]  	Ein Array mit den Platzhaltern für das Prepared Statement
				*
				* @return 	Int /						Die Anzahl der gefundenen Treffer bei SELECT COUNT / 
				*				Object /					Das Statement-Objekt bei SELECT /
				*				Array /					Den rowCount und die lastInsertId bei INSERT /
				*				Int 						Die Anzahl der betroffenen Datensätze bei DELETE oder UPDATE
				*/
				function dbOperation($pdo, $sql, $params=NULL) {
if(DEBUG_DB)	echo "<p class='debugDb'><b>Line  " . __LINE__ .  "</b>: Aufruf " . __FUNCTION__ . "($sql) <i>(" . basename(__FILE__) . ")</i></p>\r\n";	
					
					// ggf. Whitespaces vor und nach dem String abschneiden,
					// da ansonsten die Zählung mittels substr() durcheinander
					// kommen könnte
					$sql = trim($sql);
					
					
					/********** OPERATIONSMODUS BESTIMMEN **********/
					
					// SELECT COUNT()
					if( substr($sql, 0, 12) == "SELECT COUNT" ) {
						$operationMode = substr($sql, 0, 12);
					
					// INSERT, UPDATE, DELETE, SELECT
					} else {
						$operationMode = substr($sql, 0, 6);
					}
if(DEBUG_DB)	echo "<p class='debugDb'><b>Line " . __LINE__ . "</b>: \$operationMode: $operationMode <i>(" . basename(__FILE__) . ")</i></p>\r\n";
					
					
					/********** DATENBANKOPRATION DURCHFÜHREN **********/
					
					$statement = $pdo->prepare($sql);
					$statement->execute($params);
if(DEBUG_DB)	if($statement->errorInfo()[2]) echo "<p class='debugDb err'><b>Line " . __LINE__ . "</b>: " . $statement->errorInfo()[2] . " <i>(" . basename(__FILE__) . ")</i></p>\r\n";														
					
					
					/********** RÜCKGABEWERTE ERZEUGEN **********/
					
					switch($operationMode) {
						case "SELECT COUNT":			return $statement->fetchColumn();
															break;
															
						case "SELECT":					return $statement;
															break;
															
						case "INSERT":					return array(
																			"rowCount" 		=> $statement->rowCount(),
																			"lastInsertId" => $pdo->lastInsertId()
																			);
															break;
															
						case "DELETE":									
						case "UPDATE":					return $statement->rowCount();
															break;	
					}					
				}


/******************************************************************************************************/


				/**
				*
				*	Liest PDOStatement::debugDumpParams() aus und schneidet das simulierte SQL-Query aus
				*	Erzeugt mittels des ausgeschnittenen SQL-Querys eine DEBUG-Ausgabe
				*
				*	@param Object	$statement		Das aktuell verwendete Statement-Objekt
				*
				*	@return VOID
				*
				*/
				function showQuery($statement) {
if(DEBUG_DB)	echo "<p class='debugDb'><b>Line  " . __LINE__ .  "</b>: Aufruf " . __FUNCTION__ . "($sql) <i>(" . basename(__FILE__) . ")</i></p>\r\n";	

					// Outputbuffering aktivieren, um die originale Debug-Ausgabe abzufangen
					ob_start();
					
					$statement->debugDumpParams();
					// Debug-Ausgabe in Variable speichern
					$queryString = ob_get_contents();
					// Outputbufferung beenden und Bufferinhalt löschen
					ob_end_clean();
					
					// Prüfen, ob der Teilstring "Sent SQL:" im Debug-String vorkommt
					if( strpos($queryString, "Sent SQL:") ) {
						// Startposition für das Ausschneiden anhand des Teilstrings "Sent SQL:" bestimmen
						$startpos = strpos($queryString, "Sent SQL:") + 15;
					} else {
						// Startposition für das Ausschneiden anhand des Teilstrings "SQL:" bestimmen
						$startpos = strpos($queryString, "SQL:") + 10;
					}
					
					// Endposition für das Ausschneiden anhand des Teilstrings "Params:" bestimmen
					$endpos = strpos($queryString, "Params:");
					$length = $endpos - $startpos;
					// Querystring zwischen Start- und Stopmarker ausschneiden
					$queryString = substr($queryString, $startpos, $length);
			
					// DEBUG-Ausgabe erzeugen
if(DEBUG_DB)	echo "<p class='debugDb'><b>Line " . __LINE__ . ":</b> <b>SQL:</b> $queryString <i>(" . basename(__FILE__) . ")</i></p>\r\n";
				}


/******************************************************************************************************/
?>