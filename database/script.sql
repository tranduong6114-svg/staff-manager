CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE employees (
    emp_id VARCHAR(50) PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    base_salary BIGINT NOT NULL,
    actual_salary BIGINT NOT NULL,
    birthday DATE NOT NULL,
    department_id INT,
    position_id INTEGER,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (position_id) REFERENCES positions(id)
);

INSERT INTO departments (name)
VALUES ('Phòng IT'), ('Phòng Kế toán'), ('Phòng Nhân sự');
INSERT INTO positions (name)
VALUES ('Giám đốc'), ('Trưởng phòng'), ('Nhân viên');