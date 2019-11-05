-- be sure to read the prompts at the bottom.
-- this procedure is for the project_location report
-- This is an example problem to show what is required 
-- in general in the PL/SQL problem.
-- 
-- The specific problem here is to produce a report on each project location, that is,
-- any location where any department has a project.  There is to be only
-- one report for each location, even if there are several projects there.
-- Of course your problem is not this one, but it is similar in format.
-- 
-- We want to know for each location:
-- 
-- lname: The name of that location;
-- num_proj: The number of projects in that location;
-- num_emp: The number of employees with addresses in that location;
-- num_dept: The number of departments with offices in that location;
-- num_work: The number of different employees who work on projects in that location;
-- tot_hours: The total hours worked by employees on projects in that location;
-- tot_cost: The total cost of those hours;
--
-- This produre uses the BIG LOOP approach. An outer loop gets the project locations
-- (aliased to lname) as well as one value (num_emp).  For each location the loop
-- opens a series of cursors that get the rest of the values for that particular
-- location.  When all the values for a location have been collected, they are
-- inserted. Note that the outer loop could just get the locations and use a 
-- separate cursor for num_emp.

CREATE OR REPLACE PROCEDURE report_loc AS
   -- cursor to fetch the project locations
   -- and the number of emps in each.
   CURSOR proj_loc IS
   select plocation lname, count(fname) num_emp
   from (select distinct plocation from project)
   left join employee on address like '%'||plocation||'%'
   group by plocation
   order by lname;

   c_proj_loc  proj_loc%rowtype;
   -- v_lname proj_loc.lname%type would seem more natural and useful
   -- but it is not legal PL/SQL.
   -- this record will hold the location :  c_proj_loc.lname 
   -- that all the following cursors reference.
   -- the cursor above, proj_loc, is the outer cursor.
   -- the following cursors are opened once for each location.

   -- the number of projects at the location
   CURSOR nproj IS
   select  count(*) num_proj
   from project
   where plocation = c_proj_loc.lname;

   c_nproj nproj%rowtype;

   -- number of employees who work on projects
   -- in this location
   CURSOR nwork IS
   select count(distinct ssn) num_work
   from (employee join works_on on ssn=essn) 
         right join project on pno=pnumber
   where plocation = c_proj_loc.lname;

   c_nwork nwork%rowtype;

   -- total hours and cost of those employees
   CURSOR emp_totals IS
   select nvl(sum(hours),0) tot_hours, nvl(sum(hours*salary/2000),0) tot_cost
   from project left join (works_on join employee on essn=ssn) on pnumber = pno
   where plocation = c_proj_loc.lname;

   c_emp_totals emp_totals%rowtype;

   -- number of depts with offices in that location
   CURSOR dloc IS
   select count(distinct dnumber) num_dept
   from dept_locations
   where dlocation = c_proj_loc.lname;

   c_dloc dloc%rowtype;

   i_num  NUMBER := 0; -- insert_number

BEGIN
   for locrec in proj_loc loop
      c_proj_loc.lname := locrec.lname;

      -- all the inner cursors calculate single tuples
      -- so no looping is necessary: just open, one fetch and close
      open nproj;
      fetch nproj into c_nproj;
      close nproj;

      open nwork;
      fetch nwork into c_nwork;
      close nwork;

      open emp_totals;
      fetch emp_totals into c_emp_totals;
      close emp_totals;

      open dloc;
      fetch dloc into c_dloc;
      close dloc;

      i_num := i_num + 1;

      cs450.ins_proj_loc_summary(
            c_proj_loc.lname,
            c_nproj.num_proj,
            locrec.num_emp, -- calculated by the outer cursor
            c_dloc.num_dept,
            c_nwork.num_work,
            c_emp_totals.tot_hours,
            c_emp_totals.tot_cost,
            'CS450',
            i_num
            );


   end loop; -- proj_loc
END;
/
show errors
prompt Before running this procedure, be sure to
prompt exec cs450.clean_proj_loc_summary('CS450'); -- replace with your username
prompt to remove any tuples you inserted before
prompt Change the CS450 user name in cs450.ins_proj_loc_summary to yours, IN CAPS
prompt After running this procedure, be sure to
prompt select * from TABLE(cs450.v_proj_loc_summary('CS450')) -- replace with your username
prompt to see what was inserted
