# 🎓 BAUST Student Result Management System (BSRMS)

A PHP + MySQL based web application for managing student academic results at the university level. Developed as part of the CSE 2206 Database Management Systems Sessional course at BAUST.

---

## 📌 Project Overview

The BSRMS is a centralized result processing platform designed to replace inefficient, error-prone manual systems. It allows:

- Teachers to upload and update student results
- Students to view their academic performance instantly
- Administrators to track and manage all academic records securely

---

## ✅ Features

- Role-based login (Admin, Teacher, Student)
- Add/update/view results (theory + lab)
- CGPA calculation and sessional separation
- Real-time access to academic records
- Admin dashboard for assigning teachers and managing courses
- Popup modals for clean user interaction
- Secure session and input validation
- Scalable database design using normalization

---

## 🛠️ Tools & Technologies

| Area          | Tools                           |
| ------------- | ------------------------------- |
| Backend       | PHP (Server-side logic)         |
| Frontend      | HTML, CSS, JavaScript           |
| Interactivity | JavaScript (modals, validation) |
| Database      | MySQL (Relational DBMS)         |
| Dev Server    | XAMPP (Apache + MySQL)          |

---

## 🧱 System Architecture

- Follows **Waterfall Model**
- Modules: Login → Role Dashboard → Result Handling → Reporting
- ER Diagram and normalized database used to manage complexity

---

## 📸 Screenshots (Attach Images in GitHub)

- BSRMS Home Page
- Admin Login and Course Assignment
- Teacher Dashboard and Result Entry
- Student Login and Dashboard View
- Result Update, Email Change, and CGPA View

---

## 🚀 How to Run Locally

1. Install **XAMPP**
2. Place the project folder inside:  
   `C:/xampp/htdocs/result_management/`
3. Import the SQL database via `phpMyAdmin`
4. Start **Apache** and **MySQL**
5. Access the system at:  
   `http://localhost/result_management/`

---

## 📥 Sample Data (Included in SQL dump)

- Students table with ID, name, section
- Teachers assigned to multiple courses
- Results for both lab and theory courses

---

## 📋 Limitations

- No downloadable PDF or printable result feature
- Course-type logic (sessional vs theory) is hardcoded
- No built-in notification or messaging system

---

## 👨‍💻 Team Members

This project was developed by a group of three students:

- **Oli Ahmed Khan** 
- **Md. Ehashanul Haque** 
- **Shah Md. Abdur Razzak** 

---

## 🎯 Objectives Achieved

- Automated result entry and retrieval
- Role-based secure access
- Reduced administrative workload
- Improved accessibility and transparency for students

---

## 📚 References

- [Student Management System – JSP Project](https://www.sourcecodester.com/java/16076/student-management-system-using-jsp-servlet-and-mysql.html)
- [Fedena – Academic ERP](https://fedena.com/)
- [Freeprojectz College Management System](https://www.freeprojectz.com/java-projects/college-management-system)

---

## 📫 Contact

**Oli Ahmed Khan**  
CSE Student, BAUST  
📧 oli.khan.contact@gmail.com
