CREATE OR REPLACE PROCEDURE ins_dept_summary(
       
       CURSOR dep_info IS
       select distinct dname dname, dnumber dnumber
       from department;

       p_dep_info dept_summary.dep_info%TYPE, 
       
       
       CURSOR emp_type IS
       select ssn as 'DEPT'
       from employee, department, project, works_on
       where department.dname = p_dep_infp.dname
       and department.dnumber = employee.dno
       and employee.ssn = works_on.essn
       and works_on.pno = project.pnumber
       and project.dnum = department.dnumber
       group by ssn;

       p_emp_type dept_summary.emp_type%TYPE, 
    

       CURSOR proj_type IS
       select ssn as 'DEPT'
       from EMPLOYEE, DEPARTMENT, PROJECT, WORKS_ON
       where department.dname = p_dep_info.dname
       and department.dnumber = employee.dno
       and employee.ssn = works_on.essn
       and works_on.pno = project.pnumber
       and project.dnum = department.dnumber
       group by ssn

       p_proj_type dept_summary.proj_type%TYPE, 
       

       CURSOR num_emps IS
       select count(distinct ssn) num_emps
       from (employee join department on dno=dnumber) 
       where dname = p_dep_info.dname;

       p_num_emps dept_summary.num_emps%TYPE, 
       

       CURSOR emp_totals IS
       select nvl(sum(hours),0) tot_hours, nvl(sum(hours*salary/2000),0) tot_cost
       from project left join (works_on join employee on essn=ssn) on pnumber = pno
       where dname = p_dep_info.dname;
  
       p_emp_totals dept_summary.emp_totals%TYPE, 
        

       
       p_insert_number NUMBER := 0);

BEGIN

    p_insert_number := p_insert_number + 1;

    cs450.ins_dept_summary(
       p_dep_info.dname ,
       p_dep_info.dnumber ,
       p_emp_type.emp_type ,
       p_proj_type.proj_type ,
       p_num_emps.num_emps ,
       p_emp_totals.tot_hours ,
       p_emp_totals.tot_cost ,
       'HBROW',
       p_insert_number.insert_number);
END;
/
