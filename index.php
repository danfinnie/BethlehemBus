<?php

date_default_timezone_set('America/New_York');

class TimeOfDay {
	private $hour;
	private $min;
	private $ampm;

	function __construct($str) {
		// var_dump($str);
		$matches = null;
		preg_match('/^(\d\d?)(\d\d)(a|p)$/', $str, $matches);
		$this->hour = intval($matches[1]);
		$this->min = intval($matches[2]);
		$this->ampm = $matches[3] . "m";
	}

	// Returns true if the next instance of this TimeOfDay occurs before $other.
	function compareNextOccurence($other) {
		$me = $this->getNextOccurence();
		$friend = $other->getNextOccurence();

		if ($me < $friend) {
			return -1;
		} else if ($me === $friend) {
			return 0;
		} else {
			return 1;
		}
	}

	private function getNextOccurence() {
		$today = strtotime($this->getHumanReadable());

		if($today < time()) {
			return strtotime("+1 day", $today);
		} else {
			return $today;
		}
	}

	private function getHumanReadable() {
		return sprintf('%d:%02d%s', $this->hour, $this->min, $this->ampm);
	}

	function __toString() {
		return $this->getHumanReadable();
	}
}

$data = json_decode(file_get_contents("data.json"));
$data = $data->westbound;

$data = array_map(function($entry) {
	$entry->departureTime = new TimeOfDay($entry->departureTime);
	$entry->arrivalTime = new TimeOfDay($entry->arrivalTime);
	return $entry;
}, $data);

usort($data, function($a, $b) {
	$a = $a->departureTime;
	$b = $b->departureTime;

	return $a->compareNextOccurence($b);
});

for ($i = 0; $i < 2; $i++) {
	array_unshift($data, array_pop($data));
}

?><!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Bethlehem Buses</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
        <script src="js/vendor/modernizr-2.6.2.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <h1>Upcoming Departures</h1>
        <table id="sorted-departures">
        	<thead>
        		<tr>
    				<td>Company</td>
        			<td>Departure Time</td>
        			<td>Departure Location</td>
        			<td>Arrival Time</td>
        			<td>Arrival Location</td>
        		</tr>
        	</thead>
        	<tbody>
	        	<?php foreach($data as $i=>$row): ?>
	        		<tr class="<?php echo $i < 2 ? "past" : ""?>">
	        			<td><?= $row->company ?></td>
	        			<td><?= $row->departureTime ?></td>
	        			<td><?= $row->departureLocation ?></td>
	        			<td><?= $row->arrivalTime ?></td>
	        			<td><?= $row->arrivalLocation ?></td>
	        		</tr>
		        <?php endforeach; ?>
		    </tbody>
	    </table>

	    <p>
		    <img src="img/github.png" />
		    <a href="https://github.com/danfinnie/BethlehemBus">
			    View on GitHub
			</a>
		</p>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.8.2.min.js"><\/script>')</script>
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>
    </body>
</html>
