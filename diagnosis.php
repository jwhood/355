
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Diagnosis</title>
		
		<style>
			body {
				background: gainsboro; /* Fills the page */
				max-width: 1280px;
				margin: 0 auto;
			}
		</style>
	</head>
	
	<body>
		<center>
			<br/>
			<table style="background-color:white; border: 20px solid white">
				<tr>
					<td><a href='index.php'>Home</a></td>
					<td align='right'></td>
					<td align='right'></td>
				</tr>
				<tr>
					<td colspan='3'><hr/></td>
				</tr>
				<tr>
					<td valign='top'>
						<center><h1>Disease diagnosis system</h1></center>
						<br/>
						<h2>Diagnosis</h2>
						Here are the most likely diseases.
						<br/><br/><br/>

						<?php
						// retrieve values submitted by the previous php page
						$bodyPartId = $_POST["hdnBodyPartId"];
						$bodyPartName = $_POST["hdnBodyPartName"];

						// connect to the MySQL server
						if ($connection = mysql_connect("localhost", "root", ""))
						{
							// select database 'fuzzy' as default for this connection
							if (mysql_select_db("fuzzy", $connection))
							{
								// retrieve all the diseases from the MySQL database and put them in a PHP variable $diseases
								$query = sprintf("SELECT * FROM diseases WHERE BodyPartId=%s ORDER BY Id", $bodyPartId);
								$recsDiseases = mysql_query($query, $connection);

								// if the returned recordset is not empty
								if ($recsDiseases && mysql_num_rows($recsDiseases) > 0)
								{
									$diseases = array();

									// for each disease row
									while ($row = mysql_fetch_array($recsDiseases))
									{
										// extract the Id and Name
										$diseaseId = $row["Id"];
										$diseaseName = $row["Name"];

										// add it to the array $diseases
										$diseases[$diseaseId] = $diseaseName;
									}
								}

								// create a query to retrieve the rules from the database
								$query = sprintf("SELECT * FROM rules WHERE BodyPartId=%s ORDER BY DiseaseId, SymptomId", $bodyPartId);

								// retrieve all rules from the MySQL database
								$recsRules = mysql_query($query, $connection);
								if ($recsRules && mysql_num_rows($recsRules) > 0)
								{
									$lastDiseaseId = -1;

									// for each rule calculate the disease degree depending on the symptom value
									while ($row = mysql_fetch_array($recsRules))
									{
										// extract all fields from each rule
										$diseaseId = $row["DiseaseId"];
										$symptomId = $row["SymptomId"];
										$symptomValue = $_POST["symptom" . $symptomId];
										$symptomDegreeVeryLow = $row["SymptomDegreeVeryLow"];
										$symptomDegreeLow = $row["SymptomDegreeLow"];
										$symptomDegreeModerate = $row["SymptomDegreeModerate"];
										$symptomDegreeHigh = $row["SymptomDegreeHigh"];
										$symptomDegreeVeryHigh = $row["SymptomDegreeVeryHigh"];
										$weight = $row["Weight"];

										// if this is a new rule, calculate average for the previous one
										if ($lastDiseaseId != $diseaseId)
										{
											if ($lastDiseaseId != -1)
											{
												$averageDiseaseDegree = $totalDiseaseDegree / $totalWeight;
												$diseaseDegrees[$lastDiseaseId] = $averageDiseaseDegree;
											}

											// init total values to 0 for the next rule
											$totalWeight = 0.0;
											$totalDiseaseDegree = 0.0;
											$lastDiseaseId = $diseaseId;
										}

										// $disease degree is assigned based on user selection of $symtpomValue in previous form
										switch ($symptomValue)
										{
											case 1:
												$diseaseDegree = $symptomDegreeVeryLow;
												break;

											case 2:
												$diseaseDegree = $symptomDegreeLow;
												break;

											case 3:
												$diseaseDegree = $symptomDegreeModerate;
												break;

											case 4:
												$diseaseDegree = $symptomDegreeHigh;
												break;

											case 5:
												$diseaseDegree = $symptomDegreeVeryHigh;
												break;
										}

										// add degree and weight to total
										$totalDiseaseDegree += $weight * $diseaseDegree;
										$totalWeight += $weight;
									}

									// calculate the average degree for this disease
									if ($lastDiseaseId != -1)
									{
										$averageDiseaseDegree = $totalDiseaseDegree / $totalWeight;
										$diseaseDegrees[$diseaseId] = $averageDiseaseDegree;
									}

									// sort the diseases with their degrees in descending order based on the degrees
									arsort($diseaseDegrees);
								}

								// print all diseases with their degrees to an HTML table
								echo "<table border='1'>";
								echo "<th colspan=2>Diagnosis for body part: " . $bodyPartName . "</h2></th>";
								echo "<tr><th>Disease</th><th>Score</th></tr>";
								$id = 0;
								foreach ($diseaseDegrees as $diseaseId => $diseaseDegree)
								{
									$id = $id + 1;
									// print a disease name and disease degree for each row of the table
									$diseaseName = $diseases[$diseaseId];
									if ($id == 1)
										echo "<tr><td><font color='Red'>" . $diseaseName . "</font></td><td><font color='Red'>" . sprintf("%.2f%%", (100 * $diseaseDegree)) . "</font></td><tr>";
									else if ($id == 2)
										echo "<tr><td><font color='Blue'>" . $diseaseName . "</font></td><td><font color='Blue'>" . sprintf("%.2f%%", (100 * $diseaseDegree)) . "</font></td><tr>";
									else
										echo "<tr><td>" . $diseaseName . "</td><td>" . sprintf("%.2f%%", (100 * $diseaseDegree)) . "</td><tr>";
								}
								echo "</table>";
							}
							mysql_close($connection);
						}

						// we use these hidden fields to record values with javascript so that they will be available when the next page is loaded
						// when the next page is loaded we access them using the $_POST structure
						echo "<input type='hidden' id='hdnBodyPartId' name='hdnBodyPartId' value='" . $bodyPartId . "'>";
						echo "<input type='hidden' id='hdnBodyPartName' name='hdnBodyPartName' value='" . $bodyPartName . "'>";
						?>
								
					</td>
					<td colspan='2'><img src='MusculoSkeletal.gif'/></td>
				</tr>
				
			</table>
		</center>
	</body>
</html>
