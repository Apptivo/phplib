<?php
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

//Retrieve my task data
$task_data = $apptivo->get_all_tasks();

if($_POST['action'] == 'reschedule_task')
{	
	$apptivo->update_task('endDate',$_POST['id']);
	
	$message = 'Task Updated';
}
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
			');
			
			//Loop through each task
			foreach($task_data->tasks as $ctask)
			{
				//We need to calculate the reschedule defaults to pass in
				$time_1day = date("m/d/Y H:i A",strtotime("+1 day", strtotime($ctask->endDate)));
				$time_7day = date("m/d/Y H:i A",strtotime("+7 day", strtotime($ctask->endDate)));
				$time_21day = date("m/d/Y H:i A",strtotime("+21 day", strtotime($ctask->endDate)));
				
				echo('
					<div class="task_cnt">
						<h2>'.$ctask->subject.'</h2>
						<div class="task_lft">
							<p>'.$ctask->description.'</p>
							<p>'.$ctask->creationDate.'</p>
						</div>
						<div class="task_rgt">
							<h3>Reschedule</h3>
							<p>
								<a href="/task_dasboard.php?action=reschedule_task&id='.$ctask->id.'&time='.urlencode($time_1day).'">1 Day</a>
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

