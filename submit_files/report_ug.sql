CREATE OR REPLACE PROCEDURE ins_dept_summary(
       p_dname dept_summary.dname%TYPE, 
       p_dnumber dept_summary.dnumber%TYPE, 
       p_emp_type dept_summary.emp_type%TYPE, 
       p_proj_type dept_summary.proj_type%TYPE, 
       p_num_emps dept_summary.num_emps%TYPE, 
       p_hours dept_summary.hours%TYPE, 
       p_cost dept_summary.cost%TYPE, 
       p_user_name dept_summary.user_name%TYPE, 
       p_insert_number dept_summary.insert_number%TYPE)
