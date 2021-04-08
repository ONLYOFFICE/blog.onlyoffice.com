@echo off
SETLOCAL ENABLEDELAYEDEXPANSION

set sitename="%poolname%"


FOR /F "tokens=* USEBACKQ" %%F IN (`C:\windows\system32\inetsrv\AppCmd.exe list apppool %sitename% /text:state`) DO (
SET status=%%F
)

if %status% equ Started (
  "%systemroot%\system32\inetsrv\AppCmd.exe" stop apppool /apppool.name:%sitename%
) else (
  "%systemroot%\system32\inetsrv\AppCmd.exe" start apppool /apppool.name:%sitename% 
)
