<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!-- vim: set filetype=php: -->
<?php
 require_once('highlight.php');
 if (isset($_GET['code'])) { die(highlight_file(__FILE__, 1)); }
?>

<html>
<head>
<title>EMp Summary Example</title>
<style type='text/css'>
H1,H2,H3,H4{background:#ddddbb;}
TABLE{background:#ddbbdd;}
PRE{font-style:italic;
background:#bbdddd;
}
div.notice{background:#ddbbbb;
 font-size: 120%;}
</style>
</head>
<body>
<?php 
# note several things here
# comments may be pound signs as here or C++ double slashes
# HTML outside of PHP tags works like regular html
# so the tags above will be sent to the HTML page
#
# The first page to send is form.  We can tell whether to send the form
# because user will have provided values that end up in the $_POST array.
#
if (! isset($_POST['user_name'] , $_POST['password'] ,$_POST['sid'])
    || !$_POST['user_name'] || !$_POST['password'] || !$_POST['sid']) {
  ?><h2>Supply information to login to your Oracle Account</h2><br/><?php 
  # error in elmasri: PHP_SELF below must not be in quotes
    # note this is another way to send HTML to the page
    # this is creating a form.  the advantage is the PHP variable
    # value is embedded in the action tab.  When this is printed
    # the form will show up as something like
    # action="/~cs450/php/example.php"
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
  #error in elmasri: _HTML_ must be snug up against LHS, not indented
} else {  # we have the values we need, so we can go to work.
  ?><h1>Working with Oracle Using PHP</h1><br> <br>
    <table border="1">
    <tr><td colspan="2">This page is the solely the work of</td></tr>
    <tr><td>pratik naik</td><td>pnaik</td></tr>
    <tr><td>scott fox</td><td>sfox</td></tr>
    <tr><td colspan="2">We have not recieved aid from anyone<br>
    else in this assignment.  We have not given <br>
      anyone else aid in the 
    assignment</td></tr>
    </table>
    
    <?php 
  require_once 'MDB2.php';
  # elmasri uses DB.php which is now out of date
  #
  #here is the magic incantation for how to connect
   $sid = strtoupper($_POST['sid']);
//$sid .= '.cs.odu.edu'; #new fall10--error in oracle setup?
  //not this semester
   $uid = strtoupper($_POST['user_name']);
  # to limit access to the page, uncomment the following code
  # and add or subtract UIDs from the $legal_names array
  # (be sure the 'HTML;' ends up flush with the LHS to terminate the print statement
//   $legal_names=array(
//       'CS450','CS450A','CS450B','CS450C','CS450D');
//   $legal = 0;
//   for($i=0; $i<count($legal_names); $i++){
//     if(! strcmp($uid,$legal_names[$i]) ){
//       $legal++;
//       break;
//     }
//   }
//   if (! $legal){
//     print <<<HTML
//       <h1>ACCESS DENIED</h1>
//       </body>
//       </html>
//HTML;
//     exit;
//   }
   $dsn= array(
   'username' =>$uid,
   'password' => $_POST['password'],
   'hostspec'=>"oracle.cs.odu.edu:1521/$sid",
   'phptype'=>'oci8',
   );
  $con =& MDB2::connect($dsn, array('emulate_database' => false));
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
  # 
  # some error checking
function error_check($e2check,$e_in_msg){
  global $con;
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
  error_check($con,'connect');
  echo("<br><h2>SIMPLE QUERY</h2><br>"); # another way to send html to the html page

  $query = "select * from C";
  echo("<br><pre>$query</pre><br>");
  $result =& $con->query($query);
  # Since this is a select, the result will be an array.

  error_check($result, 'querying C');

  # now we end the PHP tag briefly to print the HTML TABLE tag and then resume PHP
  ?><table border='1'><?php 
  $header=0;
  while ($array = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
       # the MDB2_FETCHMODE_ASSOC param means each row that is returned is really
    # a hash with the field names as the keys to the values.  
    # For example, $array['snum'] == 'S1'.  We use the first row to build both
    # the table header as well as the first row of values.
    if (!$header){
      $header=1;
      # just to show you can embed the closing and opening PHP tags inside an if
      # or while.
      ?><TR><?php 
        foreach($array as $key => $field){
           # this form of foreach gets both the key and value of the hash
           # here we print the tags b/c we want to send a PHP variable too
           print("<th>$key</th>");
        }
      ?></TR><?php 
    }
    ?><tr><?php 
          #this form of foreach only gets the values
       foreach ($array as $field){
         print("<td>$field</td>");
       }
    ?></tr><?php 
  }
  # important to free memory used by $result
  $result->free();

  ?></table>
  <h2> Database procedure calls</h2>
  
  <?php 
  # IMPORTANT!!!
  # See note above about setting $uid to your Oracle login
  $query = "BEGIN cs450.clean_emp_summary('$uid'); END;";
  print("cleaning with <pre>$query</pre><br/>");
  $result =& $con->query($query);
  error_check($result,'cleaning');
$DB = array(1=>'John:Smith:123456789:Research:Franklin T Wong:30000',
'Franklin:Wong:333445555:Research:James E Borg:40000',
'Alicia:Zelaya:999887777:Administration:Jennifer S Wallace:25000',
'Jennifer:Wallace:987654321:Administration:James E Borg:43000',
'Ramesh:Narayan:666884444:Research:Franklin T Wong:38000',
);
  ?><div class='notice'>Just producing a report on a few employees.  The
  assignment needs a report on all employees<br> Most of this data is faked with random numbers.<br>
  In your program you have to show the SQL queries you use to get the
  real data and their results.<br>
  </div><?php 

for ($v_insert_number=1; $v_insert_number<=count($DB); $v_insert_number++){

  list($v_fname,$v_lname,$v_ssn,$v_dname,$v_sup_name,$v_salary) = 
                                          explode(":",$DB[$v_insert_number]);
  $hourly = $v_salary / 2000;
  // just makin' up data here
  $v_n_deps = rand(0,3);
  $v_d_proj_num = rand(0,3);
  $v_d_proj_hrs = $v_d_proj_num?  5 * $v_d_proj_num * rand(1,2) : 0;
  $v_d_proj_cost = $v_d_proj_hrs * $hourly;
  $v_nd_proj_num = rand(0,3);
  $v_nd_proj_hrs = $v_nd_proj_num?  5 * $v_nd_proj_num * rand(1,2) : 0;
  $v_nd_proj_cost = $v_nd_proj_hrs * $hourly;


      $query =<<<QUERY
        BEGIN cs450.ins_emp_summary( '$v_fname', '$v_lname', '$v_ssn', '$v_dname', 
        '$v_sup_name', $v_salary, $v_n_deps, $v_d_proj_num, $v_d_proj_hrs, 
        $v_d_proj_cost, $v_nd_proj_num, $v_nd_proj_hrs, $v_nd_proj_cost, 
        '$uid', $v_insert_number); END; 
QUERY;
# if you write your code on windows rather than UNIX you will need to add this next line
# to avoid an error.  
$query=preg_replace('/\r/','',$query);
# The error occurs because Windows ends lines with two characters
# ascii 13 and 10 but unix only uses 10.  The PL/SQL process gets upset when it finds
# the carriage returns (ascii 13) in the code for the procedure calls (but in the
# SQL there is no problem).
  # IMPORTANT!!!
   # See note above about setting $uid to your Oracle login
   # or you will not get credit for your inserts
      print("inserting with <pre>$query</pre><br/>");
      $result =& $con->query($query);
      error_check($result,'procedure call');
      $result->free();
  }
  $query="select * from TABLE(cs450.v_emp_summary('$uid'))";
   # See note above about setting $uid to your Oracle login
  print <<<_HTML_
    <h3>Checking results of Database Procedure Inserts</h3>
    using query <pre>$query</pre><br/>
_HTML_;
  $result =& $con->query($query);
  error_check($result,'querying v_emp_summary');

  ?><br><table border='1'><?php 
  $header=0;
  while ($array = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    if (!$header){
      $header=1;
      ?><TR><?php 
        foreach($array as $key => $field){
           print("<th>$key</th>");
        }
      ?></TR><?php 
    }
    ?><tr><?php 
       foreach ($array as  $field){
         print("<td>$field</td>");
       }
    ?></tr><?php 
  }
  # important to free memory used by $result
  $result->free();

  ?></table><?php 
 $con->disconnect();

}
?>

</body>
</html>
