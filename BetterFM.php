<?php 

require_once('FileMaker.php');

// FM's LTE and GTE signs...weird bunch of people
define('BFM_NOT_EQUAL',		'≠');
define('BFM_LESS_THAN',		'≤');
define('BFM_GREATER_THAN',	'≥');

class BetterFM {

	// This PHP class provides a slicker interface to the default FileMaker class.
	// Because the one that ships with the godforsaken database server just sucks.
	//
	// As a precautionary tale, try optimizing this page.
	//
	// Then increase the sum of wasted hours below since you got lost in all that
	// disgusting code that is the Filemaker API, so the next guy who works here
	// understands the pain.
	//
	// hours_wasted = 23.4
	//
	// Have fun!
	// PS: To make your life easier part of this is derived from http://bit.ly/wIzfRP
	// But that code is a little too much.
	//
	// This class needs to be initiated for EACH CONNECTION of the server
	//
	// @author		Chester Li
	// @version		0.1b
	//
	
	protected	$host	= '';		// IP or hostname of FileMaker Server Host
	protected	$user	= null;		// If using Guest leave this blank
	protected	$pass	= null;		// If Guest leave blank
	
	private		$fmo;		// The internal FM object
	private 	$cl;		// Current layout name for ease of use...
	
	public		$luo; 		// The last used object
	
	
	// Constructor object populates the FileMaker object
	public function __construct($db) {
		$this->fmo = new FileMaker($db, $this->host, $this->user, $this->pass);
		$this->luo = $this->fmo;
	}
	
	
	/*
	 * Sets the current layout so we don't have to keep passing it
	 *
	 * @param		string	$layout_name
	 */
	public function setLayout($layout_name) {
		$this->cl = $layout_name;
	}
	
	public function getHostAddr() {
		return $this->host;
	}
	
	
	/*
	 * Gets the current layout name
	 *
	 * @return		string	$layout_name
	 */
	public function getLayout() {
		return $this->cl;
	}
	
	
	/*
	 * Selection query
	 *
	 * @param		array	$filter	Find criterion ("where X = Y")
	 * @param		array	$order	Sort order (first to last)
	 * @param		bool	$peaceful_death	Specifies whether to issue a die() or a return false for future getErrors() use
	 * @return		array	Returns the result object (also saved to ::$luo)
	 */
	public function selectData($filter, $order, $return_array) {
		$f = $this->fmo->newFindCommand($this->cl);
		
		// Find criterion
		foreach($filter as $criterion => $value) {
			$f->addFindCriterion($criterion, $value);
		}
		
		// Order criterion
		$true_order = array_reverse($order);
		foreach($true_order as $order => $type) {
			$f->addSortRule($order, 1, (($type == 'ascending') ? FILEMAKER_SORT_ASCEND : FILEMAKER_SORT_DESCEND));
		}
		
		$results = $f->execute();
		$this->luo = $results;
		
		if (!$this->isError()) {
			$fields = $results->getFields();
			$records = $results->getRecords();
			
			//Loops through the records retrieved
			$i = 0;
			foreach ($records as $record ) {
				foreach ( $fields as $field ) {
					if (in_array($field, $return_array)) $arrOut[$i][$field]     = $record->getField( $field );
				}
				$i++;
			}
		} else {
			$arrOut['errorCode'] = $this->isError();
		}
		
		return $arrOut;
	}
	
	/*
	 * Get errors
	 *
	 * @return		array	Returns the message of an error, or false if no error
	 */
	public function isError($o = NULL) {
		$o = (!$o ? $this->luo : $o);
		if (FileMaker::isError($o)) {
			return $o->getMessage();
		} else {
			return false;
		}
	}

}

?>