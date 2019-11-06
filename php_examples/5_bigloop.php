<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!-- vim:set filetype=php: -->

<?php
ini_set('display_errors', "1");
ini_set('display_startup_errors', "1");
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
<?php 
// Opening HTML and CSS printed with TopMatter()  
echo TopMatter();
if (! isset($_POST['user_name'] , $_POST['password'] ,$_POST['sid'])
    || !$_POST['user_name'] || !$_POST['password'] || !$_POST['sid']) {
       echo LoginForm();
} else {  # we have the values we need, so we can go to work.
   $uid = strtoupper($_POST['user_name']);
   VerifyUID($uid);
   echo OverallExplanation();
   require_once 'MDB2.php';
   $con =& MakeConnection($uid);
   error_check($con,'MakeConnection');
///////////////////////////////////////////////////
// IMPORTANT NOTE:
// if your inserts are to be credited to you
// they have to have your ORACLE ID attached to them.
// In the code below, they are going to be credited to
// whoever logged in which might be you, your partner
// or your instructor, because they are passed with the
// parameter $uid.
//
// To make sure you get credit, be sure you uncomment
// the following line and replace XXXXXXXX with your
// ORACLE ID:
//
//      $uid = 'XXXXXXXX';
//
// DO NOT make this change until you are ready to submit
// this code.  The insert and clean procedures will not work
// properly for anyone except your instructor and $uid
// after you make the change, i.e., they will not work for
// your partner.
// 
// Be sure not to move this line earlier in the code
// because the orginal $uid is needed to connect the
// user and their password with the DB
///////////////////////////////////////////////////

// This sequence of functions all output text that is either
// 1. sent to standard output, or
// 2. stored in program variables
echo Approach();
echo ExplainQueries();
CleanTable();
$query0=Query0();

echo "Retrieving the list of locations with <br><pre>$query0</pre> ";
$outer = & $con->query($query0);
error_check($outer, 'querying with query0');
  $insert_number = 1;

//while ($location = $outer->fetchRow(MDB2_FETCHMODE_ASSOC)){
   // protect against faulty return from fetchRow
while( is_array(   $location = $outer->fetchRow(MDB2_FETCHMODE_ASSOC))){
   $loc = $location['plocation'];
   echo "<h3>Retrieving results for location $loc</h3>";
   $query1=Query1($loc);
   $query2=Query2($loc);
   $query3=Query3($loc);
   $query4=Query4($loc);
   $query5=Query5($loc);
   echo ShortExp('num_emp',$query1);
   $num_emp = $con->queryOne($query1);
   error_check($num_emp,"num_emp for $loc");
   echo "Retrieved $num_emp<br>";
   echo ShortExp('num_proj',$query2);
   $num_proj = $con->queryOne($query2);
   error_check($num_proj,"num_proj for $loc");
   echo "Retrieved $num_proj<br>";
   echo ShortExp('num_work',$query3);
   $num_work = $con->queryOne($query3);
   error_check($num_work,"num_work for $loc");
   echo "Retrieved $num_work<br>";
   echo ShortExp('tot_hours and tot_cost',$query4);
   $tot2 = $con->queryRow($query4);
   error_check($tot2,"tot_hours and tot_cost for $loc");
   $tot_hours = $tot2[0];
   $tot_cost = $tot2[1];
   echo "Retrieved $tot_hours and $tot_cost<br>";
   echo ShortExp('num_dept',$query5);
   $num_dept = $con->queryOne($query5);
   error_check($num_dept,"num_dept for $loc");
   echo "Retrieved $num_dept<br>";
   echo "<h4>Inserting for $loc</h4>";
   InsertRow($loc,
      $num_proj,
      $num_emp,
      $num_dept,
      $num_work,
      $tot_hours,
      $tot_cost
          );

   $insert_number++;
}
   # important to free memory used by $result
$outer->free();
DisplayResults();
   $con->disconnect();

}
?>
<?php 
function TopMatter(){
   return <<<TOP
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

   <title>PHP Example: #5</title>
   </head>
   <body>
TOP;
}
function LoginForm(){
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
   <h2>Sample Problem Statement</h2>
   <pre>
   This is an example problem to show what is required in the PHP problem.

   The problem is to produce a report on each project location, that is,
   any location where any department has a project.  There is to be only
   one report for each location, even if there are several projects there.

   We want to know for each location:

   lname: The name of that location;
   num_proj: The number of projects in that location;
   num_emp: The number of employees with addresses in that location;
   num_dept: The number of departments with offices in that location;
   num_work: The number of different employees who work on projects in that location;
   tot_hours: The total hours worked by employees on projects in that location;
   tot_cost: The total cost of those hours;

   It is essential that the way your program figures out the values
   that are required be clearly explained on the web page.  This means that you may
   find that a complex query cannot be used because it is too difficult to explain.

   It is also essential that the result of each query be shown on the web page.  From
   here on down is an example of what your application should look like.

   NOTE: Your project output does not need a beginning section like this one.  The
   example should be considered to start from the next section.
   </pre></div>
      <table border="1">
      <tr><td colspan="2">This page is the solely the work of</td></tr>
      <tr><td>pratik naik</td><td>pnaik</td></tr>
      <tr><td>scott fox</td><td>sfox</td></tr>
      <tr><td colspan="2">We have not recieved aid from anyone<br>
      else in this assignment.  We have not given <br>
         anyone else aid in the 
      assignment</td></tr>
      </table>

EXPLAIN;
}
function VerifyUID($uid){
   $legal_names=array(
       'CS450','CS450A','CS450B','CS450C','CS450D');
   // Add the team members to the array by replacing PARTNER1 and PARTNER2 
   // with your team's Oracle LOGINS
   $legal_names[] = 'JDOMINGU'; // have to be in upper case
   $legal_names[] = 'HBROW';
   if ( ! in_array($uid, $legal_names)){
     print <<<HTML
       <h1>ACCESS DENIED</h1>
HTML;
     if (preg_match('/cs450\b/',__FILE__)){
     print <<<HTML
       <b>Copy the source code to your own secure_html directory<br>
          by clicking on the "To see the code" link<br>
          and add your UID to the \$legal_names array in function VerifyUID</b>
HTML;
     }
     print <<<HTML
       </body>
       </html>
HTML;
     exit;
   }
}
function error_check($e2check,$e_in_msg){
   global $con;
   if(PEAR::isError($e2check)) {
      echo("<br>Error in $e_in_msg : ");
      echo( $e2check->getMessage() );
      echo( ' - ');
      echo( $e2check->getUserinfo());
      if ($con->disconnect){
      $con->disconnect();
      }
      print "</body></html>";
      exit;
   }else {
      echo("no error in $e_in_msg<br>");
   }
}
function MakeConnection($uid){
   // $_POST is automatically global
   $sid = strtoupper($_POST['sid']);
#  $sid .= '.cs.odu.edu'; # sometimes needed
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
function Approach(){
return <<<HTML
<h3>Overview</h3>
<div class='problem'>
The approach taken here is the Big Loop approach.  An outer loop retrieves a list
of all the locations.  Then for each location the values to be reported for that location
are calculated. These values are then inserted, and we proceed to the next location.
</div>
HTML;
}
function CleanTable(){
   # builds the procedure call to clean the table, executes the query,
   # checks the result and explains what is going on.
   global $uid,$con;
   print "<h3>First clean proj_loc_summary table</h3>";
   // $query & $result are automatically local to CleanTable because not global
   $query = "
   BEGIN 
      cs450.clean_proj_loc_summary('$uid'); 
   END;
   ";
   $query=preg_replace('/\r/','',$query);
   print("cleaning with <pre class='query'>$query</pre>");
   $result =& $con->query($query);
   error_check($result,'cleaning');
   $result->free();
}
function Query0(){
   return  <<<QUERY
   select distinct plocation from project
QUERY;
}
function ExplainQuery0($query0){
   return <<<HTML
   <h3>We start with an Outer Loop</h3>
   <pre class="query">$query0</pre>
   <div class="indent"><b>Explanation</b>:
   The result set of all the project locations will be the outer loop for
   the Big Loop approach.  We will get the correct parameters for each of the
   parameters for the cs450.ins_proj_loc_summary() procedure for this particular
   location, and then do the insert.
   </div>
HTML;
}
function Query1($loc){
   return <<<QUERY
   select count(fname) num_emp
   from employee 
   where  address like '%'||'$loc'||'%'
QUERY;
}
function ExplainQuery1($query1){
   return <<<HTML
   <h3>Column: num_emp--query1</h3>
   <pre class="query">$query1</pre>
   <div class="indent"><b>Explanation</b>:
   <ul><li>Once the location is specified, counts the number of employees who live there</li>
   <li><u>count(fname):</u> count will return 0 if there are no such employees</li>
   </ul></div>
HTML;
}
function ShortExp($colname, $query){
   return <<<HTML
   <p>Retrieving $colname with <pre class="query">$query</pre></p>
HTML;
}
function Query2($loc){
   return<<<QUERY
   select count(*) num_proj
   from project
   where plocation = '$loc'
QUERY;
}
function ExplainQuery2($query2){
   return <<<HTML
   <h3>Column: num_proj--query2</h3>
   <pre class="query">$query2</pre>
   <div class="indent"><b>Explanation</b>:
   <p>Simple count of tuples per project location</p></div>
HTML;
}
function Query3($loc){
   return <<<QUERY
   select count(distinct ssn) num_work
   from (employee join works_on on ssn=essn) 
      join project on pno=pnumber
   where plocation = '$loc'
QUERY;
}
function ExplainQuery3($query3){
   return <<<HTML
   <h3>Column: num_work--query3</h3>
   <pre class="query">$query3</pre>
   <div class="indent"><b>Explanation</b>:
   <p>Number of employees who work at this location.</p>
   <ul>   <li><u>count(distinct ssn):</u>
          projects with no workers will have a count of 0; 
          <u>distinct</u> means workers will only be counted once.</li>
   </ul></div>
HTML;
}
function Query4($loc){
   return <<<QUERY
   select nvl(sum(hours),0) tot_hours, nvl(sum(hours*salary/2000),0) tot_cost
   from project join (works_on join employee on essn=ssn) on pnumber = pno
   where plocation = '$loc'
QUERY;
}
function ExplainQuery4($query4){
   return <<<HTML
   <h3>Column: tot_hours, and tot_cost-- query4</h3>
   <pre class="query">$query4</pre>
   <div class="indent"><b>Explanation</b>:
   <ul>
   <li><u>sum(hours*salary/2000)</u> calculates this value for each tuple in the join
   on the from line so it is correct for each employee.</li>
   <li>NVL: if there is no employee who works on the project, 
   ensures that zeros are returned rather than nulls</li>
   </ul></div>
HTML;
}
function Query5($loc){
   return <<<QUERY5
      select count(distinct dnumber) num_dept
      from dept_locations
      where dlocation = '$loc'
QUERY5;
}

function ExplainQuery5($query5){
   return <<<HTML
   <h3>Column: num_dept -- query5</h3>
   <pre class="query">$query5
   </pre>
   <div class="indent"><b>Explanation</b>:
    Simple count of the number of different departments at a given location.
   </div>
HTML;
}
function ExplainQueries(){
$query0 = Query0();
echo ExplainQuery0($query0);
$query1 = Query1('XXXXXX');
echo ExplainQuery1($query1);
$query2 = Query2('XXXXXX');
echo ExplainQuery2($query2);
$query3 = Query3('XXXXXX');
echo ExplainQuery3($query3);
$query4 = Query4('XXXXXX');
echo ExplainQuery4($query4);
$query5 = Query5('XXXXXX');
echo ExplainQuery5($query5);
}

function InsertRow($lname,$num_proj,$num_emp,$num_dept,$num_work,$tot_hours,$tot_cost){
   global $uid,$con,$insert_number;
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
   ShowTable($result, 1); # 1 means free $result
}
function ShowTable($result,$free = 0){
  #changed from last version.  do not always want to free $result
  #$free is assumed to be ==0 unless stated otherwise 
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
   if ($free) { $result->free();}
   # this version does automatically not free because of reuse of $result

   echo "</table>";
}
?>

</body>
</html>
