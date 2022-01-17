<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();

$SQL_arac_medya = <<< SQL
SELECT
	*
FROM
	tb_arac_medya
WHERE
	arac_id = ?
SQL;

if( $_REQUEST[ 'islem' ] == 'medya_indir' ){
	$arac_id = $_REQUEST[ 'id' ];
	$arac_no = $_REQUEST[ 'arac_no' ];
}

$arac_medya	= $vt->select( $SQL_arac_medya, array( $arac_id ) );
foreach( $arac_medya[2] as $medya ){
	$medyalar[] = $medya['dosya_adi'];
}

/**
 * @author: Sohel Rana <me.sohelrana@gmail.com>
 * @author URI: http://sohelrana.me
 * @description: Create zip file and download in PHP
 */

function createZipAndDownload($files, $filesPath, $zipFileName)
{
    // Create instance of ZipArchive. and open the zip folder.
    $zip = new \ZipArchive();
    if ($zip->open($zipFileName, \ZipArchive::CREATE) !== TRUE) {
        exit("cannot open <$zipFileName>\n");
    }

    // Adding every attachments files into the ZIP.
    foreach ($files as $file) {
        $zip->addFile($filesPath . $file, $file);
    }
    $zip->close();

    // Download the created zip file
    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename = $zipFileName");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile("$zipFileName");
}

// Files which need to be added into zip
$files = $medyalar;;
// Directory of files
$filesPath = "../../arac_resimler/$arac_no/";
// Name of creating zip file
$zipName = $arac_no.'_medya.zip';

echo createZipAndDownload($files, $filesPath, $zipName);
unlink($zipName);
?>