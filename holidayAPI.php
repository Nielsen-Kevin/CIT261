<?php
class HolidaysApi {
	private $year;
	private $month;
	public $output = array();
	
	function __construct($year = NULL, $month = NULL) {
		date_default_timezone_set('America/Los_Angeles');
		
		$this->year = is_null($year) ? date("Y") : (int) $year;
		$this->month = (!is_null($month)) ? (int) $month : false;
		
		if($this->year < 1970 || $this->year > 2038) {
			$this->output[] = $this->_error('invalid year was used, must be between 1970-2038');
			return;//end api
		}
		
		//Run
		if($this->month) {
			//Get Holidays for the Month
			$this->_byMonth($month);
		} else {
			//Get Holidays for the year
			$this->_byYear();
		}
	}

	private function _error($message) { 
		return array(
			'error' => $message
		);
	}

	private function _set($timestamp, $name) { 
		return array(
			'name' =>  $name,
			'day' => date('j', $timestamp), 	// 1 to 31
			'month' => date('n', $timestamp), 	// 1 to 12
			'year' => date('Y', $timestamp), 	// full 4 digits
			'weekday' => date('l', $timestamp), // day of the week
			'time' => $timestamp
		);
	}

	private function _byYear() {		
		for ($m = 1; $m <= 12; $m++) {
			$this->_byMonth($m);
		}
	}
	
	private function _byMonth($month) {
		if($month == 3 || $month == 4) {
			$easterThisMonth = 0;
			if($this->year > 1969 && $this->year < 2038) {
				$days = @easter_days($this->year);//This function is only valid for years between 1970 and 2037
				$easter = strtotime($this->year . '-03-21 +' . $days .' days');
				$easterThisMonth = date('n', $easter);
			}
		}
		switch ($month) {
			case 1: # January
				$this->output[] = $this->_set(mktime(0, 0, 0, 1, 1, $this->year), 'New Year\'s Day'); //fixed day
				$this->output[] = $this->_set(strtotime('Third Monday January ' . $this->year), 'Martin Luther King Jr. Day'); //3rd Monday of January
				break;
				
			case 2: # February				
				$this->output[] = $this->_set(mktime(0, 0, 0, 2, 14, $this->year), 'Valentine\'s Day'); //fixed day
				$this->output[] = $this->_set(strtotime('Third Monday February ' . $this->year), 'Presidents\' Day'); //3rd Monday of February
				break;
			case 3: # March
				$this->output[] = $this->_set(mktime(0, 0, 0, 3, 17, $this->year), 'St. Patrick\'s Day'); //fixed day

				if($easterThisMonth == 3) {
					$this->output[] = $this->_set($easter, 'Easter'); //easter function
				}
				break;
			case 4: # April
				$this->output[] = $this->_set(mktime(0, 0, 0, 4, 15, $this->year), 'Tax Day'); //fixed day

				if($easterThisMonth == 4) {
					$this->output[] = $this->_set($easter, 'Easter'); //easter_date function
				}
				break;
			case 5: # May
				$this->output[] = $this->_set(mktime(0, 0, 0, 5, 5, $this->year), 'Cinco de Mayo'); //fixed day
				$this->output[] = $this->_set(strtotime('second Sunday May ' . $this->year), 'Mother\'s Day'); //Second Sunday of May
				$this->output[] = $this->_set(strtotime('Fourth Monday May ' . $this->year), 'Memorial Day'); //Fourth Monday of May
				break;
			
			case 6: # June
				$this->output[] = $this->_set(strtotime('Third Sunday June ' . $this->year), 'Father\'s Day'); //3rd Sunday of June
				break;
			
			case 7: # July
				$this->output[] = $this->_set(mktime(0, 0, 0, 7, 4, $this->year), 'Independence Day'); //fixed day
				break;
			case 8: # August
				//No Holidays
				break;
			case 9: # September
				$this->output[] = $this->_set(strtotime('First monday September ' . $this->year), 'Labor Day'); //1st Monday of September
				break;
			case 10: # October
				$this->output[] = $this->_set(strtotime('Second Monday October ' . $this->year), 'Columbus Day'); //2nd Monday of October
				$this->output[] = $this->_set(mktime(0, 0, 0, 10, 31, $this->year), 'Halloween'); //fixed day
				break;
			case 11: # November
				$this->output[] = $this->_set(mktime(0, 0, 0, 11, 11, $this->year), 'Veterans Day'); //fixed day
				$this->output[] = $this->_set(strtotime('Fourth Thursday November ' . $this->year), 'Thanksgiving Day'); //4th Thursday of November
				$this->output[] = $this->_set(strtotime('Fourth Friday November ' . $this->year), 'Black Friday'); //4th Friday of November
				break;
			case 12: # December
				$this->output[] = $this->_set(mktime(0, 0, 0, 12, 24, $this->year), 'Christmas Eve'); //fixed day
				$this->output[] = $this->_set(mktime(0, 0, 0, 12, 25, $this->year), 'Christmas Day'); //fixed day
				$this->output[] = $this->_set(mktime(0, 0, 0, 12, 31, $this->year), 'New Year\'s Eve'); //fixed day
				break;
			default:
				$this->output[] = $this->_error('invalid month was used');
		}
	}
}

$year = (isset($_GET['y'])) ? (int) $_GET['y'] : NULL;
$month = (isset($_GET['m'])) ? (int) $_GET['m'] : NULL;
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
$holidays = new HolidaysApi($year, $month);
echo json_encode( $holidays->output );
