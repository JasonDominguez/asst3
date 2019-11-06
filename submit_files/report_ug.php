<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!-- vim:set filetype=php: -->

<?php
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
   require_once 'MDB2.php';
   $uid = strtoupper($_POST['user_name']);
   VerifyUID($uid);
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
// To make sure you get credit, be sure you uncomment**************************************!!!
// the following line and replace XXXXXXXX with your
// ORACLE ID:
//
//      $uid = 'JDOMINGU';
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

//DNAME, DNUMBER, EMP_TYPE, PROJ_TYPE, NUM_EMPS, HOURS, COST, USER_NAME, INSERT_NUMBER

// This sequence of functions all output text that is either
// 1. sent to standard output, or
// 2. stored in program variables
echo Approach();
$query1 = Query1();
echo ExplainQuery1($query1);
$query2 = Query2();
echo ExplainQuery2($query2);
$query3 = Query3();
echo ExplainQuery3($query3);
$query4 = Query4();
echo ExplainQuery4($query4);
$bigquery = BigQuery($query1,$query2,$query3,$query4);
echo ExplainBigQuery($bigquery);

CleanTable();

echo "<h4>Then run the query explained above</h4>
   <p>The query is shown above so there is no need to display it again here</p>";
$result =& $con->query($bigquery);
error_check($result,'querying with bigquery');
ShowTable($result,0);
  # we do not free $result since we need the results for inserting below
  # the 0 means do not free.
  # but fetchRow() is an iterator that has run off the end of the result set
  # so we have to set it back to the beginning:
$result->seek(0);

echo ExplainExtraQuery();

echo  "<h4>Insert each tuple of the results of bigquery</h4>";
  $insert_number = 1;
while ($array = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
   # you can retrieve individual fields by name from the $array hash
   # and put them in individual variables like this:
   $lname =$array['lname']; 
   # or use the method shown below, where they have to be enclosed
   # in curly braces
   #
   # Here we use a separate query to get the num_dept information for each row
   $query5 = Query5($lname);
   $num_dept = $con->queryOne($query5);
   error_check($num_dept,'retrieving num_dept');
   #queryOne is a handy shortcut to return a single value from the DB
   print "Ran <pre class='query'>$query5 </pre> and retrieved <b> $num_dept</b> for num_dept.<br>";

   InsertRow($lname,
      $array['num_proj'],
      $array['num_emp'],
      $num_dept,
      $array['num_work'],
      $array['tot_hours'],
      $array['tot_cost']
          );

   $insert_number++;
}
   # important to free memory used by $result
$result->free();
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

   <title>PHP Example: #4</title>
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
   // Sometimes you have to uncomment the next line
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
<div class='problem'>The approach taken here is to calculate the columns for 
most of the answer in separate SQL queries that are to be collected together in 
a WITH clause at the beginning of one select statement.  The queries are all 
joined together on lname to produce the final result.  One difficult part is 
collected on a per-row basis. This is to show that you are not required to use 
the WITH approach.
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
function Query1(){
   return <<<QUERY
   select plocation lname, count(fname) num_emp
   from (select distinct plocation from project)
   left join employee on address like '%'||plocation||'%'
   group by plocation
   order by lname
QUERY;
}
function ExplainQuery1($query1){
   return <<<HTML
   <h3>Columns: lname and num_emp--query1</h3>
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
}
function Query2(){
   return<<<QUERY
   select plocation lname, count(*) num_proj
   from project
   group by plocation
   order by lname
QUERY;
}
function ExplainQuery2($query2){
   return <<<HTML
   <h3>Columns: lname and num_proj--query2</h3>
   <pre class="query">$query2</pre>
   <div class="indent"><b>Explanation</b>:
   <p>Simple count of tuples per project location</p></div>
HTML;
}
function Query3(){
   return <<<QUERY
   select plocation lname, count(distinct ssn) num_work
   from (employee join works_on on ssn=essn) 
      right join project on pno=pnumber
   group by plocation
   order by lname
QUERY;
}
function ExplainQuery3($query3){
   return <<<HTML
   <h3>Columns: lname and num_work--query3</h3>
   <pre class="query">$query3</pre>
   <div class="indent"><b>Explanation</b>:
   <ul><li><u>right join:</u> ensures each project appears, even if no one works on it</li>
   <li><u>count(distinct ssn):</u> does two things-- 
          counting ssn means nulls will not be counted
          and projects with no workers will have a count of 0; 
          <u>distinct</u> means workers will only be counted once.</li>
   </ul></div>
HTML;
}
function Query4(){
   return <<<QUERY
   select plocation lname, nvl(sum(hours),0) tot_hours, nvl(sum(hours*salary/2000),0) tot_cost
   from project left join (works_on join employee on essn=ssn) on pnumber = pno
   group by plocation
   order by lname
QUERY;
}
function ExplainQuery4($query4){
   return <<<HTML
   <h3>Columns: lname, tot_hours, and tot_cost-- query4</h3>
   <pre class="query">$query4</pre>
   <div class="indent"><b>Explanation</b>:
   <ul><li><u>left join:</u>ensures each project appears, even if no one works on it</li>
   <li><u>sum(hours*salary/2000)</u> calculates this value for each tuple in the join
   on the from line so it is correct for each employee.</li>
   </ul></div>
HTML;
}

function BigQuery($query1,$query2,$query3,$query4){
   return <<<QUERY
   with 
      query1 as 
   ($query1),
      query2 as 
   ($query2),
      query3 as
   ($query3),
      query4 as 
   ($query4)            /* main query starts here */
   select query1.lname, num_proj, num_emp,  num_work, tot_hours, tot_cost
   from query1, query2, query3, query4
   where query1.lname = query2.lname
   and   query2.lname = query3.lname
   and   query3.lname = query4.lname
QUERY;
}
function ExplainBigQuery($bigquery){
   return <<<HTML
   <h3>Final Query--bigquery</h3>
   <pre class="query">$bigquery</pre>
   <div class="indent">This just unites all four queries using the <u>with</u> construct</div>
HTML;
}
function ExplainExtraQuery(){
   return <<<QUERY
   <p>This gets all the information we need except <b>num_dept</b> which we will
   calculate for each row using the following query:</p>
   <pre class="query">
   select count(distinct dnumber) num_dept
   from dept_locations
   where dlocation = 'XXXX'
   </pre>

   <p>with the current plocation substituted for XXXX.</p>
QUERY;
}
function Query5($lname){
   return <<<QUERY5
      select count(distinct dnumber) num_dept
      from dept_locations
      where dlocation = '$lname'
QUERY5;
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

