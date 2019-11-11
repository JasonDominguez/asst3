CREATE OR REPLACE PROCEDURE report_ug AS 

    CURSOR dname IS
    select distinct dname
    from department;
    
    v_dname dept_summary.dname%TYPE;


    CURSOR dnumber IS
    select distinct dnumber
    from department;

    v_dnumber dept_summary.dnumber%TYPE;


    CURSOR num_emps IS
    select count(distinct ssn) num_emps
    from employee,department
    where dno=dnumber and
          dname = v_dname.dname;

    v_num_emps dept_summary.num_emps%TYPE;


    CURSOR emp_totals IS
    select nvl(sum(hours),0) tot_hours, nvl(sum(hours*salary/2000),0) tot_cost
    from project, (select *
                   from works_on,employee
                   where essn=ssn)
    where pnumber=pno and
          dname = v_dname.dname;

    v_emp_totals dept_summary.emp_totals%TYPE;

    insert_number  NUMBER := 0;
        

BEGIN
    for locrec in dname loop

        v_dname.dname := locrec.dname;

        open dnumber;
        fetch dnumber into v_dnumber;
        close dnumber;

        open num_emps;
        fetch num_emps into v_num_emps;
        close num_emps;

        open emp_totals;
        fetch emp_totals into v_emp_totals;
        close emp_totals;


        cs450.ins_dept_summary(
        v_dname,
        v_dnumber,
        'DEPT',
        'DEPT',
        v_num_emps,
        v_emp_totals.tot_hours,
        v_emp_totals.tot_cost,
        'HBROW',
        v_insert_number);
    end loop;
END;
/
