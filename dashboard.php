<?php
/*******************************************************************************/

				/**************************************************/
				/*************** LOGIN PROTECTION *****************/
				/**************************************************/

				// Session fortführen (bzw. wenn nicht vorhanden, neue Session starten)
				session_name("blog");
				session_start();
				
				// (neue) Session wieder löschen, wenn sie keine User-ID enthält
				// Verlassen der Seite
				if( !isset($_SESSION['usr_id']) ) {
					session_destroy();
					header("LOCATION: index.php");
					exit;
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


/*******************************************************************************/

				/**************************************************/
				/************* INITIALIZE VARIABLES ***************/
				/**************************************************/

				$cat_name				= NULL;
				$cat_Id					= NULL;
				$blog_headline			= NULL;
				$blog_image				= NULL;
				$blog_imageAlignment	= NULL;
				$blog_content			= NULL;
				
				$dbMessageBlog			= NULL;
				$dbMessageCategory	= NULL;
				$errorCategory			= NULL;
				$errorHeadline			= NULL;
				$errorContent			= NULL;
				$errorBlogEntry		= NULL;
				$errorImageUpload		= NULL;

/*******************************************************************************/

				/**************************************************/
				/**************** PROCESS DATABASE ****************/
				/**************************************************/

				// Schritt 1 DB: DB-Verbindung herstellen
				$pdo = dbConnect();


/*******************************************************************************/

				/**************************************************/
				/************* PROCESS URL-PARAMETER **************/
				/**************************************************/

				// Schritt 1 URL: Prüfen, ob URL-Parameter übergeben wurde
				if( isset($_GET['action']) ) {
if(DEBUG)		echo "<p class='debug'>URL-Parameter 'action' wurde übergeben ...</p>\r\n";										
				
					// Schritt 2 URL: Werte auslesen, entschärfen, DEBUG-Ausgabe
					$action		= cleanString($_GET['action']);
if(DEBUG)		echo "<p class='debug'>\$action: $action</p>\r\n";					
					
					/************** LOGOUT ****************/
					
					// Schritt 3 URL: i. d. R. Verzweigung
					if($action['logout']) {
						
						// Schritt 4 URL: Daten weiterverarbeiten
						
						session_destroy();
						header("LOCATION: index.php");
						exit;
					} else {
					
													
					} // LOGOUT END
						
				}	// PROCESS URL-PARAMETER END

/*******************************************************************************/						
				
				/**************************************************/
				/*********** PROCCESS FORM NEW CATEGORY ***********/
				/**************************************************/

				// Schritt 1 FORM: Prüfen, ob Daten übergeben wurden
				if( isset($_POST['formsentCategory']) ) {
if(DEBUG)		echo "<p class='debug'>Formular 'Category' wurde abgeschickt.</p>\r\n";										
					
					// Schritt 2 FORM: Daten auslesen, entschärfen, DEBUG-Ausgabe
					$cat_name		= cleanString($_POST['cat_name']);
if(DEBUG)		echo "<p class='debug'>\$cat_name: $cat_name</p>\r\n";

					/******** VALIDATION NEW CATEGORY **********/

					// Schritt 3 FORM: Daten validieren
					$errorCategory		= checkInputString($cat_name);
				
					if( $errorCategory ) {
						// Fehlerfall
if(DEBUG)			echo "<p class='debug err'>Das Formular 'Category' enthält noch Fehler!</p>\r\n";

					} else {
						// Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'>Das Formular 'Category' ist formal fehlerfrei. Es wird geprüft, ob eine gleichnamige Kategorie vorhanden ist ...</p>\r\n";
												
						// Schritt 4 FORM: Daten weiterverarbeiten
				
						/********** CHECK NEW CATEGORY **********/
						
						// Schritt 1 DB: DB-Verbindung herstellen
						// Ist bereits geschehen
						
						// Schritt 2 DB: SQL-Statement vorbereiten
						$statement = $pdo->prepare("SELECT COUNT(cat_name) FROM categories
															WHERE cat_name = :ph_cat_name
															");
				
						// Schritt 3 DB: Sql-Statement ausführen
						$statement->execute( array("ph_cat_name" => $cat_name) );
						
if(DEBUG)			if($statement->errorInfo()[2]) echo "<p class='debug err'>" . $statement->errorInfo()[2] . "</p>\r\n";
				
						// Schritt 4 DB: Daten weiterverarbeiten
						$anzahl = $statement->fetchColumn();
if(DEBUG)			echo "<p class='debug'>\$anzahl: $anzahl</p>\r\n";						

						if($anzahl) {
							// Fehlerfall
if(DEBUG)				echo "<p class='debug err'>Die Kategorie '$cat_name' ist bereits in der DB vorhanden!</p>\r\n";							
							$errorCategory = "Die Kategorie '$cat_name' ist bereits vorhanden!";
						} else {
							// Erfoglsfall
if(DEBUG)				echo "<p class='debug ok'>Die Kategorie '$cat_name' ist noch nicht in der DB vorhanden.</p>\r\n";

							/********* SAVE TO DB **********/						
					
							// Schritt 1 DB: DB-Verbindung herstellen
							// ist bereits geschehen
							
							// Schritt 2 DB: SQL-Statement vorbereiten
							$statement = $pdo->prepare("INSERT INTO categories
																	(cat_name)
																	VALUES
																	(:ph_cat_name)
																	");
							
							// Schritt 3 DB: Sql-Statement ausführen
							$statement->execute( array("ph_cat_name"	=> $cat_name) );
if(DEBUG)				if($statement->errorInfo()[2]) echo "<p class='debug err'>" . $statement->errorInfo()[2] . "</p>\r\n";						
							
							// Schritt 4 DB: Daten weiterverarbeiten
							// Bei schreibendem Zugriff: Schreiberfolg prüfen
							$rowCount = $statement->rowCount();
if(DEBUG)				echo "<p class='debug'>\$rowCount: $rowCount</p>\r\n";

							if(!$rowCount) {
								// Fehlerfall
if(DEBUG)					echo "<p class='debug err'>Es wurde kein Datensatz angelegt.</p>\r\n";														
								
							}else {
								// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'>Die Kategorie wurde erfolgreich angelegt.</p>\r\n";							
								$dbMessageCategory = "<p class='success'>Die Kategorie wurde erfolgreich angelegt.</p>";
								
								// Formularfeld leeren
								$cat_name = NULL;
							
							}	// NEW CATEGORY ENTRY END
							
						}	// CHECK NEW CATEGORY END
						
					}	// VALIDATION NEW CATEGORY END
					
				}	// PROCCESS NEW CATEGORY END
				
/****************************************************************************************/
						
				/******* FETCH CATEGORIES FROM DB *******/
				
				// Schritt 1 DB: DB-Verbindung herstellen
				// ist bereits geschehen
				
if(DEBUG) 	echo "<p class='debug'>Kategorien werden aus DB ausgelesen ...</p>\r\n";

				// Schritt 2 DB: SQL-Statement vorbereiten
				$sql = "SELECT * FROM categories ORDER BY cat_name";
				
				$params = NULL;
				
				$statement = $pdo->prepare($sql);
				
				// Schritt 3 DB: Sql-Statement ausführen
				$statement->execute($params);
if(DEBUG)	if($statement->errorInfo()[2]) echo "<p class='debug err'>" . $statement->errorInfo()[2] . "</p>\r\n";

				// Schritt 4 DB: Daten weiterverarbeiten
				$categoriesArray = $statement->fetchAll(PDO::FETCH_ASSOC);
				
/*******************************************************************************/

				/**************************************************/
				/********* PROCCESS FORM NEW BLOG ENTRY ***********/
				/**************************************************/

				// Schritt 1 FORM: Prüfen, ob Daten übergeben wurden
				if( isset($_POST['formsentBlogEntry']) ) {
if(DEBUG)		echo "<p class='debug'>Formular 'New Blog Entry' wurde abgeschickt.</p>\r\n";										
					
					// Schritt 2 FORM: Daten auslesen, entschärfen, DEBUG-Ausgabe
					$cat_Id						= cleanString($_POST['cat_Id']);
					$blog_imageAlignment		= cleanString($_POST['blog_imageAlignment']);
					$blog_headline				= cleanString($_POST['blog_headline']);
					$blog_content				= cleanString($_POST['blog_content']);

if(DEBUG)		echo "<p class='debug'>\$cat_Id: $cat_Id</p>\r\n";
if(DEBUG)		echo "<p class='debug'>\$blog_imageAlignment: $blog_imageAlignment</p>\r\n";
if(DEBUG)		echo "<p class='debug'>\$blog_headline: $blog_headline</p>\r\n";
if(DEBUG)		echo "<p class='debug'>\$blog_content: $blog_content</p>\r\n";


					/********* VALIDATION BLOG ENTRY ***********/

					// Schritt 3 FORM: Daten validieren
					$errorHeadline		= checkInputString($blog_headline);
					$errorContent		= checkInputString($blog_content,4, 10000);
					
					if($errorHeadline || $errorContent) {
						// Fehlerfall
if(DEBUG)			echo "<p class='debug err'>Das Formular 'Blog Entry' enthält noch Fehler!</p>\r\n";
						$errorBlogEntry = "Das Formular ist noch fehlerhaft!";
					} else {
						// Erfoglsfall
if(DEBUG)			echo "<p class='debug ok'>Das Formular 'Blog Entry' ist formal fehlerfrei. Es wird geprüft, ob ein Bildupload vorliegt..</p>\r\n";
					
						// Schritt 4 FORM: Daten weiterverarbeiten
						
						/************* IMAGE UPLOAD **************/

						// Prüfen, ob ein Bild hochgeladen wurde
						if($_FILES['blog_image']['tmp_name']) {
if(DEBUG)				echo "<p class='debug hint'>Bildupload ist aktiv ...</p>\r\n";
							
							// Funktion zum prüfen der Bilddatei aufrufen
							$imageUploadReturnArray = imageUpload($_FILES['blog_image']);
							
							// Prüfen, ob es einen Bilduploadfehler gab
							if($imageUploadReturnArray['imageError']) {
								// Fehlerfall
								$errorImageUpload = $imageUploadReturnArray['imageError'];
							} else {
								// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'>Das Bild wurde erfolgreich auf dem Server gespeichert.</p>\r\n";
							
								$blog_image = $imageUploadReturnArray['imagePath'];
if(DEBUG)					echo "<p class='debug'>\$blog_image: $blog_image</p>\r\n";
							
							}// IMAGE UPLOAD ERROR END
							
						}	// IMAGE UPLOAD END
						
						/****** VALIDATION IMAGE UPLOAD *******/
						
						if($errorImageUpload) {
							// Fehlerfall
if(DEBUG)				echo "<p class='debug err'>Das Formular enthält noch Fehler!</p>\r\n";
							$errorBlogEntry = "Das Formular ist noch fehlerhaft!";																		
						} else {
						
							/************ SAVE TO DB **************/
					
							// Schritt 1 DB: DB-Verbindung herstellen
							// ist bereits geschehen
							
							// Schritt 2 DB: SQL-Statement vorbereiten
							$sql	= "INSERT INTO blogs 
										(blog_headline, blog_image, blog_imageAlignment, blog_content, cat_id, usr_id)
										VALUES
										(:ph_blog_headline, :ph_blog_image, :ph_blog_imageAlignment, :ph_blog_content, :ph_cat_id, :ph_usr_id)
										";
										
							$params	= array(
													"ph_blog_headline"			=> $blog_headline,
													"ph_blog_image"				=> $blog_image,
													"ph_blog_imageAlignment"	=> $blog_imageAlignment,
													"ph_blog_content"				=> $blog_content,
													"ph_cat_id"						=> $cat_Id,
													"ph_usr_id"						=> $_SESSION['usr_id']
													);
							
							$statement = $pdo->prepare($sql);
							
							// Schritt 3 DB: Sql-Statement ausführen
							$statement->execute($params);
if(DEBUG)				if($statement->errorInfo()[2]) echo "<p class='debug err'>" . $statement->errorInfo()[2] . "</p>\r\n";

							// Schritt 4 DB: Daten weiterverarbeiten
							// Bei schreibendem Zugriff: Schreiberfolg prüfen
							$rowCount = $statement->rowCount();
if(DEBUG)				echo "<p class='debug'>\$rowCount: $rowCount</p>\r\n";

							if(!$rowCount) {
								// Fehlerfall
if(DEBUG)					echo "<p class='debug err'>Es wurde kein Datensatz angelegt.</p>\r\n";														
									
							}else {
								// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'>Der Blog-Eintrag wurde erfolgreich angelegt.</p>\r\n";							
									$dbMessageBlog = "<p class='success'>Der Blog-Eintrag wurde erfolgreich angelegt.</p>";
								
								// Formularfelder leeren
								$cat_Id					= NULL;
								$blog_headline			= NULL;
								$blog_imageAlignment	= NULL;
								$blog_content			= NULL;
							
							}	// SAVE TO DB END
							
						}	// VALIDATION IMAGE UPLOAD END
					
					}	// VALIDATION BLOG ENTRY END

						
				}	// PROCCESS BLOG ENTRY END

/************************** PROCCESS BLOG ENTRY END **************************/			
?>
<!doctype html>
<html>
	<head>
		<link rel="stylesheet" href="css/main.css">
		<link rel="stylesheet" href="css/debug.css">
		<meta charset="utf-8">
		<title>PHP-Projekt Blog - Dashboard</title>
	</head>
	<body>
		<!--------------- PAGE HEADER ---------------->
		<header>
			<div class="fright tolock">
				<a class="darkred" href="?action=logout">Logout</a>
				<a class="darkred" href="index.php">zum Frontend</a>
			</div>
			<div class="clearer"></div>
			<!------------- PAGE HEADER END -------------->
			<h1 class="darkred">PHP-Projekt Blog Dashboard</h1>
			<div class="fleft width100">

				<h3 class="darkred width65">Neuen Blog-Eintrag verfassen</h3>

				<h3 class="darkred">Neue Kategorie anlegen</h3>
			</div>
			<div class="clearer"></div>
		</header>
		<br>		
		<!------------- FORM BLOG INPUTS -------------->
		<main>
			<div class="fleft width62 pright">
				<form action="" method="POST" enctype="multipart/form-data">
					<fieldset class="ptop" name="blogEntry">
						<input type="hidden" name="formsentBlogEntry">
						<!------------ CATEGORY INPUT --------------->
						<select name="cat_Id">
							<?php foreach($categoriesArray AS $category): ?>
								<?php if($category['cat_id'] == $cat_Id): // Vorbelegung der Auswahl?> 
									<option value="<?= $cat_Id ?>" selected><?= $category['cat_name'] ?></option>;
								<?php else: ?>
									<option value="<?= $category['cat_id'] ?>"><?= $category['cat_name'] ?></option>;
								<?php endif ?>
							<?php endforeach ?>
						</select>
						<br>
						<!------------ HEADLINE INPUT --------------->
						<span class='error'><?= $errorHeadline ?></span>
						<br>
						<input class="" type="text" name="blog_headline" placeholder="Überschrift" value="<?= $blog_headline // Vorbelegung der Auswahl?>">
						<br>
						<!-------------- IMAGE INPUT ---------------->
						<span class='error'><?= $errorImageUpload ?></span>
						<br>
						<label for="blogImage">Bild hochladen</label>
						<input id="blogImage" type="file" name="blog_image">
						<!------------ IMAGE ALIGNMENT -------------->
						<select name="blog_imageAlignment">
							<?php if($blog_imageAlignment == 'left'): ?>
									<option value="<?= $blog_imageAlignment ?>" selected>align left</option>;
									<option value="right">align right</option>;
								<?php elseif($blog_imageAlignment == 'right'): ?>
									<option value="<?= $blog_imageAlignment ?>" selected>align right</option>;
									<option value="left">align left</option>;
								<?php else: ?>
									<option value="left">align left</option>;
									<option value="right">align right</option>;
							<?php endif ?>
						</select>
						<br>
						<!--------------- TEXT INPUT ---------------->
						<span class='error'><?= $errorContent ?></span>
						<br>
						<textarea class="" name="blog_content" placeholder="Text ..."><?= $blog_content ?></textarea>
						<input class="" type="submit" value="Veröffentlichen">
					</fieldset>
				</form>
				<span class='error'><?= $errorBlogEntry ?></span>
				<?= $dbMessageBlog ?>
			</div>
			<!---------- FORM BLOG INPUTS END ----------->
			
			<!------------- FORM CATEGORY --------------->
			<div>
				<form action="" method="POST">
					<input type="hidden" name="formsentCategory">
					<fieldset name="categoryinput">
						<span class='error'><?= $errorCategory ?></span>
						<br>
						<input class="" type="text" name="cat_name" placeholder="Name der Kategorie" value="<?= $cat_name ?>">
						<input class="" type="submit" value="Neue Kategorie anlegen">
					</fieldset>
				</form>
				<span><?= $dbMessageCategory ?></span>
			</div>
			<div class="clearer"></div>
		</main>
		<!----------- FORM CATEGORY END ------------>
	</body>
</html>