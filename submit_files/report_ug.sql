CREATE OR REPLACE PROCEDURE report_ug(
    v_dname report_ug.dname%TYPE, 
    v_dnumber report_ug.dnumber%TYPE, 
    v_emp_type report_ug.emp_type%TYPE, 
    v_proj_type report_ug.proj_type%TYPE, 
    v_num_emps report_ug.num_emps%TYPE, 
    v_hours report_ug.hours%TYPE, 
    v_cost report_ug.cost%TYPE, 
    v_user_name report_ug.user_name%TYPE, 
    v_insert_number dreport_ug.insert_number%TYPE) AS

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
