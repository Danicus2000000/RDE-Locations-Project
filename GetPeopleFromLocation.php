<html>
    <head>
        <title>Get the last 24 hours of locations for a student</title>
        <link rel="stylesheet" type="text/css" href="main.css"/>
    </head>
    <body>
        <ul>
            <li><a href="index.php">Return to index page</a></li>
        </ul>
        <br>
        <br>
        <form method="GET" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <?php
            echo "<select name='locations'>";
            $server = 'sql.rde.hull.ac.uk';//connect to database
            $connectionInfo = array( "Database"=>"rde_636948");//your ID
            $link = sqlsrv_connect($server,$connectionInfo);

            $query="Select LocationName from Locations";//generates the location options for the dropdown box
            $result=sqlsrv_query($link,$query);
            echo "<option value='' selected disabled hidden>--Please select a location--</option>";
            while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) 
            {
                echo "<option value=\"".$row["LocationName"]."\">".$row["LocationName"]."</option>";
            } 
            echo "</select>";
            sqlsrv_free_stmt($query);
            sqlsrv_close($link);
        ?>
        <br/>
        <input onclick="GET" type="submit" value="Get students who have this location as their most recently visited">
        </form>
        <?php
            if ($_SERVER["REQUEST_METHOD"] = "GET")
            {
                $locationSelected=$_GET["locations"];
                if(!empty($locationSelected))
                {
                    //get all students in that location as their most recent
                    $server = 'sql.rde.hull.ac.uk';//connect to database
                    $connectionInfo = array( "Database"=>"rde_636948");//your ID
                    $link = sqlsrv_connect($server,$connectionInfo);

                    //get max student ID in the database
                    $query="Select max(id) as maxID from Students";
                    $result=sqlsrv_query($link,$query);
                    $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $maxStudentID=$row["maxID"];
                    if(empty($maxStudentID))
                    {
                        echo "There are no students in the database!";
                    }
                    else
                    {
                        $studentDates=array();
                        for($i=1; $i<=$maxStudentID;$i++)
                        {
                            $query="Select max(TimeOfVisit) as maxDate from StudentAssociations where StudentID='$i'";//get the visit time from the student id
                            $result=sqlsrv_query($link,$query);
                            $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                            $newestDate=$row["maxDate"];
                            $trueDate=date_format($newestDate,"Y-m-d H:i:s");

                            $query="Select LocationID from StudentAssociations where StudentID='$i' and TimeOfVisit='$trueDate'";//get the location id of where the student went
                            $result=sqlsrv_query($link,$query);
                            $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                            $locationId=$row["LocationID"];

                            $query="Select LocationName from Locations where id='$locationId'";//use the location id to get the location name
                            $result=sqlsrv_query($link,$query);
                            $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                            $location=$row["LocationName"];
                            if($location==$locationSelected)
                            {
                                $query="Select FirstName,LastName from Students where id='$i'";//get the name of the student from their id
                                $result=sqlsrv_query($link,$query);
                                $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                                $firstname=$row["FirstName"];
                                $lastname=$row["LastName"];

                                $data=array($firstname,$lastname,$location,$trueDate);//stores this as an array to be outputted after all students have been checked
                                $studentDates[]=$data;
                            }
                        }
                        sqlsrv_free_stmt($query);
                        sqlsrv_close($link);
                        if(!empty($studentDates))//if there are students who have this as their most recent location list them
                        {
                            foreach($studentDates as $validstudent)
                            {
                                echo $validstudent[0]." ".$validstudent[1]." was most recently at ".$validstudent[2]." on the ".$validstudent[3]."<br/>";
                            }
                        }
                        else
                        {
                            echo "Nobody has this location as their most recent!";
                        }
                    }
                }
            }
        ?>
    </body>
</html>