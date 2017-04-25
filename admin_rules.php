<?php
session_start(); // needed to be able to use the $_SESSION variable

// if admin was not logged in, then redirect user to page index.php
if (!array_key_exists("admin", $_SESSION) || $_SESSION["admin"] != "fuzzy")
	header("Location: index.php");

// if we're not coming from page admin_diseases.php then redirect to page index.php
if (!array_key_exists("hdnDiseaseId", $_POST) || $_POST["hdnDiseaseId"] == "")
	header("Location: index.php");
?>

<!DOCTYPE html>

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Add/Edit/Delete Rules</title>

		<style>
			body {
				background: gainsboro; /* Fills the page */
				max-width: 1280px;
				margin: 0 auto;
			}
		</style>
	</head>
	<body>
		<script type="text/javascript">

			// if a new symptom was selected from the combobox then add a rule for this symptom for the current body part
			function cbSymptom_Changed()
			{
				// same pattern again
				cbSymptom = document.getElementById("cbSymptom");
				symptomId = cbSymptom.options[cbSymptom.selectedIndex].value;
				symptomName = cbSymptom.options[cbSymptom.selectedIndex].text;
				if (symptomId !== "0")
				{
					document.getElementById("hdnAction").value = "add";
					document.getElementById("hdnSymptomId").value = symptomId;
					document.getElementById("hdnSymptomName").value = symptomName;
					document.getElementById("frmRules").action = "admin_rules.php";
					document.getElementById("frmRules").submit();
				}
			}

			// update the values for the currently selected rule
			function frmRules_Update()
			{
				// same pattern again
				document.getElementById("hdnAction").value = "update";
				document.getElementById("frmRules").action = "admin_rules.php";
				document.getElementById("frmRules").submit();
			}

			// delete the current rule
			function frmRules_Delete(symptomId, symptomName)
			{
				// same pattern again
				if (confirm("Are you sure you want the symptom '" + symptomName + "' deleted?"))
				{
					document.getElementById("hdnAction").value = "delete";
					document.getElementById("hdnSymptomId").value = symptomId;
					document.getElementById("hdnSymptomName").value = symptomName;
					document.getElementById("frmRules").action = "admin_rules.php";
					document.getElementById("frmRules").submit();
				}
			}
		</script>

		<center>

		
			<br/>
			<table style="background-color:white; border: 20px solid white">
				<tr>
					<td><a href='index.php'>Home</a></td>
					<td align='right'><a href="admin_diseases.php" onclick='document.getElementById("frmRules").submit();return false;'>Back to diseases</a></td>
					<td align='right'></td>
				</tr>
				<tr>
					<td colspan='3'><hr/></td>
				</tr>
				<tr>
					<td valign='top'>
						<center><h1>Disease diagnosis system</h1></center>
						<br/>

						<?php
						// retrieve values submitted by the previous php page
						$bodyPartId = $_POST["hdnBodyPartId"];
						$bodyPartName = $_POST["hdnBodyPartName"];

						$diseaseId = $_POST["hdnDiseaseId"];
						$diseaseName = $_POST["hdnDiseaseName"];

						echo "<h2>Rules for body part: '" . $bodyPartName . "' and disease: '" . $diseaseName . "'</h2>";
						echo "These are all rules in the database for the given body part and disease.";
						echo "<br/><br/><br/>";

						// if the previous php page made a change to the rules, now read it from the $_POST structure and do its appropriate action (insert/edit/delete)
						if (array_key_exists("hdnAction", $_POST) && $_POST["hdnAction"] != "")
						{
							$symptomId = array_key_exists("hdnSymptomId", $_POST) ? $_POST["hdnSymptomId"] : "";
							$symptomName = array_key_exists("hdnSymptomName", $_POST) ? $_POST["hdnSymptomName"] : "";

							// insert the new rule to the database
							if ($_POST["hdnAction"] == "add")
							{
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										// create a query to insert a new rule
										$query = sprintf("INSERT INTO rules (BodyPartId, DiseaseId, SymptomId) VALUES (%s, %s, '%s')", $bodyPartId, $diseaseId, $symptomId);
										
										// execute the query
										$retval = mysql_query($query, $connection);
										if ($retval)
											echo "<font color='green'>Symptom '" . $symptomName . "' added to the rules for this disease<br/>";
										else
											echo "<font color='red'>Could not insert symptom '" . $symptomName . "' to the list of rules: " . mysql_error() . "</font>";
									}
									mysql_close($connection);
								}
							}
							// edit existing rules in the database
							else if ($_POST["hdnAction"] == "update")
							{
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										// create a query to delete all rules for the current body part and disease
										$query = sprintf("DELETE FROM rules WHERE BodyPartId=%s AND DiseaseId=%s", $bodyPartId, $diseaseId);
										$retval = mysql_query($query, $connection);

										// now build a query to reinsert all rules with the new values from the previous form submission
										$query = "";
										foreach ($_POST as $key => $value)
										{
											if (substr($key, 0, strlen("selSymptomDegreeVeryLow")) == "selSymptomDegreeVeryLow")
											{
												// extract all fields from the previous form submission
												$symptomId = substr($key, strlen("selSymptomDegreeVeryLow_"));

												$symptomDegreeVeryLow = $_POST["selSymptomDegreeVeryLow_" . $symptomId];
												$symptomDegreeLow = $_POST["selSymptomDegreeLow_" . $symptomId];
												$symptomDegreeModerate = $_POST["selSymptomDegreeModerate_" . $symptomId];
												$symptomDegreeHigh = $_POST["selSymptomDegreeHigh_" . $symptomId];
												$symptomDegreeVeryHigh = $_POST["selSymptomDegreeVeryHigh_" . $symptomId];
												$symptomWeight = $_POST["selSymptomWeight_" . $symptomId];

												// add an SQL row for the current rule to the query we're building
												if ($query == "")
													$query = sprintf("INSERT INTO rules (BodyPartId, DiseaseId, SymptomId, SymptomDegreeVeryLow, " .
															"SymptomDegreeLow, SymptomDegreeModerate, SymptomDegreeHigh, SymptomDegreeVeryHigh, Weight) VALUES " .
															"(%s, %s, %s, %s, %s, %s, %s, %s, %s)", $bodyPartId, $diseaseId, $symptomId, $symptomDegreeVeryLow, $symptomDegreeLow, $symptomDegreeModerate, $symptomDegreeHigh, $symptomDegreeVeryHigh, $symptomWeight);
												else
													$query = sprintf($query . ", (%s, %s, %s, %s, %s, %s, %s, %s, %s)", $bodyPartId, $diseaseId, $symptomId, $symptomDegreeVeryLow, $symptomDegreeLow, $symptomDegreeModerate, $symptomDegreeHigh, $symptomDegreeVeryHigh, $symptomWeight);
											}
										}
										// if query was not blank then submit it
										if ($query !== "")
										{
											$retval = mysql_query($query, $connection);
											if ($retval)
												echo "<font color='green'>Rules for disease '" . $diseaseName . "' saved.</font><br>";
											else
												echo "<font color='red'>Could not save rules for disease '" . $diseaseName . "': " . mysql_error() . "</font>";
										}
									}
									mysql_close($connection);
								}
							}
							// delete an existing rule from the database
							elseif ($_POST["hdnAction"] == "delete")
							{
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										// create deletion query
										$query = sprintf("DELETE FROM rules WHERE BodyPartId=%s AND DiseaseId=%s AND SymptomId=%s", $bodyPartId, $diseaseId, $symptomId);

										// execute the query
										$retval = mysql_query($query, $connection);
										if ($retval)
											echo "<font color='green'>Symptom '" . $symptomName . "' deleted from rules.</font><br>";
										else
											echo "<font color='red'>Could not delete symptom '" . $symptomName . "': " . mysql_error() . "</font>";
									}
									mysql_close($connection);
								}
							}
							$_POST["hdnAction"] = "";
						}
						?>

						<!-- all fields are in an HTML form so that their values will be submitted for processing by PHP when the next page loads -->
						<form id = "frmRules" action = "admin_diseases.php" method = "post">

							<!-- print an HTML table of all rules for the selected disease -->
							<table border = "1">
								<th>Symptom</th>
								<th>Very Low</th>
								<th>Low</th>
								<th>Moderate</th>
								<th>High</th>
								<th>Very High</th>
								<th>Weight</th>
								<th>Remove</th>

								<?php
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										// create a query to select all rules for a given bodypart and disease
										$query =
												"SELECT " .
												"	rules.SymptomId, " .
												"	symptoms.Name, " .
												"	rules.SymptomDegreeVeryLow, " .
												"	rules.SymptomDegreeLow, " .
												"	rules.SymptomDegreeModerate, " .
												"	rules.SymptomDegreeHigh, " .
												"	rules.SymptomDegreeVeryHigh, " .
												"	rules.Weight " .
												"FROM symptoms, rules " .
												"WHERE " .
												"	symptoms.BodyPartId = " . $bodyPartId . " AND " .
												"	rules.BodyPartId = " . $bodyPartId . " AND " .
												"	rules.DiseaseId = " . $diseaseId . " AND " .
												"	rules.SymptomId = symptoms.Id " .
												"ORDER BY symptoms.Name";
										
										// execute the query
										$records = mysql_query($query, $connection);
										
										// if returned recordset is not empty, print it in an HTML table
										if ($records && mysql_num_rows($records) > 0)
										{
											// print each rule as a row to the HTML table
											while ($row = mysql_fetch_array($records))
											{
												// extract all fields from the row for this rule
												$symptomId = $row["SymptomId"];
												$symptomName = $row["Name"];
												$symptomDegreeVeryLow = $row["SymptomDegreeVeryLow"];
												$symptomDegreeLow = $row["SymptomDegreeLow"];
												$symptomDegreeModerate = $row["SymptomDegreeModerate"];
												$symptomDegreeHigh = $row["SymptomDegreeHigh"];
												$symptomDegreeVeryHigh = $row["SymptomDegreeVeryHigh"];
												$symptomWeight = $row["Weight"];

												// print this rule as an HTML row
												echo "<tr>";
												echo "<td>" . $symptomName . "</td>";
												echo "<td><select name='selSymptomDegreeVeryLow_" . $symptomId . "'>";
												echo "	<option value='0.0'" . ($symptomDegreeVeryLow == 0.0 ? " selected='selected'" : "") . ">No</option>";
												echo "	<option value='0.5'" . ($symptomDegreeVeryLow == 0.5 ? " selected='selected'" : "") . ">Maybe</option>";
												echo "	<option value='1.0'" . ($symptomDegreeVeryLow == 1.0 ? " selected='selected'" : "") . ">Yes</option>";
												echo "</select></td>";
												echo "<td><select name='selSymptomDegreeLow_" . $symptomId . "'>";
												echo "	<option value='0.0'" . ($symptomDegreeLow == 0.0 ? " selected='selected'" : "") . ">No</option>";
												echo "	<option value='0.5'" . ($symptomDegreeLow == 0.5 ? " selected='selected'" : "") . ">Maybe</option>";
												echo "	<option value='1.0'" . ($symptomDegreeLow == 1.0 ? " selected='selected'" : "") . ">Yes</option>";
												echo "</select></td>";
												echo "<td><select name='selSymptomDegreeModerate_" . $symptomId . "'>";
												echo "	<option value='0.0'" . ($symptomDegreeModerate == 0.0 ? " selected='selected'" : "") . ">No</option>";
												echo "	<option value='0.5'" . ($symptomDegreeModerate == 0.5 ? " selected='selected'" : "") . ">Maybe</option>";
												echo "	<option value='1.0'" . ($symptomDegreeModerate == 1.0 ? " selected='selected'" : "") . ">Yes</option>";
												echo "</select></td>";
												echo "<td><select name='selSymptomDegreeHigh_" . $symptomId . "'>";
												echo "	<option value='0.0'" . ($symptomDegreeHigh == 0.0 ? " selected='selected'" : "") . ">No</option>";
												echo "	<option value='0.5'" . ($symptomDegreeHigh == 0.5 ? " selected='selected'" : "") . ">Maybe</option>";
												echo "	<option value='1.0'" . ($symptomDegreeHigh == 1.0 ? " selected='selected'" : "") . ">Yes</option>";
												echo "</select></td>";
												echo "<td><select name='selSymptomDegreeVeryHigh_" . $symptomId . "'>";
												echo "	<option value='0.0'" . ($symptomDegreeVeryHigh == 0.0 ? " selected='selected'" : "") . ">No</option>";
												echo "	<option value='0.5'" . ($symptomDegreeVeryHigh == 0.5 ? " selected='selected'" : "") . ">Maybe</option>";
												echo "	<option value='1.0'" . ($symptomDegreeVeryHigh == 1.0 ? " selected='selected'" : "") . ">Yes</option>";
												echo "</select></td>";
												echo "<td><select name='selSymptomWeight_" . $symptomId . "'>";
												echo "	<option value='0.1'" . ($symptomWeight == 0.1 ? " selected='selected'" : "") . ">0.1</option>";
												echo "	<option value='0.2'" . ($symptomWeight == 0.2 ? " selected='selected'" : "") . ">0.2</option>";
												echo "	<option value='0.3'" . ($symptomWeight == 0.3 ? " selected='selected'" : "") . ">0.3</option>";
												echo "	<option value='0.4'" . ($symptomWeight == 0.4 ? " selected='selected'" : "") . ">0.4</option>";
												echo "	<option value='0.5'" . ($symptomWeight == 0.5 ? " selected='selected'" : "") . ">0.5</option>";
												echo "	<option value='0.6'" . ($symptomWeight == 0.6 ? " selected='selected'" : "") . ">0.6</option>";
												echo "	<option value='0.7'" . ($symptomWeight == 0.7 ? " selected='selected'" : "") . ">0.7</option>";
												echo "	<option value='0.8'" . ($symptomWeight == 0.8 ? " selected='selected'" : "") . ">0.8</option>";
												echo "	<option value='0.9'" . ($symptomWeight == 0.9 ? " selected='selected'" : "") . ">0.9</option>";
												echo "	<option value='1.0'" . ($symptomWeight == 1.0 ? " selected='selected'" : "") . ">1.0</option>";
												echo "</select></td>";
												echo "<td><button type='button' onclick='frmRules_Delete(" . $symptomId . ", \"" . $symptomName . "\")'>Delete</button></td>";
												echo "</tr>";
											}
										}
									}
									// close the MySQL connection
									mysql_close($connection);
								}
								?>
								<tr><td colspan='8'><center><button type='button' onclick='frmRules_Update()'>Save Rule Updates</button></center></td></tr>
							</table>
							<br/>
							<br/>

							<!-- a combo box that contains all symptoms for which there is no rule for the current disease,
							  so when a symptom of this combo box is selected, a new rule for that symptom will be created -->
							<select id="cbSymptom" onChange="cbSymptom_Changed()">
								<option value="0" selected="selected">Add a new symptom ...</option>
								<?php
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										// select only those symptoms which are not rules for this disease
										$query =
												"SELECT symptoms.Id, symptoms.Name " .
												"FROM " .
												"		(SELECT Id, Name " .
												"		FROM symptoms " .
												"		WHERE BodyPartId=$bodyPartId) AS symptoms " .
												"	LEFT OUTER JOIN " .
												"		(SELECT SymptomId " .
												"		FROM rules " .
												"		WHERE BodyPartId=$bodyPartId AND DiseaseId=$diseaseId) AS rules " .
												"	ON " .
												"		symptoms.Id = rules.SymptomId " .
												"WHERE " .
												"	rules.SymptomId IS NULL " .
												"ORDER BY symptoms.Name";
										
										// execute the query
										$records = mysql_query($query, $connection);
										
										// if returned recordset is not empty, fill these symptoms in a combo box
										if ($records && mysql_num_rows($records) > 0)
										{
											// each symptom is added as an option to the combo box
											while ($row = mysql_fetch_array($records))
											{
												$symptomId = $row["Id"];
												$symptomName = $row["Name"];
												echo '<option value="' . $symptomId . '">' . $symptomName . '</option>';
											}
										}
									}
									mysql_close($connection);
								}
								?>
							</select>
							<br/>

							<input type='hidden' id='hdnAction' name='hdnAction' value='diseases'>

							<?php
							// we use these hidden fields to record values with javascript so that they will be available when the next page is loaded
							// when the next page is loaded we access them using the $_POST structure
							echo "<input type='hidden' id='hdnBodyPartId' name='hdnBodyPartId' value='" . $bodyPartId . "'>";
							echo "<input type='hidden' id='hdnBodyPartName' name='hdnBodyPartName' value='" . $bodyPartName . "'>";

							echo "<input type = 'hidden' id = 'hdnDiseaseId' name = 'hdnDiseaseId' value = '" . $diseaseId . "'>";
							echo "<input type = 'hidden' id = 'hdnDiseaseName' name = 'hdnDiseaseName' value = '" . $diseaseName . "'>";

							echo "<input type = 'hidden' id = 'hdnSymptomId' name = 'hdnSymptomId' value = ''>";
							echo "<input type = 'hidden' id = 'hdnSymptomName' name = 'hdnSymptomName' value = ''>";
							?>
							<br/>
							<br/>

						</form>

					</td>
					<td colspan='2'><img src='MusculoSkeletal.gif'/></td>
				</tr>
			</table>
		
		</center>
	</body>
</html>
