@echo OFF

if "%BRANCH_NAME%"=="production.blog" (
set ComputerName=https://18.185.13.201:8172/msdeploy.axd
set username=%SECRET_ID_01_USR%
set password=%SECRET_ID_01_PSW%
)

if "%BRANCH_NAME%"=="test.blog" (
set ComputerName=35.174.218.204
set username=%SECRET_ID_01_USR%
set password=%SECRET_ID_01_PSW%
)

rmdir /s /q  %WORKSPACE%\wp-content\uploads

rem del web.config /f

%msdeployv2% -verb:sync -source:iisapp="%WORKSPACE%" -dest:iisapp="teamlab.info\blog",computerName="%ComputerName%",username="%username%",password="%password%" -skip:Directory="\\.jenkins" -skip:Directory="teamlab.info\\blog\\wp-content\\uploads" -skip:Directory="teamlab.info\\blog\\wp-content\\cache\\autoptimize"