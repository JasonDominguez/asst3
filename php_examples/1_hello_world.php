<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

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

<title>PHP Example: #1</title>
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
} else {  
   // all the variables have been supplied correctly in the form
   // so this time we generate page 2
   // note that all the HTML from <head> to <body> is generated in either case.
   //
   // ORACLE wants login info in UPPER case
   $uid = strtoupper($_POST['user_name']);
   $sid = strtoupper($_POST['sid']);
print <<<EXPLAIN
<div class='problem'>
<h2>This is Page 2</h2>
<h2>Hello World</h2>
We will not log $uid into $sid just yet.
</div>
<hr>
   <h2>Debugging a simple variable with print_r(): print_r(\$uid)</h2>
EXPLAIN;
// print_r will print out the value of a simple or complex variable
print_r ($uid);
// echo works like print but it can output a list of items.
echo "<hr>","<h2>",'Debugging an Array with print_r(): print_r($_POST)','</h2>';
echo  "<h3>First we will protect the password with: \$_POST['password'] = '########';</h3>";
$_POST['password'] = '########';
print_r($_POST);
echo '<hr><h2>A nicer result surrouding print_r() with PRE tags</H2>';
echo '<pre>';
print_r($_POST); 
echo '</pre>';

?>
<h4>&lt;pre> makes the HTML engine render all white space as given.  Without it, whitespace is
collapsed.</h4>
<h2>This is done with 'echo': echo '&lt;pre>'; print_r ($_POST); echo '&lt;/pre>';</h2>
<?php 
}
?>
</body>
</html>
