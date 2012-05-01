<?php

include 'Class.MaestroPanelApiCient.php';

$api_key 	= ''; 	//Api anahtarý
$host 		= ''; 	//Sunucu ip adresi ya da alan adý (Örnek : 127.0.0.1 ya da alanadi.com)
$port		= 9715; //MaestroPanel portu
$ssl		= false;//Ssl kullanýp kullanýlmadýðýný belirtir.Þu anki versiyonda ssl olmadýðý için false deðerini verin

$client = new MaestroPanelApiClient($api_key, $host, $port, $ssl);

$domain				= 'phpuzmani.com'; //Ýþlem yapýlacak alan adý
$domain_alias		= '1GB_HOST'; 	//Domain paketinin alias adý
$username			= 'phpapi';//Kullanýcý adý
$password			= 'kemal1!*';	//Þifre (En az 8 karakterden oluþmalý ve en az 2 alfa nümerik olmayan karakter içermelidir.class dosyasýndan bu özellikler deðiþtirilebilir.)
$active_domain_user	= true;			//Kullanýcýnýn aktif edilip edilmeyeceðini belirtir.
$first_name			= 'Kemal';		//Müþterinin adý
$last_name			= 'Birinci';	//Müþterinin soyadý
$email				= 'kemal@bilgisayarmuhendisi.net'; //Müþterinin e-posta adresi

//$result deðiþkenine baþarýsýz durumda false, baþarý durumunda ise SimpleXMLElement nesne tipinde sonuç döner.

//Yeni bir domain oluþturur
$result = $client->domain_create($domain, $domain_alias, $username, $password, $active_domain_user, $first_name, $last_name, $email);

//Domaini durdurur
//$result = $client->domain_stop($domain);

//Domaini baþlatýr
//$result = $client->domain_start($domain);

//Domaini siler
//$result = $client->domain_delete($domain);

/**
* Domain þifresini deðiþtirir
* 
* Þifre deðiþtirmek için 2 yöntem vardýr.
*
* 1) Þifreyi belirttiðiniz bir þifre ile deðiþtirir. 
* $result = $client->domain_reset_password($domain, '123456*!');
* 
* 2) Þifre parametresine boþ bir deðiþken vermeniz ile þifreyi kendisi üretir ve deðiþkeninize de þifreyi atar.
* $password = ''; 
* $client->domain_reset_password($domain, $password);
* echo $password;
*/

if($result){
	echo 'Ýþlem Baþarýyla gerçekleþti.<br />';
	echo 'Kod : '.$result->Code . '<br />Mesaj : ' . $result->Message.'<br />Detaylý Mesaj<pre>'.$result->OperationResult.'</pre>';		

	
	foreach($client->get_errors() as $error){
		echo '<pre>' . $error . '</pre>';
	}
}else{
	foreach($client->get_errors() as $error){
		echo '<pre>' . $error . '</pre>';
	}
}

?>