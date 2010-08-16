<?php
/**
 * Q&E PHP Internationalization
 *
 * @author Manfred Dreese / TTEG
 */
class Internat {
	/**
	 * Static String Table
	 */
	static $stbl=Array("artistalbums" 	
						=> Array(	"EN"=>	"Artist/Albums",
									"DE"=>	"Interpret/Alben",
									"FR"=>	"Artiste/Album",
									"NL"=>	"Artiest/Album",
									"IT"=>	"Artista/Album",
									"ES"=>	"Artista/Album" ),
						"artistssearch"
						=> Array(	"EN"=>	"Artists (Search)",
									"DE"=>	"Interpret (Suche) ",
									"FR"=>	"Artistes (Chercher)",
									"NL"=>	"Artiest (Zoek)",
									"IT"=>	"Artista (Cerca)",
									"ES"=>	"Artista (búsqueda)" ),
						"genres"
						=> Array(	"EN"=>	"Genres",
									"DE"=>	"Musikrichtungen",
									"FR"=>	"Genres",
									"NL"=>	"Genres",
									"IT"=>	"Genere",
									"ES"=>	"Genero" ),
						"playlists"
						=> Array(	"EN"=>	"Playlists",
									"DE"=>	"Wiedergabelisten",
									"FR"=>	"Listes de lecture",
									"NL"=>	"Playlists",
									"IT"=>	"Playlist",
									"ES"=>	"Lista de reproducción" ),
						"similar_artists"
						=> Array(	"EN"=>	"Similar Artists",
									"DE"=>	"Ähnliche Interpreten",
									"FR"=>	"Interprètes semblables",
									"NL"=>	"Vergelijkbare Artiesten",
									"IT"=>	"Artisti simili",
									"ES"=>	"Artistas similares" ),
						"similar_titles"
						=> Array(	"EN"=>	"Similar Titles",
									"DE"=>	"Ähnliche Titel",
									"FR"=>	"Titres semblables",
									"NL"=>	"Vergelijkbare Titels",
									"IT"=>	"Titoli simili",
									"ES"=>	"Títulos similares" )
									
									);
	
	/**
	 * Substitutes various Country Codes to the ones used in this class
	 *
	 * @param string $rhs
	 * @return string
	 */					
	static function substituteCountryCode ($rhs="") {
		switch (strtolower($rhs)) {
			case "49":
			case "ger":
			case "deu":
				return "DE";
				break;
				
			case "01":
			case "44":
			case "eng":
				return "EN";
				break;
			
			case "33":
			case "fre":
			case "fra":
				return "FR";
				break;
			
			case "31":
			case "nld":
			case "dut":
				return "NL";
				break;
				
			case "ita":
			case "itl":
				return "IT";
				break;
				
			case "esp":
			case "spa":
			case "spn":
			case "cas":
			case "cat":
				return "ES";
				break;
			default:
				return $rhs;
		}
	}
									
	/**
	 * Request internationalized String
	 * 
	 * If language is not found, english will be used as default.
	 * 
	 * input : 	$inLang = Target Language
	 * 			$inIndex = Target Item
	 */
	static function getString($inLang, $inIndex) {
		$inLang = strtoupper($inLang);
		if (isset(Internat::$stbl[$inIndex])) {
			if (isset(Internat::$stbl[$inIndex][$inLang])
			 && Internat::$stbl[$inIndex][$inLang]!="" ) {
				return Internat::$stbl[$inIndex][$inLang];
			}
			else return Internat::$stbl[$inIndex]["EN"];
		}
		else return "...";
	}
	
}

?>