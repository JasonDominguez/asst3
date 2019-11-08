CREATE OR REPLACE PROCEDURE ins_dept_summary AS

    CURSOR nemps IS
    select count(*) num_emps
    from department
