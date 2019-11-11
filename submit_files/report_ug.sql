CREATE OR REPLACE PROCEDURE report_ug AS
       
       CURSOR dep_info IS
       select distinct dname dname, dnumber dnumber
       from department;

       v_dep_info dept_summary.dep_info%TYPE; 
       

       CURSOR num_emps IS
       select count(distinct ssn) num_emps
       from (employee join department on dno=dnumber) 
       where dname = v_dep_info.dname;

       v_num_emps dept_summary.num_emps%TYPE; 
       

       CURSOR emp_totals IS
       select nvl(sum(hours),0) tot_hours, nvl(sum(hours*salary/2000),0) tot_cost
       from project left join (works_on join employee on essn=ssn) on pnumber = pno
       where dname = v_dep_info.dname;
  
       v_emp_totals dept_summary.emp_totals%TYPE; 
        

       v_insert_number NUMBER := 0;

BEGIN
    for dep in dep_info loop
        v_dep_info.dname := dep.dname;

        open num_emps;
        fetch num_emps into v_num_emps;
        close num_emps;
        
        open emp_totals;
        fetch emp_totals into v_emp_totals;
        close emp_totals;


        v_insert_number := v_insert_number + 1;

        cs450.ins_dept_summary(
        v_dep_info.dname ,
        v_dep_info.dnumber ,
        'DEPT' ,
        'DEPT' ,
        v_num_emps.num_emps ,
        v_emp_totals.tot_hours ,
        v_emp_totals.tot_cost ,
        'HBROW',
        v_insert_number.insert_number);
    end loop;
END;
/
