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
   echo nameTable();
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
    $uid = 'JDOMINGU';
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
echo ExplainQueries();
CleanTable();
$query0=Query0();

echo "Retrieving the list of locations with <br><pre class='query'>$query0</pre> ";
$outer = & $con->query($query0);
error_check($outer, 'querying with query0');
$insert_number = 1;


while( is_array(   $department = $outer->fetchRow(MDB2_FETCHMODE_ASSOC))){
   $dep = $department['dname'];
   $dnum = $department['dnumber'];
   echo "<h3>Retrieving results for department $dep</h3>";

   $query1 = Query1($dep);
   $deptDept = & $con->query($query1);
   error_check($deptDept, 'querying with query1');
   $row1 = $deptDept->fetchRow(MDB2_FETCHMODE_ASSOC);
   echo ShortExp($row1, $query1);
   InsertRow($dep, $dnum, 'DEPT', 'DEPT', $row1['num_emps'], $row1['hours'], $row1['cost']);
   $deptDept->free();
   $insert_number++;

   $query2 = Query2($dep);
   $deptNonDept = & $con->query($query2);
   error_check($deptNonDept, 'querying with query2');
   $row2 = $deptNonDept->fetchRow(MDB2_FETCHMODE_ASSOC);
   echo ShortExp($row2, $query2);
   InsertRow($dep, $dnum, 'DEPT', 'NONDEPT', $row2['num_emps'], $row2['hours'], $row2['cost']);
   $deptNonDept->free();
   $insert_number++;

   $query3 = Query3($dep);
   $nonDeptDept = & $con->query($query3);
   error_check($nonDeptDept, 'querying with query3');
   $row3 = $nonDeptDept->fetchRow(MDB2_FETCHMODE_ASSOC);
   echo ShortExp($row3, $query3);
   InsertRow($dep, $dnum, 'NONDEPT', 'DEPT', $row3['num_emps'], $row3['hours'], $row3['cost']);
   $nonDeptDept->free();
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

   <title>Assignment 3</title>
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
function nameTable(){
    return <<<EXPLAIN
       <table border="1">
       <tr><td colspan="2">This page is the solely the work of</td></tr>
       <tr><td>jdomingu</td><td>hbrow</td></tr>
       <tr><td>Jason Dominguez</td><td>Hanna Brown</td></tr>
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
<div class='problem'>
The approach taken here is the Big Loop approach.  An outer loop retrieves a list
of all the departments.  For each department three queries are made. One detailing department
employees working on department projects, one for department employees working on non-department projects,
and one for non-department employees working on department projects. The resulting data from each
query is inserted along with corresponding department information.
</div>
HTML;
}
function CleanTable(){
    # builds the procedure call to clean the table, executes the query,
    # checks the result and explains what is going on.
    global $uid,$con;
    print "<h3>First clean proj_dept_summary table</h3>";
    // $query & $result are automatically local to CleanTable because not global
    $query = "
    BEGIN 
        cs450.clean_dept_summary('$uid'); 
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
    select distinct dname, dnumber from department
QUERY;
}
function ExplainQuery0($query0){
    return <<<HTML
    <h3>Query0: We start with an Outer Loop</h3>
    <pre class="query">$query0</pre>
    <div class="indent"><b>Explanation</b>:
    The resulting table of department names and their corresponding department numbers 
    will be used as the outer loop in the Big Loop approach.
    </div>
HTML;
}
function Query1($dep){
    return <<<QUERY
    with
        query as
    (select ssn, sum(hours) as sum_hours, salary
    from EMPLOYEE, DEPARTMENT, PROJECT, WORKS_ON
    where department.dname = '$dep'
    and department.dnumber = employee.dno
    and employee.ssn = works_on.essn
    and works_on.pno = project.pnumber
    and project.dnum = department.dnumber
    group by ssn, salary)
    select count(ssn) num_emps, nvl(sum(sum_hours),0) hours, nvl(sum(sum_hours*salary/2000),0) cost
    from query
QUERY;
}
function ExplainQuery1($query1){
    return <<<HTML
    <h3>Query1: The department name is used to find department employees working on department projects</h3>
    <pre class="query">$query1</pre>
    <div class="indent"><b>Explanation</b>:
    <ul>
        <li>Queries a selection of all department employees assigned to any projects belonging to the department</li>
        <li>The number of employees in this selection is counted as num_emps.</li>
        <li>The total number of hours department employees worked on departement projects is summed as hours.</li>
        <li>The hours each department employee worked on departement projects is summed, multiplied by their salary and divided by 2000.
            This value is summed with the other queried employees as cost.</li>
    </ul></div>
HTML;
}
function Query2($dep){
    return<<<QUERY
    with
        query as
    (select ssn, sum(hours) as sum_hours, salary
    from EMPLOYEE, DEPARTMENT, PROJECT, WORKS_ON
    where department.dname = '$dep'
    and department.dnumber = employee.dno
    and employee.ssn = works_on.essn
    and works_on.pno = project.pnumber
    and project.dnum <> department.dnumber
    group by ssn, salary)
    select count(ssn) num_emps, nvl(sum(sum_hours),0) hours, nvl(sum(sum_hours*salary/2000),0) cost
    from query
QUERY;
}
function ExplainQuery2($query2){
    return <<<HTML
    <h3>Query2: The department name is used to find department employees working on non-department projects</h3>
    <pre class="query">$query2</pre>
    <div class="indent"><b>Explanation</b>:
    <ul>
        <li>Queries a selection of all department employees assigned to any projects belonging to other department</li>
        <li>The number of employees in this selection is counted as num_emps.</li>
        <li>The total number of hours department employees worked on non-departement projects is summed as hours.</li>
        <li>The hours each department employee worked on non-departement projects is summed, multiplied by their salary and divided by 2000. 
            This value is summed with the other queried employees as cost.</li>
    </ul></div>
HTML;
}
function Query3($dep){
    return <<<QUERY
    with
        query as
    (select ssn, sum(hours) as sum_hours, salary
    from EMPLOYEE, DEPARTMENT, PROJECT, WORKS_ON
    where department.dname = '$dep'
    and department.dnumber <> employee.dno
    and employee.ssn = works_on.essn
    and works_on.pno = project.pnumber
    and project.dnum = department.dnumber
    group by ssn, salary)
    select count(ssn) num_emps, nvl(sum(sum_hours),0) hours, nvl(sum(sum_hours*salary/2000),0) cost
    from query
QUERY;
}
function ExplainQuery3($query3){
    return <<<HTML
    <h3>Query3: The department name is used to find non-department employees working a on department projects</h3>
    <pre class="query">$query3</pre>
    <div class="indent"><b>Explanation</b>:
    <ul>
        <li>Queries a selection of all non-department employees assigned to any projects belonging to the department.</li>
        <li>The number of employees in this selection is counted as num_emps.</li>
        <li>The total number of hours non-department employees worked on departement projects is summed as hours.</li>
        <li>The hours each non-department employee worked on the departement's projects is summed, multiplied by their salary and divided by 2000. 
            This value is summed with the other queried employees as cost.</li>
    </ul></div>
HTML;
}
function ShortExp($row, $query){
    return <<<HTML
    <p>Retrieving NUM_EMPS, HOURS, COST with <pre class="query">$query</pre></p>
    <p>Retrieved NUM_EMPS: $row[num_emps], HOURS: $row[hours], COST: $row[cost]</p>
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
}

function InsertRow($dep, $dnum, $emp_type, $proj_type, $num_emps, $hours, $cost){
    global $uid,$con,$insert_number;
    $query=<<<QUERY
    BEGIN
        cs450.ins_dept_summary(
            '$dep',  -- department name
            $dnum,  -- department number
            '$emp_type',  -- type of employee (DEPT or NONDEPT)
            '$proj_type',  -- type of project (DEPT or NONDEPT)
            $num_emps,  -- number of $emp_type employees working on $proj_type projects
            $hours,  -- total number of hours
            $cost, -- total cost
            '$uid',     -- me
            $insert_number -- row number
            );
    END;
QUERY;

    print("Inserting with <pre class='query'>$query</pre>");
    $result =& $con->query($query);
    error_check($result,'procedure call');
    $result->free();
    # important to free memory used by $result
}
function DisplayResults(){
    global $con, $uid;
    $query="select * from TABLE(cs450.v_dept_summary('$uid'))";
    print <<<_HTML_
        <h4>Checking results of Database Procedure Inserts</h4>
        using query <pre class='query'>$query</pre>
_HTML_;
    $result =& $con->query($query);
    error_check($result,'querying v_dept_summary');
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
    

