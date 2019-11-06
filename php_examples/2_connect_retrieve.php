<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!-- vim:set filetype=php: -->

<html>
<head>
<style type="text/css">
H1,H2,H3,H4 {background: #bbdddd;}
BODY {margin-left:5%; margin-right:5%;}
PRE.query {font-size: 120%;
           font-style: italic;
           font-weight: 600;
           background: #ddbbdd;
}
.problem {background: #ddddbb;}
.indent {margin-left: 5%;}

</style>

<title>PHP Example: #2 </title>
</head>
<body>
<?php 

if (! isset($_POST['user_name'] , $_POST['password'] ,$_POST['sid'])
    || !$_POST['user_name'] || !$_POST['password'] || !$_POST['sid']) {
  ?><h2>Supply information to login to your Oracle Account</h2><br/><?php 
  print <<<_HTML_
    <form method="post" action="$_SERVER[PHP_SELF]">
    Enter your oracle account id: <input type="text" name="user_name" id="user_name">
    <br/>
    Enter your oracle account password: <input type="password" name="password" id="password">
    <br/>
    Enter your oracle account SID: <input type="text" name="sid" id="sid">
    <br/>
    <input type='submit' value='SUBMIT INFO'>
    </form>
_HTML_;
} else {  # we have the values we need, so we can go to work.
   print <<<EXPLAIN
      <div class='problem'>
      <h2>Connect, Retrieve, Display</h2>
      We will connect to the Database, Retrieve information, and Display it.<br>
EXPLAIN;
      require_once 'MDB2.php';
   # elmasri uses DB.php which is now out of date
   #
   #here is the magic incantation for how to connect
 $sid = strtoupper($_POST['sid']);
# $sid .= '.cs.odu.edu'; # sometimes needed
   $uid = strtoupper($_POST['user_name']);
   $dsn= array(
         'phptype'  => 'oci8',
         'dbsyntax' => 'oci8',
         'username' => $uid,
         'password' => $_POST['password'],
         'hostspec' => "oracle.cs.odu.edu",
         'service'  => $sid,
         );
   $con =& MDB2::connect($dsn, array('emulate_prepared' => false));
   # $con now represents the connection

   # some error checking
   error_check($con,'connect');// this func defined below

   # the query starts out as just a string of SQL
   $query1=<<<QUERY
      select plocation lname, count(fname) num_emp
      from (select distinct plocation from project)
      left join employee on address like '%'||plocation||'%'
      group by plocation
      order by lname
QUERY;

   # notice the query ($query1) is included in the explanation string
   print<<<HTML
      <h3>Columns: lname and num_emp--query1</h3>
      Gets the department location name and the number of employees
      who live in that location<br>
      <pre class="query">$query1</pre>
      <div class="indent"><b>Explanation</b>:
      <ul><li><u>select distinct plocation</u>: ensures that each location is only used once.
      Otherwise the same employee would be counted for each different plocation</li>
      <li><u>left join:</u> ensures that each plocation appears in the result even if no
      employee lives there</li>
      <li><u>count(fname):</u> count does not count nulls so plocations with no employees will
                            show up with 0s</li>
      </ul></div>
HTML;

   # run the query and error check it. ALWAYS error check.
   $result =& $con->query($query1);
   error_check($result,'querying with query1');

   // this code prints out a table for ANY query.
   echo "<br><table border='1'>"; // make a table to print out the result
   $header=0;
   while ($array = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
      if (!$header){ // print the header row
         $header=1;
         echo "<TR>";
            foreach($array as $key => $field){
               print("<th>$key</th>");
            }
         echo "</TR>";
      }
      // always print a values row
      echo "<tr>";
         foreach ($array as  $field){
            print("<td>$field</td>");
         }
      echo "</tr>";
   }
   echo "</table><br>";
   $result->free();
   $con->disconnect();
}

function error_check($e2check,$e_in_msg){
   global $con;
   # functions must say when they are using global variables
   if(PEAR::isError($e2check)) {
      echo("<br>Error in $e_in_msg : ");
      echo( $e2check->getMessage() );
      echo( ' - ');
      echo( $e2check->getUserinfo());
      $con->disconnect();
      print "</body></html>";
      exit;
   }else {
      echo("no error in $e_in_msg<br>");
   }
}
?>

</body>
</html>
