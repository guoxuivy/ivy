<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy * @since 1.0 
 */
namespace Ivy\core\lib;
/**
 * DES可逆加密算法 php实现
 * 如果需要可使用更高强度的3DES加密算法
 * demo：
 * 
 *    	$des = new CryptDes("w1s5de68","548e93355");//（秘钥向量，混淆向量）
 *    	echo $ret = $des->encrypt("4");//加密字符串
 *		echo $det = $des->decrypt($ret);//加密字符串
 **/
class CryptDes {
	//密钥
	private $key;
	//加密向量
	private $iv;

	public function __construct($key, $iv) {
        $this->key = $key;
		$this->iv = $iv;
    }

	
	/**
	 * 加密实现
	 * @param  [type] $input [description]
	 * @return [type]        [description]
	 */
	public function encrypt($input){
		$size = mcrypt_get_block_size(MCRYPT_DES,MCRYPT_MODE_CBC); //3DES加密将MCRYPT_DES改为MCRYPT_3DES
		$input = $this->pkcs5_pad($input, $size); //如果采用PaddingPKCS7，请更换成PaddingPKCS7方法。
		$key = str_pad($this->key,8,'0'); //3DES加密将8改为24
		$td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_CBC, '');
		if( $this->iv == '' ){
			$iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		}else{
			$iv = $this->iv;
		}
		@mcrypt_generic_init($td, $key, $iv);
		$data = mcrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$data = base64_encode($data);//如需转换二进制可改成  bin2hex 转换
		return $data;
	}
	/**
	 * 解密实现
	 * @param  [type] $encrypted [description]
	 * @return [type]            [description]
	 */
	public function decrypt($encrypted){
		$encrypted = base64_decode($encrypted); //如需转换二进制可改成  bin2hex 转换
		$key = str_pad($this->key,8,'0'); //3DES加密将8改为24
		$td = mcrypt_module_open(MCRYPT_DES,'',MCRYPT_MODE_CBC,'');//3DES加密将MCRYPT_DES改为MCRYPT_3DES
		if( $this->iv == '' ){
			$iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		}else{
			$iv = $this->iv;
		}
		$ks = mcrypt_enc_get_key_size($td);
		@mcrypt_generic_init($td, $key, $iv);
		$decrypted = mdecrypt_generic($td, $encrypted);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$y=$this->pkcs5_unpad($decrypted);
		return $y;
	}
	/**
	 * 加密步长运算
	 */
	private function pkcs5_pad($text,$blocksize) {
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}
	
	/**
	 * 解密步长运算
	 */      
	private function pkcs5_unpad($text){
		$pad = ord($text{strlen($text)-1});
		if ($pad > strlen($text)) {
			return false;
		}
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad){
			return false;
		}
		return substr($text, 0, -1 * $pad);
	}
	
	/**
	 * 3DES加密步长运算
	 */      
	private function PaddingPKCS7($data) {
		$block_size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);//3DES加密将MCRYPT_DES改为MCRYPT_3DES
		$padding_char = $block_size - (strlen($data) % $block_size);
		$data .= str_repeat(chr($padding_char),$padding_char);
		return $data;
	}
}
