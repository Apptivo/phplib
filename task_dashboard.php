<?php
session_start();
/* ABOUT THIS FILE 
   Todd Task!
*/
  
// *****START CONFIGURATION*****
	//Supply the API & Access keys for your Apptivo account
	
	$api_key = 'cb83cbc3-7efc-4457-9beb-a72871187cea'; // Replace this with your business api key
	$access_key = 'grxPZSZKvEtB-eIArCNDnLNXl-0910a13e-651b-4e63-8175-86cb8f243b2a';  //Replace this with your business access key
// *****END CONFIGURATION*****

// Initialize the apptivo_toolset object
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'apptivo_toolset.php');
$apptivo = new apptivo_toolset($api_key, $access_key);

if($_GET['action'] == 'reschedule_task')
{	
	$taskData = Array (
		'endDate'=> $_GET['endDate']
	);

	$apptivo->update_task('endDate',$_GET['id'], $taskData);
	
	$message = 'Task Updated';
} elseif ($_GET['action'] == 'sort')
{		
	if($_SESSION['sortColumn'] == $_GET['sortColumn'])
	{
		if($_SESSION['sortDir'] == 'desc')
		{
			$sortDir = 'asc';
		}else{
			$sortDir = 'desc';
		}
	}else{
		$sortColumn = $_GET['sortColumn'];
		$sortDir = $_GET['sortDir'];
	}
}

//Default the sort to priority desc if none found
if($_SESSION['sortColumn'] == '' && $sortDir == '')
{
	$sortColumn = 'priorityName.sortable';
	$sortDir = 'desc';
}

$_SESSION['sortColumn'] = $sortColumn;
$_SESSION['sortDir'] = $sortDir;

//Retrieve my task data
$task_data = $apptivo->get_all_tasks($sortColumn,$sortDir);
?>

<html>
	<head>
	<link rel="stylesheet" type="text/css" media="all" href="task_style.css" />
	</head>
	<body>
		<?php
			/* START Task Dashboard HTML */
			echo('
				<div id="page_cnt">
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
				$time_1day = date("m/d/Y",strtotime("+1 day", strtotime($ctask->endDate)));
				$time_7day = date("m/d/Y",strtotime("+7 day", strtotime($ctask->endDate)));
				$time_21day = date("m/d/Y",strtotime("+21 day", strtotime($ctask->endDate)));
				
				echo('
					<div class="task_cnt">
						<h2>'.$ctask->subject.'</h2>
						<div class="task_lft">
							<p>'.$ctask->description.'</p>
							<p><strong>Priority: </strong>'.$ctask->priorityName.'</p>
							<p><strong>Due: </strong>'.$ctask->endDate.'</p>
							<p><strong>Created: </strong>'.$ctask->creationDate.'</p>
						</div>
						<div class="task_rgt">
							<h3>Reschedule</h3>
							<p>
								<a href="/task_dashboard.php?action=reschedule_task&id='.$ctask->id.'&endDate='.urlencode($time_1day).'">1 Day</a>
								<a href="/task_dashboard.php?action=reschedule_task&id='.$ctask->id.'&endDate='.urlencode($time_7day).'">7 Days</a>
								<a href="/task_dashboard.php?action=reschedule_task&id='.$ctask->id.'&endDate='.urlencode($time_21day).'">21 Days</a>
							</p>
						</div>
					</div>
				');
			}
			
			echo('
				</div>
			');
			/* START Task Dashboard HTML */
		?>
	</body>
</html>

