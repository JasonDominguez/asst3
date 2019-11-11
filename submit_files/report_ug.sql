CREATE OR REPLACE PROCEDURE report_ug(
    v_dname dept_summary.dname%TYPE, 
    v_dnumber dept_summary.dnumber%TYPE, 
    v_emp_type dept_summary.emp_type%TYPE, 
    v_proj_type dept_summary.proj_type%TYPE, 
    v_num_emps dept_summary.num_emps%TYPE, 
    v_hours dept_summary.hours%TYPE, 
    v_cost dept_summary.cost%TYPE, 
    v_user_name dept_summary.user_name%TYPE, 
    v_insert_number dept_summary.insert_number%TYPE) AS

    CURSOR dname IS
    select distinct dname
    from department;

    CURSOR dnumber IS
    select distinct dnumber
    from department;
    
    CURSOR num_emps IS
    select count(distinct ssn) num_emps
    from employee join department on dno=dnumber 
    where dname = v_dname.dname;      

    CURSOR emp_totals IS
    select nvl(sum(hours),0) tot_hours, nvl(sum(hours*salary/2000),0) tot_cost
    from project left join (works_on join employee on essn=ssn) on pnumber = pno
    where dname = v_dname.dname;

    insert_number  NUMBER := 0;
        

BEGIN
    
    cs450.ins_dept_summary(
    V_DNAME,
    V_DNUMBER,
    V_EMP_TYPE,
    V_PROJ_TYPE,
    V_NUM_EMPS,
    V_HOURS,
    V_COST,
    V_USER_NAME,
    V_INSERT_NUMBER
    );
END;
/
