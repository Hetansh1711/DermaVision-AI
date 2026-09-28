# 🩺 DermaVision AI

DermaVision AI is an AI-powered skin analysis web application designed to analyze skin images and provide preliminary information about possible skin conditions. The project combines a web-based interface, Python-based AI processing, and a MySQL database to provide an end-to-end skin analysis platform.

The main goal of DermaVision AI is to make preliminary skin analysis simple, accessible, and easy to understand through an intuitive web application.

> ⚠️ **Medical Disclaimer:** DermaVision AI is intended for educational and preliminary screening purposes only. It is not a medical diagnostic system and should not replace professional medical advice, diagnosis, or treatment. Always consult a qualified healthcare professional for medical concerns.

---

## ✨ Features

- 🖼️ Upload and analyze skin images
- 🤖 AI-based skin image analysis
- 👤 User registration and authentication
- 🗄️ MySQL database integration
- 🌐 Web-based user interface
- 🔗 Integration between the web application and Python AI component
- 📊 Display of AI-generated analysis results
- 🔐 Structured application configuration

---

## 🛠️ Technologies Used

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **AI Processing:** Python
- **Database:** MySQL
- **Web Server:** Apache / XAMPP
- **Version Control:** Git & GitHub

---

## 📁 Project Structure

```text
DermaVision-AI/
│
├── config/
│   └── Application configuration files
│
├── includes/
│   └── Reusable backend components
│
├── public/
│   └── Main web application files
│
├── python_ai/
│   └── Python AI/image analysis components
│
├── database.sql
│   └── MySQL database structure
│
├── LICENSE.txt
│   └── Project license
│
└── README.md
    └── Project documentation
```

---

## 🔄 How It Works

```text
User
  │
  ▼
Upload Skin Image
  │
  ▼
Web Application
  │
  ▼
Python AI Processing
  │
  ▼
Skin Image Analysis
  │
  ▼
Analysis Result
  │
  ▼
User
```

The user uploads a skin image through the web application. The image is processed by the Python AI component, and the generated analysis is returned to the web application and displayed to the user.

---

# 🚀 Installation & Setup

## 1. Requirements

Before running the project, install the following:

- Git
- PHP
- MySQL
- Python 3.x
- XAMPP
- A modern web browser

XAMPP is recommended because it provides Apache and MySQL for running the web application locally.

---

## 2. Clone the Repository

Open Command Prompt, PowerShell, or Terminal and run:

```bash
git clone https://github.com/Hetansh1711/DermaVision-AI.git
```

Then move into the project directory:

```bash
cd DermaVision-AI
```

---

## 3. Move the Project to XAMPP

If you are using XAMPP on Windows, copy the project folder into:

```text
C:\xampp\htdocs\
```

The final project location should be:

```text
C:\xampp\htdocs\DermaVision-AI
```

---

## 4. Start XAMPP

Open the **XAMPP Control Panel**.

Start the following services:

```text
Apache
MySQL
```

Make sure both services are running successfully.

---

## 5. Create the Database

Open your browser and go to:

```text
http://localhost/phpmyadmin
```

Create a new database named:

```text
dermavision_ai
```

Select the newly created database.

Then click **Import** and select:

```text
database.sql
```

Finally, click **Go** to import the database structure.

---

## 6. Configure the Database Connection

Open the configuration files inside:

```text
config/
```

Configure the database connection according to your local MySQL setup.

For a default XAMPP installation, the settings are usually:

```text
Host: localhost
Username: root
Password:
Database: dermavision_ai
```

If your MySQL installation has a password, use your configured password instead.

> **Important:** Never upload database passwords or other private credentials to GitHub.

---

## 7. Set Up the Python AI Environment

Open a terminal inside the Python AI directory:

```bash
cd python_ai
```

Create a virtual environment:

```bash
python -m venv venv
```

### Windows

Activate the environment:

```bash
venv\Scripts\activate
```

### macOS / Linux

Activate the environment:

```bash
source venv/bin/activate
```

---

## 8. Install Python Dependencies

If the `python_ai` directory contains a `requirements.txt` file, install the dependencies using:

```bash
pip install -r requirements.txt
```

If a `requirements.txt` file is not included, install the Python packages required by the AI component.

---

## 9. Configure the AI Component

Check the files inside:

```text
python_ai/
```

Configure any required:

- AI model files
- Model paths
- API keys
- Input/output paths
- Service configuration
- Other required settings

Do not upload private API keys, passwords, or secret credentials to GitHub.

---

## 10. Start the Python AI Component

From the `python_ai` directory, run the Python entry-point file used by the project.

For example:

```bash
python main.py
```

If the project uses a different Python entry file, run that file instead.

Keep the Python service running while using the web application.

---

## 11. Open the Web Application

Make sure **Apache** and **MySQL** are running in XAMPP.

Then open the application in your browser:

```text
http://localhost/DermaVision-AI/public/
```

---

# 🧪 How to Use

1. Start **Apache** and **MySQL** from XAMPP.
2. Start the Python AI component.
3. Open the DermaVision AI web application.
4. Create an account or log in.
5. Upload a skin image.
6. Submit the image for analysis.
7. Wait for the AI processing to complete.
8. View the generated analysis.

---

# 🗄️ Database

DermaVision AI uses MySQL for storing application data.

### Database Name

```text
dermavision_ai
```

### Database Setup File

```text
database.sql
```

Import `database.sql` into phpMyAdmin to create the required database structure.

---

# 🔐 Security

Do not commit sensitive information to GitHub.

Never upload:

```text
.env
API keys
Database passwords
Secret keys
Private credentials
```

Use environment variables or local configuration files for sensitive information.

If credentials are accidentally exposed in a public repository, revoke and replace them immediately.

---

# ⚠️ Medical Disclaimer

DermaVision AI provides AI-generated information for educational and preliminary screening purposes only.

The results produced by this application should **not** be considered a medical diagnosis.

AI-based image analysis may produce incorrect or incomplete results. If you have a concerning, changing, painful, bleeding, or persistent skin condition, consult a qualified dermatologist or other healthcare professional.

---

# 🎯 Project Objectives

- Develop an AI-powered skin analysis platform.
- Explore the use of Artificial Intelligence in skin-image analysis.
- Integrate Python AI processing with a web application.
- Provide users with understandable preliminary information.
- Combine web development, AI, and database technologies in a single application.
- Create a foundation for future improvements in automated skin analysis.

---

# 🔮 Future Enhancements

- 🧠 Improved AI models
- 📊 Confidence scores and analysis visualization
- 📋 Skin analysis history
- 👨‍⚕️ Doctor/dermatologist consultation integration
- 📱 Mobile application
- 🌍 Multi-language support
- ☁️ Cloud deployment
- 🔐 Improved authentication and security
- 📈 AI model performance monitoring
- 👤 Advanced user dashboard

---

# 📄 License

This project is licensed under the terms specified in:

```text
LICENSE.txt
```

---

# 👨‍💻 Author

**Hetansh1711**

GitHub Repository:

https://github.com/Hetansh1711/DermaVision-AI

---

## ⭐ Support

If you find this project useful, consider giving the repository a ⭐ on GitHub.

---

**DermaVision AI — AI-powered skin analysis for preliminary screening and awareness.**
