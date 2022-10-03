<?php 

include "../../_cekirdek/fonksiyonlar.php";
$fn = new Fonksiyonlar();
$vt = new VeriTabani();




$SQL_tum_personel = <<< SQL
SELECT
    id
    ,adi
    ,soyadi
    ,grup_id
    ,ucret
FROM
    tb_personel AS p
WHERE
    p.firma_id  = ? AND 
    p.aktif     = 1 
SQL;

/*Belirli tarihte hangi */
$SQL_giris_cikis_grup_id = <<< SQL
SELECT 
    g.grup_id,
    p.ucret
FROM 
    tb_giris_cikis AS g
INNER JOIN tb_personel AS p ON p.id = g.personel_id
WHERE 
    g.tarih     = ? AND 
    p.firma_id  = ?  AND 
    g.aktif     = 1
GROUP BY g.grup_id
SQL;

/*Donem Çağırma */
$SQL_donem_oku = <<< SQL
SELECT 
    *
FROM 
    tb_donem
WHERE 
    yil         = ?  AND 
    ay          = ?  AND 
    firma_id    = ?  AND 
    aktif       = 1
SQL;


/*
Bugunkü Tarifeye ait giriş cıkış saatlerini veya tatil durumunu getiriyor
*/
$SQL_giris_cikis_saat = <<< SQL
SELECT 
    t1.*
from
    tb_tarifeler AS t1
LEFT JOIN tb_mesai_turu AS mt ON  t1.mesai_turu = mt.id
WHERE 
    t1.baslangic_tarih <= ? AND 
    t1.bitis_tarih >= ? AND
    mt.gunler LIKE ? AND 
    t1.grup_id LIKE ? AND
    t1.aktif = 1
ORDER BY t1.id DESC
LIMIT 1      
SQL;

//TARİFEYE AİT SAAT LİSTESİ
$SQL_tarife_saati = <<< SQL
SELECT 
    *
from
    tb_tarife_saati 
WHERE 
    tarife_id = ? AND 
    aktif = 1
ORDER BY baslangic ASC
SQL;


//TARİFEYE AİT MOLA LİSTESİ
$SQL_mola_saati = <<< SQL
SELECT 
    *
from
    tb_molalar
WHERE 
    tarife_id = ? AND 
    aktif = 1
ORDER BY baslangic ASC
SQL;


/*Maaslar listesi*/
$SQL_maaslar = <<< SQL
SELECT 
    *
FROM
    tb_kapatilan_maas 
WHERE 
    firma_id = ?
SQL;

/*Kapatılan maaşlar tablosunda olmayan maaşları cekiyoruz*/
$SQL_olmayan_maaslar = <<< SQL
SELECT 
    ucret 
FROM 
    tb_personel AS p 
WHERE 
    p.firma_id = ? AND
    aktif      = 1 AND 
    p.ucret NOT IN( SELECT maas from tb_kapatilan_maas WHERE firma_id = ?   )
GROUP BY ucret 
SQL;

/*Kapatılan maaşlar tablosunda olmayan maaşları cekiyoruz*/
$SQL_genel_ayarlar = <<< SQL
SELECT 
    *
FROM 
    tb_genel_ayarlar
WHERE 
    firma_id = ?
SQL;


/*Dönem Kapatma giriş çıkış saatlerine */
$SQL_tarife_ekle = <<< SQL
UPDATE 
    tb_giris_cikis 
SET
    tarife = ?
WHERE 
    grup_id = ? AND
    tarih   = ? 
SQL;

/*Dönem Kapatma giriş çıkış saatlerine */
$SQL_giris_cikis_maas_ekle = <<< SQL
UPDATE 
    tb_giris_cikis 
SET
    maas         = ?
WHERE 
    personel_id  = ? AND 
    DATE_FORMAT(tb_giris_cikis.tarih ,'%Y-%m')  = ?
SQL;


/*Kapatılan Doneme Ait verileri tablolara kaydetme*/
$SQL_kapatilan_tarife_ekle = <<< SQL
INSERT INTO 
    tb_kapatilan_tarifeler
SET 
    firma_id            = ?,
    min_calisma_saati   = ?,
    gun_donumu          = ?,
    tatil               = ?,
    maasa_etki_edilsin  = ?
SQL;

$SQL_kapatilan_tarife_saati = <<< SQL
INSERT INTO 
    tb_kapatilan_tarife_saati
SET 
    tarife_id           = ?,
    baslangic           = ?,
    bitis               = ?,
    carpan              = ?
SQL;

$SQL_kapatilan_molalar = <<< SQL
INSERT INTO 
    tb_kapatilan_molalar
SET 
    tarife_id            = ?,
    baslangic            = ?,
    bitis                = ?
SQL;

/*Kapanan Dönem Ekleme*/
$SQL_donem_ekle = <<< SQL
INSERT INTO 
    tb_donem
SET 
    firma_id                = ?,
    yil                     = ?,
    ay                      = ?,
    aylik_calisma_saati     = ?,
    haftalik_calisma_saati  = ?,
    pazar_kesinti_sayisi    = ?,
    beyaz_yakali_personel   = ?
SQL;

/*Kapatılan Maaşlara veri ekleme*/
$SQL_maas_ekle = <<< SQL
INSERT INTO 
    tb_kapatilan_maas
SET 
    firma_id    = ?,
    maas        = ?
SQL;

$vt->islemBaslat();

    $olmayan_maaslar            = $vt->select( $SQL_olmayan_maaslar, array( $_SESSION[ 'firma_id' ], $_SESSION[ 'firma_id' ] ) )[ 2 ];
    $tum_personel               = $vt->select( $SQL_tum_personel, array( $_SESSION[ 'firma_id' ] ) )[ 2 ];
    $genel_ayarlar              = $vt->select( $SQL_genel_ayarlar, array( $_SESSION[ 'firma_id' ] ) )[ 2 ][ 0 ];

    

    $kapanacakAy        = $_REQUEST[ 'ay' ];
    $kapanacakYil       = $_REQUEST[ 'yil' ];
    $suankiAy           = date( "m" ); 
    //Gegerleri integeri ceviriyoruz 06 olan deger 6 olarak gelecektir.
    settype( $suankiAy, "integer" );

    /*Kapatılacak Olan Ayın Onceden Kapatılıp Kapatılmadığını Kontrol ediyoruz*/
    $kapananAyVarmi     = $vt->select( $SQL_donem_oku, array( $kapanacakYil, $kapanacakAy, $_SESSION[ 'firma_id' ] ) )[ 2 ];
    if ( count( $kapananAyVarmi ) > 0 ) {

        $___islem_sonuc = array( 'hata' => true, 'mesaj' => 'Belirtmiş Oldugunuz ay Kapanmıştır. İşlem Yapmak için yönetici ile iletişime geçiniz.' ); 

        $_SESSION[ 'sonuclar' ] = $___islem_sonuc;
        header( "Location:../../index.php?modul=anasayfa");
        die();
    }

    /*Gelecek olan ayı kapatmak isterse izin verilmiyor*/
    if( $kapanacakAy > $suankiAy ){

        $___islem_sonuc = array( 'hata' => true, 'mesaj' => 'Belirtmiş Oldugunuz ay için Dönem suan için kapatılmaz.' ); 

        $_SESSION[ 'sonuclar' ] = $___islem_sonuc;
        header( "Location:../../index.php?modul=anasayfa");

    }else{

        /*Maaşları güncelenen personel var ise maaşları kapatılan maaşlar tablosuna aktardık*/
        foreach ($olmayan_maaslar as $maas) {
            $vt->insert( $SQL_maas_ekle, array( $_SESSION[ 'firma_id' ], $maas[ 'ucret' ] ) );
        }

        /*Kapan  Tüm maaşları çektik*/
        $maaslar   = $vt->select( $SQL_maaslar, array( $_SESSION[ 'firma_id' ] ) )[ 2 ];
        foreach ( $maaslar as $id => $maas ) {
            $eklenenMaaslar[ $maas[ 'maas' ] ]  = $maas[ 'id' ];
        }

        /*Giriş Çıkış tablsunda pesonelin maaşını tanımlıyoruz*/
        foreach ( $tum_personel as $personel ) {
            
            $vt->update( $SQL_giris_cikis_maas_ekle, array( $eklenenMaaslar[ $personel[ 'ucret' ] ], $personel[ 'id' ], $kapanacakYil.'-'.$fn->ikiHaneliVer( $kapanacakAy ) ) );
            
        }

        $gunSayisi = $fn->ikiHaneliVer($kapanacakAy) == date("m") ? date("d") - 1  : date("t",mktime(0,0,0,$kapanacakAy,01,$kapanacakYil));

        $sayi               = 1; 
        $eklenenTarifeler   = array(); 
        
        while( $sayi <= $gunSayisi ) {
            
            $gruplar = $vt->select( $SQL_giris_cikis_grup_id, array( $kapanacakYil.'-'.$kapanacakAy.'-'.$sayi, $_SESSION[ "firma_id" ] ) )[ 2 ];  
            foreach ($gruplar as $grup) {
                
                /*Tairhin hangi güne denk oldugunu getirdik*/
                $gun = $fn->gunVer( $kapanacakYil.'-'.$kapanacakAy.'-'.$sayi );

                $tarife = $vt->select( $SQL_giris_cikis_saat, array( $kapanacakYil.'-'.$kapanacakAy.'-'.$sayi , $kapanacakYil.'-'.$kapanacakAy.'-'.$sayi , '%,'.$gun.',%', '%,'.$grup["grup_id"].',%' ) ) [ 2 ][ 0 ];
                //Mesaiye 10 DK gec Gelme olasıılıgını ekledik 10 dk ya kadaar gec gelebilir 

                /*tarifeye ait mesai saatleri */
                $saatler = $vt->select( $SQL_tarife_saati, array( $tarife[ 'id' ] ) )[ 2 ];

                /*tarifeye ait mola saatleri */
                $molalar = $vt->select( $SQL_mola_saati, array( $tarife[ 'id' ] ) )[ 2 ];

                if ( !array_key_exists($tarife[ "id" ], $eklenenTarifeler) ) {
                    /*Tarifeyi kapatılan tarifeler saatler ve molaları kapatılan tablolara eklıyoruz*/
                    $tarife_ekle = $vt->insert( $SQL_kapatilan_tarife_ekle, array( $_SESSION[ 'firma_id' ], $tarife[ "min_calisma_saati" ],$tarife[ "gun_donumu" ],$tarife[ "tatil" ],$tarife[ "maasa_etki_edilsin" ] ) );
                    
                    $eklenenTarifeler[ $tarife["id"] ]  = $tarife_ekle[ 2 ];
                    $eklenenTarifeId                    = $eklenenTarifeler[ $tarife[ "id" ] ]; 

                    foreach ($saatler as $saat) {
                        $vt->insert( $SQL_kapatilan_tarife_saati, array( $eklenenTarifeId, $saat[ "baslangic" ], $saat[ "bitis" ], $saat[ "carpan" ] ) );
                    }

                    foreach ($molalar as $mola) {
                        $vt->insert( $SQL_kapatilan_molalar, array( $eklenenTarifeId, $mola[ "baslangic" ], $mola[ "bitis" ] ) );
                    }
                }

                $eklenenTarifeId  = $eklenenTarifeler[ $tarife[ "id" ] ]; 
                /*Personelin yaptığı giriş çıkışlara tarifeyi ekliyoruz*/
                $vt->update( $SQL_tarife_ekle, array( $eklenenTarifeId, $grup[ "grup_id" ], $kapanacakYil.'-'.$kapanacakAy.'-'.$sayi ) );

            }
            $sayi++;
        }
        /*Kapatılan donemi genel ayarlardan verileri ekliyoruz*/
        $vt->insert( $SQL_donem_ekle, array( $_SESSION[ 'firma_id' ], $kapanacakYil, $kapanacakAy, $genel_ayarlar[ "aylik_calisma_saati" ], $genel_ayarlar[ "haftalik_calisma_saati" ], $genel_ayarlar[ "pazar_kesinti_sayisi" ],$genel_ayarlar[ "beyaz_yakali_personel" ] ) );
        $vt->islemBitir();

        $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'Belirtmiş Oldugunuz ay kapatıldı.' ); 

        $_SESSION[ 'sonuclar' ] = $___islem_sonuc;
        header( "Location:../../index.php?modul=anasayfa");
    }
    



        
