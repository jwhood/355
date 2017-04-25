<?php
session_start(); // needed to be able to use the $_SESSION variable

// if admin was not logged in, then redirect user to page index.php
if (!array_key_exists("admin", $_SESSION) || $_SESSION["admin"] != "fuzzy")
	header("Location: index.php");

// if we're not coming from page admin_bodyparts.php then redirect to page index.php
if (!array_key_exists("hdnBodyPartId", $_POST) || $_POST["hdnBodyPartId"] == "")
	header("Location: index.php");
?>

<!DOCTYPE html>

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Add/Edit/Delete Diseases</title>

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

			// insert a new disease
			function frmDiseases_Insert()
			{
				// same pattern again
				var diseaseNewName = prompt("Enter a new name for the disease:", "");
				if (diseaseNewName !== null && diseaseNewName !== "")
				{
					document.getElementById("hdnAction").value = "insert";
					document.getElementById("hdnDiseaseNewName").value = diseaseNewName;
					document.getElementById("frmDiseases").submit();
				}
			}

			// edit the name of an already existing disease
			function frmDiseases_Edit(diseaseId, diseaseName)
			{
				// same pattern again
				var symptomNewName = prompt("Rename the disease", diseaseName);
				if (symptomNewName !== null && symptomNewName !== "")
				{
					document.getElementById("hdnAction").value = "edit";
					document.getElementById("hdnDiseaseId").value = diseaseId;
					document.getElementById("hdnDiseaseName").value = diseaseName;
					document.getElementById("hdnDiseaseNewName").value = symptomNewName;
					document.getElementById("frmDiseases").submit();
				}
			}

			// delete an already existing disease
			function frmDiseases_Delete(diseaseId, diseaseName)
			{
				// same pattern again
				if (confirm("Are you sure you want the disease '" + diseaseName + "' deleted? All rules associated to this disease will be deleted as well."))
				{
					document.getElementById("hdnAction").value = "delete";
					document.getElementById("hdnDiseaseId").value = diseaseId;
					document.getElementById("hdnDiseaseName").value = diseaseName;
					document.getElementById("frmDiseases").submit();
				}
			}

			// open the rules PHP page for the selected disease
			function frmDiseases_Rules(diseaseId, diseaseName)
			{
				// same pattern again
				document.getElementById("hdnAction").value = "rules";
				document.getElementById("hdnDiseaseId").value = diseaseId;
				document.getElementById("hdnDiseaseName").value = diseaseName;
				document.getElementById("frmDiseases").action = "admin_rules.php";
				document.getElementById("frmDiseases").submit();
			}
		</script>

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

						<?php
						// retrieve values submitted by the previous php page
						$bodyPartId = $_POST["hdnBodyPartId"];
						$bodyPartName = $_POST["hdnBodyPartName"];

						echo "<h2>Diseases for body part: " . $bodyPartName . "</h2>";
						echo "These are all diseases in the database for the given body part.";
						echo "<br/><br/><br/>";

						// if the previous php page made a change to the diseases, now read it from the $_POST structure and do its appropriate action (insert/edit/delete)
						if (array_key_exists("hdnAction", $_POST) && $_POST["hdnAction"] != "diseases")
						{
							$diseaseId = array_key_exists("hdnDiseaseId", $_POST) ? $_POST["hdnDiseaseId"] : "";
							$diseaseName = array_key_exists("hdnDiseaseName", $_POST) ? $_POST["hdnDiseaseName"] : "";
							$diseaseNewName = array_key_exists("hdnDiseaseNewName", $_POST) ? $_POST["hdnDiseaseNewName"] : "";

							// insert the new disease to the database
							if ($_POST["hdnAction"] == "insert")
							{
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										// create a query to select symptoms
										$query = sprintf("SELECT * FROM diseases WHERE BodyPartId=%s AND Name='%s'", $bodyPartId, $diseaseNewName);

										// run the query
										$records = mysql_query($query, $connection);

										if (mysql_num_rows($records) > 0)
											echo sprintf("<font color='red'>Error! Disease '%s' already exists in the database. No new record was inserted.</font><br>", $diseaseNewName);
										else
										{
											// find the maximum Id field value in the table 'symptoms'
											$query = sprintf("SELECT MAX(Id) AS Max FROM diseases WHERE BodyPartId=%s", $bodyPartId);
											$result = mysql_query($query, $connection);
											if ($result)
											{
												$row = mysql_fetch_assoc($result);
												$maxId = $row["Max"];
											}
											else
												$maxId = 0;
											$diseaseNewId = strval(1 + intval($maxId));

											// create query to insert new disease
											$query = sprintf("INSERT INTO diseases (BodyPartId, Id, Name) VALUES (%s, %s, '%s')", $bodyPartId, $diseaseNewId, $diseaseNewName);

											// execute query
											$retval = mysql_query($query, $connection);
											if ($retval)
												echo "<font color='green'>New disease '" . $diseaseNewName . "' inserted into the database.</font><br>";
											else
												echo "<font color='red'>Could not insert new disease '" . $diseaseNewName . "': " . mysql_error() . "</font>";
										}
									}
									mysql_close($connection);
								}
							}
							// edit an existing disease in the database
							else if ($_POST["hdnAction"] == "edit")
							{
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										$query = sprintf("SELECT * FROM diseases WHERE BodyPartId=%s AND Name='%s'", $bodyPartId, $diseaseNewName);
										$records = mysql_query($query, $connection);

										if (mysql_num_rows($records) > 0)
											echo sprintf("Error! Disease '%s' already exists in the database. '%s' was not renamed to '%s'.<br>", $diseaseNewName, $diseaseName, $diseaseNewName);
										else
										{
											$query = sprintf("UPDATE diseases SET Name='%s' WHERE BodyPartId=%s AND Id=%s", $diseaseNewName, $bodyPartId, $diseaseId);
											$retval = mysql_query($query, $connection);
											if ($retval)
												echo "<font color='green'>Disease '" . $diseaseName . "' renamed to '" . $diseaseNewName . "'.</font><br>";
											else
												echo "<font color='red'>Could not rename disease '" . $diseaseName . "': " . mysql_error() . "</font>";
										}
									}
									mysql_close($connection);
								}
							}
							// delete an existing disease in the database
							elseif ($_POST["hdnAction"] == "delete")
							{
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										$query = sprintf("DELETE FROM diseases WHERE BodyPartId=%s AND Id=%s", $bodyPartId, $diseaseId);
										$retval = mysql_query($query, $connection);
										if ($retval)
											echo "<font color='green'>Disease '" . $diseaseName . "' deleted from database.</font><br>";
										else
											echo "<font color='red'>Could not delete disease '" . $diseaseName . "': " . mysql_error() . "</font>";
									}
									mysql_close($connection);
								}
							}
							$_POST["hdnAction"] = "";
						}
						?>

						<br/>

						<!-- all fields are in an HTML form so that their values will be submitted for processing by PHP when the next page loads -->
						<form id = "frmDiseases" action = "admin_diseases.php" method = "post">
							<table border = "1">
								<th>Disease Name</th>
								<th>Edit</th>
								<th>Delete</th>
								<th>Rules</th>
								<?php
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										// create a query to select the diseases
										$query = sprintf("SELECT * FROM diseases WHERE BodyPartId=%d ORDER BY Name", $bodyPartId);
										
										// execute the query
										$records = mysql_query($query, $connection);
										
										// if the recordset is not empty
										if ($records && mysql_num_rows($records) > 0)
										{
											// fetch each row from the recordset
											while ($row = mysql_fetch_array($records))
											{
												// extract each field for this disease from the row and print it as HTML
												$diseaseId = $row["Id"];
												$diseaseName = $row["Name"];
												echo "<tr>";
												echo "<td>" . $diseaseName . "</td>";
												echo "<td><button type='button' onclick='frmDiseases_Edit(" . $diseaseId . ", \"" . $diseaseName . "\")'>Edit</button></td>";
												echo "<td><button type='button' onclick='frmDiseases_Delete(" . $diseaseId . ", \"" . $diseaseName . "\")'>Delete</button></td>";
												echo "<td><button type='button' onclick='frmDiseases_Rules(" . $diseaseId . ", \"" . $diseaseName . "\")'>Rules</button></td>";
												echo "</tr>";
											}
										}
									}
									// close the MySQL connection
									mysql_close($connection);
								}
								?>
								<tr><td colspan='4'><center><button type='button' onclick='frmDiseases_Insert()'>Insert New Disease</button></center></td></tr>
							</table>
							<br/>
							<br/>
							<input type='hidden' id='hdnAction' name='hdnAction' value=''>

							<?php
							// we use these hidden fields to record values with javascript so that they will be available when the next page is loaded
							// when the next page is loaded we access them using the $_POST structure
							echo "<input type='hidden' id='hdnBodyPartId' name='hdnBodyPartId' value='" . $bodyPartId . "'>";
							echo "<input type='hidden' id='hdnBodyPartName' name='hdnBodyPartName' value='" . $bodyPartName . "'>";

							echo "<input type = 'hidden' id = 'hdnDiseaseId' name = 'hdnDiseaseId' value = ''>";
							echo "<input type = 'hidden' id = 'hdnDiseaseName' name = 'hdnDiseaseName' value = ''>";
							echo "<input type = 'hidden' id = 'hdnDiseaseNewName' name = 'hdnDiseaseNewName' value = ''>";
							?>
						</form>

					</td>
					<td colspan='2'><img src='MusculoSkeletal.gif'/></td>
				</tr>
			</table>
		</center>
	</body>
</html>
