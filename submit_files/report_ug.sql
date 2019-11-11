CREATE OR REPLACE PROCEDURE report_ug(
    V_DNAME VARCHAR2(15) := dname;
    V_DNUMBER NUMBER(3)  := dnumber;
    V_EMP_TYPE VARCHAR2(16):= 'DEPT';
    V_PROJ_TYPE VARCHAR2(16):= 'NONDEPT';
    V_NUM_EMPS NUMBER(3) := num_emps;
    V_HOURS NUMBER(5) := tot_hours;
    V_COST NUMBER(8,2) := tot_cost;
    V_USER_NAME VARCHAR2(10) := 'HBROW';
    V_INSERT_NUMBER NUMBER(4) := insert_number;
) AS
       
       CURSOR dname IS
       select distinct dname
       from department;

       CURSOR dnumber IS
       select distinct dnumber
       from department;
    
       CURSOR num_emps IS
       select count(distinct ssn) num_emps
       from (employee join department on dno=dnumber) 
       where dname = v_dep_info.dname;      

       CURSOR emp_totals IS
       select nvl(sum(hours),0) tot_hours, nvl(sum(hours*salary/2000),0) tot_cost
       from project left join (works_on join employee on essn=ssn) on pnumber = pno
       where dname = v_dep_info.dname;

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
