<?php

class UKtneUtils
{

/*

	Class: UKtneUtils v0.2.1
	http://github.com/g1smd/uktne-utils


	Copyright (c) 2011 Ian Galpin.

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.

*/



	/**
	 * Configuration
	 * - optionally set these values with $object->var_name = 'value';
	 */

	# Status wording for not designated entries
	var $NoStatusText = '/Not\ Designated|Protected|Unusable/';

	# Area code wording for unassigned entries
	var $unassigned = '/Geographic - unassigned/';

	# Pattern for 'X Digit Area Code' wording
	var $dareacode = '/([2345]) Digit Area Code/';

	# Pattern for 'ELNS' area wording
	var $elnstext = '/ELNS/';

	# Pattern for 'Mixed' area wording
	var $mixedtext = '/Mixed/';

	# Pattern for 'Director' area wording
	var $directortext = '/Director/';

	# Pattern for 'protected' usage
	var $expansiontext = '/([Ee](xpansion|mergency Services))/';

	# Pattern for 'National Dialling' ranges
	var $nationaldiallingtext = '/(National [Dd]ialling)/';



	/**
	 * Internal variables
	 */

	# Column titles expected in parsed CSV data
	var $titlesexpected = array(
		0 => 'SABC',
		1 => 'D',
		2 => 'Status',
		3 => 'Use',
		4 => 'Code Length',
		5 => 'Area Type',
		6 => 'Notes',
		7 => 'Date',
		8 => 'Changes'
	);

	# Column titles found in parsed CSV data
	var $titlesn = array();

	# Two dimensional array of parsed CSV data
	var $datan = array();

	# Column titles for new CSV data
	var $titlesnew = array(
		0 => 'Code',
		1 => 'Location',
		2 => 'Local',
	);

	# Two dimensional array of new CSV data
	var $datanew = array();



	/**
	 * Constructor UKtneUtils
	 * @param  titlesn  array  parsed column titles
	 * @param  datan    array  parsed data
	 * @return nothing
	 */
	function UKtneUtils($titlesn = null, $datan = null)
	{
		if ( $titlesn !== null ) $this->titlesn = $titlesn;
		if ( $datan !== null ) $this->datan = $datan;

		# Check column titles are OK
		if ($this->titlesn !== $this->titlesexpected)
		{
			print "Error in column titles. Check original file for unexpected format changes.";
		}
		else
		{
			# Do this thing
			$this->fixup();
		}
	}



	/**
	 * Function fixup
	 * @param  none
	 * @return nothing
	 */
	function fixup()
	{
		# Initialise loop
		$keynew = 0;


		# Process each record in turn...
		foreach ($this->datan as $key => $row)
		{
			# ...initialise variables...
			$this->linecodeSABCDcurrent = $this->datan[$key]["SABC"] . $this->datan[$key]["D"];
			$this->isNoStatus = (preg_match($this->NoStatusText, $this->datan[$key]["Status"]));
			$this->isNationalDialling = (preg_match($this->nationaldiallingtext, $this->datan[$key]["Use"]));
			$this->isExpansion = (preg_match($this->expansiontext, $this->datan[$key]["Use"]));
			$this->isMixed = (preg_match($this->mixedtext, $this->datan[$key]["Area Type"]));
			$this->isELNS = (preg_match($this->elnstext, $this->datan[$key]["Area Type"]));
			$this->isDirector = (preg_match($this->directortext, $this->datan[$key]["Area Type"]));

			# Process only those records where SABC is full 4 digits...
			if (strlen($this->datan[$key]["SABC"]) == "4")
			{
				# ... and only for codes with a location...
				if (($this->isNoStatus !== false) && ($this->datan[$key]["Code Length"] !== ""))
				{
					# ...find length of all digits, length of area code...
					$this->combinedlength = strlen($this->linecodeSABCDcurrent);
					preg_match($this->dareacode, $this->datan[$key]["Code Length"], $matches1);
					$this->areacodelength = $matches1[1];

					# ...if area code length not defined, assume 4 digits...
					if ($this->areacodelength < "2")
					{
						$this->areacodelength = "4";
					}
					# ...and find length of local number part...
					$this->localpartlength = $this->combinedlength - $this->areacodelength;

					# ...find area code, location and local number part...
					$this->newcode = "0" . (substr($this->linecodeSABCDcurrent,0,$this->areacodelength));
					$this->newlocation = $this->datan[$key]["Use"];
					$this->newlocal = (substr($this->linecodeSABCDcurrent,$this->areacodelength,$this->localpartlength));

					# ...retrieve previous area code and previous location
					$this->newcodeprev = ($this->datanew[$keynew-1]["Code"]);
					$this->newlocationprev = ($this->datanew[$keynew-1]["Location"]);


					# Process area code only if it is not 'unassigned' and not 'expansion'...
					if (($this->isNationalDialling == false) && ($this->isExpansion == false))
					{
						# Dump repeat entries in areas that are not ELNS or Mixed...
						if (($this->newcode === $this->newcodeprev) && ($this->newlocation === $this->newlocationprev) && (!(($this->isMixed == true) || ($this->isELNS == true))))
						{
							# Clear local number part for this area code (on previous entry)
							$this->datanew[$keynew-1]["Local"] = "";
						}
						else
						{
							# Copy data to new array
							$this->datanew[$keynew]["Code"] = $this->newcode;
							$this->datanew[$keynew]["Location"] = $this->newlocation;
							$this->datanew[$keynew]["Local"] = $this->newlocal;

							# Increment pointer ready for next record in new array
							$keynew++;
						}
					}
				}
			}
		}
	}
}
?>