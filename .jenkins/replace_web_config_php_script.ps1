if ( $Env:BRANCH_NAME -eq "test.blog") {
	$REPLACE_STRING_PATH = "/.jenkins/replace_strings_test.txt"
}

if ( $Env:BRANCH_NAME -eq "production.blog") {
	$REPLACE_STRING_PATH = "/.jenkins/replace_strings_prod.txt"
}

$wpConfigPhpPath = "$Env:WORKSPACE\wp-config.php"
$REPLACE_STRING = Get-Content $Env:WORKSPACE$REPLACE_STRING_PATH
$REPLACE_STRING_1 = echo $REPLACE_STRING[0]
$REPLACE_STRING_2 = echo $REPLACE_STRING[1]
$REPLACE_STRING_3 = echo $REPLACE_STRING[2]
$REPLACE_STRING_4 = echo $REPLACE_STRING[3]
$REPLACE_STRING_5 = echo $REPLACE_STRING[4]
$REPLACE_STRING_6 = echo $REPLACE_STRING[5]
$REPLACE_STRING_7 = echo $REPLACE_STRING[6]
$REPLACE_STRING_8 = echo $REPLACE_STRING[7]

(get-content $wpConfigPhpPath ) | %{$_ -replace "cache1",'D:\www.teamlab.info\Blog\wp-content\plugins\wp-super-cache/'} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "name-database","teamlab_blog2"} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "user-database","tm-site"} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "password-database","tm-site"} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "host1",'teamlab-4testing.cyxlgbdbuyvm.us-east-1.rds.amazonaws.com'} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "root1",'https://teamlab.info'} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "some_key1","$REPLACE_STRING_1"} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "some_key2","$REPLACE_STRING_2"} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "some_key3","$REPLACE_STRING_3"} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "some_key4","$REPLACE_STRING_4"} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "some_key5","$REPLACE_STRING_5"} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "some_key6","$REPLACE_STRING_6"} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "some_key7","$REPLACE_STRING_7"} | set-content $wpConfigPhpPath
(get-content $wpConfigPhpPath ) | %{$_ -replace "some_key8","$REPLACE_STRING_8"} | set-content $wpConfigPhpPath
				
if ( $REPLACE_STRING.Count -eq 7 ) { 
	write-host "Not enought variables." 
	Rename-Item -path "$Env:WORKSPACE/.jenkins/webdeploy2.bat" -NewName "$Env:WORKSPACE/.jenkins/webdeploy2.battt"
}