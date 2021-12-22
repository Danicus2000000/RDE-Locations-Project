<html>
    <head>
        <title>Get Locations</title>
        <link rel="stylesheet" type="text/css" href="main.css"/>
    </head>
    <body>
        <ul>
            <li><a href="index.php">Return to index page</a></li>
        </ul>
        <br>
        <br>
        <form method="GET" action="<?php echo $_SERVER['PHP_SELF'];?>">
          First Name: <input type="text" name="getfirstname">
          Last Name: <input type="text" name="getlastname">
          <input onclick="GET" type="submit" value="Get most recent location">
        </form>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>">
          First Name: <input type="text" name="postfirstname">
          Last Name: <input type="text" name="postlastname">
          Location: <input type="text" name="postlocation">
          Time: <input type="datetime-local" name="postdate" step="1">
          <input onclick="POST" type="submit" value="Update user's location">
        </form>
        <?php
            $server = 'sql.rde.hull.ac.uk';//connect to database
            $connectionInfo = array( "Database"=>"rde_636948");//your ID
            $link = sqlsrv_connect($server,$connectionInfo);

            if ($_SERVER["REQUEST_METHOD"] = "POST") 
            {
                $firstname = $_POST['postfirstname'];
                $lastname = $_POST['postlastname'];
                $location=$_POST['postlocation'];
                $time=$_POST['postdate'];
                date_default_timezone_set('GMT');
                $timecheck=strtotime($time);//the time to check 
                $current=strtotime(date("Y-m-d H:i:s"));
                if((!(empty($firstname) || empty($lastname) || empty($location) || empty($time))) && $current>=$timecheck)//if all fields are entered and the time is not in the future
                {
                    //get or create student ID so we can associate them to a location ID
                    $query="Select id from Students where FirstName='$firstname' and LastName='$lastname'";//get id of student
                    $result=sqlsrv_query($link,$query);
                    $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $studentID=$row["id"];
                    if(empty($studentID))//if student id is not found create it then get its id
                    {
                        $insert_query="INSERT INTO Students (FirstName, LastName) VALUES (?,?)";
                        $params=array($firstname,$lastname);
                        $result=sqlsrv_query($link,$insert_query,$params);
                        if(result)
                        {
                            echo "Student ".$firstname." ".$lastname." was created. <br/>";
                            $query="Select id from Students where FirstName='$firstname' and LastName='$lastname'";//get id of new student so we can continue
                            $result=sqlsrv_query($link,$query);
                            $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                            $studentID=$row["id"];
                        }
                        else
                        {
                            echo "An error occured creating the student";
                        }
                    }

                    //get or create a Location ID so we can asocciate it to a student ID
                    $query="Select id from Locations where LocationName='$location'";//get id of location
                    $result=sqlsrv_query($link,$query);
                    $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $locationID=$row["id"];
                    if(empty($locationID))
                    {
                        $insert_query="INSERT INTO Locations (LocationName) VALUES (?)";
                        $params=array($location);
                        $result=sqlsrv_query($link,$insert_query,$params);
                        if(result)
                        {
                            echo "Location ".$location." was created. <br/>";
                            $query="Select id from Locations where LocationName='$location'";//get id of new location so we can continue
                            $result=sqlsrv_query($link,$query);
                            $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                            $locationID=$row["id"];
                        }
                        else
                        {
                            echo "An error occured creating the Location";
                        }
                    }

                    //Create the association between the student and their location
                    $insert_query="INSERT INTO studentAssociations (StudentID,LocationID,TimeOfVisit) VALUES (?,?,?)";
                    $params=array($studentID,$locationID,$time);
                    $result=sqlsrv_query($link,$insert_query,$params);
                    if($result)
                    {
                        echo "Student Association added: ".$firstname." ".$lastname." was at ".$location." at ".str_replace("T"," ",$time);
                    }
                }
                elseif(empty($firstname) && empty($lastname) && empty($location) && empty($time))
                {
                    //do nothing as the data probably hasn't been entered yet
                }
                elseif($current<$timecheck)
                {
                    echo "You cannot enter a date in the future!";
                }
                else
                {
                    echo "A Fist Name, Last Name, Location and Time are required!";
                }
            }
            if ($_SERVER["REQUEST_METHOD"] = "GET")
            {
                    $firstname=$_GET["getfirstname"];//get id of student
                    $lastname=$_GET["getlastname"];
                    $query="Select id from Students where FirstName='$firstname' and LastName='$lastname'";
                    $result=sqlsrv_query($link,$query);
                    $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $studentID=$row["id"];
                    if(!empty($studentID))//if student id is found
                    {
                        $query="Select max(TimeOfVisit) as maxDate from StudentAssociations where StudentID='$studentID'";//get the visit time from the student id
                        $result=sqlsrv_query($link,$query);
                        $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                        $newestDate=$row["maxDate"];
                        $trueDate=date_format($newestDate,"Y-m-d H:i:s");
                        if(empty($trueDate))//if the date is not found the student must not of visited any locations
                        {
                            echo "This Student has not been to any locations!";
                        }
                        else//if a time is found
                        {
                            $query="Select LocationID from StudentAssociations where StudentID='$studentID' and TimeOfVisit='$trueDate'";//get the location id of where the student went
                            $result=sqlsrv_query($link,$query);
                            $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                            $locationId=$row["LocationID"];

                            $query="Select LocationName from Locations where id='$locationId'";//use the location id to get the location name
                            $result=sqlsrv_query($link,$query);
                            $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                            $location=$row["LocationName"];
                            echo $firstname." ".$lastname."'s most recent location was ".$location." the date was ".$trueDate;//output the result

                        }
                    }
                    elseif (empty($firstname) && empty($lastname))
                    {
                        //dont display anything if both boxes are empty as this function has likely not actually been called yet
                    }
                    elseif (empty($firstname) || empty($lastname))//if there has been incomplete or no user input echo a reminder of required data
                    {
                        echo "A First Name and Last Name is required to retrieve student data";
                    }
                    else//If the student is not found create student
                    {
                        $insert_query="INSERT INTO Students (FirstName, LastName) VALUES (?,?)";
                        $params=array($firstname,$lastname);
                        $result=sqlsrv_query($link,$insert_query,$params);
                        if(result)
                        {
                            echo "Student ".$firstname." ".$lastname." was created, as such they also have no location records to display!";
                        }
                        else
                        {
                            echo "An error occured creating the student";
                        }
                    }
                    sqlsrv_free_stmt($query);
                    sqlsrv_close($link);
            }
        ?>
    </body>
</html>