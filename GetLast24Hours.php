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
            echo "<select name='students'>";
            $server = 'sql.rde.hull.ac.uk';//connect to database
            $connectionInfo = array( "Database"=>"rde_636948");//your ID
            $link = sqlsrv_connect($server,$connectionInfo);

            $query="Select FirstName,LastName from Students";//generates the location options for the dropdown box
            $result=sqlsrv_query($link,$query);
            echo "<option value='' selected disabled hidden>--Please select a student--</option>";
            while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) 
            {
                echo "<option value=\"".$row["FirstName"]." ".$row["LastName"]."\">".$row["FirstName"]." ".$row["LastName"]."</option>";
            } 
            echo "</select>";
            sqlsrv_free_stmt($query);
            sqlsrv_close($link);
        ?>
        <br/>
        <input onclick="GET" type="submit" value="Get list of locations this student has visited in the last 24 hours">
        </form>
        <?php
            if ($_SERVER["REQUEST_METHOD"] = "GET")
            {
                $parts=explode(" ",$_GET["students"]);
                $firstname=$parts[0];
                $lastname=$parts[1];
                if(!empty($_GET["students"]))
                {
                    $server = 'sql.rde.hull.ac.uk';//connect to database
                    $connectionInfo = array( "Database"=>"rde_636948");//your ID
                    $link = sqlsrv_connect($server,$connectionInfo);
                    $query="select id from Students where FirstName='$firstname' and LastName='$lastname'";
                    $result=sqlsrv_query($link,$query);
                    $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $studentId=$row["id"];

                    $query="select LocationID,TimeOfVisit from StudentAssociations where StudentID='$studentId'";//get the locations the student has visited
                    $result=sqlsrv_query($link,$query);
                    $times=array();
                    while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) //place all the data in an array to be sorted through
                    {
                        $newestDate=$row["TimeOfVisit"];
                        $trueDate=date_format($newestDate,"Y-m-d H:i:s");
                        $timetoadd=array($firstname,$lastname,$row["LocationID"],$trueDate);
                        $times[]=$timetoadd;
                    } 

                    $validtimes=array();
                    date_default_timezone_set('GMT');
                    foreach($times as $visit)//stores any records that are within the last 24 hours in a new array
                    {
                        $tocompare=strtotime($visit[3]);
                        $current=strtotime(date('Y-m-d H:i:s'));
                        if($tocompare>=$current-86400 && $tocompare<=$current)
                        {
                            $validtimes[]=$visit;
                        }
                    }
                    if(empty($validtimes))//if the student hasnt been anywhere in the last 24 hours echo back the response
                    {
                        echo $firstname." ".$lastname." has not been to any locations in the last 24 hours!";
                    }
                    else//if they have get the name of the location from the database and output it alongside the name of the student and the time of the visit
                    {
                        foreach($validtimes as $output)
                        {
                            $locationid=$output[2];
                            $query="select LocationName from Locations where id='$locationid'";
                            $result=sqlsrv_query($link,$query);
                            $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                            $locationname=$row["LocationName"];
                            echo $output[0]." ".$output[1]." visited ".$locationname." at ".$output[3]."<br/>";
                        }
                    }
                    sqlsrv_free_stmt($query);
                    sqlsrv_close($link);
                }
            }
        ?>
    </body>
</html>