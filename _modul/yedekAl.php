<?php
session_start();
if( $_SESSION[ 'firma_turu' ] == 1 ) {
$DBHOST = 'localhost';
$DBNAME = 'bwyolmez_mp6500';
$DBUSER = 'bwyolmez_mp6500';
$DBPASS = 'bQVxW)1{0,cd';
$firma = 'olmezmaden';
$sifre = uniqid();
}else{
$DBHOST = 'localhost';
$DBNAME = 'bwyolmez_oldogu';
$DBUSER = 'bwyolmez_oldogu';
$DBPASS = '%Xgn}Hx8Juxn';
$firma = 'olmezdogumaden';
$sifre = uniqid();
}
$compression = TRUE;
$dst_dir = '../_yedekler';
$DBH = new PDO("mysql:host=".$DBHOST.";dbname=".$DBNAME."; charset=utf8", $DBUSER, $DBPASS);
if(is_null($DBH) || $DBH===FALSE)
{
    die('ERROR');
}
$DBH->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL );
$fileName = 'backup-db-' . date('Y-m-d_h:i:s');
//create/open files
if ($compression)
{
    $fileName .= '__'.$sifre; 
    $fileName .= '.sql.gz'; 
    $zp = gzopen($dst_dir.'/'.$firma.'_'.$fileName, "a9");
}
else
{
	$fileName .= '__'.$sifre; 
    $fileName .= '.sql';
    $handle = fopen($dst_dir.'/'.$firma.'_'.$fileName,'a+');
}
//array of all database field types which just take numbers
$numtypes=array('tinyint','smallint','mediumint','int','bigint','float','double','decimal','real');
//get all of the tables
if(empty($tables))
{
    $pstm1 = $DBH->query('SHOW TABLES');
    while ($row = $pstm1->fetch(PDO::FETCH_NUM))
    {
        $tables[] = $row[0];
    }
}
else
{
    $tables = is_array($tables) ? $tables : explode(',',$tables);
}
//cycle through the table(s)
foreach($tables as $table)
{
    $result = $DBH->query("SELECT * FROM $table");
    $num_fields = $result->columnCount();
    $num_rows = $result->rowCount();
    $return="";
    //uncomment below if you want 'DROP TABLE IF EXISTS' displayed
    //$return.= 'DROP TABLE IF EXISTS `'.$table.'`;';
    //table structure
    $pstm2 = $DBH->query("SHOW CREATE TABLE $table");
    $row2 = $pstm2->fetch(PDO::FETCH_NUM);
    $ifnotexists = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $row2[1]);
    $return.= "\n\n".$ifnotexists.";\n\n";
    if ($compression)
    {
        gzwrite($zp, $return);
    }
    else
    {
        fwrite($handle,$return);
    }
    $return = "";
    //insert values
    if ($num_rows)
    {
        $return= 'INSERT INTO `'."$table"."` (";
        $pstm3 = $DBH->query("SHOW COLUMNS FROM $table");
        $count = 0;
        $type = array();
        while ($rows = $pstm3->fetch(PDO::FETCH_NUM))
        {
            if (stripos($rows[1], '('))
            {
                $type[$table][] = stristr($rows[1], '(', true);
            }
            else
            {
                $type[$table][] = $rows[1];
            }
            $return.= "`".$rows[0]."`";
            $count++;
            if ($count < ($pstm3->rowCount()))
            {
                $return.= ", ";
            }
        }
        $return.= ")".' VALUES';
        if ($compression)
        {
            gzwrite($zp, $return);
        }
        else
        {
            fwrite($handle,$return);
        }
        $return = "";
    }
    $count =0;
    while($row = $result->fetch(PDO::FETCH_NUM))
    {
        $return= "\n(";
        for($j=0; $j<$num_fields; $j++)
        {
            if (isset($row[$j]))
            {
                //if number, take away "". else leave as string
                if ((in_array($type[$table][$j], $numtypes)) && $row[$j]!=='')
                {
                    $return.= $row[$j];
                }
                else
                {
                    $return.= $DBH->quote($row[$j]);
                }
            }
            else
            {
                $return.= 'NULL';
            }
            if ($j<($num_fields-1))
            {
                $return.= ',';
            }
        }
        $count++;
        if ($count < ($result->rowCount()))
        {
            $return.= "),";
        }
        else
        {
            $return.= ");";
        }
        if ($compression)
        {
            gzwrite($zp, $return);
        }
        else
        {
            fwrite($handle,$return);
        }
        $return = "";
    }
    $return="\n\n-- ------------------------------------------------ \n\n";
    if ($compression)
    {
        gzwrite($zp, $return);
    }
    else
    {
        fwrite($handle,$return);
    }
    $return = "";
}
$error1= $pstm2->errorInfo();
$error2= $pstm3->errorInfo();
$error3= $result->errorInfo();
echo $error1[2];
echo $error2[2];
echo $error3[2];
$fileSize = 0;
if ($compression)
{
    gzclose($zp);
    $fileSize = filesize($dst_dir.'/'.$fileName);
}
else
{
    fclose($handle);
    $fileSize = filesize($dst_dir.'/'.$fileName);
}
$dosya = '../'.$dst_dir.'/'.$firma.'_'.$fileName;
header("Location:  $dosya");
?>