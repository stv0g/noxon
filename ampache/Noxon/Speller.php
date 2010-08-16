<?php
/*
 * Speller
 * 
 * General Speller class for use on small display devices
 * 
 * See the NoxonServer Project Documentation for further infos
 *
 * @author Manfred Dreese / TTEG
 */
class Speller {	
	/**
	 * Integer.
	 * Is set during the construction and represents the number
	 * or items the speller terminates below.
	 */
	private $criticalMass;
	// private $strAlphaScope;
	
	/** Filters for next Speller run */
	private $strNextRunElements;
	/** Input Data Subset, filtered by Speller imputdata */
	private $strFilterSubset;
	
	/*
	 * Construction
	 */
	function Speller ($inCriticalMass) {
		$this->criticalMass = $inCriticalMass;
	}
	
	/*
	 *	IsBelowCriticalMass
	 * 
	 *  Populates the Search Index and starts the Speller
	 * 
	 * Parametres:
	 * $inStrFilter		: Current Filter Text ("A".."JEA")
	 * $inData 			: Reference to Array with Data for the speller
	 * $strAlphaIndex 	: Alpha-Index Column of the Array for Sorting
	 * 
	 * Returns:
	 * (bool) 	TRUE : 	filtered resultset size is below critical mass,
	 * 			FALSE: 	more items than critical mass.
	 * 					One more run should be made in application logic
	 * 
	 * Post-Run Actions in Application Logic :
	 * getNextRunElements : Returns the Stringlets for the Next Run
	 * 						i.E. A -> AB, AC, AX
	 * 
	 * getFilteredSubset :	Returns a copy of the $inData with only
	 * 						the entries that match the filter.
	 * 						Should be used if result below critical mass
	 */
	function isBelowCriticalMass ($inStrFilter, &$inData, $strAlphaIndex) {
		if (isset($inStrFilter)) {
			$ipos = strlen($inStrFilter)+1;
			// Output Items
			$this->strFilterSubset =	Array();
			// Speller next Run Items
			$this->strNextRunElements = Array();
			$inStrFilter = strtoupper($inStrFilter);

			// Generate Wordbook with current Input Filter

			$this->strAlphaItems = Array();
			foreach ($inData as $arrayIndex => $data) {
				$strSpellerLength = trim(strtoupper(substr($data[$strAlphaIndex],0,$ipos)));
				if (substr($strSpellerLength,0,$ipos-1) 		== $inStrFilter) {
					$this->strNextRunElements[$strSpellerLength] = true;
					$this->strFilterSubset[$arrayIndex] 		= $data;
				}
			}
			
			// Is result below critical mass ?
			if (sizeof($this->strFilterSubset) <= $this->criticalMass) {
				// Future: Try to recurse into getSpellerData to look if we can go deeper
				return true;
			} // fi
			else {
				// Still more possibilities than wanted, return input filters for next run.
				// Sort NextElements Array only here to increase speed
				ksort($this->strNextRunElements);
				return false;
			}
		}
	}
	
	function getNextRunElements() {
		return ($this->strNextRunElements);
	}
	
	function getFilteredSubset() {
		return ($this->strFilterSubset);
	}	
} // Speller

?>