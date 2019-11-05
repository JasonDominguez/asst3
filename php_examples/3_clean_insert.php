<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!-- vim:set filetype=php: -->

<?php
// DO NOT INCLUDE THIS SECTION OF PHP IN YOUR CODE
// it is used to show the syntax highlighted version
// YOU DO NOT HAVE 'highlight.php' AVAILABLE.
if (isset($_GET['code'])) { 
   require_once('highlight.php');
   die(highlight_file(__FILE__, 1)); }
// WARNING
// This code uses insert..., clean..., and v... procedures and functions
// in account CS450 that are appropriate to this proj_loc problem.
// If you use this code as a model be sure to change the names of
// these procedures and functions to the ones appropriate to your
// problem.  For example, change 'cs450.clean_proj_loc_summary'
// to cs450.clean_dept_summary or cs450.clean_proj_summary
// etc.
?>

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

<title>PHP Example: #3</title>
</head>
<body>
<?php 
/*
  This version modularizes the code by using funtions.  The functions are
  employed in two ways:
  
  1. Code functions that encapsulate code for some actions such as 
     inserting a row or displaying results.

  2. Text functions that return a large string to be displayed in the 
     output, such as an explanation or the HTM to create a form.  These
     functions return the string which is then sent to the standard
     output using 'echo'.
*/

if (! isset($_POST['user_name'] , $_POST['password'] ,$_POST['sid'])
    || !$_POST['user_name'] || !$_POST['password'] || !$_POST['sid']) {
   # the html text for the Login Form is returned by
   # by the LoginForm() function. 
       echo LoginForm();
} else {  # we have the values we need, so we can go to work.
   # like the HTML for the Form, this HTML explanation 
   # is encapsulated
   echo OverallExplanation();
   require_once 'MDB2.php';
   $uid = strtoupper($_POST['user_name']);
   $con =& MakeConnection($uid);
   error_check($con,'connect');
   CleanTable();
   # Make up a bunch of values for insertion
   # In the next version, the made up values will be calculated
   # not just for one insert but for each location.
   $insert_number = 1;
   $lname = 'Norfolk';
   $num_proj = 2;
   $num_emp = 4;
   $num_dept = 3;
   $num_work = 4;
   $tot_hours = 36;
   $tot_cost = 512;
   InsertRow($lname,$num_proj,$num_emp,$num_dept,$num_work,$tot_hours,$tot_cost);
   DisplayResults();
   $con->disconnect();

}
?>
<?php 
function LoginForm(){
  # this just encapsulates the HTML for the form from previous versions.
  return <<<_HTML_
  <h2>Supply information to login to your Oracle Account</h2><br/>
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
}
function OverallExplanation(){
   return <<<EXPLAIN
      <div class='problem'>
      <h2>Clean, Insert, Display</h2>
      We will connect to the database, clean any tuples in the
      proj_loc_summary table using the procedure provided by CS450.

      Then we will insert one tuple, using the procedure provided by CS450.

      Finally we will view the result using the special view provided by CS450.
EXPLAIN;
}

function error_check($e2check,$e_in_msg){
   global $con;
   if($e2check instanceof PEAR_Error){
      echo("<br>Error in $e_in_msg : ");
      echo( $e2check->getMessage() );
      echo( ' - ');
      echo( $e2check->getUserinfo());
      $con->disconnect();
      print "</body></html>";
      exit;
   }else {
      echo("<br>no error in $e_in_msg<br>");
   }
}

function MakeConnection($uid){
   # NEW. Encapsulates the connection and returns it.
   # $uid is passed as a parameter.  All other variables are
   # automatically local to the function except $_POST which
   # is automatically global.
   $sid = strtoupper($_POST['sid']);
#   $sid .= '.cs.odu.edu'; # sometimes needed
   $dsn= array(
      'phptype'  => 'oci8',
      'dbsyntax' => 'oci8',
      'username' => $uid,
      'password' => $_POST['password'],
      'hostspec' => "oracle.cs.odu.edu",
      'service'  => $sid,
   );
   $con =& MDB2::connect($dsn, array('emulate_prepared' => false));
   return $con;
}

function CleanTable(){
   global $uid,$con;
   # better to pass $uid as param.
   print "<h3>First clean proj_loc_summary table</h3>";
   // $query & $result are automatically local to CleanTable because not global
   $query = "
   BEGIN 
      cs450.clean_proj_loc_summary('$uid'); 
   END;
   ";
   # note alternative to HERE (<<<) document.
   # next line explained below.
   $query=preg_replace('/\r/','',$query);
   print("cleaning with <pre class='query'>$query</pre>");
   $result =& $con->query($query);
   error_check($result,'cleaning');
   $result->free();
   /* note the steps taken here
      1. make the query into a string
      2. execute the query
      3. error check the result
      4. use the results if appropriate (here it is not)
      5. free the resources.
   */
}

function InsertRow($lname,$num_proj,$num_emp,$num_dept,$num_work,$tot_hours,$tot_cost){
   global $uid,$con,$insert_number;
   # note quotes required for string params in ins_proj_loc_summary
   # IF YOU USE THIS AS A BASE FOR YOUR PROGRAM, BE SURE TO CHANGE
   # THE NAME TO THE CORRECT INSERT PROCEDURE
   $query=<<<QUERY
   BEGIN
      cs450.ins_proj_loc_summary(
            '$lname',  -- proj location
            $num_proj, -- number of projects
            $num_emp,  -- number of employees living here
            $num_dept, -- number of depts with offices here
            $num_work, -- number of emps working on projects here
            $tot_hours, -- their total hours
            $tot_cost,  -- their total cost
            '$uid',     -- me
            $insert_number -- row number
            );
   END;
QUERY;
   # if you write your code on windows rather than UNIX you will need to add this next line
   # to avoid an error.  
   $query=preg_replace('/\r/','',$query);
   # The error occurs because Windows ends lines with two characters
   # ascii 13 and 10 but unix only uses 10.  The PL/SQL process gets upset when it finds
   # the carriage returns (ascii 13) in the code for the procedure calls (but in the
   # SQL there is no problem).

   print("Inserting with <pre class='query'>$query</pre>");
   $result =& $con->query($query);
   error_check($result,'procedure call');
   $result->free();
   # important to free memory used by $result
}
function DisplayResults(){
   global $con, $uid;
   $query="select * from TABLE(cs450.v_proj_loc_summary('$uid'))";
   print <<<_HTML_
      <h4>Checking results of Database Procedure Inserts</h4>
      using query <pre class='query'>$query</pre>
_HTML_;
   $result =& $con->query($query);
   error_check($result,'querying v_proj_loc_summary');
   ShowTable($result);
}
function ShowTable($result){
   # Warning: code in next version is slightly different.
   echo "<br><table border='1'>";
   $header=0;
   while ($array = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
      if (!$header){
         $header=1;
         echo "<TR>";
            foreach($array as $key => $field){
               echo("<th>$key</th>");
            }
         echo "</TR>";
      }
      echo "<tr>";
         foreach ($array as  $field){
            echo("<td>$field</td>");
         }
      echo "</tr>";
   }
   # important to free memory used by $result
   # next version does not free result
   $result->free();

   echo "</table>";
}
?>
</body>
</html>
