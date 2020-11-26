<?php
/*******************************************************************************/

				/**************************************************/
				/****************** LOGIN CHECK *******************/
				/**************************************************/

				// Session starten (bzw. wenn vorhanden, Session fortführen)
				session_name("blog");
				session_start();
				
				// (neue) Session wieder löschen, wenn sie keine User-ID enthält
				if( !isset($_SESSION['usr_id']) ) {
					session_destroy();
				}							
				
/*******************************************************************************/

				/**************************************************/
				/***************** CONFIGURATION ******************/
				/**************************************************/

				
				// include(Pfad zur Datei): Bei Fehler wird das Skript weiter ausgeführt. Problem mit doppelter Einbindung derselben Datei
				// require(Pfad zur Datei): Bei Fehler wird das Skript gestoppt. Problem mit doppelter Einbindung derselben Datei
				// include_once(Pfad zur Datei): Bei Fehler wird das Skript weiter ausgeführt. Kein Problem mit doppelter Einbindung derselben Datei
				// require_once(Pfad zur Datei): Bei Fehler wird das Skript gestoppt. Kein Problem mit doppelter Einbindung derselben Datei
				
				require_once("include/config.inc.php");
				require_once("include/form.inc.php");
				require_once("include/db.inc.php");
				require_once("include/dateTime.inc.php");

/*******************************************************************************/

				/**************************************************/
				/************* INITIALIZE VARIABLES ***************/
				/**************************************************/

				$errorLogin			= NULL;
			
						
/*******************************************************************************/

				/**************************************************/
				/************** DATABASE CONNECTION ***************/
				/**************************************************/	

				// Schritt 1 DB: DB-Verbindung herstellen
				$pdo = dbConnect();

/*******************************************************************************/

				/**************************************************/
				/************** PROCCESS FORM LOGIN ***************/
				/**************************************************/

				// Schritt 1 FORM: Prüfen, ob Daten übergeben wurden
				if( isset($_POST['formsentLogin']) ) {
if(DEBUG)		echo "<p class='debug'>Formular 'formsentLogin' wurde abgeschickt.</p>\r\n";										
					
					// Schritt 2 FORM: Daten auslesen, entschärfen, DEBUG-Ausgabe
					$email		= cleanString($_POST['email']);
					$password	= cleanString($_POST['password']);
if(DEBUG)		echo "<p class='debug'>\$email: $email</p>\r\n";
if(DEBUG)		echo "<p class='debug'>\$password: $password</p>\r\n";
					
					/********* LOGIN VALIDATION ***********/
					
					// Schritt 3 FORM: Daten validieren
					$errorEmail		= checkEmail($email);
					$errorPassword	= checkInputString($password, 4);
					
					if($errorEmail || $errorPassword) {
						// Fehlerfall
if(DEBUG)			echo "<p class='debug err'>Das Formular enthält noch Fehler!</p>\r\n";
						$errorLogin = "Benutzername oder Passwort falsch!";	
					} else {
						// Erfoglsfall
if(DEBUG)			echo "<p class='debug ok'>Das Formular ist fehlerfrei und wird nun weiterverarbeitet ...</p>\r\n";
					
						// Schritt 4 FORM: Daten weiterverarbeiten


						/************** DATABASE OPERATIONS ***************/
					
						// Schritt 1 DB: DB-Verbindung herstellen
						// Ist bereits geschehen
						
						// Schritt 2 DB: SQL-Statement vorbereiten
						$statement = $pdo->prepare("SELECT usr_email, usr_password, usr_id FROM users
															WHERE usr_email = :ph_usr_email");
						
						// Schritt 3 DB: SQL-Statement ausführen
						$statement->execute( array("ph_usr_email" => $email) );
						
						/*********** CHECK ACCOUNT ************/
						
						// Schritt 4 DB: Daten weiterverarbeiten
						$row = $statement->fetch(PDO::FETCH_ASSOC);
						
						/********** 1. CHECK EMAIL ***********/
						
						// Prüfen, ob ein Datensatz geliefert wurde
						// Wenn Datensatz geliefert wurde, muss die E_Mail stimmen
						if(!$row) {
							// Fehlerfall
if(DEBUG)				echo "<p class='debug err'>Die E_Mail '" . $row['usr_email'] . "' existiert nicht in der DB!</p>\r\n";
							$errorLogin = "Benutzername oder Passwort falsch!";
							
						} else {
							// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'>Die E_Mail '" . $row['usr_email'] . "' wurde in der DB gefunden.</p>\r\n";

							/******* 2. VERIFY PASSWORD ******/	

							if( !password_verify($password, $row['usr_password']) ) {
								// Fehlerfall
if(DEBUG)					echo "<p class='debug err'>Das Passwort aus dem Formular stimmt NICHT mit dem Passwort aus der DB überein!</p>\r\n";								
								$errorLogin = "Benutzername oder Passwort falsch!";	
								
							} else {
								// Erfoglsfall
if(DEBUG)					echo "<p class='debug ok'>Das Passwort aus dem Formular stimmt mit dem Passwort aus der DB überein.</p>\r\n";																							
							
								/****** PROCCESS LOGIN ******/
								
								// Session starten
								session_name("blog");
								session_start();
								
								// User-ID ind Session schreiben
								$_SESSION['usr_id']		= $row['usr_id'];
								
								// Verlinkung zur dashboard.php
								header("LOCATION: dashboard.php");	
							
							}	// VERIFY PASSWORD END
							
						}	// CHECK EMAIL END
						
					}	// LOGIN VALIDATION END	
					
				}	// PROCCESS FORM LOGIN END
				
/*******************************************************************************/				
				
				/**************************************************/
				/************ FETCH CATEGORIES FROM DB ************/
				/**************************************************/
			
				// Schritt 1 DB: DB-Verbindung herstellen
				// Ist bereits geschehen 
				
				// Schritt 2 DB: SQL-Statement vorbereiten
				$statement = $pdo->prepare("SELECT * FROM categories ORDER BY cat_name");
				
				// Schritt 3 DB: Sql-Statement ausführen
				$statement->execute();
if(DEBUG)	if($statement->errorInfo()[2]) echo "<p class='debug err'>" . $statement->errorInfo()[2] . "</p>\r\n";						
				
				// Schritt 4 DB: Daten weiterverarbeiten
				$allCategoriesArray = $statement->fetchAll(PDO::FETCH_ASSOC);
			
/*******************************************************************************/

				/**************************************************/
				/************* PROCESS URL-PARAMETER **************/
				/**************************************************/

				// Schritt 1 URL: Prüfen, ob URL-Parameter übergeben wurde
				if( isset($_GET['action']) ) {
if(DEBUG)		echo "<p class='debug'>URL-Parameter 'action' wurde übergeben ...</p>\r\n";										
				
					// Schritt 2 URL: Werte auslesen, entschärfen, DEBUG-Ausgabe
					$action = cleanString($_GET['action']);
if(DEBUG)		echo "<p class='debug'>\$action: $action</p>\r\n";					
					
					// Schritt 3 URL: LINKING
					
					/************** LOGOUT ****************/
					
					if($action == "logout") {
						
						// Schritt 4 URL: Daten werden weiterverarbeitet
						session_destroy();
						header("LOCATION: index.php");
						exit;
					
					/********** CATEGORY FILTER **********/
					
					} elseif($action == "showCat") {
if(DEBUG)			echo "<p class='debug'>Blogbeiträge werden nach Kategrie gefiltert ...</p>\r\n";										
				
						// zweiter Parameter wird ausgelesen
						$catId = cleanString($_GET['catId']);
if(DEBUG)			echo "<p class='debug'>\$catId: $catId</p>\r\n";					
						
						// Schritt 4 URL: Daten werden weiterverarbeitet
						
						$sql	= "SELECT * FROM blogs 
									INNER JOIN users USING(usr_id)
									INNER JOIN categories USING(cat_id)
									WHERE cat_id = :ph_cat_id
									ORDER BY blog_date DESC
									";
									
						$params = array("ph_cat_id" => $catId);

					}	// LINKING END
						
				}	// PROCESS URL-PARAMETER END
			
/*******************************************************************************/

				/**************************************************/
				/***************** BLOGS CONTENT ******************/
				/**************************************************/
				
				// Schritt 1 DB: DB-Verbindung herstellen
				// Ist bereits geschehen
				
				// Prüfen, ob bereits ein SQL_Statement vorliegt (s. LINKING)
				if( !isset($sql) ) {
					$sql	 = "SELECT * FROM blogs
								INNER JOIN users USING(usr_id)
								INNER JOIN categories USING(cat_id)
								ORDER BY blog_date DESC
								";
							
					$params = NULL;
				}
				
				// Schritt 2 DB: SQL-Statement vorbereiten
				$statement = $pdo->prepare($sql);
				
				// Schritt 3 DB: Sql-Statement ausführen
				$statement->execute($params);
if(DEBUG)	if($statement->errorInfo()[2]) echo "<p class='debug err'>" . $statement->errorInfo()[2] . "</p>\r\n";						
				
				// Schritt 4 DB: Daten weiterverarbeiten
				$allBlogsArray = $statement->fetchAll(PDO::FETCH_ASSOC);
				
/*******************************************************************************/		
?>

<!doctype html>
<html>
	<head>
		<link rel="stylesheet" href="css/main.css">
		<link rel="stylesheet" href="css/debug.css">
		<meta charset="utf-8">
		<title>PHP-Projekt Blog</title>
	</head>
	<body>
		<!------------- FORM LOGIN/LOGOUT ------------->
		<header>
			<div class="fright">
				<!------------------- LOGIN ------------------->
				<?php if( !isset($_SESSION['usr_id']) ): ?>
					<div class="tolock">
						<form action="" method="POST">
							<span class="error"><?= $errorLogin ?></span>
							<input class="short" type="text" name="email" placeholder="E-Mail">
							<input class="short" type="password" name="password" placeholder="Passwort">
							<input class="short" type="submit" value="Login">
							<input type="hidden" name="formsentLogin">
							<div class='clearer'></div>	
						</form>
					</div>
				<!------------------ LOGOUT ------------------->
				<?php else: ?>
					<div class="tolock">
						<a class="darkred" href="?action=logout">Logout</a>
						<a class="darkred" href="dashboard.php">zum Backend</a>
					</div>
				<?php endif ?>
			</div>
			<div class="clearer"></div>
			<!---------- FORM LOGIN/LOGOUT END ------------>
			<h1 class="darkred">PHP-Projekt Blog</h1>
			<a class="darkred" href="<?= $_SERVER['SCRIPT_NAME'] ?>">Alle Einträge anzeigen</a>
			<br>
			<br>
		</header>
		<!--------------- BLOG ENTRIES ---------------->
		<main class="fleft width65">
			<?php foreach( $allBlogsArray AS $blogEntry ): ?>
				<article>
					<!------------- DATE CONVERSION --------------->
					<?php $dateTime = isoToEuDateTime($blogEntry['blog_date']) ?>
					<!----------------- CATEGORY ------------------>
					<p class="darkred fright">Kategorie: <b><?= $blogEntry['cat_name'] ?></b></p>
					<!----------------- HEADLINE ------------------>
					<h2 class="darkred"><?= $blogEntry['blog_headline'] ?></h2>
					<!-------------- NAME/CITY/DATE --------------->
					<p class="grey"><?= $blogEntry['usr_firstname'] . " " . $blogEntry['usr_lastname'] . " aus " . $blogEntry['usr_city'] . " schrieb am " . $dateTime['date'] . " um " . $dateTime['time'] . " Uhr:" ?></p>
					<!------------------- IMAGE ------------------->
					<?php if($blogEntry['blog_image']): ?>
						<img style="float: <?= $blogEntry['blog_imageAlignment'] ?>" src="<?= $blogEntry['blog_image'] ?>">
					<?php endif ?>
					<!------------------ CONTENT ------------------>
					<p class="darkgrey"><?= nl2br($blogEntry['blog_content']) // Auslesen mit Absätzen?></p>
					<div class="clearer">&nbsp;</div>
				</article>
			<?php endforeach ?>
		</main>
		<!-------------- BLOG ENTRIES END ------------->
		<!----------- CATEGORIES SELECTION ------------>
		<nav class="fright">
			<?php foreach( $allCategoriesArray AS $category ): ?>
				<a class="darkred" href="?action=showCat&catId=<?= $category['cat_id'] ?>">
					<h3><?= $category['cat_name'] ?></h3>
				</a>
				<br>
			<?php endforeach ?>	
		</nav>
		<div class="clearer">&nbsp;</div>		
		<!-------- CATEGORIES SELECTION END ---------->
	</body>
</html>