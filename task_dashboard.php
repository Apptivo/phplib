<?php
session_start();
/* ABOUT THIS FILE 
   A minimal task dashboard with lot's of hard-coded items to match my specific preferences.  Maybe later this can be adapted for general usage.
*/
  
// *****START CONFIGURATION*****
	include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'apptivo.config.php');
	$configData = getConfig();

	//Apptivo API credentials
	$api_key = $configData['api_key'];
	$access_key = $configData['access_key'];
	$user_name = $configData['user_name'];
// *****END CONFIGURATION*****

// Initialize the apptivo_toolset object
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'apptivo_toolset.php');
$apptivo = new apptivo_toolset($api_key, $access_key, $user_name);

if($_GET['action'] == 'reschedule_task')
{	


	$taskData = Array (
		'startDate'=> $_GET['endDate'],
		'endDate'=> $_GET['endDate']
	);


	$apptivo->update_task('endDate',$_GET['id'], json_encode($taskData));
	
	$message = 'Task Updated';
}
if ($_GET['action'] == 'sort') {		
	$sortColumn = $_GET['sortColumn'];
	$sortDir = $_GET['sortDir'];
	if($sortColumn == 'priorityName.sortable')
	{
		$sortDir = 'desc';
	}elseif ($sortColumn == 'endDate') {
		$sortDir = 'asc';
	}
}else{
	//Default the sort to priority desc if none found
	if(!$_SESSION['sortColumn'])
	{
		$sortColumn = 'priorityName.sortable';
		$sortDir = 'desc';
	}else{
		$sortColumn = $_SESSION['sortColumn'];
		$sortDir = $_SESSION['sortDir'];
	}
}



$_SESSION['sortColumn'] = $sortColumn;
$_SESSION['sortDir'] = $sortDir;

if($_GET['test'] == 'true')
{
	$taskData = Array (
		'priorityCode'=> null,
		'priorityName' => '',
		'priorityId' => '22125297'
	);

	//"priorityCode":null,"priorityName":"","priorityId":"22125297"


	$apptivo->update_task('priorityCode','38125', json_encode($taskData));
}


//Retrieve my task data
$task_data = $apptivo->get_all_tasks($sortColumn,$sortDir);

//Get the priorities for this firm to render in dropdown below
$task_priorities = $apptivo->get_task_priorities();



?>

<html>
	<head>
	<link rel="stylesheet" type="text/css" media="all" href="task_style.css" />
	<script href="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	</head>
	<body>
		<?php
			/* START Task Dashboard HTML */
			echo('
				<div id="page_cnt">
					<form id="task_form" method="POST"  action="/task_dashboard.php">
					<div class="message">'.$message.'</div>
					<div class="nav">
						<a href="/task_dashboard.php?action=sort&sortColumn=priorityName.sortable">Priority View</a>
						<a href="/task_dashboard.php?action=sort&sortColumn=endDate">Due Date View</a>
					</div>
			');
						
			//Loop through each task
			foreach($task_data->tasks as $ctask)
			{
				//We need to calculate the reschedule defaults to pass in
				$time_today = date("m/d/Y");
				$time_1day = date("m/d/Y",strtotime("+1 day"));
				$time_3day = date("m/d/Y",strtotime("+3 day"));
				$time_7day = date("m/d/Y",strtotime("+7 day"));
				$time_14day = date("m/d/Y",strtotime("+14 day"));
				$time_21day = date("m/d/Y",strtotime("+21 day"));
				
				echo('
					<div class="task_cnt">
						<h2>'.$ctask->subject.'</h2>
						<div class="task_lft">
							<p>'.$ctask->description.'</p>
							<p><strong>Priority: </strong>
							<select value="'.$ctask->priorityName.'" class="priority_dd">
							
								<option value="P1 - Low">P1 - Low</option>
								<option value="P4 - Urgent">P4 - Urgent</option>
							</select>
							
							'.$ctask->priorityName.'</p>
							<p><strong>Due: </strong>'.$ctask->endDate.'</p>
							<p><strong>Created: </strong>'.$ctask->creationDate.'</p>
						</div>
						<div class="task_rgt">
							<h3>Reschedule</h3>
							<p>
								<a href="/task_dashboard.php?action=reschedule_task&id='.$ctask->id.'&endDate='.urlencode($time_today).'">Today</a>
								<a href="/task_dashboard.php?action=reschedule_task&id='.$ctask->id.'&endDate='.urlencode($time_1day).'">1 Day</a>
								<a href="/task_dashboard.php?action=reschedule_task&id='.$ctask->id.'&endDate='.urlencode($time_3day).'">3 Days</a>
								<a href="/task_dashboard.php?action=reschedule_task&id='.$ctask->id.'&endDate='.urlencode($time_7day).'">7 Days</a>
								<a href="/task_dashboard.php?action=reschedule_task&id='.$ctask->id.'&endDate='.urlencode($time_14day).'">14 Days</a>
								<a href="/task_dashboard.php?action=reschedule_task&id='.$ctask->id.'&endDate='.urlencode($time_21day).'">21 Days</a>
							</p>
						</div>
						<div style="clear:both"></div>
					</div>
				');
			}
			
			echo('
				</form>
				</div>
			');
			/* START Task Dashboard HTML */
		?>
		<script>
		$(function() {
			$('.priority_dd').change(function() {
				this.form.submit();
			});
		});
		</script>
	</body>
</html>

