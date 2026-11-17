@echo off
title ServisYuk Node.js AI Chatbot Server
echo ========================================================
echo   ServisYuk AI Chatbot Server (Node.js + Groq)
echo ========================================================
echo.
if exist "C:\xampp\nodejs\node.exe" (
    echo Menjalankan chatbot server di port 5005...
    "C:\xampp\nodejs\node.exe" "%~dp0server.js"
) else (
    echo Menjalankan dengan node sistem...
    node "%~dp0server.js"
)
pause
