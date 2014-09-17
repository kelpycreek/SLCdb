<?php
	/*
		Export
		Western Washington University
		Service Learning Center Database
	*/
	include 'database.php';
	$user = 'gebauee_writer';
	$pass = 'A2AWvYYCR';
	$host = 'db.cs.wwu.edu';
	$dbname = 'db_slc1410';
	$database = mysqli_connect($host, $user, $pass, $dbname) or die("Error: " . mysqli_error($connection));
	
	//Post the file name to outname
	//File name will be 'output.csv' if not posted
	$outname = $_POST['outname'];
	if ($outname) $filename = $outname . ".csv";
	else $filename = "output.csv";
	
	//Header info
	header('Content-type: application/csv');
	header('Content-Disposition: attachment; filename='.$filename);
	
	//Post the SQL query to export
	if (isset($_POST['export']))
	{
		//Query the database
		$query = $_POST['export'];
		$query = str_replace('\\', '', $query);
		$sql = mysqli_query($database, $query) or die("Error: " . mysqli_error($database));
		$columns = $sql->field_count;
		
		//Write field names to the first row
		for ($i = 0; $i < $columns; $i++)
		{
			$heading = $sql->fetch_field_direct($i)->name;
			echo $heading;
			echo ",";
		}
		echo "\n";
		//Write each row
		
		if($_POST["exportKeyword"])
		{
			$rowIndex = 0;
			$exportRows = $_POST["exportRows"];
		}
		while ($row = mysqli_fetch_array($sql))
		{
			if ($_POST["exportKeyword"])
			{	
				if (in_array(strval($rowIndex), $exportRows)){
					for ($i = 0; $i < $columns; $i++)
					{
						echo $row[$i];
						echo ",";
					}
					echo "\n";
				}
				
			}
			else
			{
				for ($i = 0; $i < $columns; $i++)
				{
					echo $row[$i];
					echo ",";
				}
				echo "\n";
			}
			
			if($_POST["exportKeyword"])
			{
				$rowIndex = $rowIndex + 1;
			}
		}

		//Echo the table
		echo $output;
	}
?>
