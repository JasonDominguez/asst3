CREATE OR REPLACE PROCEDURE report_ug AS
   CURSOR dep_name IS
   select distinct dname
   from department;

   c_dep_name  dep_name%rowtype;


   CURSOR dep_num IS
   select distinct dnumber
   from department
   where dname = c_dep_name.dname;

   c_dep_num dep_num%rowtype;


   CURSOR e_num IS
   select count(distinct ssn) e_num
   from (employee join works_on on ssn=essn) 
         right join project on pno=pnumber
   where dname = c_dep_name.dname;

   c_e_num e_num%rowtype;


   CURSOR emp_totals IS
   select nvl(sum(hours),0) tot_hours, nvl(sum(hours*salary/2000),0) tot_cost
   from project left join (works_on join employee on essn=ssn) on pnumber = pno
   where dname = c_dep_name.dname;

   c_emp_totals emp_totals%rowtype;


   i_num  NUMBER := 0; -- insert_number

BEGIN
   for dep in dep_name loop
      c_dep_name.dname := dep.dname;


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

      cs450.ins_dept_summary(
            c_dep_name.dname,
            c_dep_num.dnumber,
            'DEPT',
            'DEPT',
            dep.e_num, -- calculated by the outer cursor
            c_emp_totals.tot_hours,
            c_emp_totals.tot_cost,
            'HBROW',
            i_num
            );

   end loop; 
END;
/
