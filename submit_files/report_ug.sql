-- this one is for the project_location report
-- This is an example problem to show what is required in the PL/SQL problem.
-- 
-- The problem is to produce a report on each project location, that is,
-- any location where any department has a project.  There is to be only
-- one report for each location, even if there are several projects there.
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

CREATE OR REPLACE PROCEDURE report_ug AS
   -- cursor to fetch the project locations
   -- and the number of emps in each.
   CURSOR dep_name IS
   select distinct dname
   from department

   c_dep_name  dep_name%rowtype;
   -- v_lname proj_loc.lname%type would seem more natural and useful
   -- but it is not legal PL/SQL.
   -- this record will hold the location :  c_proj_loc.lname 
   -- that all the following cursors reference.
   -- the cursor above, proj_loc, is the outer cursor.
   -- the following cursors are opened once for each location.

   -- the number of projects at the location
   CURSOR dep_num IS
   select distinct dnumber
   from department
   where dname = c_dep_name.dname;

   c_dep_num dep_num%rowtype;

   -- number of employees who work on projects
   -- in this location
   CURSOR e_num IS
   select count(distinct ssn) e_num
   from (employee join works_on on ssn=essn) 
         right join project on pno=pnumber
   where dname = c_dep_name.dname;

   c_e_num e_num%rowtype;

   -- total hours and cost of those employees
   CURSOR emp_totals IS
   select nvl(sum(hours),0) tot_hours, nvl(sum(hours*salary/2000),0) tot_cost
   from project left join (works_on join employee on essn=ssn) on pnumber = pno
   where dname = c_dep_name.dname;

   c_emp_totals emp_totals%rowtype;


   i_num  NUMBER := 0; -- insert_number

BEGIN
   for locrec in dep_name loop
      c_dep_name.dname := locrec.dname;

      -- all the inner cursors calculate single tuples
      -- so no looping is necessary: just open, one fetch and close
      open dep_num;
      fetch dep_num into c_dep_num;
      close dep_num;

      open e_num;
      fetch e_num into c_e_num;
      close e_num;

      open emp_totals;
      fetch emp_totals into c_emp_totals;
      close emp_totals;


      i_num := i_num + 1;

      CS450.ins_dept_summary(
            c_dep_name.dname,
            c_dep_num.dnumber,
            'DEPT',
            'DEPT',
            locrec.e_num, -- calculated by the outer cursor
            c_emp_totals.tot_hours,
            c_emp_totals.tot_cost,
            'HBROW',
            i_num
            );

   end loop; -- proj_loc
END;
/
