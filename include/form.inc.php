<?php
/**************************************************************************************************/


					/**
					*
					* Entschärft und säubert einen String, falls er einen Wert besitzt.
					* Falls der String keinen Wert besitzt (NULL, "", 0, false), wird er 
					* 1:1 zurückgegeben.
					*
					* @param String $value 	- Der zu entschärfende und zu bereinigende String
					*
					* @return String 			- Originalwert oder der entschärfte und bereinigte String
					*
					*/
					function cleanString($value) {
if(DEBUG_F)			echo "<p class='debugCleanString'>Aufruf cleanString('$value')</p>\r\n";	
					
						// htmlspecialchars() entschärft HTML-Steuerzeichen wie < > & '' ""
						// und ersetzt sie durch &lt;, &gt;, &amp;, &apos; &quot;
						// ENT_QUOTES | ENT_HTML5 ersetzt zusätzlich ' durch &#039;
						$value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5);
						
						// trim() entfernt am Anfang und am Ende eines Strings alle 
						// sog. Whitespaces (Leerzeichen, Tabulatoren, Zeilenumbrüche)
						$value = trim($value);
					
						// Damit cleanString() nicht NULL-Werte in Leerstings verändert, wird 
						// geprüft, ob $value überhaupt einen gültigen Wert besitzt
						if(!$value) {
							$value = NULL;
						}
					
						return $value;
					}


/**************************************************************************************************/


					/**
					*
					* Prüft einen String auf Leerstring, Mindest- und Maxmimallänge
					*
					* @param String $value - Der zu prüfende String
					* @param [Integer $minLength=MIN_INPUT_LENGTH] - Die erforderliche Mindestlänge
					* @param [Integer $maxLength=MAX_INPUT_LENGTH] - Die erlaubte Maximallänge
					*
					* @return String/NULL - Ein String bei Fehler, ansonsten NULL
					* 
					*/
					
					function checkInputString($value, $minLength = MIN_INPUT_LENGTH, $maxLength = MAX_INPUT_LENGTH) {
if(DEBUG_F)			echo "<p class='debugCheckInputString'>Aufruf checkInputString('$value')</p>\r\n";					
					
						$errorMessage = NULL;
						
						// Prüfen auf leeres Feld
						if(!$value) {
							$errorMessage = "Dies ist ein Pflichtfeld!";
							
						// Prüfen auf Mindestlänge
						} elseif(mb_strlen($value) < $minLength) {
							$errorMessage = "Muss mindestens $minLength Zeichen lang sein!";
							
						// Prüfen auf Maximallänge
						} elseif(mb_strlen($value) > $maxLength) {
							$errorMessage = "Darf maximal $maxLength Zeichen lang sein!";
						} 
					
						return $errorMessage;
					}


/**************************************************************************************************/


					/**
					*
					* Prüft eine Email-Adresse auf Leerstring und Validität
					*
					* @param String $value - Die zu prüfende Email-Adresse
					*
					* @return String/NULL - Ein String bei Fehler, ansonsten NULL
					*
					*/
					function checkEmail($value) {
if(DEBUG_F)			echo "<p class='debugCheckEmail'>Aufruf checkEmail($value)</p>\r\n";

						$errorMessage = NULL;
						
						// Prüfen auf leeres Feld
						if(!$value) {
							$errorMessage = "Dies idt ein Pflichtfeld!";
							
						// Email auf Validität prüfen
						} elseif( !filter_var($value, FILTER_VALIDATE_EMAIL) ) {
							$errorMessage = "Dies ist keine gültige Email-Adresse!";
						}
						return $errorMessage;
					}

/**************************************************************************************************/

					/**
					*
					* Prüft ein hochgeladenes Bild auf MIME-Type, Datei- und Bildgröße
					* Speichert das erfolgreich geprüfte Bild unter einem zufällig generierten, einmaligen Dateinamen
					*
					* @param Array $uploadedImage												- Die Bildinformationen aus $_FILES
					* @param [Int $maxWidth = IMAGE_MAX_WIDTH]							- Die maximal erlaubte Bildbreite in PX
					* @param [Int $maxHeight = IMAGE_MAX_HEIGHT]							- Die maximal erlaubte Bildhöhe in PX
					* @param [Int $maxSize = IMAGE_MAX_SIZE]								- Die maximal erlaubte Dateigröße in Bytes
					* @param [Array $allowedMimeTypes = IMAGE_ALLOWED_MIMETYPES]	- Whitelist der erlaubten MIME-Types
					* @param [String $uploadPath = IMAGE_UPLOAD_PATH]					- Das Speicherverzeichnis auf dem Server
					*
					* @return Array { "imageError" => String/NULL						- Fehlermeldung im Fehlerfall, 
					* "imagePath" => String														- Der Speicherpfad auf dem Server }
					*
					*/
					function imageUpload($uploadedImage,
												$maxWidth			= IMAGE_MAX_WIDTH,
												$maxHeight			= IMAGE_MAX_HEIGHT,
												$maxSize				= IMAGE_MAX_SIZE,
												$allowedMimeType	= IMAGE_ALLOWED_MIMETYPES,
												$uploadPath			= IMAGE_UPLOAD_PATH
												) {
if(DEBUG_F)			echo "<p class='debugimageUpload'>Aufruf imageUpload()</p>\r\n";					
					
if(DEBUG)			echo "<pre class='debug'>\r\n";					
if(DEBUG)			print_r($uploadedImage);					
if(DEBUG)			echo "</pre>\r\n";	
					
						/*
							Das Array $_FILES['avatar'] bzw. $uploadedImage enthält:
							Den Dateinamen [name]
							Den generierten (also ungeprüften) MIME-Type [type]
							Den temporären Pfad auf dem Server [tmp_name]
							Die Dateigröße in Bytes [size]
						*/
						
						/*********** BILDINFORMATIONEN SAMMELN ***********/
						
						// Dateiname
						$fileName = $uploadedImage['name'];
						// ggf. Leerzeichen durch "_" ersetzen
						$fileName = str_replace(" ", "_", $fileName);
						// Dateinamen in Kleinbuchstaben umwandeln
						$fileName = mb_strtolower($fileName);
						// Umlaute ersetzen
						$fileName = str_replace( array("ä", "ö", "ü", "ß"), array("ae", "oe", "ue", "ss"), $fileName );
						
						// zufälligen Dateinamen generieren
						$randomPrefix = rand(1, 999999) . str_shuffle("abcdefghijklmnopqrstuvwxyz") . time();
						$fileTarget = $uploadPath . $randomPrefix . "_" . $fileName;
						
						// Dateigröße
						$fileSize = $uploadedImage['size'];
						
						// Temporärer Pfad auf dem Server
						$fileTemp = $uploadedImage['tmp_name'];
						
if(DEBUG_F)			echo "<p class='debugimageUpload'>\$fileName: $fileName</p>\r\n";				
if(DEBUG_F)			echo "<p class='debugimageUpload'>\$fileSize: " . round($fileSize/1024, 2) . "kB</p>\r\n";					
if(DEBUG_F)			echo "<p class='debugimageUpload'>\$fileTemp: $fileTemp</p>\r\n";					

						// Genauere Informationen zum Bild holen
						$imageData = @getimagesize($fileTemp);
					
						/*
							Die Funktion getimagesize() liefert bei gültigen Bildern ein Array zurück:
							Die Bildbreite in PX [0]
							Die Bildhöhe in PX [1]
							Einen für die HTML-Ausgabe vorbereiteten String für das IMG-Tag
							(width="480" height="532") [3]
							Die Anzahl der Bits pro Kanal ['bits']
							Die Anzahl der Farbkanäle (somit auch das Farbmodell: RGB=3, CMYK=4) ['channels']
							Den echten(!) MIME-Type ['mime']
						*/
						
						$imageWidth		= $imageData[0];
						$imageHeight	= $imageData[1];
						$imageMimeType	= $imageData['mime'];
if(DEBUG_F)			echo "<p class='debugimageUpload'>\$imageWidth: $imageWidth</p>\r\n";				
if(DEBUG_F)			echo "<p class='debugimageUpload'>\$imageHeight: $imageHeight</p>\r\n";					
if(DEBUG_F)			echo "<p class='debugimageUpload'>\$imageMimeType: $imageMimeType</p>\r\n";	
						
						/*************** BILD PRÜFEN ***************/
					
						// MIME-Type prüfen
						// Whitelist mit erlaubten Bildtypen
						$allowedMimeTypes = $allowedMimeType;
						
						if( !in_array($imageMimeType ,$allowedMimeTypes) ) {
							$errorImage = "Dies ist kein gültiger Bildtyp!";
							// Maximal erlaubte Bildhöhe
						} elseif($imageHeight > $maxHeight) {
							$errorImage = "Die Bildhöhe darf maximal $maxHeight Pixel betragen!";

							// Maximal erlaubte Bildbreite
						} elseif($imageWidth > $maxWidth) {
							$errorImage = "Die Bildbreite darf maximal 4maxWidth Pixel betragen!";

							// Maximal erlaubte Dateigröße
						} elseif($fileSize > $maxSize) {
							$errorImage = "Die Dateigröße darf " . round($maxSize/1024, 2) . "nicht überschreiten!";
							
							// Wenn es keinen Fehler gab
						} else {
							$errorImage = NULL;
						}
					
						/******* ABSCHLIESSENDE BILDPRÜFUNG *******/
						if($errorImage) {
							// Fehlerfall
if(DEBUG)				echo "<p class='debugimageUpload err'>$errorImage</p>\r\n";							
						} else {
							// Erfolgsfal
if(DEBUG)				echo "<p class='debugimageUpload ok'>Die Bildprüfung ergab keine Fehler.</p>\r\n";
							
							/************ BILD SPEICHERN ************/
							
							if(!@move_uploaded_file($fileTemp, $fileTarget) ) {
								// Fehlerfall
if(DEBUG)					echo "<p class='debugimageUpload err'>Fehler beim Speichern des Bildes auf dem Server!</p>\r\n";
								$errorMessage = "Fehler beim Speichern des Bildes! Bitte versuchen Sie es später noch einmal.";
							} else {
								// Erfolgsfall
if(DEBUG)					echo "<p class='debugimageUpload ok'>Das Bild wurde erfolgreich auf dem Server gespeichert.</p>\r\n";
							
							}	// BILD SPEICHERN ENDE
						
						}	// ABSCHLIESSENDE BILDPRÜFUNG ENDE
						
					/******* FEHLERMELDUNG ZURÜCKGEBEN *******/	
					
					return array("imageError" => $errorImage, "imagePath" => $fileTarget);
						
					}	// IMAGE UPLOAD ENDE







/**************************************************************************************************/

?>