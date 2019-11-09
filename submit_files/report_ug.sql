CREATE OR REPLACE PROCEDURE ins_dept_summary(
       v_dname dept_summary.dname%TYPE, 
       v_dnumber dept_summary.dnumber%TYPE, 
       v_emp_type dept_summary.emp_type%TYPE, 
       v_proj_type dept_summary.proj_type%TYPE, 
       v_num_emps dept_summary.num_emps%TYPE, 
       v_hours dept_summary.hours%TYPE, 
       v_cost dept_summary.cost%TYPE, 
       v_user_name dept_summary.user_name%TYPE, 
       v_insert_number dept_summary.insert_number%TYPE)

BEGIN
    cs450.ins_dept_summary(
       v_dname ,
       v_dnumber ,
       v_emp_type ,
       v_proj_type ,
       v_num_emps ,
       v_hours ,
       v_cost ,
       'STUDEN_A',
       v_insert_number);
END
