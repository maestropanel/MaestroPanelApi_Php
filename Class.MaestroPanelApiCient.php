<?php
/**
* MaestroPanel Rest Api Client
*
* @version 1.0
* @author Mustafa Kemal Birinci <kemal@bilgisayarmuhendisi.net>
* @license GPL
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @copyright Mustafa Kemal Birinci
*/

class MaestroPanelApiClient
{
	private $_key;
	private $_uri;
	private $_min_password_length = 8;
	private $_min_nonalphanumeric_chars = 2;
	private $_connettion_timeout = 15; //sec
	private $_output_timeout = 45; // this defines how long time we will wait to get result after connection
	private $_errors = array();
	
	public function __construct($key, $host, $port = 9715, $ssl = false){
		if($this->is_valid_ip($host) || $this->is_valid_domain($host)){
			$this->_key = $key;
			$this->_uri = $ssl ? 'https://' : 'http://' . $host . ':' . $port . '/Api'; 
		}else{
			$this->_errors[] = 'Sunucu api baðlantýsý için geçersiz ip ya da alan adý girdiniz!';
		}
	}
	
	/**
	* Delete Domain
	*
	* @param string $name Domain
	* @return SimpleXMLElement|false Returns SimpleXMLElement on success or false on failure
	*/
	public function domain_delete($name){
		$args = array(
					'key'				=> $this->_key,
					'name'				=> $name,
				);
				
		return $this->send_api('Domain/Delete', 'DELETE', $args);
	}

	/**
	* Start Domain
	*
	* @param string $name Domain
	* @return SimpleXMLElement|false Returns SimpleXMLElement on success or false on failure
	*/	
	public function domain_start($name){
		$args = array(
					'key'				=> $this->_key,
					'name'				=> $name,
				);
				
		return $this->send_api('Domain/Start', 'POST', $args);
	}

	/**
	* Stop Domain
	*
	* @param string $name Domain
	* @return SimpleXMLElement|false Returns SimpleXMLElement on success or false on failure
	*/
	public function domain_stop($name){
		$args = array(
					'key'				=> $this->_key,
					'name'				=> $name,
				);
				
		return $this->send_api('Domain/Stop', 'POST', $args);
	}
	
	/**
	* Create domain
	*
	* @param string $name Domain
	* @param string $plan_alias Domain plan alias name
	* @param string $username Ftp username
	* @param string $password Ftp password
	* @param bool $active_domain_user Active or inactive user control
	* @param string $first_name Hosting owner's first name
	* @param string $last_name Hosting owner's last name
	* @param string $email Hosting owner's email
	* @return SimpleXMLElement|false Returns SimpleXMLElement on success or false on failure
	*/
	public function domain_create($name, $plan_alias, $username, $password, $active_domain_user, $first_name = '', $last_name = '', $email = ''){
		$args = array(
					'key'				=> $this->_key,
					'name'				=> $name,
					'planAlias'			=> $plan_alias,
					'username'			=> $username,
					'password'			=> $password,
					'activedomainuser'	=> $active_domain_user ? 'true':'false',
					'firstname'			=> $first_name,
					'lastname'			=> $last_name,
					'email'				=> $email
		);
		
		return $this->send_api('Domain/Create', 'POST', $args);
	
	}
	
	/**
	* Reset Password
	*
	* @param string $name Domain
	* @param string $password Password
	* @return SimpleXMLElement|false Returns SimpleXMLElement on success or false on failure
	*/
	public function domain_reset_password($name, &$password){
		if(trim($password) == ''){
			$password = $this->generate_password();
		}else{			
			if(!$this->is_valid_password($password))
				return false;
		}
					
		$args = array(
					'key'				=> $this->_key,
					'name'				=> $name,
					'newpassword'		=> $password
				);
		
		return $this->send_api('Domain/Password', 'GET', $args);
	}
	
	private function send_api($action, $method, $args){
		try{
			if(!$this->is_valid_domain($args['name']))
			$this->_errors[] = 'Geçersiz bir alan adý girdiniz!';
		
			if(count($this->_errors)>0)
				return false;
			
			$curl=curl_init();
			curl_setopt($curl, CURLOPT_URL,$this->_uri . '/' . $action);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->_connettion_timeout);
			curl_setopt($curl, CURLOPT_TIMEOUT, $this->_output_timeout);
			$source = curl_exec($curl);

			if(curl_errno($curl)){
				$this->_errors[] = curl_error($curl);
				curl_close($curl);
				return false;
			}
			
			$xml = new SimpleXMLElement($source);

			if($xml->Code == '0'){
				return $xml;
			}else{
				$this->_errors[] = 'Hata Kodu : ' . $xml->Code . ' | Mesaj : ' . $xml->Message .' | Detaylý Mesaj : ' . $xml->OperationResult;
				
				return false;
			}
			
		}catch(Exception $e){
			$this->_errors[] = $e->getMessage();
			return false;
		}
	}
	
	private function is_valid_password($password){
		if(strlen($password) < $this->_min_password_length)
			$this->_errors[] = 'Þifre uzunluðu en az ' . $this->_min_password_length. ' karakterden oluþmalýdýr!';
			
		if(ctype_space($password))
			$this->_errors[] = 'Þifre boþluk karakteri içeremez!';
		
		$chars = str_split($password);
		
		$nonalphanumeric_count = 0;
		
		foreach($chars as $char){
			if(!ctype_alnum($char))
				$nonalphanumeric_count++;
		}
		
		if($nonalphanumeric_count < $this->_min_nonalphanumeric_chars)
			$this->_errors[] = 'Þifre en az ' . $this->_min_nonalphanumeric_chars . ' alfa nümerik olmayan karakter içermelidir!';
		
		if(count($this->_errors)>0)
			return false;
		else
			return true;
	}
	
	private function generate_password(){
		$chars1 = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',0,1,2,3,4,5,6,7,8,9);
		$chars2 = array('!','*','+','-','@','=','#','$','/','?','(',')');
		$password='';
		
		for($i = 0;$i < $this->_min_password_length - $this->_min_nonalphanumeric_chars;$i++){
			$password.=$chars1[rand(0,count($chars1)-1)];
		}
		
		for($i = 0;$i < $this->_min_nonalphanumeric_chars;$i++){
			$password.=$chars2[rand(0,count($chars2)-1)];
		}
		
		return str_shuffle($password);
	}
	
	private function is_valid_ip($ip){
		return preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $ip );
	}
	
	private function is_valid_domain($domain){
		return preg_match("/^([a-z0-9]([-a-z0-9]*[a-z0-9])?\\.)+((a[cdefgilmnoqrstuwxz]|aero|arpa)|(b[abdefghijmnorstvwyz]|biz)|(c[acdfghiklmnorsuvxyz]|cat|com|coop)|d[ejkmoz]|(e[ceghrstu]|edu)|f[ijkmor]|(g[abdefghilmnpqrstuwy]|gov)|h[kmnrtu]|(i[delmnoqrst]|info|int)|(j[emop]|jobs)|k[eghimnprwyz]|l[abcikrstuvy]|(m[acdghklmnopqrstuvwxyz]|mil|mobi|museum)|(n[acefgilopruz]|name|net)|(om|org)|(p[aefghklmnrstwy]|pro)|qa|r[eouw]|s[abcdeghijklmnortvyz]|(t[cdfghjklmnoprtvwz]|travel)|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw])$/i",$domain);
	}
	
	/**
	* Get Errors
	*
	* @return array Returns errors
	*/
	public function get_errors(){
		return $this->_errors;
	}
}
?>