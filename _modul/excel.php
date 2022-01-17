<?php

include "../../include_files/conn_open.php";

/** PHPExcel */
include '../../eklentiler/phpExcel/Classes/PHPExcel.php';

/** PHPExcel_Writer_Excel2007 */
include '../../eklentiler/phpExcel/Classes/PHPExcel/Writer/Excel2007.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");


///////////////////////////////
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'TC KİMLİK NO')
            ->setCellValue('B1', 'AD SOYAD')
            ->setCellValue('C1', 'CEP TEL')
            ->setCellValue('D1', 'ENSTİTÜ')
            ->setCellValue('E1', 'ANABİLİM DALI')
            ->setCellValue('F1', 'PROGRAM')
            ->setCellValue('G1', 'SINAV TERCİHİ')
            ->setCellValue('H1', 'LİSANS')
            ->setCellValue('I1', 'Y.LİSANS')
            ->setCellValue('J1', 'ALES-SAY')
            ->setCellValue('K1', 'ALES-EA')
            ->setCellValue('L1', 'ALES-SOZ')
            ->setCellValue('M1', 'GEÇERLİ ALES')
            ->setCellValue('N1', 'Y.DİL')
            ->setCellValue('O1', 'LİSANS ORT')
            ->setCellValue('P1', 'Y.LİSANS ORT')
            ->setCellValue('Q1', 'SINAV')
            ->setCellValue('R1', 'BAŞARI NOTU');
			
			$sql1=mysql_query("select * from anabilimdali where id=".(int)($_GET['anaBilimDaliID'])."") or die(mysql_error());
			$row1=mysql_fetch_assoc($sql1);
			$anabilimAdi2=$row1['anabilimAdi'];
			$anabilimAdi2= $anabilimAdi2;
			$sql1=mysql_query("select * from enstituler where id=".(int)($_GET['enstituID'])."") or die(mysql_error());
			$row1=mysql_fetch_assoc($sql1);
			$enstituAdi2=$row1['enstituAdi'];
			$enstituAdi2= $enstituAdi2;
			$sql1=mysql_query("select * from programlar where id=".(int)($_GET['programID'])."") or die(mysql_error());
			$row1=mysql_fetch_assoc($sql1);
			$program2=$row1['program'];
			$program2= $program2;

			$hucreNo=1;
            $sql=mysql_query("select * from basvurular where enstituID=".$_GET['enstituID']." and anaBilimDaliID=".$_GET['anaBilimDaliID']." and programID=".$_GET['programID']) or die(mysql_error());
			while($row=mysql_fetch_assoc($sql)){
			$hucreNo=$hucreNo+1;
			
			
				if($row['enstituID']==8){
					if($row['sinavTercihi']==0)
						$sinavTercihi="Secilmemis";
					elseif($row['sinavTercihi']==1)
						$sinavTercihi="Kurmanci";
					elseif($row['sinavTercihi']==2)
						$sinavTercihi="Zazaca";
				}else{
					$sinavTercihi="";
				}
			$alesSayPuan=$row['alesSayPuan'];
			$alesEaPuan=$row['alesEaPuan'];
			$alesSozPuan=$row['alesSozPuan'];
			$alesSayPuan=str_replace(",",".",$alesSayPuan);
			$alesEaPuan=str_replace(",",".",$alesEaPuan);
			$alesSozPuan=str_replace(",",".",$alesSozPuan);
			
			$sinav=trim($row['sinav']);
			$sql1=mysql_query("select * from kisiler where id=".(int)($row['kisiID'])." order by id desc") or die(mysql_error());
			$row1=mysql_fetch_assoc($sql1);
			$tcKimlikNo=$row1['tcKimlikNo'];
			$ad=$row1['ad'];
			$ad= $ad;
			$soyad=$row1['soyad'];
			$soyad= $soyad;
			
			$sql1=mysql_query("select * from enstituler where id=".(int)($row['enstituID'])."") or die(mysql_error());
			$row1=mysql_fetch_assoc($sql1);
			$enstituAdi=$row1['enstituAdi'];
			$enstituAdi= $enstituAdi;
						
			$sql1=mysql_query("select * from anabilimdali where id=".(int)($row['anaBilimDaliID'])."") or die(mysql_error());
			$row1=mysql_fetch_assoc($sql1);
			$anabilimAdi=$row1['anabilimAdi'];
			$anabilimAdi= $anabilimAdi;
					$abdAlesSay=$row1['alesSayPuan'];
					$abdAlesEa=$row1['alesEaPuan'];
					$abdAlesSoz=$row1['alesSozPuan'];

					if($abdAlesSay==1){
					$kisiAles=$alesSayPuan;
					}
					if($abdAlesEa==1){
					$kisiAles=$alesEaPuan;
					}
					if($abdAlesSoz==1){
					$kisiAles=$alesSozPuan;
					}
					if($abdAlesSay==1 and $abdAlesEa==1){
					$kisiAles=max($alesSayPuan,$alesEaPuan);
					}
					if($abdAlesSay==1 and $abdAlesSoz==1){
					$kisiAles=max($alesSayPuan,$alesSozPuan);
					}
					if($abdAlesEa==1 and $abdAlesSoz==1){
					$kisiAles=max($alesEaPuan,$alesSozPuan);
					}
					if($abdAlesSay==1 and $abdAlesEa==1 and $abdAlesSoz==1){
					$kisiAles=max($alesSayPuan,$alesEaPuan,$alesSozPuan);
					}
			
			
						
			$sql1=mysql_query("select * from basvuru_iletisim_bilgileri where kisiID=".(int)($row['kisiID'])."") or die(mysql_error());
			$row1=mysql_fetch_assoc($sql1);
			$cepTel=$row1['cepTel'];
			
			$sql1=mysql_query("select * from programlar where id=".(int)($row['programID'])."") or die(mysql_error());
			$row1=mysql_fetch_assoc($sql1);
			$program=$row1['program'];
			$program= $program;
			$programID=$row1['id'];
			
			$sql1=mysql_query("select * from basvuru_sinav_bilgileri where kisiID=".(int)($row['kisiID'])) or die(mysql_error());
			$row1=mysql_fetch_assoc($sql1);
			$yDil=$row1['puan'];
			$yDil=str_replace(",",".",$yDil);
			$yDil=trim($yDil);
			
			$sql1=mysql_query("select * from basvuru_ogrenim_bilgileri where kisiID=".(int)($row['kisiID'])."") or die(mysql_error());
			$row1=mysql_fetch_assoc($sql1);
			$lisansOgrenim=trim($row1['universite'])."/".trim($row1['fakulte'])."/".trim($row1['bolum']);
			$lisansOgrenim= $lisansOgrenim;
			$ylisansOgrenim=trim($row1['yuniversite'])."/".trim($row1['yfakulte'])."/".trim($row1['ybolum']);
			$ylisansOgrenim= $ylisansOgrenim;
			$lisans=trim($row1['mezuniyetOrtalamaYuzluk']);
			$ylisans=trim($row1['ymezuniyetOrtalamaYuzluk']);
			
			$lisans=str_replace(",",".",$lisans);
			$ylisans=str_replace(",",".",$ylisans);
			$sinav=str_replace(",",".",$sinav);
			
			if($programID==1 or $programID==8){  //Tezli YL ve Ünip Tezli YL
				//$basari=($ales*50/100)+($lisans*20/100)+($sinav*30/100);
				$basari="=(M$hucreNo*50/100)+(O$hucreNo*20/100)+(Q$hucreNo*30/100)";
				if($sinav>=50 and $ales>=55){
					
					if($basari>=60)
					$sonuc="BAŞARILI";
					else
					$sonuc="BAŞARISIZ";
					
				}else{
					$sonuc="BAŞARISIZ";
				}
			}
			
			
			if($programID==2 or $programID==4){   //Tezsiz YL ve Tezsiz YL (IÖ)
				if($row['anaBilimDaliID']==163){
					//$basari=($ales*25/100)+($lisans*25/100)+($sinav*50/100);
					$basari="=(M$hucreNo*25/100)+(O$hucreNo*25/100)+(Q$hucreNo*50/100)";
					}else{
					//$basari=($ales*30/100)+($lisans*70/100);
					$basari="=(M$hucreNo*30/100)+(O$hucreNo*70/100)";
					}
				$sonuc="";
			}
			
			
			if($programID==3 or $programID==9){   // Doktora ve Ünip Doktora
				//$basari=($kisiAles*50/100)+($lisans*15/100)+($ylisans*15/100)+($sinav*20/100);
				$basari="=(M$hucreNo*50/100)+(O$hucreNo*15/100)+(P$hucreNo*15/100)+(Q$hucreNo*20/100)";
				if($sinav>=50 and $ales>=55){
					
					if($basari>=65)
					$sonuc="BAŞARILI";
					else
					$sonuc="BAŞARISIZ";
					
				}else{
					$sonuc="BAŞARISIZ";
				}
			}
			
			
			if($programID==5){   //Bütünlesik Doktora
				//$basari=($ales*50/100)+($lisans*15/100)+($ylisans*15/100)+($sinav*20/100);
				$basari="=(M$hucreNo*50/100)+(O$hucreNo*15/100)+(P$hucreNo*15/100)+(Q$hucreNo*20/100)";
				if($sinav>=55 and $ales>=80 and $lisans>=76.66){
					
					if($basari>=70)
					$sonuc="BAŞARILI";
					else
					$sonuc="BAŞARISIZ";
					
				}else{
					$sonuc="BAŞARISIZ";
				}
			}
			
			
			if($programID==6 or $programID==7){   //Yabanci uyruklu YL - Doktora
				$basari="";
				$sonuc="";
			}
$sira++;	
$basari=str_replace(".",",",$basari);
$alesSayPuan=str_replace(".",",",$alesSayPuan);
$alesEaPuan=str_replace(".",",",$alesEaPuan);
$alesSozPuan=str_replace(".",",",$alesSozPuan);
$yDil=str_replace(".",",",$yDil);
$lisans=str_replace(".",",",$lisans);
$ylisans=str_replace(".",",",$ylisans);
$sinav=str_replace(".",",",$sinav);
$kisiAles=str_replace(".",",",$kisiAles);

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$hucreNo, $tcKimlikNo)
            ->setCellValue('B'.$hucreNo, $ad." ".$soyad)
            ->setCellValue('C'.$hucreNo, $cepTel)
            ->setCellValue('D'.$hucreNo, $enstituAdi)
            ->setCellValue('E'.$hucreNo, $anabilimAdi)
            ->setCellValue('F'.$hucreNo, $program)
            ->setCellValue('G'.$hucreNo, $sinavTercihi)
            ->setCellValue('H'.$hucreNo, $lisansOgrenim)
            ->setCellValue('I'.$hucreNo, $ylisansOgrenim)
            ->setCellValue('J'.$hucreNo, $alesSayPuan)
            ->setCellValue('K'.$hucreNo, $alesEaPuan)
            ->setCellValue('L'.$hucreNo, $alesSozPuan)
            ->setCellValue('M'.$hucreNo, $kisiAles)
            ->setCellValue('N'.$hucreNo, $yDil)
            ->setCellValue('O'.$hucreNo, $lisans)
            ->setCellValue('P'.$hucreNo, $ylisans)
            ->setCellValue('Q'.$hucreNo, $sinav)
            ->setCellValue('R'.$hucreNo, $basari); 
}


///////////////////////
// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle($enstituAdi2);


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$enstituAdi2.'-'.$anabilimAdi2.'-'.$program2.'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
