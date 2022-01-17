<?php
include "../../_cekirdek/fonksiyonlar.php";
include "../../_cekirdek/BarcodeQR.php";
if( array_key_exists( 'giris_var', $_SESSION ) && $_SESSION[ 'giris_var' ] == 'evet' ) { 
$fn = new Fonksiyonlar();
$vt	= new VeriTabani();
$arac_no = $_REQUEST['arac_no'];
		$url = "http://galeri.otowow.com/".$arac_no;
		$dosya_yolu = "../../arac_resimler/".$arac_no."/".$arac_no."_qr.png";
		$qr = new BarcodeQR(); 
		$qr->url($url); 
		$qr->draw(350, $dosya_yolu);
		//$qr->draw();
}


?>
<table align="center" style="border:solid 1px gray;width:10cm;height:10cm;">
	<tr>
		<td align="center" valign="middle" style="font-size:24;">
			<b style="font-size:48;"><?php echo $arac_no;?></b><br>
			<b style="font-size:18;background-color:#000;color:#FFF;">galeri.otowow.com/<?php echo $arac_no;?></b>											
		</td>
	</tr>
	<tr>
		<td align="center" style="border:solid 0px gray">
			<img src="<?php echo $dosya_yolu;?>" style="width:7cm;">
		</td>
	</tr>

</table>
